<?php
require "../core/init.php";
if(isset($_POST["groupID"]) && isset($_POST["postType"])){

	$fb = new Facebook();
	$spintax = new Spintax();
	$params = array();

	if(Input::Get("message")){
		$message = $spintax->get(Input::Get("message"));
		// If is unique post enabled
		if(isset($user->Options()->uniquePost)){
			if($user->Options()->uniquePost == 1){
				$uniqueID = strtoupper(uniqid()); // Generate unique ID
				$message .= "\n\n". $uniqueID;
			}
		}

		$params[] = "message=".urlencode($message);
	}

	if(Input::Get("postType") == "link"){

		$link = $spintax->get(Input::Get("link"));

		// If is unique post link enabled
		if(isset($user->Options()->uniqueLink)){
			if($user->Options()->uniqueLink == 1){
				$uniqueID = strtoupper(uniqid()); // Generate unique ID
				if (strpos($link, '?') !== false) {
					$link = rtrim($link, "/")."&post_".$uniqueID."=".Input::get("groupID");
				}else{
					$link = rtrim($link, "/")."/?post_".$uniqueID."=".Input::get("groupID");
				}
			}
		}

		$params[] = "link=".urlencode($link);

		if(Input::Get("picture")) $params[] = "picture=".urlencode($spintax->get(Input::Get("picture")));
		if(Input::Get("name")) $params[] = "name=".urlencode($spintax->get(Input::Get("name")));
		if(Input::Get("caption")) $params[] = "caption=".urlencode($spintax->get(Input::Get("caption")));
		if(Input::Get("description")) $params[] = "description=".urlencode($spintax->get(Input::Get("description")));

	}else if (Input::Get("postType") == "image") {
		$params[] = "url=".$spintax->get(Input::Get("image"));
	}else if (Input::Get("postType") == "video") {
		$params[] = "file_url=".$spintax->get(Input::Get("file_url"));
		if(Input::Get("message")) $params[] = "title=".urlencode($spintax->get(Input::Get("message")));
		if(Input::Get("description")) $params[] = "description=".urlencode($spintax->get(Input::Get("description")));
	}


	if($result = $fb->Post(Input::get("groupID"),$params,Input::Get("postType"))){
		echo json_encode($result,128);
	}

}else{
	echo json_encode(array('error' => lang('EMPTY_REQUEST')),128);
}
?>