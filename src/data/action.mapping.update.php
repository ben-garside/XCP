<?php
require("../php/init.php");
$type = Input::get('type');
switch ($type) {
	case 'field':
		$data = Input::get('data');
		$id = Input::get('id');
		try {
			Activity::updateActionRule($id, $data);
		} catch (Exception $e) {
			print_r($e);
		}
		break;
	case 'addRule':
		$data = Input::get('data');
		try {
			Activity::addActionRule($data);
		} catch (Exception $e) {
			print_r($e);
		}
		break;
	case 'info':
		$id = Input::get('id');
		$action_name = Input::get('action_name');
		$action_description = Input::get('action_description');
		$action_title = Input::get('action_title');
		$action_type = Input::get('action_type');
		try {
			Activity::updateActionInfo($id, $action_type, $action_title, $action_name, $action_description);
		} catch (Exception $e) {
			print_r($e);
		}
		break;
	case 'delete':
		$id = Input::get('id');
		try {
			Activity::deleteActionField($id);
		} catch (Exception $e) {
			print_r($e);
		}
		break;
	default:
		# code...
		break;
}
echo 'OK';