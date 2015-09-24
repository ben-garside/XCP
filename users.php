<?php 
require_once('php/templates/header.php');

if(!$user->isLoggedIn() || !$user->inRole('SystemAdministrator')){
    Redirect::to('index.php');
}
?>
<div class="page-header">
	<h1>Manage Users </h1>
</div>

<table id="userList" role="table" class="table table-hover">
<thead>
	<tr>
		<th>Id</th>
		<th>Username</th>
		<th>First Name</th>
    <th>Last Name</th>
		<th>Email</th>
		<th>Joined</th>
		<th></th>
	</tr>
</thead>
<tbody>
</tbody>
</table>

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
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" placeholder="Username">
          </div>
          <div class="form-group">
            <label for="name_first">First Name</label>
            <input type="text" class="form-control" id="name_first" placeholder="First name">
          </div>
          <div class="form-group">
            <label for="name_last">Last Name</label>
            <input type="text" class="form-control" id="name_last" placeholder="Last name">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" placeholder="Email">
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


<?php
require_once('php/templates/footer.php');
?>
<script src="js/users.js"></script>