<?php 

require("../php/init.php");

$db = DB::getInstance();
$type = Input::get('type');
$key = Input::get('key');

switch ($type) {
	case 'statusName':
		$actData = Activity::splitStage($key, ",");
		$out = Activity::getStatusDescription($actData['status'], $actData['activity']);
		break;

	case 'statusDescription':
		$actData = Activity::splitStage($key, ",");
		$out = Activity::getStatusDescriptionDescription($actData['status'], $actData['activity']);
		break;

	case 'persistantAssignment':
		$stages = split("\|", $key);
		$actDataFrom = Activity::splitStage($stages[0], ",");
		$actDataTo   = Activity::splitStage($stages[1], ",");
		$pipeline = $stages[2];

		$out = (Activity::maintainAssign($actDataFrom['activity'],$actDataFrom['status'],$actDataTo['activity'],$actDataTo['status'],$pipeline));
		break;

	case 'getAction':
		$stages = split("\|", $key);
		$actDataFrom = Activity::splitStage($stages[0], ",");
		$actDataTo   = Activity::splitStage($stages[1], ",");
		$pipeline = $stages[2];
		$out = (Activity::getAction($actDataFrom['activity'],$actDataFrom['status'],$actDataTo['activity'],$actDataTo['status'],$pipeline));
		break;
	
	default:
		$out = false;
		break;
}

if($out) {
	echo $out;
}