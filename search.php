<?php 
require_once('php/templates/header.php');
if(!$user->isLoggedIn()){
    Redirect::to('login.php?nexturl=index.php');
}
if($term = Input::get('term')){
	if(!$user->hasPermission('admin')){
	    Redirect::to('index.php');
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
		$type = 'success';
		foreach ($results as $key => $value) {
			if(!$user = $value->username){
				$user = 'Unassigned';
			} else {
				$type = 'warning';
			}

			if(!$value->stream_id){
				$pipe = 'N/A';
				$type = 'danger';
			} else {
				$pipe = 'PL'.$value->stream_id;
			}

			if(!$stage = $value->stage){
				$stage = ' N/A ';
			}

			echo '<div class="row"><div class="bs-callout bs-callout-'.$type.' results">';
			echo '<h1><span class="stream_id">'. $pipe .'</span> <small>'. $value->feed_name .'</small></h1>';
			echo '<h4><span class="xcp_id">'. $value->xcp_id .'</span> | <span class="material_id">'. $value->material_id .'</span> | <span class="stage">'. $stage .'</span> | <span class="stage">'. $user .'</span></h4>';
			echo '<hp><span class="materialTitle">'. $value->materialTitle .'</span></h4>';
			echo '</div></div>';
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