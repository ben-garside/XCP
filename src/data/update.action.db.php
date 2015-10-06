<?php 
require("../php/init.php");
$user = new User();
$xcpid = Input::get('xcpid');
$datas = Input::get('data');
$datas = json_decode($datas);
$mainStatus = '100';
$status = array();

 foreach ($datas as $key => $data) {
	$id = $data->id;
	$oldVal = (Activity::showItemValue($id, $xcpid, $data->source));
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
			$status[$id] = Activity::changeItemData($xcpid, $id, $newVal, $type, $source, $user->data()->id, $dataType);
		} 		
 	} else {

 		$status[$id] = $isValid;
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