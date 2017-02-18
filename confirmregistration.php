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

if($user->isLoggedIn()){
	$user->logout();
	Redirect::to(CurrentPath());
	die();
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<title><?php echo Options::get("sitename"); ?> | <?php echo lang('EMAIL_CONFIRMATION'); ?></title>
<link rel="stylesheet" href="theme/signin.css" type="text/css" />
<link href="theme/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<script src="theme/js/jquery.js"></script>
<script src="theme/js/jsui.js"></script>
<script src="theme/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
				<?php 
				// Validate the confirmation code & email
				if($confirm = $user->UserCode(Input::Get("email","GET"),Input::Get("code","GET"))){
					try{
							// Activate the account 
							$user->Update(array('active' => 1,'act_code'=>''),$confirm->id);

							// Set success message
							Session::flash("signin","success",lang('ACCOUNT_ACTIVATED_SUCCESS'),true);
							
							// Redirect the user to the login page
							Redirect::To("signin.php");
						
						}catch(Exception $e){
							die("Oops somthing went wrong Please try again!");
						}
				} else {
					echo "<div class='alert alert-danger' role='alert'>";
					echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
					echo lang('INVALID_CONFIRMATION_CODE')."</br>";
					echo "</div>";
				}
			?>
			<p class="loginFooter"><?php echo lang('COPYRIGHT'); ?></p>
    </div> <!-- /container -->
</body>
</html>