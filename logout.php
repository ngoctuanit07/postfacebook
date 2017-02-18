<?php
require_once 'core/init.php';

$user = new User();
$user->logout();

if(isset($_SERVER['HTTP_REFERER'])){
	Redirect::to($_SERVER['HTTP_REFERER']);
}else{
	Redirect::to("index.php");
}
	
?>