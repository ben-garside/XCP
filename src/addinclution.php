<?php
require_once 'php/templates/header.php';
?>
	<div class="page-header">
	<h1> Include some content</h1>
	</div>
<?php
	if(Input::get('includeUpis')) {
		$strUpis =  Input::get('includeUpis');
		$upis = preg_split("/\r\n|\n|\r/", $strUpis);
		?>
		<div class="panel panel-default">
  			<div class="panel-heading">Result...</div>
  			<table class="table">
  		<?php
		foreach ($upis as $upi) {
			if(is_numeric($upi) && strlen($upi) == 8){
				try {
					$newId = Xcp::includeUpi($upi,Input::get('feed'),$user->data()->id,Input::get('comment'), Input::get('stage'));
					echo "<tr><td><span class='glyphicon glyphicon-ok' aria-hidden='true'></span> " . $upi . "</td><td>Included: <a href='item.php?xcpid=" . $newId . "'>" . $newId . "</a></td></tr>";
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
			<label class="col-sm-2" for="includeUpis">UPIS (One per line) *</label>
			<div class="col-sm-6">
	    		<textarea class="form-control" id="includeUpis" name="includeUpis" rows="5" required></textarea>
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
			<label class="col-sm-2" for="select_feed">Stage to start the item *</label>
			<div class="col-sm-6">
				<select id="select_stage" name="stage" class="form-control" required>
      				<option value="">Select stage...</option>
       				<?php
      				foreach (Activity::listStages() as $stage => $description) {
        				echo '<option value="' . $stage . '">' . $stage . ' - ' . $description . '</option>';
      				}
      				?>
    			</select>
		    </div>
		</div>
	</div>
	<div class="form-group">
		<div class="row">
			<label class="col-sm-2" for="comment">Reason for inclusion</label>
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