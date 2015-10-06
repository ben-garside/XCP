<?php
require_once 'php/templates/header.php';
?>
	<div class="page-header">
	<h1><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> View Exclusions</h1>
	</div>
	<table id="exclutions" class="table table-hover ">
	<thead>
		<tr><th>[EXCLUTION_ID]</th><th>[UPI]</th><th>[FEED_ID]</th><th>[DT_ADDED]</th><th>[USER_ID]</th><th>[COMMENT]</th></tr>
	</thead>
	<tbody>
	</tbody>
	</table>
<?php

require_once('php/templates/footer.php');
?>
<script src="js/viewexclusion.js"></script>