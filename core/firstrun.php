<?php
/*
|--------------------------------------------------------------------------
| Check the existance of the db file
|--------------------------------------------------------------------------
|
| If the database is not exists create one and create required tables
| Create the database file and insert the default sittengs
|
|--------------------------------------------------------------------------
*/

$dbFile = ABSPATH.'/database/'.Config::Get('db/dbname').'.db';

if ( ! file_exists($dbFile) ) {
	Session::Put("setup",true);
	Redirect::To("install");
	die();
}
/*
|--------------------------------------------------------------------------
| Check If there an admin 
|--------------------------------------------------------------------------
|
*/
if ( file_exists($dbFile) ) {
	try{
		if(DB::GetInstance()->queryGet("SELECT id FROM users where roles = 1 ")->count() == 0) {
			Session::Put("setup",true);
			Redirect::To("install");
			die();
		}
	}catch(Exception $ex){
		session_destroy();
		die("Some database table(s) is missing Please delete the database file an reinstall the application.");
	}
}

// Everything is okay
Session::Delete("setup");
Options::CheckSiteUrl();

?>