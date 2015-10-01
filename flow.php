<?php 
require_once('php/templates/header.php');
if(!$user->isLoggedIn()){
    Redirect::to('login.php?nexturl=flow.php');
}
?>
<?php 
if($stage = Input::get('stage')){
	if(!$user->inRole('administrator')){
	    Redirect::to('flow.php');
	}
	?>
	<div class="page-header">
	  <a href="flow.php" class="btn btn-success pull-right"><i class="fa fa-chevron-left"></i> Go Back</a>
	<h1>Stage Manager</h1>
	</div>
	<?php
	// Show info fopr selected stage
	$stageInfo = Activity::splitStage($stage, ',');
	$act = $stageInfo['activity'];
	$status = $stageInfo['status'];
	echo '<h3 id="currentStage">' . $act . ":" . $status . '</h3>';
	$Info = Activity::showStages($act,$status);
	$stageInfo = $Info[$act]['INFO'];
	$statusInfo = $Info[$act]['STATUSES'][$status];
	?>

	<form action="" method="post" class="form-horizontal">

	  <div class="form-group" id="name_form">
	    <label for="name" class="col-sm-3 control-label">Name</label>
	    <div class="col-sm-6">
	     	<input value="<?php echo $statusInfo->name ;?>" type="text" class="form-control" name="terst" id="name">
	     	<span id="feedbackSuccess_name" style="display: none" class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
	      	<span id="inputSuccess3Status_name" style="display: none" class="sr-only">(success)</span>
	      	<span id="feedbackError_name" class="glyphicon glyphicon-remove form-control-feedback" style="display: none" aria-hidden="true"></span>
			<span id="inputError2Status_name" style="display: none" class="sr-only">(error)</span>
	    </div>
	  </div>
	  <div class="form-group" id="desc_form">
	    <label for="description" class="col-sm-3 control-label">Description</label>
	    <div class="col-sm-6">
	     	 <textarea class="form-control" id="description"><?php echo $statusInfo->description ;?></textarea>
	      	<input value="<?php echo $statusInfo->id ;?>" type="hidden" id="statusId">
			<span id="feedbackSuccess_desc" style="display: none" class="glyphicon glyphicon-ok form-control-feedback" aria-hidden="true"></span>
			<span id="inputSuccess3Status_desc" style="display: none" class="sr-only">(success)</span>
			<span id="feedbackError_desc" class="glyphicon glyphicon-remove form-control-feedback" style="display: none" aria-hidden="true"></span>
			<span id="inputError2Status_desc" style="display: none" class="sr-only">(error)</span>
	    </div>
	  </div>

	  <hr>
	  <div class="rules">
	    <?php
	    	foreach ($statusInfo->rules as $ruleset => $rules) {
	    		echo '<div class="ruleset_'.$ruleset.'"><h3>Ruleset ' . $ruleset . '</h3>';
	    		$pipelines = "";
	    		foreach (Activity::showPipelinesForRuleset($ruleset) as $pipe) {
	    			$pipelines .= $pipe->pipeline_id . ", ";
	    		}
	    		echo '<p>Pipelines: ' . removeAtEnd($pipelines, 2) . '</p>';
	    		?>
	    		<button type="button" onclick="addRuleInput(<?php echo "'" . $ruleset . "'"; ?>)" class="btn btn-warning"><i class="fa fw fa-plus"></i> Add rule</button>
	    		<button type="button" onclick="update()" class="btn btn-primary"><i class="fa fa-pencil"></i> Update</button>
	    		<hr>
	    		<?php
		    		foreach ($rules as $rule) {
			    		?>
			    		<div class="editRule">
							<div class="form-group" id="<?php echo $rule['id'] ;?>">
							  <p class="col-sm-1" style="padding-top: 7px;"><span class="label label-default"><?php echo $rule['id'] ;?></span></p>
							  <label class="control-label col-sm-2 " for="activity_<?php echo $rule['id'] ;?>">Destination</label>
							  <div class="col-sm-2">
							  <select  class="form-control activity" id="activity_<?php echo $rule['id'] ;?>" aria-describedby="inputSuccess2Status">
								<option  value="" disabled >Activity</option>
								<?php
									$activities = $activities = Activity::listActivities();
	                 				foreach ($activities as $key => $value) {
	                 					if($value == $rule['activity']){
											echo '<option selected value="' . $value . '">' . $value . '</option>';
	                 					} else {
		                   					echo '<option value="' . $value . '">' . $value . '</option>';
	                 					}
	                 				}
								  ?>
							  </select>
							  </div>			  
							  <div class="col-sm-2">
							  <select type="text" class="form-control status" id="status_<?php echo $rule['id'] ;?>" aria-describedby="inputSuccess2Status">
								  <option class="status" value="" disabled >Status</option>
									<?php
										$activities = Activity::listStatuses($rule['activity']);
		                 				foreach ($activities as $key => $value) {
		                 					if($value == $rule['status']){
												echo '<option selected value="' . $value . '">' . $value . '</option>';
		                 					} else {
			                   					echo '<option value="' . $value . '">' . $value . '</option>';
		                 					}
		                 				}
									  ?>
							  </select>
							  </div>
							  <div class="col-sm-1">
							  <p id="ok_<?php echo $rule['id'] ;?>" style="color: #3C763D; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-check"></i> Updated</p>
							  <p id="err_<?php echo $rule['id'] ;?>"style="color: #A94442; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-times"></i> Error</p>
							  </div>
							</div>
							<div class="form-group">
							  <label class="control-label col-sm-3 " for="action_<?php echo $rule['id'] ;?>">Action</label>
							  <div class="col-sm-4">
							  <select class="form-control action" id="action_<?php echo $rule['id'] ;?>" aria-describedby="inputSuccess2Status">
							  	<option value="" selected >None</option>
									<?php
										$activities = Activity::listActions();
										print_r($activities);
		                 				foreach ($activities as $key => $value) {
		                 					if($value['id'] == $rule['action']){
												echo '<option selected value="' . $value['id'] . '">' . $value['id'] . ' - ' . $value['name'] . '</option>';
		                 					} else {
			                   					echo '<option value="' . $value['id'] . '">' . $value['id'] . ' - ' . $value['name'] . '</option>';
		                 					}
		                 				}
									  ?>
							  </select>
							  </div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-10">
									<div class="checkbox">
										<label>
										<input id="assign_<?php echo $rule['id'] ;?>" type="checkbox" value="" <?php if($rule['assign'] == 1){ echo " checked";} ;?>> Sticky assignment
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
							  <label class="control-label col-sm-3 " for="inputSuccess2"></label>
							  <div class="col-sm-4">
							  	<button type="button" onclick="removeRule('editRule_<?php echo $rule['id'] ;?>')" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete</button>
								<!--<button type="button" onclick="addRuleInput(<?php echo "'" . $ruleset . "'"; ?>)" class="btn btn-warning"><i class="fa fw fa-plus"></i> Add rule</button>-->
								<!--<button type="button" onclick="update()" class="btn btn-primary"><i class="fa fa-pencil"></i> Update</button>-->
								</div>
							</div>
						<hr>
						</div>
						
			    		<?php 
			    	}
		    	?>
		    	</div>
	  			<?php
		    }
	    ?>
	  </div>
	</form>

	<?php
} else {
	// Show list of ACT and STAT
	?>
	<div class="page-header">
	<h1>Stage Manager <button type="button" onclick="addStage()" class="btn btn-primary pull-right"><i class="fa fw fa-plus"></i> Add stage</button></h1>
	</div>
	<?php
	$stageInfo = Activity::showStages();
	foreach ($stageInfo as $key => $value) {
		echo '<h3>' . $key . ' - ' . $value['INFO']->SHORT_NAME . ' <small>' . $value['INFO']->DESCRIPTION . '</small></h3>';
		echo '<table class="table table-hover"><thead>';
		if($user->inRole('administrator')){
				$editHead = '<th class="col-md-1"></th>';
			}
		echo '<tr><th class="col-md-1">Stage</th><th class="col-md-2">Name</th><th class="col-md-6">Description</th><th class="col-md-1"></th>' . $editHead . '</tr></thead>';
		foreach ($value['STATUSES'] as $statusId => $statusVal) {
			
			if($user->inRole('administrator')){
				$edit = '<td class="col-md-1"><div class="pull-right"><a href="?stage='. $statusVal->act . ',' . $statusVal->status .'" class="btn btn-success btn-sm" >Edit</a> <button class="btn btn-danger btn-sm" onclick="deleteStage(\''. $statusVal->act . '\',\'' . $statusVal->status .'\')">Delete</button></div></td>';
			}
			echo '<tr><td class="col-md-1">' . $statusVal->act . ':' . $statusVal->status . '</td><td class="col-md-2">' . $statusVal->name . '</td><td class="col-md-6">' . $statusVal->description . '</td><td class="col-md-1">' . $ruleAllow . '</td>' . $edit . '</tr>';
			unset($ruleAllow);
		}
		echo '</table>';
	}
}
?>
<!-- clonable form -->

