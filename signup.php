<?php
/*
|--------------------------------------------------------------------------
| Common init file
|--------------------------------------------------------------------------
|
*/
require_once "core/commoninit.php";
require_once "core/language/language.php";

$user = new user();
$template = new Template();

if($user->isLoggedIn()){
	Redirect::to(Options::Get("siteurl").'index.php');
	exit();
}

if(Options::Get('users_can_register') == "0"){
	Redirect::to(Options::Get("siteurl").'index.php');
	exit();
}

$template->signUp();
?>