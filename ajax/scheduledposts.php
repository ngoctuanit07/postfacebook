<?php if (!defined('ABSPATH')) exit('No direct script access allowed');

if(!Session::Exists('scheduleInProcess')):

	// Set a session to prevent multi proccess at once
	Session::Put('scheduleInProcess',true);
	
	// Get posts that have status 0 (not completed) and pause = 0 and date <= current date
	$scheduledposts = new scheduledposts();

	$fb = new Facebook();
	$posts = new Posts();
	$spintax = new Spintax();

	foreach($scheduledposts->post() as $scheduled){
		
		$user = new User($scheduled->userid);

		// Set user timezone if the user defined the timezone
		if(isset($user->Options()->timezone)){
			if($user->Options()->timezone){
				date_default_timezone_set($user->Options()->timezone);
			}
		}

		// Check if the post date <= current datetime of the user
		// Get current time 
		$currentDateTime = new DateTime();
		$next_post_time = new DateTime($scheduled->next_post_time);

		if(strtotime($currentDateTime->format("Y-m-d H:i")) >= strtotime($next_post_time->format("Y-m-d H:i"))){
		
			// get the post 
			$post = $posts->GetPost($scheduled->post_id);

			// Post is ready
			if(count($post) == 0){
				logs::Save($scheduled->id,lang('POST_NOT_FOUND'));
			}else{

				// Send the post
				$params = array();	
				
				// Post param
				$postParam = json_decode($post->content);
				$postType = Posts::PostType($post->content);

				// Get list of groups
				$groups = json_decode($scheduled->targets,true);
				
				// Check if the current target is the last one
				if($scheduled->next_target+1 >= count($groups)){
					// This was the last target
					DB::GetInstance()->update("scheduledposts","id",$scheduled->id,array("status" => "1"));
				}else{
					// Update the scheduled
					$currentDateTime->modify("+".$scheduled->post_interval." minutes");
					// Set the next target
					DB::GetInstance()->update("scheduledposts","id",$scheduled->id,array("next_target" => $scheduled->next_target+1,"next_post_time" => $currentDateTime->format('Y-m-d H:i'),));
				}

				$message = $spintax->get($postParam->message);

				// If is unique post enabled
				if(isset($user->Options()->uniquePost)){
					if($user->Options()->uniquePost == 1){
						$uniqueID = strtoupper(uniqid()); // Generate unique ID
						$message .= "\n\n". $uniqueID;
					}
				}

				if($postParam->message != "") 	$params[] = "message=".urlencode($message);
				
				if($postType == "link"){
									
					$link = $spintax->get($postParam->link);

					// If is unique post enabled
					if(isset($user->Options()->uniqueLink)){
						if($user->Options()->uniqueLink == 1){
							$uniqueID = strtoupper(uniqid()); // Generate unique ID
							if (strpos($link, '?') !== false) {
								$link = rtrim($link, "/")."&post_".$uniqueID."=true";
							}else{
								$link = rtrim($link, "/")."/?post_".$uniqueID."=true";
							}
						}
					}

					$params[] = "link=".urlencode($link);
					if($postParam->picture != "") 	$params[] = "picture=".urlencode($spintax->get($postParam->picture));
					if($postParam->name != "") 		$params[] = "name=".urlencode($spintax->get($postParam->name));
					if($postParam->caption != "") 	$params[] = "caption=".urlencode($spintax->get($postParam->caption));
					if($postParam->description != "") $params[] = "description=".urlencode($spintax->get($postParam->description));
				}

				if($postType == "image"){
					$params[] = "url=".urlencode($spintax->get($postParam->image));
				}
				

				if($postType == "video"){
					$params[] = "file_url=".urlencode($spintax->get($postParam->video));
					if($postParam->description != "") $params[] = "description=".urlencode($spintax->get($postParam->description));
				}

				// Get app accessToken
				$accessToken = $fb->getAccessToken($scheduled->post_app,$scheduled->fb_account,$scheduled->userid);

				// Test access token
				if(!$fb->IsATValid($accessToken)){
					logs::Save($scheduled->id,lang('INVALID_ACCESS_TOKEN'));
				}else{
				
					// Send post and get the result
					$result = (object)$fb->Post($groups[$scheduled->next_target],$params,$postType,$accessToken);
					
					// Save log
					if(isset($result->status)){
						if(isset($result->id)){
							logs::Save($scheduled->id,"<a href='https://www.facebook.com/".$result->id."' target='_blank'><span class='glyphicon glyphicon-ok'></span> ".lang('VIEW_POST')." </a>");
						}else{
							if($groups[$scheduled->next_target] == "me")
								logs::Save($scheduled->id,"Your timeline - ".$result->message);
							else
								logs::Save($scheduled->id,$result->message." <a href='https://www.facebook.com/groups/".$groups[$scheduled->next_target]."' target='_blank'><span class='glyphicon glyphicon-eye-open'></span> ".lang('VISIT_GROUP')." </a>");
						}		
					}else{
						logs::Save($scheduled->id,lang('UNKNOWN_ERROR'));
					}

				} // Access token is valid

			} // The post is ready

		} // There is a post must be posted
	}
endif;

// Delete the session 
Session::Delete('scheduleInProcess');

?>