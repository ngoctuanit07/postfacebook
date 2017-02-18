<?php
require "../core/init.php";


// Add new facebook account using access token
if(Input::Get("fb_accesstoken")){
	
	$fb = new Facebook();
	$user = new User();
	$fbaccount = new fbaccount();

	// Test access token
	if(!$fb->IsATValid(Input::Get("fb_accesstoken"))){
		echo lang('INVALID_ACCESS_TOKEN');
		exit();
	}

	// get facebook User info 
	$userData = $fb->GetUserFromAccessToken(Input::Get("fb_accesstoken"));
	if($userData == null){
		echo lang("UNABLE_TO_GET_FB_ACCOUNT_DETAILS");
		exit();
	}

	// Get user groups 
	$fbgroups = $fb->LoadFbGroups(Input::Get("fb_accesstoken"));
	if(!$fbgroups){
		echo lang("UNABLE_GET_FB_GROUPS");
		exit();
	}

	// Save access token
	if($fbAppDetails = $fb->AppDetailsFromAt(Input::Get("fb_accesstoken"))){
		if($fb->GetAccessToken($fbAppDetails->id,$userData->id)){
			$fb->UpdateAccessToken($user->data()->id,$fbAppDetails->id,$userData->id,Input::Get("fb_accesstoken"));
		}else{
			$fb->SaveAccessToken($user->data()->id,$fbAppDetails->id,$userData->id,Input::Get("fb_accesstoken"));
		}
	}else{
		echo lang("UNABLE_TO_GET_FB_APP_DETAILS");
		exit();
	}

	// Save new facebook account
	$fbaccount->setUserId($user->data()->id);
	$fbaccount->setFbId($userData->id);
	$fbaccount->setLastname($userData->first_name);
	$fbaccount->setFirstname($userData->last_name);
	$fbaccount->setGroups($fbgroups);

	if(!$fbaccount->UserFbAccountDefaultApp()){
		$fbaccount->setDefaultApp($fbAppDetails->id);
	}


	// Check if this facebook account is already exists;
	if($fbaccount->exists($userData->id)){
		$fbaccount->Update();
	}else{
		$fbaccount->Save();
	}
	

	// Set the current account as the default fb account if there is no default account
	if(!$fbaccount->UserDefaultFbAccount()){
		$user->UpdateOptions(array('default_Fb_Account' => $userData->id));
	}

	echo "true";
}

?>