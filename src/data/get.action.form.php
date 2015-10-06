<?php 

require("../php/init.php");

$xcpid = Input::get('xcpid');
$action_id = Input::get('action_id');
$out = array();

$dataTypes = array( 1 =>  'text',
					2 =>  'password',
					3 =>  'date',
					4 =>  'number',
					5 =>  'email',
					20 => 'textarea'
					);

$db = DB::getInstance();
$db->query('SELECT * FROM [dbo].[ACTION_LIST] WHERE action_id = ' . $action_id);
$actionInfo = $db->first();

$db->query('SELECT * FROM [dbo].[ACTION_FIELDS] WHERE action_id = ' . $action_id);
$actionFields = $db->results();

foreach ($actionFields as $fieldInfo => $value) {
	$text .= "";
	$itemInfo = (Activity::showItemData($xcpid, $value->source_table));
	$text .= '<div class="form-group">';
	$text .= '<label for="'.$value->field_name.'" class="control-label">';
	$text .= $value->field_name_display;
	$text .= '</label> ';
	switch ($value->data_type) {
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:
			if($value->field_prefix || $value->field_suffix) {
				$text .= '<div class="input-group">';
			}
			if($value->field_prefix) {
				$text .= '<span class="input-group-addon">' 	. $value->field_prefix . '</span>';
			}
			$text .= '<input class="form-control" data-source="'. $value->source_table  .'" type="'.$dataTypes[$value->data_type].'" name="'.$value->field_name.'" id="'.$value->field_name.'" ';
			if($value->source_prefill == true && $itemInfo[$value->field_name] != "") {
				$text .= 'value="'.$itemInfo[$value->field_name].'"';
			}elseif($value->data_placeholder) {
				$text .= ' placeholder="'.$value->data_placeholder.'"';
			}
			if($value->data_required) {
				$text .= 'required ';
			}
			$text .= '>';
			if($value->field_suffix) {
				$text .= '<span class="input-group-addon">'.$value->field_suffix.'</span>';
			}
			break;
		case 20:
			$text .= '<textarea data-source="'. $value->source_table  .'" class="form-control" id="'.$value->field_name.'"';
			if($value->data_required) {
				$text .= ' required ';
			}
			if($value->source_prefill == true && $itemInfo[$value->field_name] != "") {
				$text .= '>'.$itemInfo[$value->field_name].'</textarea>';
			}elseif($value->data_placeholder) {
				$text .= ' placeholder="'.$value->data_placeholder.'"></textarea>';
			}else{
				$text .= ' ></textarea>';
			}

			break;
	
		default:
			# code...
			break;
	}
	if($value->field_prefix) {
				$text .= '</div>';
		}
	$text .= '<span id="err_'.$value->field_name.'" class="error-block"></span></div>';
}


$out = $arrayName = array('form' => $text,
						'title' => $actionInfo->action_title,
						'intro' => $actionInfo->action_description
					 );

 print(json_encode($out));

?>