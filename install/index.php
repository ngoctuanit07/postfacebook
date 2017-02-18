<?php 
	session_start();
	ob_start();
	require_once "../core/autoload.php";
	require_once "../core/language/language.php";
	if(!Session::Exists("setup")){
		Redirect::To("../index.php");
		die();
	}
?>
<html>
<head>
	<title>The kingposter setup</title>
	<meta charset="UTF-8" />
	<meta name="description" content="">
	<meta name="author" content="Abdellah Gounane - Icodix.com">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="../theme/default/css/custom.css" rel="stylesheet" />
  	<link href="../theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<script src="../theme/default/js/jquery.js"></script>
	<script src="../theme/default/js/jsui.js"></script>
	<script src="../theme/default/bootstrap/js/bootstrap.min.js"></script>
	<style>
		/* --- Install page ---*/
		form.form-install .form-control { margin-bottom: 10px;}
		.form-install .panel-heading {
		    background: #3a5795 !important;
		}
		.form-install {
		  max-width: 400px;
		  padding: 15px;
		  margin: 0 auto;
		}

		.appLogo { margin: auto; width: 300px; }

		.verifyPurchaseCode {margin: 50px auto;width: 400px;}
		#purchaseCode {margin-bottom: 5px;}
	</style>
</head>
<body>
<noscript>
<div class="alerts alert alert-danger">
	<span class="glyphicon glyphicon-warning-sign"></span>
	<p class='alerttext'>JavaScript MUST be enabled in order for you to use kingposter. However, it seems JavaScript is either disabled or not supported by your browser. If your browser supports JavaScript, Please enable JavaScript by changing your browser options, then try again.</p></div>
</noscript>
<?php	
	/*
	|-------------------------------------------------------------------
	| Errors holder
	|-------------------------------------------------------------------
	|
	*/

?>
<div id="wrapper">
<div class="appLogo">
	<img src="../theme/default/images/logo_large.png" alt="logo" />
</div>
<form class="form-install" method="POST">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title"><span class="glyphicon glyphicon-cog"></span> kingposter Setup</h3>
		</div>
		<div class="panel-body">
<?php
/*
|-------------------------------------------------------------------
| Check if the form has been submited
|-------------------------------------------------------------------
|
*/
if(Input::get('setup')){
		$db = DB::GetInstance();
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'username' => array(
				'disp_text' => 'Username',
				'required' => true,
				'min' => 2,
				'max' => 32,
				'unique' => 'users'
				),
			'password' => array(
				'disp_text' => 'Password',
				'required' => true,
				'min' => 6,
				'max' => 32
				),
			'repassword' => array(
				'disp_text' => 'Confirm Password',
				'required' => true,
				'matches' => 'password'
				),
			'email' => array(
				'disp_text' => 'E-mail',
				'required' => true,
				'unique' => 'users',
				'valid_email' => true,
				),
			'sitename' => array(
				'disp_text' => 'Site name',
				'required' => true,
				),
			));

		if($validation->passed()){
			$salt = Hash::salt(32);
			try{
					$user = new user();

					$user->create(array(
						'username' => Input::get('username'),
						'password' => Hash::make(Input::get('password'), $salt),
						'salt' => $salt,
						'email' => Input::get('email'),
						'roles' => '1',
						'active' => '1',
						'signup' => date('Y-m-d H:i:s')
					));
					
					$siteurl = substr(CurrentPath(), 0, strrpos(CurrentPath(), 'install/'));
					$db->query("INSERT INTO `options` (`option`,`value`) values ('siteurl', ? )",array($siteurl));
					$db->query("INSERT INTO `options` (`option`,`value`) values ('sitename', ? )",array(Input::get('sitename')));
					$db->query("INSERT INTO `options` (`option`,`value`) values ('users_can_register', '1' )");
					$db->query("INSERT INTO `options` (`option`,`value`) values ('users_must_confirm_email', '0' )");

					// Setup the cron jobs (Evry 5 min by default)
					$output = shell_exec('crontab -l');
					$cron_file = "/tmp/crontab.txt";
					$cmd = "* * * * * wget -O /dev/null ".$siteurl."cron.php >/dev/null 2>&1";
					file_put_contents($cron_file, $output.$cmd.PHP_EOL);
					exec("crontab $cron_file");
					
					Redirect::To("../index.php");
				
			}catch(Exception $e){
				die($e->getMessage());
			}
		}
		
		if(!empty($errors)){
			
			echo "<div class='alert alert-danger' role='alert'>";
			echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
			echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
			foreach($errors as $error){
				echo " ".$error."</br>";
			}
			echo "</div>";
			
		} elseif ($validation->errors()){
			
			echo "<div class='alert alert-danger' role='alert'>";
			echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
			echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
			foreach($validation->errors() as $error){
				echo " ".$error."</br>";
			}
			echo "</div>";
			
		} 
}
?>

			<label for="username" class="sr-only">Admin username</label>
      <input type="text" name="username" id="username" class="form-control" placeholder="Admin username" required="" autofocus="" value="<?php echo escape(Input::get('username')); ?>">

			<label for="password" class="sr-only">Admin password</label>
			<input type="password" name="password" id="password" placeholder="Admin Password" class="form-control" required="" />
			
			<label for="repassword" class="sr-only">Re-enter Admin password</label>
			<input type="password" name="repassword" id="repassword" placeholder="Re-enter Admin password" class="form-control" required="" />
			
			<label for="email" class="sr-only">Admin e-mail</label>
      <input type="text" name="email" id="email" class="form-control" placeholder="Admin e-mail" required="" autofocus="" value="<?php echo escape(Input::get('email')); ?>">
			
			<label for="sitename" class="sr-only">Site name</label>
			<input type="sitename" name="sitename" id="sitename" value="<?php if(Input::get('sitename')){ echo escape(Input::get('sitename')); }else{ echo "kingposter";} ?>" placeholder="Site name" class="form-control" required="" />
			
			<input name="setup" type="submit" id="submit" value="Setup" class="btn btn-primary" />
			<input name="reset" type="reset" id="reset" value="Reset" class="btn btn-primary" />

	</div>
</div>  
</form>
<p class="footer">All rights reserved &copy; <?php echo date("Y"); ?> Developed by <a href="http://icodix.com" target="_blank">Icodix.com</a></p>
</div> <!-- End wrapper -->
</body>
</html>