<?php
require_once 'php/templates/header.php';
?>
	<div class="page-header">
	<h1><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Exclude some content</h1>
	</div>
<?php
	if(Input::get('exlcudeUpis')) {
		$strUpis =  Input::get('exlcudeUpis');
		$upis = preg_split("/\r\n|\n|\r/", $strUpis);
		?>
		<div class="panel panel-default">
  			<div class="panel-heading">Result...</div>
  			<table class="table">
  		<?php
		foreach ($upis as $upi) {
			if(is_numeric($upi) && strlen($upi) == 8){
				try {
					Xcp::exclude($upi,Input::get('feed'),$user->data()->id,Input::get('comment'));
					echo "<tr><td><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> " . $upi . "</td><td>Excluded</td></tr>";
				} catch (Exception $e) {
					echo "<tr><td><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> " . $upi . "</td><td>" . $e->getMessage() . "</td></tr>";
				}
				
			} else {
				echo "<tr><td><span class='glyphicon glyphicon-remove' aria-hidden='true'></span> " . $upi . "</td><td>Invalid UPI</td></tr>";
			}
			
		}
		?>
		</table>
		</div>
		<?php
	}
?>
	<form method="POST">	
	<div class="form-group">
		<div class="row">
			<label class="col-sm-2" for="exlcudeUpis">UPIS (One per line) *</label>
			<div class="col-sm-6">
	    		<textarea class="form-control" id="exlcudeUpis" name="exlcudeUpis" rows="5" required></textarea>
	    	</div>
	    	
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<label class="col-sm-2" for="select_feed">Select the feed *</label>
			<div class="col-sm-6">
				<select id="select_feed" name="feed" class="form-control" required>
      				<option value="">Select feed...</option>
       				<?php
      				foreach (Activity::getFeeds() as $feed) {
        				echo '<option value="' . $feed->feed_id . '">' . $feed->feed_name . '</option>';
      				}
      				?>
    			</select>
		    </div>
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<label class="col-sm-2" for="comment">Reason for exclusion</label>
			<div class="col-sm-6">
	    		<textarea class="form-control" id="comment" name="comment" rows="2"></textarea>
	    	</div>
	    	
		</div>
	</div>
		<div class="col-md-offset-2">
			<button type="submit" class="btn btn-primary">Submit</button>
		</div>
	</form>





</div>
<?php
require_once('php/templates/footer.php');