<?php 
// set the headers origin
header('Access-Control-Allow-Origin: *');  
header('Content-Type: application/json');

// Initial files
require "../core/init.php";

// Get user information
if(Input::Get("userId") && Input::Get("action") == "get"){
	// "id","username","email","roles"
	$user = new User();
	// Only the admin has the permission to request user info
	if($user->haspermission("admin")){
		if($user->Find(Input::Get("userId"))){
			$userData = array(
				"id" => $user->data()->id,
				"username" => escape($user->data()->username),
				"email" => escape($user->data()->email),
				"roles" => $user->data()->roles
			);
			echo json_encode($userData,JSON_PRETTY_PRINT);
		}
	}else{
		echo json_encode(array("You do not have enough permission to request user info"),JSON_PRETTY_PRINT);
	}
}

// save changes
if(Input::Get("userId") && Input::Get("action") == "edit"){
	$errors = array();
	
	$user = new User();
	if($user->haspermission("admin")){

	$validate = new Validate();
	
	$user->Find(Input::Get("userId"));
	
	$_POST['roles'] = Input::Get("roles");
	$validation = $validate->check($_POST, array(
			'roles' => array(
				'disp_text' => lang('ROLE'),
				'required' => true,
				'inArray' => array(1,2),
				)
	));
		
	if(Input::Get("email") != $user->data()->email){
		$_POST['email'] = Input::Get("email");
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
		$_POST['password'] = Input::Get("password");
		$_POST['repassword'] = Input::Get("repassword");
		$validation = $validate->check($_POST, array(
				'password' => array(
					'disp_text' => lang('PASSWORD'),
					'min' => '4',
					'max' => '32',
					),
				'repassword' => array(
					'disp_text' => lang('RE_ENTER_PASSWORD'),
					'required' => true,
					'matches' => 'password',
					)
		));
	}

	if(isset($validation)){
		if($validation->passed()){
			foreach($validation->errors() as $error){
				$errors[] = $error;
			}
			try{
				
				$user->update(array('roles' => Input::get('roles')),$user->data()->id);
				
				// Update email
				if(Input::Get("email")){
					$user->update(array('email' => Input::get('email')),$user->data()->id);
				} 
				
				// Update password
			if(Input::Get("password")){
				$salt = Hash::salt(32);
				$user->update(array('password' => Hash::make(Input::get('password'), $salt),"salt" => $salt),$user->data()->id);
			}
				
			$errors[] = true;	

			}catch(Exception $e){
				echo $e->getMessage();
			}
		}else{
			foreach($validation->errors() as $error){
				$errors[] = $error;
			}
		}
	}
	}else{
		$errors[] = "You do not have enough permission to edit user info";
	}
	echo json_encode($errors,JSON_PRETTY_PRINT);
}


// add new user
if(Input::Get("action") == "add"){
	$errors = array();
	$user = new User();
	if($user->haspermission("admin")){
		$_POST['username'] = Input::Get("username");
		$_POST['password'] = Input::Get("password");
		$_POST['repassword'] = Input::Get("repassword");
		$_POST['email'] = Input::Get("email");
		$_POST['roles'] = Input::Get("roles");
			
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'username' => array(
					'disp_text' => lang('USERNAME'),
					'required' => true,
					'min' => 2,
					'max' => 32,
					'unique' => 'users'
					),
				'password' => array(
					'disp_text' => lang('PASSWORD'),
					'required' => true,
					'min' => 6,
					'max' => 16
					),
				'repassword' => array(
					'disp_text' => lang('RE_ENTER_PASSWORD'),
					'required' => true,
					'matches' => 'password'
					),
				'email' => array(
					'disp_text' => lang('EMAIL'),
					'required' => true,
					'unique' => 'users',
					'valid_email' => true
					),
				'roles' => array(
					'disp_text' => lang('ROLE'),
					'required' => true,
					'inArray' => array(1,2),
					),
				));

		if($validation->passed()){
			$salt = Hash::salt(32);
			try{
				// Activation code
				$code = Token::generate();
				
				$user->create(array(
					'username' => Input::get('username'),
					'password' => Hash::make(Input::get('password'), $salt),
					'salt' => $salt,
					'email' => Input::get('email'),
					'roles' => Input::get('roles'),
					'act_code' => $code,
					'active' => 1,
					'signup' => date('Y-m-d H:i:s')
				));
					
				$errors[] = true;
				
			}catch(Exception $e){
				$errors[] = lang('OPERATION_FAILED_TRY_AGAIN');
			}
		}else{
			foreach($validation->errors() as $error){
				$errors[] = $error;
			}
		}
	}else{
		$errors[] = "You do not have enough permission to add user info";
	}
	echo json_encode($errors,JSON_PRETTY_PRINT);
}


?>