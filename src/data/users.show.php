<?php 

require("../php/init.php");
$userId = Input::get('id');

if(is_numeric($userId)){
	$outArray = array();
	$userInfo = User::listUsers($userId);
	unset($userInfo->password);
	unset($userInfo->salt);
	unset($userInfo->joined);
	$userInfo->roles = User::showRoles($userId);
	header("Content-type: application/json");
	print(json_encode($userInfo));	
}


?>