<?php 

require("../php/init.php");
$db = DB::getInstance();
$xcpid = Input::get('xcpid');

$outArray = array();


$sql = "SELECT ACT_STATUS_2.name activityName
,ACT_DETAIL.SHORT_NAME statusName
,ACT_AUDIT.ID id
,ACT_AUDIT.ACT + ':' + ACT_AUDIT.STATUS auditStage
,initUser.username initUsername
,ACT_AUDIT.USER_ID initUserId
,initUser.name_first initNameFirst
,initUser.name_last initNameLast
,ACT_AUDIT.DATE initDate
,alloUser.username alloUsername
,allocatedTo alloUserId
,alloUser.name_first alloNameFirst
,alloUser.name_last alloNameLast
,ACT_AUDIT.allocatedOn alloDate
,ACT_AUDIT.DATA
FROM ACT_AUDIT
LEFT JOIN USERS initUser  ON initUser.id = ACT_AUDIT.USER_ID
LEFT JOIN USERS alloUser  ON alloUser.id = allocatedTo
LEFT JOIN ACT_STATUS_2 ON ACT_STATUS_2.status = ACT_AUDIT.STATUS AND ACT_STATUS_2.act = ACT_AUDIT.ACT
LEFT JOIN ACT_DETAIL ON ACT_DETAIL.ID = ACT_AUDIT.ACT
WHERE XCPID = '" . $xcpid . "'
ORDER BY ACT_AUDIT.ID DESC";

$data = $db->query($sql);					
$results = $data->results();



	foreach($results as $result) {	
    if($result->initUserId == -1){
      $userPrint = "<em>System<br>" . date("d-m-Y", strtotime($result->initDate)) . "</em>";
    } elseif(!$result->initUserId || $result->initUserId == 0) {
      $userPrint = '';
    } else {
      $userPrint = ucfirst($result->initNameFirst) . " " . ucfirst($result->initNameLast) . "<br>" . date("d-m-Y", strtotime($result->initDate));
    }

    if($result->alloUserId == -1){
      $userAlloPrint = "<em>System<br>" . date("d-m-Y", strtotime($result->alloDate)) . "</em>";
    } elseif(!$result->alloUserId || $result->alloUserId == 0) {
      $userAlloPrint = '';
    } else {
      $userAlloPrint = ucfirst($result->alloNameFirst) . " " . ucfirst($result->alloNameLast) . "<br>" . date("d-m-Y", strtotime($result->alloDate));
    }
		$outArray[] = array(
            		$result->id,
            		$result->auditStage,
            		"<strong>" . $result->activityName . "</strong><br/>" . $result->statusName,
            		$userPrint,
                $userAlloPrint,
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