<div class="addRule" style="display: none;">
	<div class="form-group" id="xx">
	  <p class="col-sm-2" style="padding-top: 7px;"><span class="label label-default">xx</span></p>
	  <label class="control-label col-sm-1 " for="activity_xx">Destination</label>
	  <div class="col-sm-2">
	  <select  class="form-control activity" id="activity_xx" >
		<option  value="" disabled selected>Activity</option>
		<?php
				$activities = Activity::listActivities();
				foreach ($activities as $key => $value) {			
   					echo '<option value="' . $value . '">' . $value . '</option>';
				}
		  ?>
	  </select>
	  </div>			  
	  <div class="col-sm-2">
	  <select type="text" class="form-control status" id="status_xx">
		  <option class="status" value="" disabled selected>Status</option>
			<?php
				$activities = Activity::listStatuses();
 				foreach ($activities as $key => $value) {
       				echo '<option value="' . $value . '">' . $value . '</option>';
 				}
			  ?>
	  </select>
	  </div>
	  <div class="col-sm-1">
	  <p id="ok_xx" style="color: #3C763D; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-check"></i> Updated</p>
	  <p id="err_xx"style="color: #A94442; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-times"></i> Error</p>
	  </div>
	</div>
	<div class="form-group">
	  <label class="control-label col-sm-3 " for="action_xx">Action</label>
	  <div class="col-sm-4">
	  <select class="form-control action" id="action_xx" aria-describedby="inputSuccess2Status">
	  	<option value="" selected >None</option>
			<?php
				$activities = Activity::listActions();
 				foreach ($activities as $key => $value) {
       				echo '<option value="' . $value['id'] . '">' . $value['id'] . ' - ' . $value['name'] . '</option>';
 				}
			  ?>
	  </select>
	  </div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-10">
			<div class="checkbox">
				<label>
				<input id="assign_xx" type="checkbox" value="" <?php if($rule['assign'] == 1){ echo " checked";} ;?>> Sticky assignment
				</label>
			</div>
		</div>
	</div>
	<div class="form-group">
	  <label class="control-label col-sm-3 " for="inputSuccess2"></label>
	  <div class="col-sm-4">
	  	<button type="button" onclick="removeRule('addRule_xx')" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete</button>
		<!--<button type="button" onclick="addRuleInput(yy)" class="btn btn-warning"><i class="fa fw fa-plus"></i> Add rule</button>-->
		<!--<button type="button" onclick="update()" class="btn btn-primary"><i class="fa fa-pencil"></i> Update</button>-->
		</div>
	</div>
	<hr>
