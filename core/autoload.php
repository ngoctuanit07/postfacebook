<?php
/*
|--------------------------------------------------------------------------
| Check php version
|--------------------------------------------------------------------------
|
*/
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die("<h3 style='line-height: 50px;text-align:center;margin-top:50px'>The King poster script requires <u>PHP version 5.4 or higher.</u><br/>Your server is running php version : " . PHP_VERSION . "</h3>");
}
/*
|--------------------------------------------------------------------------
| Constant path for the core folder
|--------------------------------------------------------------------------
|
*/
if ( ! defined('COREPATH') ) {
	define('COREPATH', dirname(__FILE__) . '/');
}
/*
|--------------------------------------------------------------------------
| Load the config file 
|--------------------------------------------------------------------------
|
*/
require_once COREPATH."../config.php";
require_once DIR_CORE."/general.php";
/*
|--------------------------------------------------------------------------
| Enable/Disable error reporting and display errors
|--------------------------------------------------------------------------
|
*/
if(!DEBUG_MODE){
	error_reporting(E_ALL);
	ini_set( 'display_errors', '0' );
}
/*
|--------------------------------------------------------------------------
| Autoload classes
|--------------------------------------------------------------------------
| @param anonymous function
|
*/
function autoload($class){
	// classes dir 
	$dir = ABSPATH.'/core/classes/';
	// note : All classes file have a lowercase name. the class name must be lowercase
	$classFile = $dir.strtolower($class).'.php';
	
	// Check file existence before including the if 
	if (file_exists($classFile)) {
		require_once $classFile;
	}
}
spl_autoload_register('autoload');
/*
|--------------------------------------------------------------------------
| Load facebook SDK required files
|--------------------------------------------------------------------------
|
*/
require_once DIR_CORE.'../facebook/autoload.php';
?>