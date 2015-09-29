<?php 

require("../php/init.php");

$mainUser = new User();
if(!$mainUser->inRole(1)){
	die('nope. not allowed!');
}

$actId = Input::get('id');

$act = new Activity;
$oldRoles = $act->showRoles($actId);

if($userData = Input::get('info')) {
	$newRoles = $userData['roles'];
	unset($userData['roles']);
	if(Input::get('add')) {
		echo 'ADD USER<br>';
		// $salt = Hash::salt(32);
		// $user->create(array(
		// 			'username' 		=> $userData['username'],
		// 			'password' 		=> Hash::make($userData['password'], $salt),
		// 			'salt' 			=> $salt,
		// 			'name_first' 	=> $userData['name_first'],
		// 			'name_last' 	=> $userData['name_last'],
		// 			'email' 		=> $userData['email'],
		// 			'joined' 		=> date('Y-m-d H:i:s'),
		// 			'group_id' 		=> 1
		// 		));
	} else {
		try {
			Activity::updateActivity($actId, $userData);
		} catch (Exception $e) {
			echo $e;
			$error = true;
		}
		if(!$error) {
			foreach ($oldRoles as $key => $oldRole) {
				if(!in_array($key, $newRoles)){
					Activity::removeRole($actId, $key);
				}
			}

			foreach ($newRoles as $key => $newRole) {
				if(!array_key_exists($newRole, $oldRoles)){
					Activity::addRole($actId, $newRole);
				}
			}	
		}		
	}
}	

// if(Input::get('delete')) {
// 	$user->delete();
// 	foreach ($oldRoles as $key => $oldRole) {
// 		User::removeFromRole($actId, $key);
// 	}		
// }