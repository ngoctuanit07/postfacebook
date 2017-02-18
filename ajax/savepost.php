<?php
require "../core/init.php";
if(Input::Get("postType",'POST') && Input::Get("post_title",'POST') && Input::Get("action",'POST') == 'add'){

	$params = array();
	
	$params['message'] 		= trim(Input::Get("message"));

	if(Input::Get("postType") == "link" ){
		$params['link'] 	= trim(Input::Get("link"));
		$params['picture']	= trim(Input::Get("picture"));
		$params['name'] 	= trim(Input::Get("name"));
		$params['caption'] 	= trim(Input::Get("caption"));
		$params['description']= trim(Input::Get("description"));
	}else if(Input::Get("postType") == "image" ){
		$params['image']= trim(Input::Get("image"));
	}else if(Input::Get("postType") == "video" ){
		$params['video']= trim(Input::Get("video"));
		$params['description']= trim(Input::Get("description"));
	}
	
	try{
		echo Posts::SavePost($params,Input::Get("post_title"));
	}catch(Exception $ex){
		echo $ex->GetMessage();
	}

}

if(Input::Get("postType",'POST') && Input::Get("postId",'POST') && Input::Get("action",'POST') == 'update'){

	$params = array();
	
	$params['message'] 		= trim(Input::Get("message"));

	if(Input::Get("postType") == "link" ){
		$params['link'] 	= trim(Input::Get("link"));
		$params['picture']	= trim(Input::Get("picture"));
		$params['name'] 	= trim(Input::Get("name"));
		$params['caption'] 	= trim(Input::Get("caption"));
		$params['description']= trim(Input::Get("description"));
	}else if(Input::Get("postType") == "image" ){
		$params['image']= trim(Input::Get("image"));
	}else if(Input::Get("postType") == "video" ){
		$params['video']= trim(Input::Get("video"));
		$params['description']= trim(Input::Get("description"));
	}

	try{
		if(Posts::UpdatePost($params,Input::Get("postId"))){
			echo "true";
		}else{
			echo "Failed to update the post";
		}
	}catch(Exception $ex){
		echo $ex->GetMessage();
	}

}

?>