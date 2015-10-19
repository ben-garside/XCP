<?php
require_once 'php/templates/header.php';
$xcpid = 'XCP3532208';
$test = new Activity($xcpid);
echo Config::get('config/sendItems/dev/ftp_server');
echo "<pre>USERID: " . $user->data()->id . "<br>";

	$activityFrom 	= '10';
	$statusFrom		= '19';

	$activityTo 	= '10';	
	$statusTo		= '20';


print_r(Activity::showAtStage($activityFrom,$statusFrom));

	$activityFrom 	= '10';
	$statusFrom		= '20';

print_r(Activity::showAtStage($activityFrom,$statusFrom));

//print_r(Activity::showFieldData('TAT'));

#print_r($test->getActRules());
#print_r($test->getInfo());

#print_r(Activity::maintainAssign(10,20,1));

echo '</pre><br>';
?>


<?php
require_once 'php/templates/footer.php';