<?php 

    if(true){
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('display_errors', '1');
    }

    
    $projectPath = $_GET["jpath"];
    $result = array();

    // $projectPath = "http://rsn-rml-test.s3.amazonaws.com/vadym/weston_mar27_a.json";

    // if (!file_exists($projectPath)) {   
    //     $result["payload"] = "not found";                       
    // }
    // else{
    //     $result["payload"] = "good";
    // }

    $handle = @fopen($projectPath,'r');

    if(!$handle) {
        $result["payload"] = "not found";   
    } else {
        $result["payload"] = "good";
    }

    echo json_encode($result);

 ?>