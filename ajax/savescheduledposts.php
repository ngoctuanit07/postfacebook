<?php 
/*
|-----------------------------------------
| Initial the environment
|-----------------------------------------
|
*/
require "../core/init.php";
/*
| Validate the & Save the scheduled post
|
*/
$validate = new Validate();
$validation = $validate->check($_POST, array(
	'scheduledPostTime' => array(
		'disp_text' => lang('SCHEDULE_POST_START'),
		'required' => true
		),
	'post_interval' => array(
		'disp_text' => lang('POST_INTERVAL'),
		'required' => true
		),
	'targets' => array(
		'disp_text' => lang('TARGETS_GROUPS'),
		'required' => true
		),
	'post_app' => array(
		'disp_text' => lang('FB_APP'),
		'required' => true
		),
));

if($validation->passed()){
	
		try{
			$user = new User();
			$fbaccount = new FbAccount();

			if(!$fbaccount->UserDefaultFbAccount()){
				throw new Exception(lang('No_FB_ACCOUNT_SELECTED'));
			}

			try{
				$next_post_time = new DateTime(Input::Get("scheduledPostTime"));
			}catch(Exception $e){
				throw new Exception(lang('SCHEDULED_POST_INVALID_DATE')." (".Input::Get("scheduledPostTime").") ".lang('IS_NOT_VALID_DATE_TIME'));
			}
			
			// check if the supplied post exists
			$posts = new Posts();
			if(!$posts->get(Input::Get("post_id"))){
				throw new Exception(lang('POST_NOT_FOUND_SAVE_AND_TRY_AGAIN'));
				exit();
			}
			
			$params = array(
				'userid' 			=> $user->data()->id,
				'next_post_time' 	=> $next_post_time->format('Y-m-d H:i'),
				'post_interval'		=> Input::Get("post_interval"),
				'next_target'		=> 0,
				'targets'			=> Input::Get("targets"),
				'post_id'			=> Input::Get("post_id"),
				'post_app'			=> Input::Get("post_app"),
				'fb_account'		=> $fbaccount->UserDefaultFbAccount(),
				'pause'				=> 0,
				'status'			=> 0
			);
			
			if(ScheduledPosts::save($params)){
				echo "true";
			}
			
		}catch(Exception $e){
			echo $e->GetMessage();
		}
}else{
	echo "<ul>";
	foreach($validation->errors() as $error){
		echo "<li>".$error."</li>";
	}
	echo "</ul>";
}


?>