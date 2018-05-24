<?php 

// GETTING COOKIE
$db = new dbClass;
$userInfo = $db->getAppCookie("empoweruser");

// IF NO COOKIE - ASK TO LOG IN. OTHERWISE CONSTRUCT NEW loggedInUser FROM COOKIE
if(!$userInfo){
	echo "<h2 style='text-align:center'>Please login first...</h2>";
	$sucess = false;
	die();
}
else
{
	$loggedInUser = new loggedInUser();
	$loggedInUser->email = $userInfo["email"];
	$loggedInUser->displayname = $userInfo["display_name"];
	$loggedInUser->permissions = $userInfo["permission"];
	$loggedInUser->username = $userInfo["user_name"];
}

 ?>