<?php 
class Facebook{
	
	private $_db = null;
	private $_groups = null;
	private $_app_id = null;
	private $_app_secret = null;
	
	/*
	|--------------------------------------------------------------------------
	| Set the accessToken
	| get user info 
	| get list of groups
	|--------------------------------------------------------------------------
	|
	*/
	public function __construct(){
		$this->_db = DB::GetInstance();
	}

	public static function GetUserFromAccessToken($accessToken){
		$data = Curl::Get("https://graph.facebook.com/me?&access_token=".$accessToken);
		if($data){
			return json_decode($data);
		}
		return null;
	}
	/*
	|--------------------------------------------------------------------------
	| Set the accessToken Check if the current acces token is valid
	|--------------------------------------------------------------------------
	|
	*/
	public function IsATValid($accessToken){
		
		if(empty($accessToken)) return false;
		
		$data = Curl::Get("https://graph.facebook.com/oauth/access_token_info?access_token=".$accessToken);
		if ($json = json_decode($data, true)) {
			if (array_key_exists("access_token",$json)){
				return $json['access_token'] == "" ? false : true;
			}
		} 
		return false;
	}
	
	public function FbUserIdFromAt($accessToken){
		$data = Curl::get("https://graph.facebook.com/me?access_token=".$accessToken);
		if ($json = json_decode($data, true)) {
			if (!array_key_exists("error",$json)){
				return $json['id'];
			}
		} 
		return false;
	}
	
	private function FbAppUserHasRole($FbUserId,$app_id,$app_secret){
		$data = json_decode(Curl::get("https://graph.facebook.com/".$app_id."/roles?fields=user,role&access_token=".$app_id."|".$app_secret."&method=get"));

		foreach($data->data as $user){
			if($user->user == $FbUserId){
				return $user->role;
			}
		}
		return false;
	}

	/*
	|--------------------------------------------------------------------------
	| Get the list of groups of the current user
	|--------------------------------------------------------------------------
	|
	*/ 
	public function LoadFbGroups($accessToken){
		$data = Curl::Get("https://graph.facebook.com/me/groups?fields=id,name,privacy&access_token=".$accessToken."&method=GET");
		
		if(isset(json_decode($data)->data)){
			return json_encode(json_decode($data)->data);
		}
		return false;
	}
	/*
	|--------------------------------------------------------------------------
	| Post to facebook group and return result
	| @return type array
	|--------------------------------------------------------------------------
	|
	*/ 
	public function Post($target,$params,$postType,$accessToken = null){

		$graph = "https://graph.facebook.com/";

		if($accessToken == null){
			$params[] = "access_token=".$this->GetAccessToken();
		}else{
			$params[] = "access_token=".$accessToken;
		}
		
		$params[] = "method=post";
		
		switch ($postType) {
			case 'image':
				$edge = $target."/photos/";
				break;
			case 'video':
				$edge = $target."/videos/";
				break;
			default:
				$edge = $target."/feed/";
				break;
		}

		// Generate the post link
		$postLink = $graph.$edge."?".implode("&",$params);

		$status = array();

		if(!$result = Curl::get($postLink)){
			$status["status"] = "error";
				// Mostly this could be a connection error if the script is on localserver
			$status["message"] = "An unexpected error occurred : Could not connect to Facebook Graph". $postLink;
			return false;
		}
		
		if ($json = json_decode($result, true)) {
			if (array_key_exists("error",$json)){
				$status["status"] = "error";
				$status["message"] = $json["error"]["message"];
			} elseif (array_key_exists("id",$json)){ 
				$status["status"] = "success";
				// get post id
				if($postType == 'image'){
					$status["id"] = substr(strrchr($json["post_id"], '_'), 1);
				}else if($postType == 'video'){
					$status["id"] = $json["id"];
				}else{
					$status["id"] = substr(strrchr($json["id"], '_'), 1);
				}
			} else {
				$status["status"] = "error";
				// Mostly this could be a connection error if the script is on localserver
				$status["message"] = "An unexpected error occurred : Check your internet connection". $postLink;
			}
		} else {
			$status["status"] = "error";
			// Mostly this could be a connection error if the script is on localserver
			$status["message"] = "An unexpected error occurred : Check your internet connection". $postLink;
		}
		return $status;
	}
	
