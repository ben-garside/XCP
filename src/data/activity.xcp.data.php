<?php 

require("../php/init.php");
$xcpid = Input::get('xcpid');


$actXcpid = new Activity($xcpid);
foreach ($actXcpid->getActRules() as $key => $value) {
	$estString = "'$xcpid','$key'";
	$form .= '<li class="actionMenu"><a href="javascript:void(0)" onclick="changeStage('.$estString.')" title="'.$key.'"><i class="fa fa-chevron-circle-right"></i> '. $value .'</a></li>';
	}

print $form;