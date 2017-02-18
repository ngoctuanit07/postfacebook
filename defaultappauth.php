<?php 
	require "core/init.php";
	$template = new template();
?>
<html>
<head>
	<meta charset="UTF-8" />
	<title><?php echo lang('AUTHENTICATE');?></title>
	<meta name="description" content="">
	<meta name="author" content="Abdellah Gounane - Icodix.com">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- CSS Files -->
	<link href="theme/default/css/custom.css" rel="stylesheet" />	
  	<link href="theme/default/css/jquery.datetimepicker.css" rel="stylesheet">
  	<link href="theme/default/css/datatables.bootstrap.min.css" rel="stylesheet">
  	<link href="theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<!-- JS Files -->
	<script src="theme/default/js/jquery.js"></script>
	<script src="core/js/lang.js"></script>
	<script src="core/js/javascript.js"></script>
	<script src="theme/default/js/jsui.js"></script>
	<script src="theme/default/js/postpreview.js"></script>
	<script src="theme/default/bootstrap/js/bootstrap.min.js"></script>
	<script src="theme/default/js/jquery.datetimepicker.min.js"></script>
	<script src="theme/default/js/jquery.dataTables.min.js"></script>
	<script src="theme/default/js/dataTables.bootstrap.min.js"></script>
</head>
<body>
<noscript>
<div class="alerts alert alert-danger">
	<p class='alerttext'>JavaScript MUST be enabled in order for you to use kingposter. However, it seems JavaScript is either disabled or not supported by your browser. If your browser supports JavaScript, Please enable JavaScript by changing your browser options, then try again.</p></div>
</noscript>
<form method='POST'>
<div class="row">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">&nbsp;<?php echo lang('GET_ACCESS_TOKEN_URL'); ?> </h3>
			</div>
			<div class="panel-body">
				<div class='messageBox'></div>
				<?php

					$app_id = trim(Input::Get("app_id","GET"));

					if(!$app_id){
						echo "<script> alertBox('".lang('APP_ID_NOT_SPECIFIED')."','danger','.messageBox',true);</script>";
					}else{
						
						$fbapp = new FbApps();
						$fbapp = $fbapp->get($app_id);

						if($fbapp){

							if(isset($_POST['submit'])){
								$accessToken = trim($_POST['accessToken']);
								if(empty($accessToken)){
									echo "<script> alertBox('".lang('ENTER_ACCESS_TOKEN')."','danger','.messageBox',true);</script>";
								}else{
									
									$user = new User();
									$fb = new Facebook();
									$fb_account = new FbAccount();

									try{
										if($fb_account->UserDefaultFbAccount()){
											if($fb->IsATValid($accessToken)){

												if($fb->GetAccessToken($app_id)){
													$fb->UpdateAccessToken($user->data()->id,$app_id,$fb_account->UserDefaultFbAccount(),$accessToken);
												}else{
													$fb->SaveAccessToken($user->data()->id,$app_id,$fb_account->UserDefaultFbAccount(),$accessToken);
												}

												echo "<script> alertBox('".lang('ACCESS_TOKEN_SAVED_SUCCESS')." <a href=\'#\' onclick=\'window.opener.location.href = window.opener.location.href;window.close();\'>Close this windiw</a>.','success','.messageBox',true);</script>";
											
											}else{
												throw new Exception(lang('INVALID_ACCESS_TOKEN'));
											}

										}else{
											throw new Exception(lang('NO_FB_ACCOUNT_SELECTED'));
										}

									}catch(Exception $e){
										echo "<script> alertBox('".$e->GetMessage()."','danger','.messageBox',true);</script>";
									}
								}
							}

							?>
							<ol>
								<li>
									<button onclick="window.open('<?php echo $fbapp->getAppAuthLink(); ?>','','height=500,width=600'); return false;" class="btn btn-primary"><?php echo lang('AUTH_APP'); ?></button> <?php echo lang('SET_VISIBILITY_PUBLIC'); ?></li>
								<li>
									<button onclick="window.open('data:text/html,<html><meta http-equiv=\'refresh\' content=\'0; url=view-source:<?php echo $fbapp->getAppAuthLink(); ?>\'></html>','','height=1,width=600'); return false;" class="btn btn-primary"><?php echo lang('GET_APP_AUTH_LINK'); ?></button>
									<?php echo lang('COPY_POPUP_LINK_IN_TEXT_EREA'); ?>
							</li>
							</ol>

						<?php

						}

						
					}
					
				?>
				<textarea name='accessTokenURL' id='accessTokenURL' rows='3' cols='100' class="form-control" placeholder='<?php echo lang('PASTE_APP_AUTH_LINK'); ?>'></textarea>
				<textarea name='accessToken' rows='3' cols='100' id="accessToken" class="form-control" placeholder='<?php echo lang('ENTER_ACCESS_TOKEN_HERE'); ?>'></textarea>
					<p>
						<input type='submit' class='btn btn-primary' name='submit' value='<?php echo lang('SET_ACCESS_TOKEN'); ?>'>
						<input type='button' class='btn btn-primary testAccessToken' value='<?php echo lang('TEST_ACCESS_TOKEN'); ?>'>
					</p>
					<script>
					$( document ).ready(function() {
						$('#accessTokenURL').bind('input propertychange', function() {
							var at = $(this).val().match(/access_token=(.*)(?=&expires_in)/);
							if(at){$("#accessToken").val(at[1]);}
						});

						$(".testAccessToken").click(function(){
							$.post( "ajax/accesstoken.php", {isAccessTokenValid:'true',accessToken:$("#accessToken").val()},function( data ) {
								if(data != "true"){
									alertBox('<?php echo lang('INVALID_ACCESS_TOKEN'); ?>','danger','.messageBox',true);
								}else{
									alertBox('<?php echo lang('ACCESS_TOKEN_IS_VALID'); ?>','success','.messageBox',true);
								}
							});
						});
					});
				</script>
			</div>
		</div>
</div>
</form>
<?php $template->footer(); ?>