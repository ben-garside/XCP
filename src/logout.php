<?php
require_once 'php/templates/header.php';

$user = new User();
$user->logout();
Session::flash('home-success','You have signed out!');
Redirect::to('index.php');