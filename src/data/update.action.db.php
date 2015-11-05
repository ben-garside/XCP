<?php 
require("../php/init.php");
$user = new User();
$xcpid = Input::get('xcpid');
$datas = Input::get('data');
$datas = json_decode($datas);
$mainStatus = '100';
$status = array();

foreach ($datas as $key => $data) {
	if($data->source == 'BATCH_DATA'){
		$dataId = Activity::getBatchId($xcpid);
	} else {
		$dataId  = $xcpid;
	}
	$id = $data->id;
	if($dataId){
		$oldVal = (Activity::showItemValue($id, $dataId, $data->source));
	 	$newVal = $data->value;	
	 	$isValid = Activity::validateItemData($id, $newVal);
	 	if($isValid === true){
			if($newVal != $oldVal) {
				//Get info
				$fieldInfo = Activity::showFieldData($id);
				$source = $fieldInfo->source_table;
				$dataType = $fieldInfo->data_type;
				if($oldVal == "" && $newVal != "") {
					//INSERT
					$type = 'insert';
				} elseif($oldVal != "" && $newVal == "") {
					//DELETE
					$type = 'delete';
				} else {
					//UPDATE
					$type = 'update';
				}
				$status[$id] = Activity::changeItemData($dataId, $id, $newVal, $type, $source, $user->data()->id, $dataType);
			} 		
	 	} else {
	 		$status[$id] = $isValid;
	 	}
	} else {
		$status[$id] = array('status' => '450', 'message' => 'Item is not in a batch');
	}
}
foreach ($status as $key => $value) {
	if($value['status'] != '100') {
		$mainStatus = '200';
	}
}
$out = array('dbStatus' => $mainStatus,
			 'details' => $status);
	

 print(json_encode($out));

 ?>