	public function AppDetails($app_id){
		$appDetails = "https://graph.facebook.com/".$app_id;
		
		if(!$result = Curl::get($appDetails))
			return false;
			
		if ($json = json_decode($result)) {
			if(!isset($json->error)){
				return $json;
			}
		}
		return false;
	}
	
	public function AppDetailsFromAt($accessToken){
		$appDetails = "https://graph.facebook.com/app/?access_token=".$accessToken;

		if(!$result = Curl::get($appDetails))
			return false;
			
		if ($json = json_decode($result)) {
			if(!isset($json->error)){
				return $json;
			}
		}
		return false;
	}
	
	public static function App($app_id){
		$app = DB::GetInstance()->QueryGet("SELECT * FROM fbapps WHERE appid = ? ",array($app_id));
		if($app->count()){
			return $app->first();
		}
		return false;
	}
	
	private function FbAppAuth($app_id,$app_secret,$redirect,$oldApi){
		$fb = new Facebook\Facebook([
					'app_id' => $app_id,
					'app_secret' => $app_secret,
					'default_graph_version' => 'v2.4',
				]);

			$helper = $fb->getRedirectLoginHelper();
			
			try {
				$accessToken = $helper->getAccessToken();
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// When Graph returns an error
				throw new Exception($e->getMessage());
				return false;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// When validation fails or other local issues
				throw new Exception($e->getMessage());
				return false;
			}

			if(Input::Get('state','GET') && Input::Get('code','GET')){
				return $accessToken;
			}else if(Input::Get('error_message')){
				throw new Exception(Input::Get('error_message','GET'));
			}{
				
				$perms = array();
				$perms[] = "publish_actions";
				$perms[] = "public_profile";
				if($oldApi == "true") $perms[] = "user_groups";
				Redirect::To($helper->getLoginUrl($redirect,$perms));
			}
	}
	
	public function FbAuth($app_id = null,$app_secret = null,$redirect = null,$oldApi = null){
	
		if($app_id == null || $app_secret == null || $redirect == null){
			throw new Exception("Required parameters not supplied!");
		}else{
			$user = new user();
			
			// Get admin access token
			$adminAccessToken = $this->_db->QueryGet("SELECT admin_access_token FROM fbapps  WHERE appid = ? ",array($app_id))->first()->admin_access_token;
			
			// Get app access token
			$accessToken = $this->FbAppAuth($app_id,$app_secret,$redirect,$oldApi);

			// Check if the access token is valid
			if($this->IsATValid($adminAccessToken)){

				$fb_account = new FbAccount();
				if($fb_account->UserDefaultFbAccount()){

					// Store user app info
					if($this->GetAccessToken($app_id)){
						$this->UpdateAccessToken($user->data()->id,$app_id,$fb_account->UserDefaultFbAccount(),$accessToken);
					}else{
						$this->SaveAccessToken($user->data()->id,$app_id,$fb_account->UserDefaultFbAccount(),$accessToken);
					}	
						
				}else{
					throw new Exception(lang('NO_FB_ACCOUNT_SELECTED'));
				}
				
				// Check if the user is an admin of the facebook app otherwise add him ass a tester
				if($this->FbAppUserHasRole($this->FbUserIdFromAt($accessToken),$app_id,$app_secret) != "administrators"){
					if(!$this->Invite($app_id,$this->FbUserIdFromAt($accessToken),$adminAccessToken)){
						throw new Exception("Unable to add your facebook account as a tester.");
					}else{
						echo "<div class='alerts alert alert-info'>
						<p class='alerttext'>You will recive a developer requests, before you can post you must confirm the request.</p>
						<a href='https://developers.facebook.com/requests/' target='_blank'>https://developers.facebook.com/requests/.</a>
						</div>";
					}
				}
				
			}else if($user->HasPermission("admin")){

				// Check if the user is an admin of the facebook app
				if($this->FbAppUserHasRole($this->FbUserIdFromAt($accessToken),$app_id,$app_secret) === "administrators"){
					$fb_account = new FbAccount();
					if($fb_account->UserDefaultFbAccount()){

						// Store user app info
						if($this->GetAccessToken($app_id)){
							$this->UpdateAccessToken($user->data()->id,$app_id,$fb_account->UserDefaultFbAccount(),$accessToken);
						}else{
							$this->SaveAccessToken($user->data()->id,$app_id,$fb_account->UserDefaultFbAccount(),$accessToken);
						}	
							
					}else{
						throw new Exception(lang('NO_FB_ACCOUNT_SELECTED'));
					}

					// Store the app admin access token
					$this->_db->Update("fbapps","appid",$app_id,array("admin_access_token"=>$accessToken));
		
				}else{
					throw new Exception("The admin must authorized this application first!");
				}
			}else{
				throw new Exception("The admin must authorized this application first!");
			}
		} // End params check
	}
	
