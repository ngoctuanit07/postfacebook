<?php  
include('core/init.php');

$user = new User();
$template = new Template();

if(!$user->HasPermission("admin")){
	Redirect::To(404);
}

if(Input::get("delete") && $user->hasPermission("admin") && Input::get("checkbox")){
	try{
		foreach(Input::get("checkbox") as $checkbox){
			$user->Delete($checkbox);
		}
		Session::Flash("usersPage","success",lang('USERS_ACCOUNT_DELETED_SUCCESS'),true);
	}catch(Exception $ex){
		Session::Flash("usersPage","danger",$ex->GetMessage(),true);
	}
}

if(Input::get("action") == "delete" && Input::get("userId") && $user->hasPermission("admin")){
	try{
		$user->Delete(Input::get("userId"));
		Session::Flash("usersPage","success",lang('USER_ACCOUNT_DELETED_SUCCESS'),true);
	}catch(Exception $ex){
		Session::Flash("usersPage","danger",$ex->GetMessage(),true);
	}
	Redirect::To("users.php");
}

if(Input::get("activate") && $user->hasPermission("admin") && Input::get("checkbox")){
	try{
		foreach(Input::get("checkbox") as $checkbox){
			$user->activate($checkbox);;
		}
		Session::Flash("usersPage","success",lang('USERS_ACCOUNT_ACTIVE_SUCCESS'),true);
	}catch(Exception $ex){
		Session::Flash("usersPage","danger",$ex->GetMessage(),true);
	}
}

if(Input::get("deactivate") && $user->hasPermission("admin") && Input::get("checkbox")){
	try{
		foreach(Input::get("checkbox") as $checkbox){
			$user->deactivate($checkbox);;
		}
		Session::Flash("usersPage","success",lang('USERS_ACCOUNT_DEACTIVE_SUCCESS'),true);
	}catch(Exception $ex){
		Session::Flash("usersPage","danger",$ex->GetMessage(),true);
	}
}

$template->header("Users");

if(Session::exists('usersPage')){
	foreach(Session::Flash('usersPage') as $error){
		echo "<div class='alert alert-".$error['type']."' role='alert'>";
		echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
		echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
		echo "&nbsp;".$error['message'];
		echo "</div>";
	}
}

?>
<div class="messageBox"></div>
	<script>
	$(document).ready(function(){
		var editUserModel = $('#editUser');
		var userId;
		var reload = false;
						
		$('#usersSection').on('click','.edit', function() {
				// Clear message box
				$(".formMessageBoxEdit").html("");
				
				// Clear the form 
				$('#username', editUserModel).val("");
				$('#password', editUserModel).val("");
				$('#repassword', editUserModel).val("");
				$('#email', editUserModel).val("");
		
				userId = $(this).attr('id');
				$.getJSON( "ajax/user.php",{userId: userId,action:"get"}, function() {
					}).done(function(data) {
						$('#username', editUserModel).val(data.username);
						$('#email', editUserModel).val(data.email);
						$('#roles', editUserModel).val(data.roles);
					})
					.fail(function() {
						alertBox("Could not get the user data,Please try again",'danger',".formMessageBoxEdit");
					});
				// and finally show the modal
				editUserModel.modal({ show: true });
				return false;
		});	
		
		// save user details
		$('#updateUser').on('click', function() {
			$(".formMessageBoxEdit").html("<img src='theme/default/images/loading.gif' alt='loading'/>");
			$.getJSON( "ajax/user.php",{ userId: userId, email: $('#email', editUserModel).val(),password: $('#password', editUserModel).val(),repassword: $('#repassword', editUserModel).val(),roles: $('#roles', editUserModel).val(),action:"edit" }, function() {
			}).done(function(data) {
				if(data != ""){
					if(data == "true"){
						alertBox("<?php echo lang('USER_DETAILS_UPDATED_SUCCESS'); ?>",'success',".formMessageBoxEdit",true);
						reload = true;
					}else{
						errors = "<ul>";
						for (error in data) {
								errors += "<li>"+data[error]+"</li>";
						}
						errors += "</ul>";
						alertBox(errors,'danger',".formMessageBoxEdit");
					}
				}
				
			})
			.fail(function(data) {
				alertBox("Could not update the user details,Please try again",'danger',".formMessageBoxEdit");
				console.log(data);
			});
		});	

		$(document).on('hide.bs.modal','#editUser', function () {
      if(reload) location.reload();
		});
		
		// add user
		$('#newUserBtn').on('click', function() {
				$(".formMessageBoxAdd").html("<img src='theme/default/images/loading.gif' alt='loading' style='margin:10px;'/>");
				$.getJSON( "ajax/user.php",{username: $('#username', newUser).val(),email: $('#email', newUser).val(),password: $('#password', newUser).val(),repassword: $('#repassword', newUser).val(),roles: $('#roles', newUser).val(),action:"add" }, function() {
				}).done(function(data) {
					if(data != ""){
						if(data == "true"){
							alertBox("<?php echo lang('USER_ADDED_SUCCESS'); ?>",'success',".formMessageBoxAdd",true);
							// Clear the form 
							$('#username', newUser).val("");
							$('#password', newUser).val("");
							$('#repassword', newUser).val("");
							$('#email', newUser).val("");
							reload = true;

						}else{
							errors = "<ul>";
							for (error in data) {
									errors += "<li>"+data[error]+"</li>";
							}
							errors += "</ul>";
							alertBox(errors,'danger',".formMessageBoxAdd");
						}
					}
					
				})
				.fail(function(data) {
					alertBox("Could not add the user,Please try again",'danger',".formMessageBoxAdd");
					console.log(data);
				});
		});
		
		$(document).on('hide.bs.modal','#newUser', function () {
			if(reload){
				reload = false;
				location.reload();
			} 
		});
		
	});
