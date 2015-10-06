<?php 

require("../php/init.php");

$mainUser = new User();
if(!$mainUser->inRole(1)){
	die('nope. not allowed!');
}

$actId = Input::get('id');

// $act = new Activity;
// $oldRoles = $act->showRoles($actId);

// if($userData = Input::get('info')) {
// 	$newRoles = $userData['roles'];
// 	unset($userData['roles']);
// 	if(Input::get('add')) {
// 		Activity::addActivity($userData, $newRoles);
// 	} else {
// 		try {
// 			Activity::updateActivity($actId, $userData);
// 		} catch (Exception $e) {
// 			echo $e;
// 			$error = true;
// 		}
// 		if(!$error) {
// 			foreach ($oldRoles as $key => $oldRole) {
// 				if(!in_array($key, $newRoles)){
// 					Activity::removeRole($actId, $key);
// 				}
// 			}

// 			foreach ($newRoles as $key => $newRole) {
// 				if(!array_key_exists($newRole, $oldRoles)){
// 					Activity::addRole($actId, $newRole);
// 				}
// 			}	
// 		}		
// 	}
// }	

if(Input::get('delete')) {
	Activity::deleteStage($actId);
	//foreach ($oldRoles as $key => $oldRole) {
	//	Activity::removeRole($actId, $key);
	//}		
}