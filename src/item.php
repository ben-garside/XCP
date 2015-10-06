<?php
require_once 'php/templates/header.php';
$xcpid = Input::get('xcpid');
if(!$xcpid) {
	Redirect::to('index.php');
} else {
	//Check to see if you can load the item
	try {
		$item = new XCP($xcpid);
	} catch (Exception $e) {
		Session::flash('home-danger',$e->getMessage());
		Redirect::to('index.php');
	}	
}
?>
<div class="page-header">
<h1><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> Item Details <small><?php echo $xcpid; ?></small></h1>
</div>
<div class="row">

	<div class="col-md-4">
		<div class="panel panel-default">
		  	<div class="panel-heading">
		    	<h3 class="panel-title">Item Data</h3>
		  	</div>
		  	<table class="table">
		  		<?php
		  			$data = Activity::showItemData($xcpid, 'ITEM_DATA');
		  			if(!empty($data)){
			  			foreach ($data as $key => $value) {
			  				echo"<tr><td><strong>" . $key ."<strong></td><td>". $value . "</td></tr>";
			  			}		  				
		  			} else {
							echo"<tr><td><em>No data found</em></td><td></td></tr>";
		  			}

		  		?>
		  	</table>
		</div>
	</div>
	<div class="col-md-8">
		<div class="panel panel-default">
		  	<div class="panel-heading">
		    	<h3 class="panel-title">Collated Files <small><?php echo $item->getCollationData()['collationDate'] ;?></small></h3>
		  	</div>
		  	<table class="table">
		  		<tr><th>ID</th><th>FILE</th><th>STATUS</th></tr>
		  		<?php
		  			foreach ($item->getCollationData()['files'] as $value) {
		  				$status = ($value->STATUS == 1) ? "OK" : "ERROR";
		  				echo "<tr><td>" . $value->ID . "</td><td>" . $value->FILE_NAME . "</td><td>". $status . "</td></tr>";
		  			}
		  		?>
		  	</table>
		  	<div class="panel-footer"><a rel="nofollow" title="Download <?php echo $item->getCollationData()['collationContainer'] ;?>" href="getitem.php?download_file=<?php echo $item->getCollationData()['collationContainer'] ;?>"><?php echo $item->getCollationData()['collationContainer'] ;?></a></div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
		<div class="panel panel-default">
		  	<div class="panel-heading">
		    	<h3 class="panel-title">Feed Data</h3>
		  	</div>
		  	<table class="table">
		  		<?php
		  			foreach ($item->getFeedData() as $key => $value) {
		  				echo"<tr><td><strong>" . $value["display"] ."<strong></td><td>". $value["value"] . "</td></tr>";
		  			}
		  		?>
		  	</table>
		</div>
	</div>


	<div class="col-md-8">
		<div class="panel panel-default">
		  	<div class="panel-heading">
		    	<h3 class="panel-title">Activity Audit</h3>
		  	</div>
		  	<table id="audit_table" class="table">
		  	<thead>
		  		<tr><th>Id</th><th>Stage</th><th>Status</th><th>Created</th><th>Assigned</th><th>Ended</th><th>Info</th></tr>
		  	</thead>
		  	<tbody>
		  	</tbody>
		  	</table>
		</div>
	</div>
</div>


<?php
require_once('php/templates/footer.php');
?>
<script src="js/item.js"></script>