<?php

    // Need to prepare XML first
    // Check status

    if(true){
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('display_errors', '1');
    }

    //set_error_handler('exceptions_error_handler');

    function exceptions_error_handler($severity, $message, $filename, $lineno) {
        if (error_reporting() == 0) {
            return;
        }
        if (error_reporting() & $severity) {
            throw new ErrorException($message, 0, $severity, $filename, $lineno);
        }
    }

    require_once('lib/DecipherLibraryPHP.php');
    require("lib/PreparedXML.php");

    $project_number = $_GET["pnum"];
    $jSONPath = $_GET["jpath"];
    $result = array();
    $da = new DecipherAPI();
    $temp = array();
    $file_data = file_get_contents($jSONPath);

    // Getting XML that will be replaced
    $xml = $da->FetchDecipherXML("ca", $project_number);

    // Check for some keywords in XML to ensure the project is the template and not other random p#
    if (strpos($da, 'WORD TO FIND') === false) {
        $temp["status"] = "error";
        $temp["message"] = "Template keywords were not found.";
        $result["payload"] = $temp;
    }
    else {
        // Making modification in XML
        try{
            $template_xml_prepared = getPreparedXML($xml["data"], json_decode($file_data));
            
            // If no errors => write output to file
            file_put_contents("output.xml", $template_xml_prepared);

            // Try to upload prepeared XML to Decipher
            $update_result = $da->UpdateDecipherXML("ca", $project_number, $template_xml_prepared);
            if ($update_result["status"] == "error"){
                $temp["status"] = "decipher_error";
                $temp["message"] = $update_result["message"];
                $result["payload"] = $temp;
            }
            else {
                $temp["status"] = "success";
                $temp["message"] = "Your XML was updated successfully.";
                $result["payload"] = $temp;
            }
        }
        catch (Exception $e){
            $temp["status"] = "error";
            $temp["message"] = "Error prepearing XML. ".$e->getMessage();
            $result["payload"] = $temp;
        }
    }

    
	
    echo json_encode($result);


?>