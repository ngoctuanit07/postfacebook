<?php if (!defined('ABSPATH')) exit('No direct script access allowed');
class Hash{
	public static function make($string, $salt= ''){
		return hash('sha256', $string . $salt);
	}

	public static function salt($length){
		$intermediateSalt = md5(uniqid(rand(), true));
		return substr($intermediateSalt, 0, $length);
	}

	public static function unique(){
		return self::make(uniqid());
	}
}
?>