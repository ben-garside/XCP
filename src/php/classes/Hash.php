<?php
class Hash {
	public static function make($string, $salt = '') {
		//return 'ooo';
		return iconv('','UTF-8',hash('md5', $string . $salt));
	}

	public static function salt($length) {
		return substr(md5(microtime()),rand(0,26),$length);
	}

	public static function unique() {
		return self::make(uniqid());
	}
}