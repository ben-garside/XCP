<?php 

require("../php/init.php");

$db = DB::getInstance();
$type = Input::get('type');
$act = Input::get('act');
$uid = Input::get('uid');

$outArray = array();


$sql = "SELECT * FROM FEED_EXCLUTION";

$data = $db->query($sql);					
$results = $data->results();

	foreach($results as $result) {	
		$outArray[] = array(
            		$result->EXCLUTION_ID,
            		$result->UPI,
            		$result->FEED_ID,
            		$result->DT_ADDED,
            		$result->USER_ID,
            		$result->COMMENT,
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