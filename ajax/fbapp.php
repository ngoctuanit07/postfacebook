<?php
require "../core/init.php";

if($user->hasPermission("admin")){
	if(Input::Get("app_id") != ""){
		$fb = new Facebook();
		if($fb->AppDetails(Input::Get("app_id"))){
			$apps = DB::GetInstance()->QueryGet("SELECT appid FROM fbapps WHERE appid = ? ",array(Input::Get("app_id")))->count() == 0 ? false : true;
			
			try{
				if($apps){
					echo $fb->AppDetails(Input::Get("app_id"))->name . " ".lang('ALREADY_EXISTS');
				}else{
					DB::GetInstance()->Insert("fbapps",array(
						'appid' => Input::Get("app_id"),
						'app_secret' => Input::Get("app_secret"),
						'app_name' => $fb->AppDetails(Input::Get("app_id"))->name,
						'app_auth_Link' => Input::Get("fbapp_auth_Link")
					));
					echo "true";
				}
			
			}catch(Exception $ex){
				echo lang('ERROR')." : ".$ex->getMessage();
			}
		}else{
			echo lang('INVALID_FB_APP');
		}
	}else{
		echo lang('MISSING_PARAMS');
	}
}
?>