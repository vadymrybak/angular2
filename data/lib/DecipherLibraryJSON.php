<?php
// This library returns JSON objects of requested data in AJAX calls




class DecipherApiJSON extends DecipherAPI
{

	function GetDecipherQuestionIDs($server, $projectNumber)
	{
		return json_encode(parent::GetDecipherQuestionIDs($server, $projectNumber));
	}

	function GetDecipherDataFromQuestions($server, $projectNumber, $fields)
	{
		return json_encode(parent::GetDecipherDataFromQuestions($server, $projectNumber, $fields));
	}

	function GetDecipherDomainsFromQuestion($server, $projectNumber, $qid)
	{
		return json_encode(parent::GetDecipherDomainsFromQuestion($server, $projectNumber, $qid));
	}

	function GetDecipherScalesFromQuestion($server, $projectNumber, $qid)
	{
		return json_encode(parent::GetDecipherScalesFromQuestion($server, $projectNumber, $qid));
	}

	function GetDecipherData($server, $projectNumber, $dataLayoutId = "", $start = "", $end = "", $cond = "")
	{
		return json_encode(parent::GetDecipherData($server, $projectNumber, $dataLayoutId = "", $start = "", $end = "", $cond = ""));
	}

	function SaveDataToFile($server, $projectNumber, $dataLayoutId = "")
	{
		return json_encode(parent::SaveDataToFile($server, $projectNumber, $dataLayoutId = ""));
	}

	function FetchDecipherSurveyProperties ($server, $projectNumber)
	{
		return json_encode(parent::fetchDecipherSurveyProperties ($server, $projectNumber));
	}

	function GetQuota($server, $projectNumber)
	{
		return json_encode(parent::getQuota($server, $projectNumber));
	}

	function GetSurveyWarnings($server, $projectNumber, $types = "", $start = "", $end = "")
	{
		return json_encode(parent::getSurveyWarnings($server, $projectNumber, $types = "", $start = "", $end = ""));
	}

}

?>