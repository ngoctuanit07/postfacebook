<?php
class Language {
	/*
	|------------------------------------
	| Available lang
	|------------------------------------
	|
	*/
	public static function getAvailableLangs(){
		$availableLang = array();
		foreach (new DirectoryIterator( DIR_LANG .'/languages') as $file) {
			if ($file->isFile()) {
					$pathinfo = pathinfo($file);
					$availableLang[] = $pathinfo['filename']; 
			}
		}
		return $availableLang;
	}
	
	public static function GetLangName(){
		echo DIR_LANG .'/languages/en_EN.php';
	}
	
}
?>