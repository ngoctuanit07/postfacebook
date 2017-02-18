<?php  if (!defined('ABSPATH')) exit('No direct script access allowed'); 

class mail {
	
	public static function send($sendTo,$subject,$message) {
	
		$header = "From: ".Options::Get('sitename')." <no-reply@".$_SERVER['HTTP_HOST'].">\r\n";
		$header .= "Content-type:text/html; charset=utf-8\r\n";

		try{
			mail($sendTo,$subject,$message,$header);
		}catch(Exception $ex){
			throw new Exception($ex);
		}
	}
}

?>
