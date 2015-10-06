<?php

require("../php/init.php");

$xcpid = Input::get('xcpid');
$status = Input::get('status');
$actI = new Activity($xcpid);

print_r($actI->unAssign());