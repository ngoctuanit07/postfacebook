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

$template->signIn();
?>