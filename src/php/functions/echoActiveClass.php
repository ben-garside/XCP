<?php 
function echoActiveClassIfRequestMatches($requestUri)
{
    $current_file_name = basename($_SERVER['PHP_SELF'], ".php");
    if ($current_file_name == $requestUri)
        echo 'class="active"';
}

function echoActiveIfAttributeMatches($attribute, $find) {
	if(Input::get($attribute) == $find)
		return 'class="active"';
}
?>