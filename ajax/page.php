<?php
require "../core/init.php";
// Get youtube json data
if(isset($_GET['youtube'])){
	$data = Page::youtube($_GET['youtube']);
	header('Content-Type: application/json');
	echo json_encode($data,JSON_PRETTY_PRINT);
}
?>