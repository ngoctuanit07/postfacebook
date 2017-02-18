<?php
// Convert to html entitis
function escape($string){
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

function CurrentPath(){
	$url = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')); 
	$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
	return $protocol.$_SERVER['HTTP_HOST'].$url."/"; 
}

function httpReferer(){
	return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false ;
}

?>