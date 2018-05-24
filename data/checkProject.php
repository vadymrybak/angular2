<?php 

    if(true){
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('display_errors', '1');
    }

    require_once('lib/DecipherLibraryPHP.php');

    $projectPath = $_GET["pnum"];
    $result = array();
    $result["payload"] = "";

    $da = new DecipherAPI();
    $project_properties = $da->FetchDecipherSurveyProperties("ca", $projectPath);

    if ($project_properties["status"] == "success"){
        $result["payload"] = "good"; 
    }
    else {
        $result["payload"] = "not found";
    }

    echo json_encode($result);
 ?>

