<?php 

require("../php/init.php");
$db = DB::getInstance();
$act = Input::get('act');

$outArray = array();

$activites = Activity::getActivities();
foreach ($activites as $activity) {
	$sql = "SELECT ACT, COUNT(*) TOTAL,
					sum(case when allocatedTo IS NOT NULL  then 1 else 0 end) as ASSIGNED,
					sum(case when allocatedTo IS NULL then 1 else 0 end) as UNASSIGNED
			FROM mainData
			OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
			WHERE ACT = ".$activity->ID."
			GROUP BY ACT";

	$data = $db->query($sql);
	$result = $data->first();
	if(!$assigned = $result->ASSIGNED){$assigned = 0;}
	if(!$unassigned = $result->UNASSIGNED){$unassigned = 0;}
	if(!$total = $result->TOTAL){$total = 0;}
	$outArray[] = array("Activity" 	=> $activity->ID,
        				"Status" 	=> "ASSIGNED",
        				"Count" => $assigned,
	);
	$outArray[] = array("Activity" 	=> $activity->ID,
        				"Status" 	=> "TOTAL",
        				"Count" => $total,
	);
	$outArray[] = array("Activity" 	=> $activity->ID,
        				"Status" 	=> "UNASSIGNED",
        				"Count" => $unassigned,
	);
}




header("Content-type: application/json");
print(json_encode($outArray));

?>


