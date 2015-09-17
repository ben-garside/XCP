<?php 
require_once('php/templates/header.php');
if(!$user->isLoggedIn()){
    Redirect::to('login.php?nexturl=flow.php');
}
if($id = Input::get('id')){
	if(!$user->hasPermission('admin')){
	    Redirect::to('flow.php');
	}
	// Show info fopr selected action
	?>
	<div class="page-header">
	<h1>Edit Action</h1>
	</div>
	<?php
	$actionInfo = Activity::listActions($id);
	$fieldInfo = Activity::listActionFields($id);
	$dataTypes = array( 1 =>  'text',
					2 =>  'password',
					3 =>  'date',
					4 =>  'number',
					5 =>  'email',
					20 => 'textarea'
					);
	if($actionInfo['type'] == 1 || $actionInfo['type'] == '')
	{
	?>

	<form action="" method="post" class="form-horizontal">
	  <div class="form-group" id="name_form">
	    <label for="name" class="col-sm-3 control-label">Name</label>
	    <div class="col-sm-6">
	    	<input value="<?php echo $actionInfo['name'] ;?>" type="text" class="form-control" id="action_name">
	     	<input value="1" type="hidden" class="form-control" id="action_type">
	     	<input value="<?php echo $id ;?>" type="hidden" class="form-control" id="action_id">
	     	<span id="helpBlock" class="help-block">Internal name of this action</span>
	    </div>
	  </div>
	  <div class="form-group" id="title_form">
	    <label for="name" class="col-sm-3 control-label">Title</label>
	    <div class="col-sm-6">
	     	<input value="<?php echo $actionInfo['title'] ;?>" type="text" class="form-control" id="action_title">
	     	<span id="helpBlock" class="help-block">You may use the following in the title: %XCPID% %UPI%</span>
	    </div>
	  </div>
	  <div class="form-group" id="desc_form">
	    <label for="description" class="col-sm-3 control-label">Description</label>
	    
	    <div class="col-sm-6">
	     	<textarea type="text" class="form-control" id="action_description"><?php echo $actionInfo['description'] ;?></textarea>
	     	<span id="helpBlock" class="help-block">This will be displayed on the form.</span>
	    </div>
	  </div>

	  <hr>
	  <div class="rules">

	    		<button type="button" onclick="addRuleInput()" class="btn btn-warning"><i class="fa fw fa-plus"></i> Add field</button>
	    		<button type="button" onclick="update()" class="btn btn-primary"><i class="fa fa-pencil"></i> Update all</button>
	    		<hr>
	    		<?php
		    		foreach ($fieldInfo as $field) {
			    		?>
			    		<div class="editRule">
							<div class="form-group" id="<?php echo $field['field_id'] ;?>">
							  <p class="col-sm-offset-0 col-sm-1" style="padding-top: 7px;"><span class="label label-default"><?php echo $field['field_id'] ;?></span></p>
							  <label class="control-label col-sm-2 " for="field_name_<?php echo $field['field_id'] ;?>">Unique name  <sup><i class="fa fa-info-circle" style="padding-top: 7px;" tabindex="0" role="button" data-toggle="popover" data-trigger="hover" data-placement="top" title="Display" data-content="The text that will be shown as the label for this field."></i></sup></label>
							  <div class="col-sm-5">
								  <input class="form-control" type="form-control" id="field_name_<?php echo $field['field_id'] ;?>" value="<?php echo $field['field_name'] ;?>">
								  <input class="form-control" type="hidden" id="action_id_<?php echo $field['field_id'] ;?>" value="<?php echo $field['action_id'] ;?>">
								  <input class="form-control" type="hidden" id="field_id_<?php echo $field['field_id'] ;?>" value="<?php echo $field['field_id'] ;?>">
							  </div>			  
							  <div class="col-sm-1">
							  <p id="ok_<?php echo $field['field_id'] ;?>" style="color: #3C763D; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-check"></i> Updated</p>
							  <p id="err_<?php echo $field['field_id'] ;?>"style="color: #A94442; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-times"></i> Error</p>
							  </div>
							  		<div class="col-sm-offset-3 col-sm-10">
									<div class="checkbox">
										<label>
										<input id="data_required_<?php echo $field['field_id'] ;?>" type="checkbox" <?php if($field['data_required'] == 1){ echo " checked";} ;?>> Required field?
										</label>
									</div>
								</div>
							</div>
							<div class="form-group" >
							  <label class="control-label col-sm-3 " for="field_name_display_<?php echo $field['field_id'] ;?>">Display <sup><i class="fa fa-info-circle" style="padding-top: 7px;" tabindex="0" role="button" data-toggle="popover" data-trigger="hover" data-placement="top" title="Display" data-content="The text that will be shown as the label for this field."></i></sup></label> 
							  <div class="col-sm-6">
								  <input class="form-control" type="form-control activity" id="field_name_display_<?php echo $field['field_id'] ;?>" value="<?php echo $field['field_name_display'] ;?>">
							  </div>
							</div>
							<div class="form-group" >
							  <label class="control-label col-sm-3 " for="field_prefix_<?php echo $field['field_id'] ;?>">Prefix / Suffix</label>
							  <div class="col-sm-3">
							  <input class="form-control" type="form-control activity" id="field_prefix_<?php echo $field['field_id'] ;?>" value="<?php echo htmlspecialchars($field['field_prefix']) ;?>" placeholder="prefix">
							  </div>			  
							  <div class="col-sm-3">
							  <input class="form-control" type="form-control activity" id="field_suffix_<?php echo $field['field_id'] ;?>" value="<?php echo htmlspecialchars($field['field_suffix']) ;?>" placeholder="suffix">
							  </div>
							</div>
							<div class="form-group" >
							  <label class="control-label col-sm-3 " for="data_placeholder_<?php echo $field['field_id'] ;?>">Placeholder</label>
							  <div class="col-sm-6">
								  <input class="form-control" type="form-control activity" id="data_placeholder_<?php echo $field['field_id'] ;?>" value="<?php echo $field['data_placeholder'] ;?>" placeholder="placeholder">
							  </div>
							</div>
							<div class="form-group" >
							  <label class="control-label col-sm-3 " for="data_validation_<?php echo $field['field_id'] ;?>">Validation rule</label>
							  <div class="col-sm-6">
								  <input class="form-control" type="form-control activity" id="data_validation_<?php echo $field['field_id'] ;?>" value="<?php echo $field['data_validation'] ;?>" placeholder="validation rule">
							  </div>
							</div>
							<div class="form-group" >
							  <label class="control-label col-sm-3" for="data_validation_helper_<?php echo $field['field_id'] ;?>">data_validation_helper</label>
							  <div class="col-sm-6">
								  <input class="form-control" type="form-control activity" id="data_validation_helper_<?php echo $field['field_id'] ;?>" value="<?php echo $field['data_validation_helper'] ;?>" placeholder="data_validation_helper">
							  </div>
							</div>
							<div class="form-group" >
								<label class="control-label col-sm-3 " for="source_table_<?php echo $field['field_id'] ;?>">Database</label>
								<div class="col-sm-2">
									<input class="form-control" type="form-control activity" id="source_table_<?php echo $field['field_id'] ;?>" value="<?php echo $field['source_table'] ;?>" placeholder="source_table">
								</div>
								<div class="col-sm-2">
									<select type="text" class="form-control status" id="data_type_<?php echo $field['field_id'] ;?>">
										<option class="status" value="" disabled >Data type</option>
										<?php
										foreach ($dataTypes as $key => $type) {
											if($key == $field['data_type']) {
												echo '<option class="status" value="' . $key . '" selected>' . $type .  '</option>';	
											}else{
												echo '<option class="status" value="' . $key . '">' . $type .  '</option>';	
											}
										}
										?>
									</select>							  
								</div>
								<div class="col-sm-1">
									<select type="text" class="form-control status" id="data_child_of_<?php echo $field['field_id'] ;?>" aria-describedby="inputSuccess2Status">
										<option class="status" value="" disabled >Child of...</option>
									</select>								  
								</div>
								<div class="col-sm-1">
									<div class="checkbox">
										<label>
											<input id="source_prefill_<?php echo $field['field_id'] ;?>" type="checkbox" value="" <?php if($field['source_prefill'] == 1){ echo " checked";} ;?>> Prefill?
										</label>
									</div>
								</div>
							</div>
							<div class="form-group">
							  <label class="control-label col-sm-3 " for="inputSuccess2"></label>
							  <div class="col-sm-4">
							  	<button type="button" onclick="removeRule('editRule_<?php echo $field['field_id'] ;?>')" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete</button>
								<button type="button" onclick="addRuleInput()" class="btn btn-warning"><i class="fa fw fa-plus"></i> Add rule</button>
								<button type="button" onclick="update()" class="btn btn-primary"><i class="fa fa-pencil"></i> Update</button>
								</div>
							</div>
						<hr>
						</div>
						
			    		<?php 
			    	}
		    	?>
	  </div>
	</form>

	<?php
	} else {
		echo 'Uh Oh, thats not the right action type';
	}
} else {
	?>
<div class="page-header">
<h1>Edit Action <button type="button" onclick="newAction()" class="btn btn-primary"><i class="fa fa-plus"></i> Add Action</button></h1>
</div>
	<?php
	// Show list of ACT and STAT
	$actionInfo = Activity::listActions();
	echo '<table class="table table-hover"><thead>';
	if($user->hasPermission('admin')){
				$editHead = '<th class="col-md-1"></th>';
	}
	echo '<tr><th class="col-md-1">ID</th><th class="col-md-1">Type</th><th class="col-md-1">Name</th><th class="col-md-1">Title</th><th class="col-md-5">Description</th>' . $editHead . '</tr></thead>';
	foreach ($actionInfo as $key => $value) {
			
			if($user->hasPermission('admin')){
				$edit = '<td class="col-md-1"><a href="?id='. $value['id'] .'">edit</a></td>';
			}
			echo '<tr><td class="col-md-1">' . $value['id'] . '</td><td class="col-md-1">' . $value['type'] . '</td><td class="col-md-1">' . $value['name'] . '</td><td class="col-md-2">' . $value['title'] . '</td><td class="col-md-4">' . $value['description'] . '</td>' . $edit . '</tr>';
			unset($ruleAllow);
	}
	echo '</table>';
}
?>
<!-- clonable form -->

