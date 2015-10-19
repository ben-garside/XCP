<?php
require_once 'php/templates/header.php';
$user->logout();
Session::flash('home-success','You have signed out!');

?>
<script>
	window.location.href = 'index.php';
</script>