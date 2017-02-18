<?php 

// Initial files
require "../core/init.php";

if(
	isset($_POST['category']) && 
	isset($_POST['group'])
){
	try{
		$fbaccount = new fbaccount();
		print $fbaccount->addGroupToCategory(json_decode($_POST['group'],true)[0],Input::get('category'));;
	}catch(Exeption $ex){
;		echo $ex->getMessage();
	}

}else{
	echo "empty request";
}


?>