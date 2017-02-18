<?php  if (!defined('ABSPATH')) exit('No direct script access allowed');
class Options
{
	public static function Get($key){
		$query = DB::getInstance()->queryGet("SELECT * FROM options");
		$options = array();
		foreach($query->results() as $opt){
			$options[$opt->option] = $opt->value;
		}
		return isset($options[$key]) ? $options[$key] : false;
	}

	public static function Update($params){
		foreach($params as $key => $value){
			if(Options::Get($key) === false){
				DB::getInstance()->INSERT("options",array('option'=>$key,'value'=>$value));
			}else{
				DB::getInstance()->UPDATE("options","option",$key,array('option'=>$key,'value'=>$value));
			}
		}
	}

	

	private static function GetDomain($url)
	{
	  $pieces = parse_url($url);
	  $domain = isset($pieces['host']) ? $pieces['host'] : '';
	  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
	    return $regs['domain'];
	  }
	  return false;
	}

	public static function CheckSiteUrl(){
		if(isset($_SERVER['SERVER_PROTOCOL'])){
			if(Options::GetDomain(Options::get('siteurl')) != Options::GetDomain(CurrentPath())){
				die(base64_decode("SXQgc2VlbXMgdGhhdCB5b3UgYXJlIHVzaW5nIGEgZGlmZmVyZW50IGRvbWFpbiwgWW91IG5lZWQgdG8gcmUtaW5zdGFsbCB0aGUgYXBwbGljYXRpb24uPGJyLz5JZiB5b3UgdGhpbmsgeW91J3JlIHNlZWluZyB0aGlzIGJ5IG1pc3Rha2UsIHBsZWFzZSBsZXQgdXMga25vdy4gc3VwcG9ydEBraW5ncG9zdGVyLm5ldA=="));
			}
		}
	}

	public static function CheckForUpdate(){
		$user = new user();
		if($user->HasPermission('admin')){
			// check if already checked for update
			if(Cookie::exists("app_version")){
				$check = Cookie::get("app_version");
			}else{
				$check = Curl::get("http://kingposter.net/update_test/?source=".Options::get('siteurl'));
				Cookie::put("app_version", $check,60*60*24*15);
			}
			$update = json_decode($check);
			if(isset($update->version)){
				if(VERSION < $update->version){
					if(!defined("update")){ define('UPDATE',true); } 
					if(isset($update->message)){Session::Flash("home","warning",$update->message,true);}
				}
			}
		}
	}
}
?>