</script>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><span class="glyphicon glyphicon-user"></span> <?php echo lang('USERS'); ?></h3>
	</div>
	<!-- Add new user -->
	<div class="modal fade" id="newUser" tabindex="-1"  data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="newUserLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="newUserLabel"><?php echo lang('NEW_USER'); ?></h4>
				</div>
				<div class="modal-body">
						<div class="formMessageBoxAdd"></div>
						<label for="username" class="sr-only"><?php echo lang('USERNAME'); ?></label>
						<input type="text" name="username" id="username" class="form-control" placeholder="Username" required autofocus="off" value="<?php echo escape(Input::get('username')); ?>"/>
						<label for="password"  class="sr-only"><?php echo lang('PASSWORD'); ?></label>
						<input type="password" id="password" name="password" class="form-control" placeholder="Password" required />
						<label for="repassword"  class="sr-only"><?php echo lang('RE_ENTER_PASSWORD'); ?></label>
						<input type="password" name="repassword" id="repassword" placeholder="Repeat password" class="form-control" required />
						<label for="email" class="sr-only"><?php echo lang('EMAIL'); ?></label>
						<input type="text" name="email" id="email" class="form-control" placeholder="E-mail" required autofocus="off" value="<?php echo escape(Input::get('email')); ?>"/>
						<label for="roles" class="sr-only"><?php echo lang('ROLES'); ?></label>
						<select name="roles" id="roles" class="form-control">
							<option value="2"><?php echo lang('STANDARD_USER'); ?></option>
							<option value="1"><?php echo lang('ADMINISTRATOR'); ?></option>
						</select>
				</div>
				<div class="modal-footer">
						<button type="button" id="addUserClose" class="btn btn-default" data-dismiss="modal"><?php echo lang('CLOSE'); ?></button>
						<button type="button" id="newUserBtn" class="btn btn-primary"><?php echo lang('ADD'); ?></button>
				</div>
			</div>
		</div>
	</div>
	<!-- Add new user End -->

	<!-- Edit user -->
	<div class="modal fade" id="editUser" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="editUserLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="editUserLabel"><?php echo lang('EDIT_USER'); ?></h4>
					</div>
					<div class="modal-body">
						<div class="formMessageBoxEdit"></div>
						<label for="username" class="sr-only"><?php echo lang('USERNAME'); ?></label>
						<input type="text" name="username" id="username" class="form-control" placeholder="Username" disabled value="<?php echo escape(Input::get('username')); ?>"/>
						<label for="password"  class="sr-only"><?php echo lang('PASSWORD'); ?></label>
						<input type="password" id="password" name="password" class="form-control" placeholder="Password" readonly onfocus="this.removeAttribute('readonly');"/>
						<label for="repassword"  class="sr-only"><?php echo lang('RE_ENTER_PASSWORD'); ?></label>
						<input type="password" name="repassword" id="repassword" placeholder="Repeat password" class="form-control"  readonly onfocus="this.removeAttribute('readonly');"/>
						<label for="email" class="sr-only"><?php echo lang('EMAIL'); ?></label>		
						<input type="text" name="email" id="email" class="form-control" placeholder="E-mail" required autofocus="off" value="<?php echo escape(Input::get('email')); ?>"/>
						<select name="roles" id="roles" class="form-control">
							<option value="2"><?php echo lang('STANDARD_USER'); ?></option>
							<option value="1"><?php echo lang('ADMINISTRATOR'); ?></option>
						</select>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('CLOSE'); ?></button>
						<button type="button" id="updateUser" class="btn btn-primary"><?php echo lang('SAVE_CHANGES'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="panel-body" id="usersSection">
	<form action="" method="POST">
		<div class="btn-group">
			<button type="button" class="btn btn-default"><?php echo lang('ACTIONS'); ?></button>
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="caret"></span>
				<span class="sr-only"><?php echo lang('ACTIONS'); ?></span>
			</button>
			<ul class="dropdown-menu">
				<li><input type='submit' class="dropdown-menu-item" name='delete' value='Delete'></li>
				<li><input type='submit' class="dropdown-menu-item" name='activate' value='Activate'></li>
				<li><input type='submit' class="dropdown-menu-item" name='deactivate' value='Deactivate'></li>
			</ul>
		</div>
		<button type="button" class="btn btn-default navbar-btn" data-toggle="modal" data-target="#newUser"><?php echo lang('ADD_NEW_USER'); ?></button>
		<div class="space"></div>
		<table class='table table-bordered table-striped' id="datatable">
			<thead>
				<tr>
					<td width='2%'>
					<input type='checkbox' name='check-all' id="checkbox-all" class='check-all checkbox-style'  value='Check All'>
					<label for="checkbox-all"></label>
					<td><?php echo lang('USERNAME'); ?></td>
					<td><?php echo lang('FIRSTNAME'); ?></td>
					<td><?php echo lang('LASTNAME'); ?></td>
					<td><?php echo lang('EMAIL'); ?></td>
					<td><?php echo lang('FACEBOOK_PROFILE'); ?></td>
					<td><?php echo lang('ROLE'); ?></td>
					<td><?php echo lang('STATUS'); ?></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
				<?php
					if($user->GetUsers()){
						foreach($user->GetUsers() as $u){
							$status =  $u->active == 0 ? lang('INACTIVE') : lang('ACTIVE');
							$roles = array("1"=>lang('ADMINISTRATOR'),"2"=>lang('STANDARD_USER'));
							echo "<tr>
								<td>";
							if($u->id != $user->data()->id){
								echo "<input type='checkbox' name='checkbox[]' id='checkbox-".$u->id."' class='checkbox  checkbox-style' value='".$u->id."'>
									<label for='checkbox-".$u->id."'></label>";
							}
							echo "
								</td>
								<td>".escape($u->username)."</td>
								<td>".escape(ucfirst($u->firstname))."</td>
								<td>".escape(ucfirst($u->lastname))."</td>
								<td>".escape($u->email)."</td>
								<td><a href='https://facebook.com/".$u->fbuserid."' title='".escape(ucfirst($u->firstname))." ".escape(ucfirst($u->lastname))."' target='_blank'>".lang('VIEW_PROFILE')." <span class='glyphicon glyphicon-link'></span></a></td>
								<td>".$roles[$u->roles]."</td>
								<td>".$status."</td>
								<td>
									<a href='#' title='' class='btn btn-primary edit' id='".$u->id."' onclick='return false;'><span class='glyphicon glyphicon-pencil'></span> ".lang('EDIT')."</a>
									<a href='users.php?action=delete&userId=".$u->id."' title='' class='btn btn-danger delete' id='".$u->id."' onclick='return confirm(\"".lang('DELETE_USER_CONFIRMATION')."\");'><span class='glyphicon glyphicon-trash'></span> ".lang('DELETE')."</a>
								</td>
							</tr>";
						}
					}
				?>
			</tbody>
		</table>
		</form>
	</div>
</div>
			
<?php $template->footer(); ?>
