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
</body>
</html>
<!DOCTYPE html>
<html>
<head>

<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />

<title><?php echo Options::get("sitename"); ?> | <?php echo lang('ACCOUNT_RECOVERY'); ?></title>

<link href="theme/default/css/signin.css" rel="stylesheet" type="text/css" />
<link href="theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<script src="theme/default/js/jquery.js"></script>
<script src="theme/default/js/jsui.js"></script>
<script src="theme/default/bootstrap/js/bootstrap.min.js"></script>

</head>
<body>
    <div class="container">
				<?php 
				// Validate the recover code & email
				if(Input::Get("email","GET") && Input::Get("code","GET")){ 
					if($recover = $user->UserCode(Input::Get("email","GET"),Input::Get("code","GET"))){
				?>
					<form class="form-signin" method="POST">
					<h2 class="form-signin-heading"><img class="logo" src="theme/default/images/logo_large.png" alt="signin"></h2>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo lang('RESET_PASSWORD'); ?></h3>
						</div>
						<div class="panel-body">
						<div class="recoverAlerts"></div>
							<?php
								if(Input::exists()){
									if(Token::check(Input::get('token'))){
										$validate = new Validate();
										$validation = $validate->check($_POST, array(
											'password' => array(
												'disp_text' => lang('RESET_PASSWORD'),
												'required' => true,
												'min' => 6,
												'max' => 16,
												),
											'repassword' => array(
												'disp_text' => $lang['RE_ENTER_NEW_PASSWORD'],
												'required' => true,
												'matches' => 'password'
												)
											));

										if($validation->passed()){
											$salt = Hash::salt(32);
											try{
													$user->Update(array(
														'password' => Hash::make(Input::get('password'), $salt),
														'salt' => $salt,
														'act_code' => '',
													),$recover->id);

													Session::flash("signin","success",lang('PASSWORD_RESET_SUCCESS'),true);
													
													Redirect::To("signin.php");
												
											}catch(Exception $e){
												die("Oops somthing went wrong Please try again!");
											}
										}else{
											foreach($validation->errors() as $error){
												echo "<div class='alert alert-danger' role='alert'>";
												echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
												echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
												echo $error."</br>";
												echo "</div>";
											}
										}
									}
								}
						?>
								<input type="hidden" name="token" id="token" value="<?php echo Token::generate(); ?>" />
								<p class="well"><?php echo lang('USERNAME');?> : <?php echo $recover->username; ?></p>
								<label for="password"  class="sr-only"><?php echo lang('NEW_PASSWORD'); ?></label>
								<input type="password" id="password" name="password" class="form-control" placeholder="<?php echo lang('NEW_PASSWORD'); ?>" required />
								
								<label for="repassword"  class="sr-only"><?php echo lang('RE_ENTER_NEW_PASSWORD'); ?></label>
								<input type="password" name="repassword" id="repassword" placeholder="<?php echo lang('RE_ENTER_NEW_PASSWORD'); ?>" class="form-control" required />

								<input name="resetPassword" class="btn btn-lg btn-primary btn-block" type="submit" value="<?php echo lang('RESET_PASSWORD'); ?>" />
							</div>
						</div>
      </form>
			<?php 
					} else {
						echo "<div class='alert alert-danger' role='alert'>";
						echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
						echo "&nbsp;Invalid recover code.</br>";
						echo "</div>";
					}
			} else { // Change password end 
				?>
				<form class="form-signin" method="POST">
					<h2 class="form-signin-heading"><img class="logo" src="theme/default/images/logo_large.png" alt="signin"></h2>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h3 class="panel-title"><?php echo lang('FIND_ACCOUNT'); ?></h3>
						</div>
						<div class="panel-body">
						<?php
							if(Input::exists()){
									if(Token::check(Input::get('token'))){
										$validate = new Validate();
										$validation = $validate->check($_POST, array(
											'email' => array(
												'disp_text' => 'E-mail',
												'required' => true,
												'exists' => 'users',
												)
											));

										if($validation->passed()){
											$code = Token::generate();
											
											try{
													DB::GetInstance()->Query("UPDATE users set act_code = ? WHERE email = ? ",array($code,Input::Get("email")));
													
													$recoverMessage = "You recently requested a new password<br /><br />";
													$recoverMessage .= "Please click the link below to complete your request<br/>";
													$recoverMessage .= "<a href='".Options::Get("siteurl")."/recover.php?email=".Input::Get("email")."&code=".$code."' >".Options::Get("siteurl")."recover.php?email=".Input::Get("email")."&code=".$code."</a>";
													
													Mail::Send(Input::Get("email"),'Account recovry request',$recoverMessage);

													echo "<div class='alert alert-success' role='alert'>";
													echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
													echo lang('RESET_PASSWORD_REQUEST_SENT')."</br>";
													echo "</div>";
													
											}catch(Exception $e){
												die($e->getMessage());
											}
										
										}else{
											foreach($validation->errors() as $error){
												echo "<div class='alert alert-danger' role='alert'>";
												echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
												echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
												echo " ".$error."</br>";
												echo "</div>";
											}
										}
									}
								}
					?>
							<input type="hidden" name="token" id="token" value="<?php echo Token::generate(); ?>" />
							<label for="email"  class="sr-only"><?php echo lang('EMAIL'); ?></label>
							<input type="text" id="email" name="email" class="form-control" placeholder="<?php echo lang('ENTER_YOUR_EMAIL'); ?>" required />
							<br/>
							<input name="recover" class="btn btn-lg btn-primary btn-block" type="submit" value="<?php echo lang('FIND_ACCOUNT'); ?>" />
						</div>
					</div>
      </form>
			<?php
			}
			?>
			<p class="loginFooter"><?php echo lang('COPYRIGHT'); ?></p>
    </div>
</body>
</html>