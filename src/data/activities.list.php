<?php 

require("../php/init.php");

$outArray = array();

$act = new Activity;
$activities = $act->getAllActivities();

foreach ($activities as $key => $activity) {
	$outArray[] = array(
					str_pad($activity->ID, 2, '0', STR_PAD_LEFT),
					$activity->SHORT_NAME,
					$activity->FULL_NAME,
					$activity->DESCRIPTION,
					'<div class="pull-right"><button class="btn btn-primary btn-sm" onclick="editActivity(\''.str_pad($activity->ID, 2, '0', STR_PAD_LEFT).'\')">Edit</button> <button class="btn btn-danger btn-sm" onclick="deleteActivity(\''.str_pad($activity->ID, 2, '0', STR_PAD_LEFT).'\')">Delete</button></div>'
	);
}

$response = array(
  'aaData' => $outArray,
  'iTotalRecords' => count($outArray),
  'iTotalDisplayRecords' => count($outArray)
);

header("Content-type: application/json");
print(json_encode($response));

?>