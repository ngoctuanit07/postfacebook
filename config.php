<?php
/*
|--------------------------------------------------------------------------
| Database name
|--------------------------------------------------------------------------
|
*/
define('ABSPATH', 		dirname( __FILE__ ) . '/');
define('DIR_DB',   		dirname( __FILE__ ) . '/database/');
define('DIR_CORE',    	dirname( __FILE__ ) . '/core/');
define('DIR_INSTALL',	dirname( __FILE__ ) . '/install/');
define('DIR_LANG',   	dirname( __FILE__ ) . '/core/language/');
/*
|------------------------------------
| Default language
|------------------------------------
|
*/
define("DEFAULT_LANG","english");
/*
|--------------------------------------------------------------------------
| Turning ON/OFF debug mode (for maintenance mode)
|--------------------------------------------------------------------------
|
*/
define("DEBUG_MODE",false);
/*
|--------------------------------------------------------------------------
| Database name (It's highly recommended to change the database name)
|--------------------------------------------------------------------------
|
*/
$dbname = "kingposter"; 
/*
|--------------------------------------------------------------------------
| System settings
|--------------------------------------------------------------------------
|
*/
$GLOBALS['config'] = array(
	'db' => array(
		'dbname' => $dbname,
	),
	'remember' => array(
		'cookie_name' => 'kp_token',
		'cookie_expiry' => 604800
	),
	'session' => array(
		'session_name' => 'kp_user',
		'token_name' => 'kp_token'
	)
);
?>