</div>

<!-- clonable form END -->

<!-- Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="dataModalLabel">Add new stage</h4>
      </div>
      <div class="modal-body">
       <p><span id="dataModalIntro">Please enter the detals of the new stage here:</span></p><hr>
        <div id="dataModalError" class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <strong><i class="fa fa-exclamation-triangle"></i> </strong><span id="errorText"></span>
        </div>
        <form>
					<div class="form-group">
				    <label for="act">Activity</label>
				    <select class="form-control" id="act" >
				    	<?php
								foreach ($stageInfo as $key => $value) {
									echo '<option value="'.$key.'">' . $key . ' - ' . $value['INFO']->SHORT_NAME . '</option>';
								}
				    	?>
				    </select>
				  </div>
				  <div class="form-group">
				    <label for="status">Status</label>
				    <input class="form-control" id="status" type="number" list="statuses" min="00" max="99" step="1" placeholder="00">
							<datalist id="statuses">
							  <option value="00">
							  <option value="02">
							</datalist>
				  </div>
				  <div class="form-group">
				    <label for="Name">Name</label>
				    <input type="text" class="form-control" id="Name" placeholder="Name">
				  </div>
				  <div class="form-group">
				    <label for="Description">Description</label>
				    <textarea class="form-control" id="Description" ></textarea>
				  </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="dataModalcancButton">Close</button>
        <button type="sumbit" class="btn btn-primary" data-complete-text="Finished!" data-error-text="Error" id="dataModalsendButton">Add</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModal">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="deleteModal"></h4>
      </div>
      <div class="modal-body">
      <p>
      	Are you <em>really</em> sure you want to delete this stage and all of its rules?<br/>You will not be able to get it back!
      </p>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-default" id="dataModaldelcancButton">Noooo... Close</button>
        <button type="sumbit" 
                class="btn btn-danger" 
                data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Updating"
                data-error-text="Error" 
                id="dataModaldelButton">
                Yup, Delete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- END Modal -->

<?php
require_once('php/templates/footer.php');
?>
<script src="js/flow.js"></script>