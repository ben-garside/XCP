<?php
require_once 'php/templates/header.php';
?>
<div class="page-header">
<h1><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Change Your Password</h1>
</div>
<?php

if(!$user->isLoggedIn()) {
	Redirect::to('index.php');
}

if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		
		$validate = new validate();
		$validation = $validate->check($_POST, array(
			'password_current' => array(
				'required' => true,
				'min' => 6
			),
			'password_new' => array(
				'required' => true,
				'min' => 6
			),
			'password_new_again' => array(
				'required' => true,
				'min' => 6,
				'matches' => 'password_new'
			)
		));

		if($validation->passed()) {

			if(Hash::make(Input::get('password_current'), $user->data()->salt) !== $user->data()->password) {
				echo 'The current password you have supplied is incorrect';
			} else {
				$salt = Hash::salt(32);
				$user->update(array(
					'password' => Hash::make(Input::get('password_new'), $salt),
					'salt' => $salt
				));

				Session::flash('home-success', 'Your password has been updated! You will need to login again.');
				Redirect::to('logout.php');
			}

		} else {
?>
			<div class="panel panel-danger">
        		<div class="panel-heading">Please fix the following errors</div>
      			<ul class="list-group">
      			<?PHP
			foreach ($validation->errors() as $error) {
				echo '<li class="list-group-item">' . $error . '</li>';
			}
			echo '</ul></div>';
		}
	}	
}

?>

<form class="form-horizontal" action="" method="post" autocomplete="off">
	
	<div class="form-group">
		<label for="password_current" class="col-sm-2 control-label">Current Password</label>
		<div class="col-sm-8">
			<input type="password" placeholder="Current Password" class="form-control" name="password_current" id="password_current" autocomplete="off" value="<?php echo escape(Input::get('username')) ?>">
		</div>
	</div>
		<div class="form-group">
		<label for="password_new" class="col-sm-2 control-label">New Password</label>
		<div class="col-sm-8">
			<input type="password" placeholder="New Password" class="form-control" name="password_new" id="password_current" autocomplete="off" value="<?php echo escape(Input::get('username')) ?>">
		</div>
	</div>
		<div class="form-group">
		<label for="password_new_again" class="col-sm-2 control-label">...again</label>
		<div class="col-sm-8">
			<input type="password" placeholder="New Password" class="form-control" name="password_new_again" id="password_current" autocomplete="off" value="<?php echo escape(Input::get('username')) ?>">
		</div>
	</div>
	<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
	<div class="form-group">
    	<div class="col-sm-offset-2 col-sm-10">
      	<button type="submit" class="btn btn-default" value="Change">Change</button>
    	</div>
  	</div>
</form>




<?php
require_once 'includes/footer.php';
