<?php
function concatFile($string, $len, $keepExt) {
	if(strlen($string) > $len){
		$shortString = substr($string,0,$len);
		if($keepExt == 1){
			return $shortString . '..' . substr($string,strrpos($string,'.'),strlen($string));
		} else {
			return $shortString . '...';
		}
	}
	return $string;
}

function concatTitle($title, $len) {
	if(strlen($title) >= $len) {
		return substr($title,0,$len) . "...";
	} else {
		return $title;
	}
	return $title;
}

function removeAtEnd($text, $char) {
	return substr($text, 0, (strlen($text) - $char));
}

?>
