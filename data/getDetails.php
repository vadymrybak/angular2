<?php 

    if(true){
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('display_errors', '1');
    }

    require_once('lib/DecipherLibraryPHP.php');

    $project_number = $_GET["pnum"];
    $result = array();
    $result["payload"] = array();

    $da = new DecipherAPI();
    $project_properties = $da->FetchDecipherSurveyProperties("ca", $project_number);
    if ($project_properties["status"] == "success"){   
        $existing_xml = $da->FetchDecipherXML("ca", $project_number);
        //print_r($existing_xml);
        $temp = array();
        $temp = $project_properties["data"];
        $temp["xml"] = $existing_xml["data"];
        $result["payload"] = $temp;
    }
    else{
        $result["payload"] = "not found";
    }



    echo json_encode($result);
 ?>