<div class="addRule" style="display: none;">
	<div class="form-group" id="xx">
	  <p class="col-sm-offset-1 col-sm-1" style="padding-top: 7px;"><span class="label label-default">xx</span></p>
	  <label class="control-label col-sm-1 " for="field_name_xx">Unique name</label>
	  <div class="col-sm-5">
		  <input class="form-control" type="form-control activity" id="field_name_xx" value="">
		  <input class="form-control" type="hidden" id="action_id_xx" value="<?php echo $id; ?>">
		  <input class="form-control" type="hidden" id="field_id_xx" value="">
	  </div>			  
	  <div class="col-sm-1">
	  <p id="ok_xx" style="color: #3C763D; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-check"></i> Updated</p>
	  <p id="err_xx"style="color: #A94442; text-align: center; padding-top: 7px; display: none;"><i class="fa fa-times"></i> Error</p>
	  </div>
	  		<div class="col-sm-offset-3 col-sm-10">
			<div class="checkbox">
				<label>
				<input id="data_required_xx" type="checkbox" > Required field?
				</label>
			</div>
		</div>
	</div>
	<div class="form-group" >
	  <label class="control-label col-sm-3 " for="field_name_display_xx">Display</label>
	  <div class="col-sm-6">
		  <input class="form-control" type="form-control activity" id="field_name_display_xx" value="">
	  </div>
	</div>
	<div class="form-group" >
	  <label class="control-label col-sm-3 " for="field_prefix_xx">Prefix / Suffix</label>
	  <div class="col-sm-3">
	  <input class="form-control" type="form-control activity" id="field_prefix_xx" value="" placeholder="prefix">
	  </div>			  
	  <div class="col-sm-3">
	  <input class="form-control" type="form-control activity" id="field_suffix_xx" value="" placeholder="suffix">
	  </div>
	</div>
	<div class="form-group" >
	  <label class="control-label col-sm-3 " for="data_placeholder_xx">Placeholder</label>
	  <div class="col-sm-6">
		  <input class="form-control" type="form-control activity" id="data_placeholder_xx" value="" placeholder="placeholder">
	  </div>
	</div>
	<div class="form-group" >
	  <label class="control-label col-sm-3 " for="data_validation_xx">Validation rule</label>
	  <div class="col-sm-6">
		  <input class="form-control" type="form-control activity" id="data_validation_xx" value="" placeholder="validation rule">
	  </div>
	</div>
	<div class="form-group" >
	  <label class="control-label col-sm-3" for="data_validation_helper_xx">data_validation_helper</label>
	  <div class="col-sm-6">
		  <input class="form-control" type="form-control activity" id="data_validation_helper_xx" value="" placeholder="data_validation_helper">
	  </div>
	</div>
	<div class="form-group" >
		<label class="control-label col-sm-3 " for="source_table_xx">Database</label>
		<div class="col-sm-2">
			<input class="form-control" type="form-control activity" id="source_table_xx" value="" placeholder="source_table">
		</div>
		<div class="col-sm-2">
			<select type="text" class="form-control status" id="data_type_xx" >
				<option class="status" value="" disabled >Data type</option>
				<?php
				foreach ($dataTypes as $key => $type) {
					echo '<option class="status" value="' . $key . '">' . $type .  '</option>';
				}
				?>
			</select>							  
		</div>
		<div class="col-sm-1">
			<select type="text" class="form-control status" id="data_child_of_xx">
				<option class="status" value="" disabled >Child of...</option>
			</select>								  
		</div>
		<div class="col-sm-1">
			<div class="checkbox">
				<label>
					<input id="source_prefill_xx" type="checkbox" value=""> Prefill?
				</label>
			</div>
		</div>
	</div>
	<div class="form-group">
	  <label class="control-label col-sm-3 " for="inputSuccess2"></label>
	  <div class="col-sm-4">
	  	<button type="button" onclick="removeRule('addRule_xx')" class="btn btn-danger"><i class="fa fa-trash-o"></i> Delete</button>
		<button type="button" onclick="addRuleInput()" class="btn btn-warning"><i class="fa fw fa-plus"></i> Add rule</button>
		<button type="button" onclick="update()" class="btn btn-primary"><i class="fa fa-pencil"></i> Update</button>
		</div>
	</div>
<hr>
</div>

<!-- clonable form END -->
<?php
require_once('php/templates/footer.php');
?>
<script src="js/actionManager.js"></script>