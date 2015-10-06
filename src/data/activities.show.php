<?php 

require("../php/init.php");
$actId = Input::get('id');
if(is_numeric($actId)){
	$outArray = array();
	$act = new Activity;
	$activityInfo = $act->getAllActivities($actId);
	$activityInfo->roles = $act->showRoles($actId);
	header("Content-type: application/json");
	print(json_encode($activityInfo));	
}


?>