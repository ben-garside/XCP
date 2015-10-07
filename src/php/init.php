<?php
session_start();

date_default_timezone_set('Europe/London');

$GLOBALS['config'] = array(
	'remember' => array(
		'cookie_name' => 'hash',
		'cookie_expiry' => 604800,
	),
	'session' => array(
		'session_name' => 'user',
		'token_name' => 'token',
	),
	'release' => array(
		'version' => 'v1.2.2-beta.2',
		'date' => '2015-10-02',

	),
);
// Include Composer files..
require __DIR__ . '/../vendor/autoload.php';

// Require database settings
require "dbinit.php";
require "buildInfo.php";

// Include all classes
spl_autoload_register(function ($class) {
	require_once __DIR__ . '/classes/' . $class . '.php';
});

// Include all functions
foreach (glob(__DIR__ . "/functions/*.php") as $filename) {
	include_once $filename;
}

if (Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))) {
	$hash = Cookie::get(config::get('remember/cookie_name'));
	$hashCheck = DB::getInstance()->get('users_session', array('hash', '=', $hash));

	if ($hashCheck->count()) {
		$user = new User($hashCheck->first()->user_id);
		$user->login();
	}
}