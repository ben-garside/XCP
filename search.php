<?php 
require_once('php/templates/header.php');
if(!$user->isLoggedIn()){
    Redirect::to('login.php?nexturl=flow.php');
}
if($term = Input::get('term')){
	if(!$user->hasPermission('admin')){
	    Redirect::to('flow.php');
	}
	// Show info fopr selected action
	$search = new Search();
	$results = $search->items($term);
	?>
	<div class="page-header">
	<h1>Results for '<?php echo $term; ?>'</h1>
	</div>
	<div id="results">
		<?php
		foreach ($results as $key => $value) {
			echo '<div class="row">';
				echo'<div class="bs-callout bs-callout-warning results">';
				echo'<h1>PL<span class="stream_id">'. $value->stream_id .'</span> <small>'. $value->feed_name .'</small></h1>';
				echo'<h4><span class="xcp_id">'. $value->xcp_id .'</span> | <span class="materialTitle">'. $value->materialTitle .'</span></h4>';			

				echo '</div>';
			echo '</div>';
		}
		?>
	</div>
<?php
} else {
?>
	<div class="page-header">
	<h1>Search...</h1>
	</div>
<?php
}
require_once('php/templates/footer.php');
?>
<script src="js/search.js"></script>