<?php  
include('core/init.php');
include('core/timezones.php');

$user = new User();
$fb = new Facebook();
$template = new Template();
$fbaccount = new fbaccount();
$fbapps = new FbApps();

// Switch facebook account request 
if(Input::Get("switchFbAccount")){
	if($fbaccount->exists(Input::Get("switchFbAccount"))){
		$user->UpdateOptions(array('default_Fb_Account' => Input::Get("switchFbAccount")));
		if(!httpReferer()){
			Redirect::To("settings.php");
		}
		Redirect::To(httpReferer());
	}
}

// Delete facebook account					
if(Input::get("action","GET") == "deletefbaccount" && Input::get("id","GET")){
	try{
		$fbaccount->delete(Input::get("id","GET"));
		Session::Flash("settings","success",lang("FB_ACCOUNT_SUCCESS_DELETED"),true);
	}catch(Exception $ex){
		Session::Flash("settings","danger",$ex->GetMessage(),true);
	}
	
	Redirect::To("settings.php#tab-fbAccounts");
}

// Delete facebook app					
if(Input::get("action","GET") == "deletefbapp" && Input::get("id","GET")){
	try{
		$fb->DeleteApp(Input::get("id","GET"));	
	}catch(Exception $ex){
		Session::Flash("settings","danger",$ex->GetMessage(),true);
	}
	
	Redirect::To("settings.php#tab-fbApps");
}

// Deauthorize
if(Input::get("action","GET") == "deauthorize" && Input::get("id","GET")){
	try{
		$fb->DeauthorizeApp(Input::get("id","GET"));
		Session::Flash("settings","success",lang('APP_DEAUTH_SUCCESS'),true);
		
	}catch(Exception $ex){
		echo $ex->GetMessage();
		Session::Flash("settings","danger",$ex->GetMessage(),true);
	}

	Redirect::To("settings.php#tab-fbApps");
}

if(Input::get('save')){
	
	$validate = new Validate();
	
	$validation = $validate->check($_POST, array(
			'postInterval' => array(
				'disp_text' => lang('POST_INTERVAL'),
				'required' => true,
			),
			'language'=> array(
				'disp_text' => lang('LANGUAGE'),
				'required'	=> true,
				'inArray' 	=> Language::GetAvailableLangs()
			)
	));
	
	if(Input::Get("email") != $user->data()->email){
		$validation = $validate->check($_POST, array(
				'email' => array(
					'disp_text' => lang('EMAIL'),
					'required' => true,
					'unique' => 'users',
					'valid_email' => true,
					)
		));
	}
	
	if(Input::Get("password")){
		$validation = $validate->check($_POST, array(
				'password' => array(
					'disp_text' => lang('PASSWORD'),
					'min' => '6',
					'max' => '32',
					),
				'repassword' => array(
					'disp_text' => lang('RE_ENTER_PASSWORD'),
					'required' => true,
					'matches' => 'password',
					)
		));
	}

	if($validation->passed()){
		try{
			$db = DB::GetInstance();
			
			$openGroupOnly = Input::Get("openGroupOnly") == "on" ? 1 : 0;
			$uniquePost = Input::Get("uniquePost") == "on" ? 1 : 0;
			$uniqueLink = Input::Get("uniqueLink") == "on" ? 1 : 0;

			$user->UpdateOptions(array(
				'postInterval' 	=> Input::Get("postInterval"),
				'openGroupOnly'	=> $openGroupOnly,
				'lang'			=> Input::Get("language"),
				'uniquePost'	=> $uniquePost,
				'uniqueLink'	=> $uniqueLink,
				'timezone'		=> Input::Get("timezone")
			));

			// Update the default app for the current facebook account
			if($fb->App(Input::Get('postApp'))){
				$fbaccount->updateDefaultApp(Input::Get('postApp'));
			}

			// User must have admin permission to update the general settings 
			if($user->HasPermission("admin")){
				Options::Update(array(
					"users_can_register" => Input::Get("usersCanRegister") == "on" ? 1 : 0,
					"users_must_confirm_email" => Input::Get("usersMustConfirmEmail") == "on" ? 1 : 0,
					"sitename" => Input::Get("sitename"),
				));
			}

			// Update email
			if(Input::Get("email")){
				$user->update(array('email' => Input::get('email')),$user->data()->id);
			} 
			
			// Update facebook user id 
			if(Input::Get("fbuserid")){
				$user->update(array('fbuserid' => Input::get('fbuserid')),$user->data()->id);
			} 
			
			// Update password
			if(Input::Get("password")){
				$salt = Hash::salt(32);
				$user->update(array('password' => Hash::make(Input::get('password'), $salt),"salt" => $salt),$user->data()->id);
			}
			
			Session::Flash("settings","success",lang('SETTINGS_UPDATED_SUCCESS'),true);

		}catch(Exception $e){
			echo $e->getMessage();
		}
	}else{
		Session::Flash("settings","danger","<ul><li>".implode("</li><li>",$validation->errors())."</li></ul>",false);
	}
}

