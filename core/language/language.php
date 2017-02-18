<?php   if (!defined('ABSPATH')) exit('No direct script access allowed');

$defaultLangFile 	= DIR_LANG . "/languages/" . strtolower( DEFAULT_LANG ) . ".php";

if( defined('USER_LANG') ){
	
	$langFile 			= DIR_LANG . "/languages/" . strtolower( USER_LANG ) . ".php";
	
	if( file_exists( $langFile )){
		require_once( $langFile );
	}else if( file_exists( $defaultLangFile )){
		require_once( $defaultLangFile );
	}else{
		throw new Exception("Language file not found!");
	}
}else{
	if( file_exists( $defaultLangFile )){
		require_once( $defaultLangFile );
	}else{
		throw new Exception("Language file not found!");
	}
}


GenerateJsLang();
	
function lang($string){
	global $lang;
	if(!isset($lang[$string])){
		return html_entity_decode($string);
	}

	if(trim($lang[$string]) == ""){
		return html_entity_decode($string);
	}

	return html_entity_decode($lang[$string]);
}

// Generate js lang file
function GenerateJsLang(){
	require dirname(__FILE__) . "/jslang.php";
	$content = "var langs = ". json_encode($jsLang) . ";\n";
	$fp = fopen(ABSPATH.'core/js/lang.js', 'w');
	flock($fp, LOCK_EX);
	ftruncate($fp, 0);
	fseek($fp, 0);
	fwrite($fp, $content);
	flock($fp, LOCK_UN);
	fclose($fp);
}

?>