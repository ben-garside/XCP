<?php 

require("../php/init.php");
$db = DB::getInstance();
$act = Input::get('act');

$outArray = array();

$streams = Activity::getStreams();
foreach ($streams as $stream) {
	$sql = "SELECT stream_id, COUNT(*) TOTAL,
					sum(case when allocatedTo IS NOT NULL  then 1 else 0 end) as ASSIGNED,
					sum(case when allocatedTo IS NULL then 1 else 0 end) as UNASSIGNED
			FROM mainData
			OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
			WHERE stream_id = ".$stream->id."
			GROUP BY stream_id";

	$data = $db->query($sql);
	$result = $data->first();
	if(!$assigned = $result->ASSIGNED){$assigned = 0;}
	if(!$unassigned = $result->UNASSIGNED){$unassigned = 0;}
	if(!$total = $result->TOTAL){$total = 0;}
	$outArray[] = array("Pipeline" 	=> $stream->id,
        				"Status" 	=> "ASSIGNED",
        				"Count" => $assigned,
	);
	$outArray[] = array("Pipeline" 	=> $stream->id,
        				"Status" 	=> "TOTAL",
        				"Count" => $total,
	);
	$outArray[] = array("Pipeline" 	=> $stream->id,
        				"Status" 	=> "UNASSIGNED",
        				"Count" => $unassigned,
	);
}




header("Content-type: application/json");
print(json_encode($outArray));

?>