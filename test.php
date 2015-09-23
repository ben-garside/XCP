<?php
require_once 'php/templates/header.php';
$xcpid = 'XCP3532208';
$test = new Activity($xcpid);

echo "<pre>USERID: " . $user->data()->id . "<br>";

print_r($user->inRole('LoggeddfbIn'));

//print_r(Activity::showFieldData('TAT'));

#print_r($test->getActRules());
#print_r($test->getInfo());

#print_r(Activity::maintainAssign(10,20,1));

echo '</pre><br>';
?>


<?php
require_once 'php/templates/footer.php';