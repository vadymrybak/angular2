<?php
// This library returns PHP objects of requested data

// Debugging
$debug_mode = true;	
if($debug_mode)
{
	error_reporting(E_ALL ^ E_NOTICE);
	error_reporting( E_ERROR | E_USER_ERROR | E_ALL);
	ini_set('display_errors', '1');
}	


class DecipherAPI
{
	private $GLOBALS = array('baseLink' => '', 'instance' => '');

	function __construct() {	
		include('gen_config.php');
		include('api_config.php');	
		$this->GLOBALS['baseLink'] = $baseLink;
		$this->GLOBALS['instance'] = $instance;	
	}
	

	// Dump function
	function Dump($data = array())
	{
		echo "<div class='container'><pre>";
		var_dump($data); 
		echo "</pre></div>";
	}

	/*
		Function gets all QIDs from provided Decipher p#

		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)

		Returns:
			String array of question IDs
	*/
	function GetDecipherQuestionIDs($server, $projectNumber)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = array();

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/datamap?format=json";
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
		}
		
		if ($returnResult["status"] != "error"){
			$returnResult["status"] = "success";
			foreach ($outputObject->questions as $value) {
				array_push($returnResult["data"], $value->qlabel);
			}
		}

		return $returnResult;
	}

	/*
		Function gets data for specified QIDs
		
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$fields - (string) comma separated list of QIDs

		Returns:
			Array of question IDs
	*/
	function GetDecipherDataFromQuestions($server, $projectNumber, $fields)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = array();

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/data?";
		$params = "format=json&fields=".$fields;
		$fullURL = $host.$path.$params;
		$token = $this->GLOBALS['instance'][$server]["key"];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host . $path . $params);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		// If responce contains any errors (Most common one - project not found)
		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
		}
		// If no errors, need to check if data exists
		else{
			$noQuestionFoundFlag = false;
			foreach ($outputArray as $value) {
				if (count($value) > 0){
					$noQuestionFoundFlag = true;
					break;
				}
			}
			if ($noQuestionFoundFlag == true){
				$returnResult["status"] = "success";
				$returnResult["data"] = $outputObject;
			}
			else{
				$returnResult["status"] = "error";
				$returnResult["message"] = "No questions were found.";
			}	
		}

		return $returnResult;
	}

	/*
		Function gets Domains from question in Decipher
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$qid - required QID

		Returns:
			Array of objects (domains)
	*/
	function GetDecipherDomainsFromQuestion($server, $projectNumber, $qid)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = array();

		$resultArray = array();
		$finalArray = array();
		$qType = "";
		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/datamap?format=json";
		$token = $this->GLOBALS['instance'][$server]["key"];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		// If responce contains any errors (Most common one - project not found)
		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
			return $returnResult;
		}

		$questions = $outputObject->questions;

		foreach ($questions as $value) {
			if ($value->qlabel == $qid){
				if ($value->type == "single"){
					$qType = "single";
					$resultArray = $value->values;
				}
				else{
					$resultArray = $value->variables;
				}
			}
		}

		// If project is found but no question is found
		if (count($resultArray) == 0){
			$returnResult["status"] = "error";
			$returnResult["message"] = "No questions were found";
			return $returnResult;
		}

		// If project is found and question(s) found
		foreach ($resultArray as$value) {
			$temp = array();
			if ($qType == "single"){
				$temp["precode"] = $value->value;
				$temp["label"] = $value->title;
			}
			else{
				$temp["precode"] = $value->row;
				$temp["label"] = $value->rowTitle;
			}
			array_push($finalArray, $temp);
		}

		$returnResult["status"] = "success";
		$returnResult["message"] = "";
		$returnResult["data"] = $finalArray;
		return $returnResult;
	}

	/*
		Function gets Scales from question in Decipher

		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$qid - (string) required QID

		Returns:
			Array of scale objects
	*/
	function GetDecipherScalesFromQuestion($server, $projectNumber, $qid)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = array();
		$resultArray = array();

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/datamap?format=json";
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		// If responce contains any errors (Most common one - project not found)
		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
			return $returnResult;
		}

		$response = $outputObject->questions;

		foreach ($response as $value) {
			if ($value->qlabel == $qid)
				$resultArray = $value->values;
		}

		// If project is found but no question is found
		if (count($resultArray) == 0){
			$returnResult["status"] = "error";
			$returnResult["message"] = "No questions were found";
			return $returnResult;
		}

		$returnResult["status"] = "success";
		$returnResult["message"] = "";
		$returnResult["data"] = $resultArray;

		return $returnResult;
	}

	/*
		Function gets data for Decipher projects
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$dataLayoutId - (string)(optional) data layout ID in Data Downloads. All questions are returned if not provided.
			$start - (string)(optional) start time using ISO 8601 format. E.g. “2011-12-14T17:50Z” for GMT time corresponding to 14th December, 2011, 17:50 GMT. This is optional: if 
				you do not specify this parameter, you will get data starting at the earliest possible record.
			$end - (string)(optional) maximum completion date of respondents to include. Like start, this is optional
			$cond - (string)(optional) condition required to retrieve the respondent. This is a Python condition as if you would enter in survey logic or crosstabs. For example, 
					“qualified and q3.r2” retrieves only respondents that were qualified and answered q3 as r2.
		Returns:
			Data array.
	*/
	function GetDecipherData($server, $projectNumber, $dataLayoutId = "", $start = "", $end = "", $cond = "")
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = array();

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/data?";

		$dataLayoutIdValue = ($dataLayoutId != "" ? "&layout=".$dataLayoutId : "");
		$startValue = ($start != "" ? "&start=".$start : "");
		$endValue = ($end != "" ? "&end=".$end : "");
		$condValue = ($cond != "" ? "&cond=".$cond : "");

		$params = "format=json".$dataLayoutIdValue.$startValue.$endValue.$condValue;
		$fullURL = $host.$path.$params;
		$token = $this->GLOBALS['instance'][$server]["key"];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host . $path . $params);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		// If responce contains any errors (Most common one - project not found)
		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
			return $returnResult;
		}

		$returnResult["status"] = "success";
		$returnResult["message"] = "";
		$returnResult["data"] = $outputObject;

		return $returnResult;
	}

	

	// Function replaces or create new data file on FTP
	function WriteDataToFile ($decipherInstance, $projectNumber, $dataLayout = ""){
		$filename = 'uploads/'.$decipherInstance.'/'.$projectNumber.'/'.'datafile.dat';
		$dataLayoutIdValue = ($dataLayout != "" ? "&layout=".$dataLayout : "");
		$host = "https://survey-".$decipherInstance.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".str_replace("_","/",$projectNumber)."/data?format=tab".$dataLayoutIdValue;
		$token = $this->GLOBALS['instance'][$decipherInstance]["key"];
		$ch = curl_init();
		$fp = fopen($filename, 'w');

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$resultArray = array();
		if (! $result = curl_exec($ch))
		{
			$resultArray["status"] = "error";
			$resultArray["message"] = curl_error($ch);
			$resultArray["fileURL"] = "";
		}
		else
		{
			$resultArray["status"] = "success";
			$resultArray["fileURL"] = $this->GLOBALS['baseLink'].'uploads/'.$decipherInstance.'/'.$projectNumber.'/'.'datafile.dat';
		}

		return $resultArray;
	}

	/*
		Function saves retrieved data into file on FTP
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$dataLayoutId - (string)(optional) data layout ID in Data Downloads. All questions are returned if not provided.
		Returns:
			String with the path to the file
	*/
	function SaveDataToFile($server, $projectNumber, $dataLayoutId = "")
	{
		// PROJECT FOLDER DOES NOT EXIST
		if (!file_exists('uploads/'.$server.'/'.$projectNumber.'/'))
		{
			mkdir('uploads/'.$server.'/'.$projectNumber, 0777, true);
			chmod('uploads/'.$server.'/'.$projectNumber, 0777);
			return $this->WriteDataToFile($server, $projectNumber, $dataLayoutId);
		}
		// PROJECT FORLDER ALREADY EXISTS
		else
		{
			return $this->WriteDataToFile($server, $projectNumber, $dataLayoutId);
		}
	}

	/*
		Function returns basic info about project
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
		Returns:
			Array with project info

	*/
	function FetchDecipherSurveyProperties ($server, $projectNumber)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = array();

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/rh/companies/all/surveys?query=survey:" . $projectNumber;
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$parsed = json_decode($result);

		if(count($parsed) > 0)
		{
			$proj_info["projectNumber"] = $projectNumber;
			$proj_info["projectName"] = $parsed[0]->title;
			$parsed[0]->createdBy->name ? $proj_info["creator"] = $parsed[0]->createdBy->name : $proj_info["creator"] = $parsed[0]->createdBy->email;
			$proj_info["lastEditDate"] = gmdate("Y-m-d H:i:s", strtotime($parsed[0]->lastEdit));

			$returnResult["status"] = "success";
			$returnResult["message"] = "";
			$returnResult["data"] = $proj_info;
		}
		else
		{
			$returnResult["status"] = "error";
			$returnResult["message"] = "Project not found";
		}

		return $returnResult;
	}

	/*
		Function returns survey XML
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
		Returns:
			Array with project info
	*/
	function FetchDecipherXML ($server, $projectNumber)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = "";

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".str_replace("_","/",$projectNumber)."/files/survey.xml";
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		// If survey is not found.
		if (is_array($outputArray)){
			if (array_key_exists('$error', $outputArray)){
				$returnResult["status"] = "error";
				$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
				return $returnResult;
			}
		}
	
		// Survey is found.
		$returnResult["status"] = "sucess";
		$returnResult["message"] = "";
		$returnResult["data"] = $rawOutput;

		return $returnResult;
	}

	/*
		Function returns quota for requested project

		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)

		Returns:
			PHP object with quota details
	*/
	function GetQuota($server, $projectNumber)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = "";

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/quota";
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
			return $returnResult;
		}


		$returnResult["status"] = "success";
		$returnResult["message"] = "";
		$returnResult["data"] = $rawOutput;

		return $returnResult;
	}

	/*
		Returns warnings for the requested project

		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$types - array (optional) Default: "blocks,missing_resources,exceptions,quota_misses" restrict events returned to just these types
			$start - date (optional) restrict events returned to just those occurring on/after this date (in ISO 8601 format–e.g., ‘YYYY-MM-DD’)
			$end - date (optional) restrict warnings counts to just those occurring on/before this date
			
		Returns:
			PHP object with warnings
	*/
	function GetSurveyWarnings($server, $projectNumber, $types = "", $start = "", $end = "")
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = "";
		
		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".$projectNumber."/warnings";
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		if (array_key_exists('$error', $outputArray)){
			$returnResult["status"] = "error";
			$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
			return $returnResult;
		}


		$returnResult["status"] = "success";
		$returnResult["message"] = "";
		$returnResult["data"] = $rawOutput;

		return $returnResult;
	}

	/*
		Function updates survey XML
		Params:
			$server - (string) Decipher server (e.g. d/uk/au/ca)
			$projectNumber - (string) project number (e.g. 53b/1609345)
			$string - whole new XML as a regular string
		Returns:
			Array with project info
	*/
	function UpdateDecipherXML ($server, $projectNumber, $string)
	{
		$returnResult = array();
		$returnResult["status"] = "";
		$returnResult["message"] = "";
		$returnResult["data"] = "";

		$data = array("contents" => $string);
		$data_string = json_encode($data); 

		$host = "https://survey-".$server.".researchnow.com";
		$path = "/api/v1/surveys/selfserve/".str_replace("_","/",$projectNumber)."/files/survey.xml";
		$token = $this->GLOBALS['instance'][$server]["key"];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $host . $path);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-apikey: ' . $token,
													'Content-Type: application/json',                                                                                
												    'Content-Length: ' . strlen($data_string)
													));

		// Need to make backup of the survey before replacing the XML
		if (!file_exists("backups")){
			mkdir("backups", 0777, true);
			chmod("backups", 0777);
		}
		$date = new DateTime();
		$timestamp = $date->getTimestamp();
		$myfile = fopen("backups/".str_replace("/","_",$projectNumber)."_".$server."_".$timestamp.".xml", "w");
		$old_xml = $this->FetchDecipherXML($server, $projectNumber);
		fwrite($myfile, $old_xml["data"]);
		fclose($myfile);
		// End of backing up

		$rawOutput = curl_exec($ch);
		$outputArray = json_decode($rawOutput, true);
		$outputObject = json_decode($rawOutput);

		// If survey is not found.
		if (is_array($outputArray)){
			if (array_key_exists('$error', $outputArray)){
				$returnResult["status"] = "error";
				$returnResult["message"] = $outputArray['$code']." - ".$outputArray['$error'];
				return $returnResult;
			}
		}
	
		// Survey is found.
		$returnResult["status"] = "sucess";
		$returnResult["message"] = "";
		$returnResult["data"] = $rawOutput;

		return $returnResult;
	}


}


?>