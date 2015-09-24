<?php 

require("../php/init.php");
$userId = Input::get('id');
$userData = Input::get('info');

$roles = $userData['roles'];
unset($userData['roles']);
$user = new User($userId);

try {
	$user->update($userData, $userId);
} catch (Exception $e) {
	echo $e;
}
