<?php 

require("../php/init.php");

$db = DB::getInstance();
$type = Input::get('type');
$act = Input::get('act');
$uid = Input::get('uid');

$outArray = array();

switch ($type) {
	case 'mine':
		$sql = "SELECT DISTINCT mainData.*, XCPID, USERS.id, USERS.name_first, USERS.name_last, USERS.username, ACT_STATUS_2.ACT + ':' +  ACT_STATUS_2.STATUS as status, ACT_STATUS_2.DESCRIPTION statusDescription, ACT_STATUS_2.name as statusName, AUDIT.allocatedOn DATE, AUDIT.ACT, AUDIT.STATUS
				FROM mainData
				OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
				LEFT JOIN USERS ON USERS.id = AUDIT.allocatedTo
				LEFT JOIN ACT_STATUS_2 on ACT_STATUS_2.status = AUDIT.STATUS AND ACT_STATUS_2.act = AUDIT.ACT
				WHERE AUDIT.ACT = ".$act." AND AUDIT.XCPID IS NOT NULL AND allocatedTo IS NOT NULL";

		if(Input::get('uid') != 0)
			$sql .= " AND allocatedTo = " . Input::get('uid');

		if(Input::get('feed') != 0)
			$sql .= " AND feed_id = " . Input::get('feed');

		if(Input::get('stream') != 0)
			$sql .= " AND stream_id = " . Input::get('stream');

		break;
	
	default:
		$sql = "SELECT DISTINCT mainData.*, USERS.*, ACT_STATUS_2.ACT + ':' +  ACT_STATUS_2.STATUS as status, ACT_STATUS_2.name as statusName, ACT_STATUS_2.DESCRIPTION statusDescription, AUDIT.*
				FROM mainData
				OUTER APPLY (SELECT TOP 1 * FROM ACT_AUDIT WHERE XCPID = mainData.XCP_ID order by id desc) AUDIT
				LEFT JOIN USERS ON USERS.id = AUDIT.USER_ID
				LEFT JOIN ACT_STATUS_2 on ACT_STATUS_2.status = AUDIT.STATUS AND ACT_STATUS_2.act = AUDIT.ACT
				WHERE AUDIT.ACT = ".$act." AND AUDIT.XCPID IS NOT NULL AND allocatedTo IS NULL";

		if(Input::get('feed') != 0)
			$sql .= " AND feed_id = " . Input::get('feed');

		if(Input::get('stream') != 0)
			$sql .= " AND stream_id = " . Input::get('stream');

		break;
}

$data = $db->query($sql);					
$results = $data->results();

	foreach($results as $result) {
		//
		switch ($type) {
			case 'mine':
			$actXcpid = new Activity($result->XCPID);
				$form = '<div class="dropdown">
						  <button class="btn btn-success btn-sm dropdown-toggle pull-right" type="button" id="actionMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						    Select stage
						    <span class="caret"></span>
						  </button>
						  <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="actionMenu">
						   <li class="dropdown-header">Current stage</li>
							<li class="disabled"><a href="#">' . $result->statusName . '</a></li>
						  <li role="separator" class="divider"></li>
						  <li class="dropdown-header">Select next stage</li>';
				
				foreach ($actXcpid->getActRules() as $key => $value) {
					$estString = "'$result->XCPID','$key'";
					$form .= '<li><a href="#" onclick="changeStage('.$estString.')" title="'.$key.'">'. $value .'</a></li>';
				}		  

			
				$form .= '<li role="separator" class="divider"></li>';
				$form .= '<li><a href="#" onclick="unassign(\''.$result->XCPID.'\')">Unclaim item</a></li>';
				$form .='</ul></div>';


				break;
			default:
				$form = '<button id="'.$result->XCPID.'" href="#" class="btn btn-warning btn-sm pull-right" onclick="claim('.$result->XCPID.');">CLAIM</button>';
				break;
		}
		if($result->id == 0){
			$userPrint = "XCP";
		} else {
			$userPrint = ucfirst($result->name_first) . " " . ucfirst($result->name_last);
		}
		$outArray[] = array(
            		'<a target="details" title="View info for ' . $result->XCPID . ' (' . $result->material_id . ')" href="item.php?xcpid=' . $result->XCPID . '">' . $result->material_id . '</a>',
            		'<span title="'. $result->materialDescription .'">' . concatTitle($result->materialDescription,20) . '</span>',
            		$result->projectType,
            		'<span title="' . wordwrap($result->materialTitle, 200, "\n") . '">' . concatTitle($result->materialTitle, 30) . '</span>',
		           	$userPrint,
					'<time class="timeago" title="' . $result->DATE . '" datetime="' . $result->DATE . '">' . $result->DATE . '</time>',
					$result->pageCount,
            		$result->stream_id . ' (' . $result->feed_name . ')',
            		'<span title="' . wordwrap($result->statusDescription, 200, "\n") . '">' . $result->status . " - " . $result->statusName . "</span>",
            		$form
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