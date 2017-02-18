<?php
class Input{
	public static function exists($type = 'post'){
		switch ($type) {
			case 'post':
				return (!empty($_POST)) ? true : false;
				break;
			case 'get':
				return (!empty($_GET)) ? true : false;
				break;
			
			default:
				return true;
				break;
		}
	}

	public static function get($item,$type = null){
		if($type){
			if($type == "POST" && isset($_POST[$item])){
				return $_POST[$item];
			}else if($type == "GET"  && isset($_GET[$item])){
				return $_GET[$item];
			}else if($type == "FILES" && isset($_FILES[$item])){
				return $_FILES[$item];
			}
		}else{
			if(isset($_POST[$item])){
			return $_POST[$item];
			}else if(isset($_GET[$item])){
				return $_GET[$item];
			}else if(isset($_FILES[$item])){
				return $_FILES[$item];
			}
		}
		
		return '';
	}

}
?>