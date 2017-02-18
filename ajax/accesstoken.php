<?php 
require "../core/init.php";
	
if(Input::Get("isAccessTokenValid")){
	$fb = new Facebook();

	$accessToken = isset($_POST["accessToken"]) ? Input::Get("accessToken") : $fb->AccessToken();

	if($fb->IsATValid($accessToken)){
		echo "true";
	}else{
		echo "false";
	}
}
?>