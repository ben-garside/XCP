<?php 

require("../php/init.php");
$db = DB::getInstance();
$xcpid = Input::get('xcpid');

$outArray = array();


$sql = "SELECT ACT_STATUS_2.name activityName
,ACT_DETAIL.SHORT_NAME statusName
,ACT_AUDIT_2.ID id
,activity + ':' + ACT_AUDIT_2.STATUS auditStage
,initUser.username initUsername
,startedBy initUserId
,initUser.name_first initNameFirst
,initUser.name_last initNameLast
,startedOn initDate
,alloUser.username alloUsername
,allocatedTo alloUserId
,alloUser.name_first alloNameFirst
,alloUser.name_last alloNameLast
,allocatedOn alloDate
,info
,endedBy endUserId
,endUser.username endUswername
,endUser.name_first endNameFirst
,endUser.name_last endNameLast
,endedOn endDate
FROM ACT_AUDIT_2
LEFT JOIN USERS initUser  ON initUser.id = ACT_AUDIT_2.startedBy
LEFT JOIN USERS alloUser  ON alloUser.id = allocatedTo
LEFT JOIN USERS endUser  ON endUser.id = endedBy
LEFT JOIN ACT_STATUS_2 ON ACT_STATUS_2.status = ACT_AUDIT_2.STATUS AND ACT_STATUS_2.act = ACT_AUDIT_2.activity
LEFT JOIN ACT_DETAIL ON ACT_DETAIL.ID = ACT_AUDIT_2.activity
WHERE XCPID = '" . $xcpid . "'
ORDER BY ACT_AUDIT_2.ID DESC";

$data = $db->query($sql);					
$results = $data->results();



	foreach($results as $result) {	
    if($result->initUserId == -1){
      $userPrint = "<em>System<br>" . date("d-m-Y", strtotime($result->initDate)) . "</em>";
    } elseif(!$result->initUserId || $result->initUserId == 0) {
      $userPrint = '';
    } else {
      $userPrint = ucfirst($result->initNameFirst) . " " . ucfirst($result->initNameLast) . "<br><span title='".$result->initDate."'>" . date("d-m-Y", strtotime($result->initDate)) . "</sapn>";
    }

    if($result->endUserId == -1){
      $endPrint = "<em>System<br>" . date("d-m-Y", strtotime($result->endDate)) . "</em>";
    } elseif(!$result->endUserId || $result->endUserId == 0) {
      $endPrint = '';
    } else {
      $endPrint = ucfirst($result->endNameFirst) . " " . ucfirst($result->endNameLast) . "<br><span title='".$result->endDate."'>" . date("d-m-Y", strtotime($result->endDate)) . "</sapn>";
    }

    if($result->alloUserId == -1){
      $userAlloPrint = "<em>System<br>" . date("d-m-Y", strtotime($result->alloDate)) . "</em>";
    } elseif(!$result->alloUserId || $result->alloUserId == 0) {
      $userAlloPrint = '';
    } else {
      $userAlloPrint = ucfirst($result->alloNameFirst) . " " . ucfirst($result->alloNameLast) . "<br><span title='".$result->alloDate."'>" . date("d-m-Y", strtotime($result->alloDate)) . "</sapn>";
    }
		$outArray[] = array(
            		$result->id,
            		$result->auditStage,
            		"<strong>" . $result->activityName . "</strong><br/>" . $result->statusName,
            		$userPrint,
                $userAlloPrint,
                $endPrint,
                $result->DATA
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