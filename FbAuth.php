<?php
require('core/init.php');

$user = new User();
$tempate = new Template();

if(!$user->isLoggedIn()){
	Redirect::to('signin.php');
	exit();
}
?>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
	<title><?php echo Options::get("sitename"); ?> | <?php echo lang("FB_AUTHENTICATION"); ?> </title>
	<link href="theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<script src="theme/default/js/jquery.js"></script>
	<script src="theme/default/js/javascript.js"></script>
	<script src="theme/default/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	<?php
	if(Input::Get("app_id")){
		
		$fb = new Facebook();
		
		$app_id = Input::Get("app_id");
		
	// check existance of appid gevin in the database
	// get app secret
		$app_secret = DB::GetInstance()->QueryGet("SELECT app_secret FROM fbapps WHERE appid = ? ",array($app_id));
		
		if($app_secret->count() == 0){
			echo "
			<div class='alerts alert alert-danger'>
			<p class='alerttext'>facebook App not found!</p>
			</div>";
		}else{
			try{
				$notic = $fb->FbAuth($app_id,$app_secret->first()->app_secret,options::Get("siteurl")."FbAuth.php?app_id=".$app_id,Input::Get("oldApi"));
				echo "<div class='alerts alert alert-success'>
				<p class='alerttext'>Successfully authorized <a href='#' onclick='window.opener.location.href = window.opener.location.href;window.close();'>Close this windiw</a>.</p>
				</div>";
			}catch(Exception $ex){
				echo "
				<div class='alerts alert alert-danger'>
				<p class='alerttext'>Error : ".$ex->GetMessage()."</a></p>
				</div>";
			}
		}
	}else{
		echo "
		<div class='alerts alert alert-danger'>
		<p class='alerttext'>Required parameters app_id not supplied.</p>
		</div>";
	}

	$tempate->footer(); 

	?>
</body>
</html>