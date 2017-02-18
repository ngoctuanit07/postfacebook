<?php
/*
|--------------------------------------------------------------------------
| Common init file
|--------------------------------------------------------------------------
|
*/
require_once "commoninit.php";
/*
|--------------------------------------------------------------------------
| Check if the user has a session and the session is valid
|--------------------------------------------------------------------------
|
*/
if(Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))){
	$hash = Cookie::get(Config::get('remember/cookie_name'));
	$hashCheck = DB::getInstance()->get('users_session', array('hash', '=', $hash));
	
	if($hashCheck->count()){
		$user = new User($hashCheck->first()->user_id);
		$user->login();
	}
}
/*
|--------------------------------------------------------------------------
| User must be signed in
|--------------------------------------------------------------------------
|
*/
$user = new user();
if(!$user->isLoggedIn()){
	Redirect::to(Options::Get("siteurl").'signin.php');
	exit();
}
/*
|-------------------------------------------------
| Set Timezone for the current user
|-------------------------------------------------
|
*/
if(isset($user->Options()->timezone)){
	if($user->Options()->timezone){
		date_default_timezone_set($user->Options()->timezone);
	}
}
/*
|------------------------------------
| Set language
|------------------------------------
|
*/
$user = new User();
$userLang = isset($user->Options()->lang) ? $user->Options()->lang : DEFAULT_LANG;
define('USER_LANG', $userLang);
require_once DIR_CORE . "/language/language.php";
/*
|--------------------------------
| Check for updates
|--------------------------------
*/
Options::CheckForUpdate();

?>