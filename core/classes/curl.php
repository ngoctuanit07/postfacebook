<?php if (!defined('ABSPATH')) exit('No direct script access allowed');

class Curl{

	private $agent = "Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36";
	private $certs = 'facebook/HttpClients/certs/DigiCertHighAssuranceEVRootCA.pem';
	
	public static function Get($url,$query = array()){

		if(function_exists('curl_init')){

			$curl = new Curl();
			$ch = curl_init();
        	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_CAINFO, ABSPATH . $curl->certs);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_USERAGENT, $curl->agent);
			curl_setopt($ch, CURLOPT_URL,$url);

			try{
				$result = curl_exec($ch);
				// Closing
				curl_close($ch);
				return $result;
			}catch(Exception $ex){
				echo "Oops. Something went wrong. Please try again.";
			}

		}else{
			return file_get_contents($url);
		}

		return false;
	}
	
}
?>