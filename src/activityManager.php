<?php 
require_once('php/templates/header.php');
if(!$user->isLoggedIn()){
    Redirect::to('login.php?nexturl=activitymanager.php');
}

if(!$user->inRole('administrator')){
    Redirect::to('activitymanager.php');
}

if(Input::get('id')){
	//Edit 
} else {
	//List
	?>
	<div class="page-header">
  <button type="button" onclick="addActivity()" class="btn btn-success pull-right"><i class="fa fw fa-plus"></i> Add Activity</button>
	<h1>Manage Activities <small><?php echo $xcpid; ?></small></h1>
	</div>

	<table id="actList" role="table" class="table table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Full Name</th>
			<th>Description</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
	<?php
}
?>

<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="dataModalLabel"></h4>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label for="SHORT_NAME">Name</label>
            <input type="text" class="form-control" id="SHORT_NAME" placeholder="Name">
          </div>
          <div class="form-group">
            <label for="FULL_NAME">FULL_NAME</label>
            <input type="text" class="form-control" id="FULL_NAME" placeholder="FULL_NAME">
          </div>
          <div class="form-group">
            <label for="DESCRIPTION">DESCRIPTION</label>
            <input type="DESCRIPTION" class="form-control" id="DESCRIPTION" placeholder="DESCRIPTION">
          </div>
          <div class="form-group">
            <label for="roles">Roles</label>
			<select id="roles" multiple class="form-control">
              <?php
              $roles = User::showRoles();
              foreach ($roles as $id => $name) {
                echo '<option value="'.$id.'">'.$name.'</option>';
              }
              ?>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="dataModalcancButton">Close</button>
        <button type="sumbit" 
                class="btn btn-primary" 
                data-loading-text="<i class='fa fa-spinner fa-pulse'></i> Updating"
                data-complete-text="Finished!" 
                data-error-text="Error" 
                id="dataModalsendButton">
                Update
        </button>
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
      	Are you <em>really</em> sure you want to delete this activity?<br/>You will not be able to get it back!
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

<?php
require_once('php/templates/footer.php');
?>
<script src="js/activityManager.js"></script>