$template->header("Settings");

if(Session::exists('settings')){
		foreach(Session::Flash('settings') as $error){
			echo "<div class='alert alert-".$error['type']."' role='alert'>";
			echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
			echo $error['message'];
			echo "</div>";
		}
}

?>
<div class="messageBox"></div>
<form method='POST' action='' class="settings">
	<div class="row">
		<div class="tabbable tabs-left">
			<div class="col-xs-3">
				<ul class="nav nav-tabs">

					<li class="active">
						<a href="#tab-userSettings" data-toggle="tab"><i class="fa fa-user"></i> <?php echo lang('USER_SETTINGS'); ?></a>
					</li>

					<li>
						<a href="#tab-generalSettings" data-toggle="tab"><i class="fa fa-tasks"></i> <?php echo lang('GENERAL_SETTINGS'); ?></a>
					</li>

					<li>
						<a href="#tab-postingSettings" data-toggle="tab"><i class="fa fa-clipboard"></i> <?php echo lang('POSTING_SETTINGS'); ?></a>
					</li>
					
					<li>
						<a href="#tab-fbAccounts" data-toggle="tab"><i class="fa fa-facebook"></i> <?php echo lang('FB_ACCOUNTS'); ?></a>
					</li>

					<li>
						<a href="#tab-fbApps" data-toggle="tab"><i class="fa fa-plug"></i> <?php echo lang('FB_APPS'); ?></a>
					</li>

				</ul>
			</div>
			<div class="col-xs-9">
				<div class="tab-content">
					<div class="tab-pane active" id="tab-userSettings">
				 		<h4 class="tab-title"><i class="fa fa-user"></i>  <?php echo lang('USER_SETTINGS'); ?></h4>
					 	<label for="username"><?php echo lang('USERNAME')?> (<small><?php echo lang('USERNAME_CAN_NOT_CHANGED'); ?></small>)
					 	</label>
						<?php $username = Input::Get("username") == false ?  $user->data()->username : Input::Get("username"); ?>
						<input type="text" name="username" class="form-control" id="username" placeholder="<?php echo lang('USERNAME'); ?>" value="<?php echo $username; ?>" disabled="disabled"/>
						
						<?php $email = Input::Get("email") == false ?  $user->data()->email : Input::Get("email"); ?>
						<label for="email"><?php echo lang('EMAIL'); ?></label>
						<input type="text" name="email" class="form-control" id="email" placeholder="<?php echo lang('EMAIL'); ?>" value="<?php echo $email; ?>" />
						
						<label for="fbuserid"><?php echo lang('FB_USER_ID');?></label>
						<?php $fbuserid = Input::Get("fbuserid") == false ?  $user->data()->fbuserid : Input::Get("fbuserid"); ?>
						<input type="text" name="fbuserid" class="form-control" id="fbuserid" placeholder="<?php echo lang('FB_USER_ID');?>" value="<?php echo $fbuserid; ?>"/>

						<label for="password"><?php echo lang('PASSWORD'); ?></label>
						<input type="password" name="password" class="form-control" id="password" value="" placeholder="<?php echo lang('NEW_PASSWORD');?>" readonly onfocus="this.removeAttribute('readonly');"/>
						
						<label for="repassword"><?php echo lang('RE_ENTER_PASSWORD'); ?></label>
						<input type="password" name="repassword" class="form-control" id="repassword" value="" placeholder="<?php echo lang('RE_ENTER_NEW_PASSWORD'); ?>" readonly onfocus="this.removeAttribute('readonly');"/>
					</div>

					<div class="tab-pane" id="tab-generalSettings">
				 		<h4 class="tab-title"><i class="fa fa-tasks"></i> <?php echo lang('GENERAL_SETTINGS'); ?></h4>
						<?php 
							if($user->HasPermission("admin")) : ?>

								<?php 
								$sitename = Input::Get("sitename") ? Input::Get("sitename")  : Options::Get("sitename");
								?>

								<div class="input-group">
									<label for="sitename"><?php echo lang('SITE_NAME'); ?></label>
									<input type="text" name="sitename" class="form-control" id="sitename" placeholder="<?php echo lang('SITE_NAME'); ?>" value="<?php echo $sitename; ?>" />
								</div>

								<?php
									if(Input::Get("usersCanRegister")){
										$usersCanRegister = Input::Get("usersCanRegister") == "on" ? "checked" : "";
									} else {
										$usersCanRegister = Options::Get("users_can_register") ? "checked" : "";
									}
								?>
								<div class="input-group">
									<input type="checkbox" class='checkbox-style' id="usersCanRegister" name="usersCanRegister" aria-label="Users can register" <?php echo $usersCanRegister;?> />
									<label for="usersCanRegister"></label>
									<span class="input-text"><?php echo lang('USERS_CAN_REGISTER'); ?></span>
								</div>

								<div class="input-group">
								<?php 
									if(Input::Get("usersMustConfirmEmail")){
										$usersMustConfirmEmail = Input::Get("usersMustConfirmEmail") == "on" ? "checked" : "";
									} else {
										$usersMustConfirmEmail = Options::Get("users_must_confirm_email") ? "checked" : "";
									}
									?>
									<input type="checkbox" id="usersMustConfirmEmail" name="usersMustConfirmEmail"  class='checkbox-style' aria-label="New users must confirm their email address" <?php echo $usersMustConfirmEmail;?> />
									<label for="usersMustConfirmEmail" ></label>
									<span class="input-text"><?php echo lang('USERS_MUST_CONFIRM_EMAIL'); ?></span>
								</div>
						<?php endif; // User has admin permission ?>


						<div class="input-group">
							<label for="timezone">
								<?php echo lang('TIMEZONE'); ?> | <?php echo lang('CURRENT_TIME'); ?> : <?php echo date("Y-m-d H:i"); ?>
							</label>
							<select name='timezone' id="timezone" class="form-control">
								<?php

									if(isset($user->Options()->timezone))
										$selected = Input::Get('timezone') ? Input::Get('timezone') : $user->Options()->timezone;
									else
										$selected = Input::Get('timezone');
									
									foreach($timezones as $timezone){
										$select = $selected == $timezone ? "selected" : "";
										echo "<option value='".$timezone."' ".$select.">".$timezone."</option>";
									}
								?>
							</select>
						</div>
					
					
						<div class="input-group">
							<label for="language"><?php echo lang('LANGUAGE'); ?></label>
							<select name='language' id="language" class="form-control">
								<?php
									$currentUserLang = isset($user->Options()->lang) ? $user->Options()->lang : DEFAULT_LANG;
									foreach(Language::GetAvailableLangs() as $language){
										$select = strtolower($currentUserLang) == strtolower($language) ? "selected" : "";
										echo "<option value='".$language."' ".$select.">".ucfirst($language)."</option>";
									}
								?>
							</select>
						</div>
					</div>


				 	<div class="tab-pane" id="tab-postingSettings">
				 		<h4 class="tab-title"><i class="fa fa-clipboard"></i> <?php echo lang('POSTING_SETTINGS'); ?></h4>
				 		<div class="input-group">
									<?php 
										if(Input::Get("openGroupOnly")){
											$openGroupOnlyChecked = Input::Get("openGroupOnly") == "on" ? "checked" : "";
										} else if(isset($user->Options()->openGroupOnly)){
											$openGroupOnlyChecked =  $user->Options()->openGroupOnly ? "checked" : "";
										}else{
											$openGroupOnlyChecked = "";
										}
										?>
									<input type="checkbox" class="checkbox-style" id="openGroupOnly" name="openGroupOnly" aria-label="Post to open group only" <?php echo $openGroupOnlyChecked;?> />
									<label for="openGroupOnly"></label>
									<span class="input-text"><?php echo lang('SHOW_OPEN_GROUPS_ONLY'); ?></span>
								</div>
								<div class="input-group">
									<?php 
										if(Input::Get("uniquePost")){
											$uniquePost = Input::Get("uniquePost") == "on" ? "checked" : "";
										} else if(isset($user->Options()->uniquePost)){
											$uniquePost =  $user->Options()->uniquePost ? "checked" : "";
										}else{
											$uniquePost = "";
										}
										?>
									<input type="checkbox" class="checkbox-style" id="uniquePost" name="uniquePost" aria-label="Unique post" <?php echo $uniquePost;?> />
									<label for="uniquePost"></label>

									<span class="input-text"><?php echo lang('UNIQUE_POST'); ?> <a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" title="<?php echo lang('UNIQUE_POST_TEXT'); ?>"><span class="glyphicon glyphicon-question-sign"></span></a> </span>
								</div>
								<div class="input-group">
									<?php 
										if(Input::Get("uniqueLink")){
											$uniqueLink = Input::Get("uniqueLink") == "on" ? "checked" : "";
										} else if(isset($user->Options()->uniqueLink)){
											$uniqueLink =  $user->Options()->uniqueLink ? "checked" : "";
										}else{
											$uniqueLink = "";
										}
										?>
									<input type="checkbox" class="checkbox-style" id="uniqueLink" name="uniqueLink" aria-label="Unique post" <?php echo $uniqueLink;?> />
									<label for="uniqueLink"></label>
									<span class="input-text"><?php echo lang('UNIQUE_LINK'); ?> <a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" title="<?php echo lang('UNIQUE_LINK_TEXT'); ?>"><span class="glyphicon glyphicon-question-sign"></span></a></span>
								</div>
								<label for="postInterval"><?php echo lang('POST_INTERVAL'); ?> (<small><?php echo lang('IN_SECONDS'); ?></small>)</label>
								<select name='postInterval' id="postInterval"  class="form-control">
									<?php 
									for($i = 10;$i<=1500;$i += 30){
										$selected = Input::Get('postInterval');
										
										if(isset($user->Options()->postInterval)){
											$selected = Input::Get('postInterval') ? Input::Get('postInterval') : $user->Options()->postInterval;
										}

										if ($i==$selected) {
											echo "<option value='$i' selected>$i Sec</option>";
										} else {
											echo "<option value='$i'>$i Sec</option>";
										}
										if($i==10) $i=0;
									}
									?>
								</select>
								<label for="postApp"><?php echo lang('FB_APP'); ?></label>
								<select name='postApp' id="postApp" class="form-control">
									<option value=""></option>
									<?php
										if($fb->AppsList()){
												$selected = Input::Get('postApp') ? Input::Get('postApp') : $fbaccount->UserFbAccountDefaultApp();
												foreach($fb->AppsList() as $app){
													$select = $selected == $app->appid ? "selected" : "";
													if($fb->getAccessToken($app->appid)){
														echo "<option value='".$app->appid."' ".$select.">".$app->app_name."</option>";
													}
												}
										}
									?>
								</select>
				 	</div>

				<div class="tab-pane" id="tab-fbAccounts">
					<h4 class="tab-title"><i class="fa fa-facebook"></i> <?php echo lang('FB_ACCOUNTS'); ?></h4>
					<button class="btn btn-primary" type="button" data-toggle="modal" data-target="#addNewFbAccount" style='float:right;'><?php echo lang('ADD_UPDATE_FACEBOOK_ACCOUNT'); ?></button>
					<div class="clear"></div>

					<table class='table table-bordered table-striped'>
						<thead>
							<tr>
								<td><?php echo lang('FB_USER_ID'); ?> (Scoped ID)</td>
								<td><?php echo lang("FIRSTNAME"); ?></td>
								<td><?php echo lang("LASTNAME"); ?></td>
								<td></td>
							</tr>
						</thead>
						<tbody id="fbAccounts">
							<?php 
								if($fbaccount->getAll()){
									foreach($fbaccount->getAll() as $fba){
										echo "<tr>";
										echo "<td>".$fba->getFbId()."</td>
											<td>".$fba->getFirstname()."</td>
											<td>".$fba->getLastname()."</td>
											<td>
												<a href='settings.php?action=deletefbaccount&id=".$fba->getFbId()."' title='".lang('DELETE')."' class='btn btn-danger'>
												<span class='glyphicon glyphicon-trash'></span> ".lang('DELETE')."
												</a>";

												if($fba->getFbId() == $fbaccount->UserDefaultFbAccount()){
													echo "<span class='btn btn-default'>
															<span class='glyphicon glyphicon-ok'></span>
															".lang('DEFAULT')."</span>";
												}

										echo "</td></tr>";
									}
								}else{
									echo "<tr><td colspan='4' class='nodata'>".lang('NO_FB_ACCOUNT_AVAILABLE')."</td></tr>";
								}
							?>
						</tbody>
					</table>

					<!-- New facebook account modal -->
					<div id="addNewFbAccount" class="modal fade" role="dialog">
					  <div class="modal-dialog">
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal">&times;</button>
					        <h4 class="modal-title"><?php echo lang('ADD_UPDATE_FACEBOOK_ACCOUNT'); ?></h4>
					      </div>
					      <div class="modal-body">
					        
					        <div class='addFbAccountalerts'></div>
							<script>
								$(function(){
									$( "#addFbAccountBtn" ).click(function(){
										var reload = false;
										alertBox("<img src='theme/default/images/loading.gif' alt='loading'/>","",".addFbAccountalerts",false,false);
										$.post(
											"ajax/fbaccount.php",
											{
													fb_accesstoken: $("#accessToken").val()
											},
											function(data){
												if(data == "true"){
													alertBox(<?php echo "'".lang("FB_ACCOUNT_SAVED_SUCCESSFULLY")."'"; ?>,"success",".addFbAccountalerts",false);
														reload = true;
														$(document).on('hide.bs.modal','#addNewFbAccount', function () {
															if(reload)
																document.location.href = "settings.php#tab-fbAccounts";
														});
												}else if(data == ""){
													alertBox(<?php echo "'".lang("EMPTY_REQUEST")."'"; ?>,"danger",".addFbAccountalerts",false);
												}else{
													alertBox(data,"danger",".addFbAccountalerts",false);
												}
												console.log(data);
											}
										);
									});
								});
							</script>
							<button onclick="window.open('https://www.facebook.com/v2.3/dialog/oauth?response_type=token&display=popup&client_id=145634995501895&redirect_uri=https%3A%2F%2Fdevelopers.facebook.com%2Ftools%2Fexplorer%2Fcallback&scope=user_managed_groups%2Cuser_groups%2Cpublish_actions','','height=500,width=600'); return false;" class="btn btn-primary">
								<?php echo lang('API_CALLBACK_URL'); ?></button>

							<?php echo lang('COPY_POPUP_LINK_IN_TEXT_EREA'); ?>
							
							<textarea name='accessTokenURL' id='accessTokenURL' rows='3' cols='100' class="form-control" placeholder='<?php echo lang('PASTE_APP_AUTH_LINK'); ?>'></textarea>

							<a href="https://developers.facebook.com/tools/explorer" target="_blank" class="btn btn-primary">
								<?php echo lang('GET_ACCESS_TOKEN_GRAPH_API_EXPLORER_PAGE'); ?>
							</a>
						
							<textarea name='accessToken' rows='3' cols='100' id="accessToken" class="form-control" placeholder='<?php echo lang('ENTER_ACCESS_TOKEN_HERE'); ?>'></textarea>
											
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					        <input type='button' class='btn btn-primary' id="addFbAccountBtn" value='Add facebook account'>
					      </div>
					    </div>

					  </div>
					</div>

				</div>

				<div class="tab-pane" id="tab-fbApps">
					<h4 class="tab-title"><i class="fa fa-plug"></i> <?php echo lang('FB_APPS'); ?></h4>
					<div class="manageAppErrors"></div>
							<?php 
								if($user->hasPermission("admin")){ 
							?>
							<script>
								$( document ).ready(function() {
									$("#addFbApp").click(function(){
										// app_id and app_serect validation
										if($("#fbapp_id").val() == ""){
											$(".manageAppErrors").html("<img src='theme/default/images/loading.gif' alt='loading'/>");
											alertBox("<?php echo lang('APP_ID_CAN_NOT_EMPTY'); ?>",'danger',".manageAppErrors");
										} else {
											$(".manageAppErrors").html("<img src='theme/default/images/loading.gif' alt='loading'/>");;
											$.post( "ajax/fbapp.php", {app_id:$("#fbapp_id").val(),app_secret:$("#fbapp_secret").val(),fbapp_auth_Link:$("#fbapp_auth_Link").val(),},function( data ) {
												if(data == "true"){
													alertBox("<?php echo lang('APP_ADDED_SUCCESS'); ?>",'success',".manageAppErrors");
												}else{
													alertBox(data,'danger',".manageAppErrors");
												}
											});
										}
									});
							});
							</script>
							<label for="fbapp_id"><?php echo lang('FB_APP_ID'); ?></label>
							<input type="text" name="fbapp_id" class="form-control" id="fbapp_id" placeholder="<?php echo lang('FB_APP_ID'); ?>" value="" />

							<label for="fbapp_secret"><?php echo lang('FB_APP_SECRET'); ?></label>
							<input type="text" name="fbapp_secret" class="form-control" id="fbapp_secret" placeholder="<?php echo lang('FB_APP_SECRET'); ?>" value=""/>

							<label for="fbapp_auth_Link"><?php echo lang('FB_APP_AUTH_LINK'); ?>
								&nbsp;<a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" style="float:right" title="<?php echo lang('FB_APP_AUTH_LINK_NOTE'); ?>"> 
								<span class="glyphicon glyphicon-question-sign"></span></a>
							</label>
							<input type="text" name="fbapp_auth_Link" class="form-control" id="fbapp_auth_Link" placeholder="<?php echo lang('FB_APP_AUTH_LINK'); ?>" value=""/>

							<input type="button" name="addFbApp" value="<?php echo lang('ADD'); ?>" id="addFbApp" class="btn btn-primary" />

							<br />
							<br />
						<?php 
						}
						?>

						<?php  if($fbaccount->UserDefaultFbAccount()){

							$currentFbAccount = $fbaccount->get($fbaccount->UserDefaultFbAccount()); 
							
							if($currentFbAccount): ?>

									<div class='alert alert-warning' role='alert'>
									<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>
									&nbsp;Please make sure to logged in to Facebook as <strong><?php echo $currentFbAccount->GetFirstname() . " " . $currentFbAccount->GetLastname(); ?></strong> before authenticate the app.
									</div>
							
							<?php 
							endif;
						}
						?>

						<table class='table table-bordered table-striped'>
							<thead>
								<tr>
									<td><?php echo lang('APP_NAME'); ?></td>
									<td><?php echo lang('STATUS'); ?></td>
									<td></td>
								</tr>
							</thead>
							<script>
								function FbAuth(app_id){
									var oldApi = "";
									if($('#oldApi').is(":checked")){
										oldApi = "&oldApi=true"
									}
									window.open('FbAuth.php?app_id='+app_id+oldApi,'','height=500,width=600');
								}
							</script>
							<tbody id="fbapps">
								<?php 
									
									foreach($fbapps->getAll() as $fbapp){

										if($fb->GetAccessToken($fbapp->getAppId())){
											$statusIcon = "ok";
											$statusText = lang('AUTHENTICATED');
											$statusBtn = "";
											$oldApi = Input::Get("oldApi") ? "&oldApi=true" : "";
										} else {
											$statusIcon = "remove";
											$statusText = lang('NOT_AUTHENTICATED');

											if($fbapp->appType($fbapp->getAppId()) == 2){
												$statusBtn = "<button onclick=\"window.open('resetaccesstoken.php','','height=570,width=600'); return false;\" class='btn btn-primary'>".lang('AUTHENTICATE')."</button> ";
											}
											
											if($fbapp->appType($fbapp->getAppId()) == 3){
												$statusBtn = "<button onclick=\"window.open('defaultappauth.php?app_id=".$fbapp->getAppId()."','','height=470,width=600'); return false;\" class='btn btn-primary'>".lang('AUTHENTICATE')."</button> ";
											}

											if($fbapp->appType($fbapp->getAppId()) == 1){
												$statusBtn = "<button onclick='FbAuth(".$fbapp->getAppId().");return false;' class='btn btn-primary'>".lang('AUTHENTICATE')."</button>";
												if($user->hasPermission("admin")){
													$statusBtn .= "&nbsp; <input type='checkbox' name='oldApi' id='oldApi'/> <label for='oldApi'>API <=2.3</label>";
												}
											}
										}

										echo "<tr><td>".$fbapp->getAppName()."</td>";

										echo "<td><span class='glyphicon glyphicon-".$statusIcon."'></span> " . $statusText . "</td>";
										echo "<td>";
										if($user->hasPermission("admin")){
											echo "<a href='?action=deletefbapp&id=".$fbapp->getAppId()."' title='".lang('DELETE')."' class='btn btn-danger'><span class='glyphicon glyphicon-trash'></span> ".lang('DELETE')."</a>";
										}
										if($fb->GetAccessToken($fbapp->getAppId())){
											echo "<a href='?action=deauthorize&id=".$fbapp->getAppId()."' title='".lang('DEAUTHENTICATE')."' class='btn btn-danger'><span class='glyphicon glyphicon-remove'></span> ".lang('DEAUTHENTICATE')."</a>";
										}else{
											echo $statusBtn;
										}
										echo "</td></tr>";	
									}
									
								?>
							</tbody>
						</table>
				</div>
				<input type="submit" name="save" value="<?php echo lang('SAVE_CHANGES'); ?>" class="btn btn-primary" />
				</div>
			</div>
		</div>
	</div>
	
</form>
<?php $template->footer(); ?>
