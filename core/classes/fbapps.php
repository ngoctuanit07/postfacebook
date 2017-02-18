<?php 
class fbapps {
	
	private $appId = null;
	private $appSecret = null;
	private $appName = null;
	private $adminAccessToken = null;
	private $appAuthLink = null;

	public function getAppId(){
		return $this->appId;
	}

	public function setAppId($appId){
		$this->appId = $appId;
	}

	public function getAppSecret(){
		return $this->appSecret;
	}

	public function setAppSecret($appSecret){
		$this->appSecret = $appSecret;
	}

	public function getAppName(){
		return $this->appName;
	}

	public function setAppName($appName){
		$this->appName = $appName;
	}

	public function getAdminAccessToken(){
		return $this->adminAccessToken;
	}

	public function setAdminAccessToken($adminAccessToken){
		$this->adminAccessToken = adminAccessToken;
	}

	public function getAppAuthLink(){
		return $this->appAuthLink;
	}

	public function setAppAuthLink($appAuthLink){
		$this->appAuthLink = appAuthLink;
	}

	public function get($fbapp){

		$fbapp = DB::GetInstance()->QueryGet("SELECT * FROM fbapps WHERE appid = ? ",array($fbapp));

		if($fbapp->count()){

			$this->appId 			= $fbapp->first()->appid;
			$this->appSecret 		= $fbapp->first()->app_secret;
			$this->appName 			= $fbapp->first()->app_name;
			$this->adminAccessToken = $fbapp->first()->admin_access_token;
			$this->appAuthLink 		= $fbapp->first()->app_auth_link;

		}

		return $this;
	}

	public function  getAll(){

		$fbapps = new ArrayObject();	
		
		$res = DB::GetInstance()->QueryGet("SELECT * FROM fbapps");

		if($res->count()){
			foreach($res->results() as $f){
				$tempfb = new fbapps();

				$tempfb->appId 				= isset($f->appid) ? $f->appid : null ;
				$tempfb->appSecret 			= isset($f->app_secret) ? $f->app_secret : null ;
				$tempfb->appName 			= isset($f->app_name) ? $f->app_name : null ;
				$tempfb->adminAccessToken 	= isset($f->admin_access_token) ? $f->admin_access_token : null ;
				$tempfb->appAuthLink 		= isset($f->app_auth_link) ? $f->app_auth_link : null ;
				
				$fbapps[] 					= $tempfb;
			}
		}

		return $fbapps;
	}

	public function  appType($appId){

		// 1 : normal app
		// 2 : Graph API Explorer
		// 3 : public app

		if($appId == "145634995501895")
			return 2;

		$res = DB::GetInstance()->QueryGet("SELECT app_auth_link FROM fbapps WHERE appid = ? ",array($appId));
		
		if(isset($res->first()->app_auth_link)){
			if($res->first()->app_auth_link != "")
				return 3;
		}

		return 1;
				
	}

}
?>