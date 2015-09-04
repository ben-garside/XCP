<?php
session_start();

date_default_timezone_set('Europe/London');

$GLOBALS['config'] = array(
	'mysql' => array(
		
		// 'host' => '10.103.109.84\cloud,1500',
		// 'username' => 'WSCRAPE_user',
		// 'password' => 'Password1',
		// 'db' => 'UAT-WSCRAPE',

		// 'host' => 'CHI-PB3YT38',
		// 'username' => 'xcp_user',
		// 'password' => 'Password1',
		// 'db' => 'XCP-DEV'

		'host' => 'CHI-IND01',
		'username' => 'XCP',
		'password' => 'Password1',
		'db' => 'XCP_TEST'

	),
	'remember' => array(
		'cookie_name' => 'hash',
		'cookie_expiry' => 604800,
	),
	'session' => array(
		'session_name' => 'user',
		'token_name' => 'token',
	),
	'release' => array(
		'version' => 'v1.2.1-beta.1',
		'date' => '2015-08-28',

	),
);
// Include Composer files..
require __DIR__ . '/../vendor/autoload.php';

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
