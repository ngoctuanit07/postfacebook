<?php  
include('core/init.php');

$template = new Template();
$template->header("Upgrade"); 

echo "<div class='alert alert-danger'>Note: This upgrade is for version >= 1.5.2 ONLY, if you have old version (<1.5.2) you need to re-install the script.</div>";

try{
	DB::GetInstance()->Query("ALTER TABLE `user_options` ADD COLUMN uniqueLink text");
}catch(Exception $e){}	

try{
	DB::GetInstance()->Query("ALTER TABLE `fbapps` ADD COLUMN app_auth_link text");			
}catch(Exception $e){}

try{
	DB::GetInstance()->Query("CREATE TABLE groups_category (
		id integer PRIMARY KEY AUTOINCREMENT,
		user_id INTEGER,
		fb_id varchar(32),
		groups text,
		category_name varchar(64),
		created_at datetime,
		updated_at datetime
	)");			
}catch(Exception $e){}

echo "<div class='alert alert-success'>Your script has been updated to the version 1.6.3</div>";

$template->footer(); 
?>
