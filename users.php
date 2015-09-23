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
		<th>Joined</th>
		<th>Roles</th>
		<th></th>
	</tr>
</thead>
<tbody>
</tbody>

<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="dataModalLabel"></h4>
      </div>
      <div class="modal-body">
       <p><span id="dataModalIntro">Hello</span></p><hr>
        <form>
        <p>There will be a form here.</p>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="dataModalcancButton">Close</button>
        <button type="sumbit" class="btn btn-primary" data-complete-text="Finished!" data-error-text="Error" id="dataModalsendButton">Update</button>
      </div>
    </div>
  </div>
</div>


<?php
require_once('php/templates/footer.php');
?>
<script src="js/users.js"></script>