	public function Invite($app_id,$fbUserId,$accessToken){
		// Invite link
		$url = "https://graph.facebook.com/".$app_id."/roles?user=".$fbUserId."&role=testers&access_token=".$accessToken."&method=POST";
		
		if(!$result = Curl::get($url))
			return false;
		if ($json = json_decode($result,true)) {
			if(array_key_exists("success",$json)){
				if($json['success'] == "true"){
					return true;
				}
			}
		}
		return false;

	}
	
	public function SaveAccessToken($userId,$app_id,$fb_id,$accessToken){
		$access_token_date = date('Y-m-d H:i:s');
		$this->_db->Insert("user_fbapp",
			array(
				'userid' => $userId,
				'appid' => $app_id,
				'fb_id' => $fb_id,
				'access_token' => $accessToken,
				'access_token_date' => $access_token_date
			));
	}
	
	public function UpdateAccessToken($userId,$app_id,$fb_id,$accessToken){
		$access_token_date = date('Y-m-d H:i:s');
		$this->_db->Query("UPDATE user_fbapp set access_token = ? , access_token_date = '$access_token_date' WHERE userid  = ? AND appid = ? AND fb_id = ? ",array($accessToken,$userId,$app_id,$fb_id));
	}
	
	public function AppsList(){
		$fbapps = $this->_db->QueryGet("SELECT appid,app_name FROM fbapps");
		if($fbapps->count() != 0){
			return $fbapps->results();
		}
		return false;
	}
	
	public function DeleteApp($app_id){
		$user = new User();
		if($user->hasPermission("admin")){
			try{
				$this->_db->Delete("fbapps",array("appid","=",$app_id));
				$this->_db->Delete("user_fbapp",array("appid","=",$app_id));
			}catch(Exception $ex){
				throw new Exception("Could not delete the app \n Error details : ".$ex->GetMessage());
			}
		}else{
			throw new Exception("You don't have permission to perform this action.");
		}
	}
	
	public function deauthorizeApp($app_id){
		$user = new User();
		try{
			$this->_db->Query("DELETE FROM user_fbapp WHERE appid = ? AND userid = ? ",array($app_id,$user->data()->id));
		}catch(Exception $ex){
			throw new Exception("Could not deauthorize the app \n Error details : ".$ex->GetMessage());
		}
	}

	public function getAccessToken($app_id = null,$fb_id = null,$userId = null){
		$fbaccount = new FbAccount();
		$user = new User();
		
		if($userId == null){
			$userId = $user->data()->id;
		}
		
		if($fb_id == null){
			$fb_id = $fbaccount->UserDefaultFbAccount();
		}

		if($app_id == null){
			$app_id = $fbaccount->UserFbAccountDefaultApp();
		}

		$fbAT = $this->_db->QueryGet("SELECT access_token FROM user_fbapp WHERE userid = ? AND appid = ? AND fb_id = ? ",array($userId,$app_id,$fb_id));
		if($fbAT->count() != 0){
			return $fbAT->first()->access_token;
		}
		return false;
	}
	
}
?>