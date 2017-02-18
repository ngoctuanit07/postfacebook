<?php
class Session{
	public static function exists($name){
		return (isset($_SESSION[$name])) ? true : false;
	}

	public static function put($name, $value){
		return $_SESSION[$name] = $value;
	}

	public static function get($name){
		return $_SESSION[$name];
	}

	public static function delete($name){
		if(self::exists($name)){
			unset($_SESSION[$name]);
		}
	}

	public static function flash($name,$type = null,$string = '',$add = false){
		if(self::exists($name)){
			$flash = (array) self::get($name);
			if($add){
				$flash[] = array("type" => $type,"message" => $string);
				self::put($name, $flash);
			}else{
				self::delete($name);
			}
			return $flash;
		}else{
			self::put($name, array(array("type" => $type,"message" => $string)));
		}
	}
}
?>