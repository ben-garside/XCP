<?php
require_once 'php/templates/header.php';
#$xcpid = 'XCP1547876';
echo "<pre>USERID: " . $user->data()->id . "<br>";
$xcpid = Input::get('xcpid');

if($xcpid){
	$test = new Xcp($xcpid);
	$pipeline = $test->findPipeline();
}

echo "|".$pipeline."|";

echo 'END</pre><br>';
?>


<?php
require_once 'php/templates/footer.php';