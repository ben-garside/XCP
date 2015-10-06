<?php 

require("../php/init.php");

$mainUser = new User();
if(!$mainUser->inRole(1)){
	die('nope. not allowed!');
}

$userId = Input::get('id');

$user = new User($userId);
$oldRoles = $user->showRoles($userId);

if($userData = Input::get('info')) {
	$newRoles = $userData['roles'];
	unset($userData['roles']);
	if(Input::get('add')) {
		echo 'ADD USER<br>';
		print_r($userData);
		echo '<br>';
		print_r($newRoles);
		$salt = Hash::salt(32);
		$user->create(array(
					'username' 		=> $userData['username'],
					'password' 		=> Hash::make($userData['password'], $salt),
					'salt' 			=> $salt,
					'name_first' 	=> $userData['name_first'],
					'name_last' 	=> $userData['name_last'],
					'email' 		=> $userData['email'],
					'joined' 		=> date('Y-m-d H:i:s'),
					'group_id' 		=> 1
				));
	} else {
		try {
			$user->update($userData, $userId);
		} catch (Exception $e) {
			echo $e;
			$error = true;
		}
		if(!$error) {
			foreach ($oldRoles as $key => $oldRole) {
				if(!in_array($key, $newRoles)){
					User::removeFromRole($userId, $key);
				}
			}

			foreach ($newRoles as $key => $newRole) {
				if(!array_key_exists($newRole, $oldRoles)){
					User::addToRole($userId, $newRole);
				}
			}	
		}		
	}
}

if($password = Input::get('password')) {
	// CHange password
	$salt = Hash::salt(32);
		try {
	$user->update(array(
		'password' => Hash::make($password, $salt),
		'salt' => $salt
	),$userId);
		} catch (Exception $e) {
			print_r($e);
		}

}	

if(Input::get('delete')) {
	$user->delete();
	foreach ($oldRoles as $key => $oldRole) {
		User::removeFromRole($userId, $key);
	}		
}