<?php

function validateInput ($string,$rx){
	if (preg_match($rx, $string)) {
		return true;
	} else {
		return false;
	}
}

function validateXcpId($xcpid) {
	if (preg_match("/(XCP[0-9]{7})/i", $xcpid)) {
    	return true;
	} else {
	    return false;
	}
}

?>