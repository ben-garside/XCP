<?php 

require("../php/init.php");

$outArray = array();
$results = User::listUsers();

foreach($results as $result) {	
	$outArray[] = array(
					$result->id,
					$result->username,
					$result->name_first,
					$result->name_last,
					$result->email,
					$result->joined,
					'<div class="pull-right"><button type="button" onclick="editUser(\''.$result->id.'\')" class="btn btn-primary btn-sm"><i class="fa fw fa-pencil"></i> Edit</button>
					<button type="button" onclick="changePassword(\''.$result->id.'\')" class="btn btn-success btn-sm"><i class="fa fw fa-pencil"></i> Change Password</button>
					<button type="button" onclick="deleteUser(\''.$result->id.'\')" class="btn btn-danger btn-sm"><i class="fa fw fa-trash-o"></i> Delete</button></div>'
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