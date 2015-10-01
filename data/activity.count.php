<?php 

require("../php/init.php");
$db = DB::getInstance();
$act = Input::get('act');

$outArray = array();

if(Input::get('uid') != 0)
	$uidPrint = " AND allocatedTo = " . Input::get('uid');

if(Input::get('feed') != 0)
	$feedPrint = " AND feed_id = " . Input::get('feed');

if(Input::get('stream') != 0)
	$streamPrint = " AND stream_id = " . Input::get('stream');

$sql = "SELECT activity, 
		sum(case when allocatedTo IS NOT NULL " . $feedPrint . " " . $uidPrint. " " . $streamPrint . " then 1 else 0 end) as STATUS_1,
		sum(case when allocatedTo IS NULL" . $feedPrint. " " . $streamPrint . " then 1 else 0 end) as STATUS_0
		FROM mainData
		OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT_2 WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
		LEFT JOIN USERS ON USERS.id = AUDIT.startedBy
		WHERE AUDIT.XCPID IS NOT NULL GROUP BY activity";
#echo  $sql;
$data = $db->query($sql);
$results = $data->results();

foreach($results as $result) {	
	$outArray[] = array(
        		$result->activity,
        		$result->STATUS_1,
        		$result->STATUS_0
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