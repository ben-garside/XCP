<?php
require_once 'php/templates/header.php';
?>
<div class="page-header">
<h1>Register an account</h1>
</div>
<?php
if(Input::exists()) {
	if(Token::check(Input::get('token'))) {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'username' => array(
				'required' => true,
				'min' => 2,
				'max' => 20,
				'unique' => 'users',
				'alpha' => true
			),
			'password' => array(
				'required' => true,
				'min' => 6
			),
			'password_again' => array(
				'required' => true,
				'matches' => 'password'
			),
			'name_first' => array(
				'required' => true,
				'min' => 2,
				'max' => 50
			),
			'name_last' => array(
				'required' => true,
				'min' => 2,
				'max' => 50
			),
		));

		if($validation->passed()) {
			$user = new User();
			$salt = Hash::salt(32);
			//$salt = 'ww';

			try {

				$user->create(array(
					'username' => Input::get('username'),
					'password' => Hash::make(Input::get('password'), $salt),
					'salt' => $salt,
					'name_first' => Input::get('name_first'),
					'name_last' => Input::get('name_last'),
					'email' => Input::get('email'),
					'joined' => date('Y-m-d H:i:s'),
					'group_id' => 1
				));

				Session::flash('home-success','Thanks ' . Input::get('name_first') . ', you have registered!');
				Redirect::to('index.php');

			} catch(Exception $e) {
				die($e->getMessage());
			}
		} else {
			?>
			<div class="panel panel-danger">
        		<div class="panel-heading">Please fix the following errors</div>
      			<ul class="list-group">
      		<?php
			foreach($validation->errors() as $error) {
				echo '<li class="list-group-item">' . $error . '</li>';
			}
			echo '</ul></div>';
		}
	}
}
?>


<form class="form-horizontal" action="" method="post" autocomplete="off">
	
	<div class="form-group">
		<label for="username" class="col-sm-2 control-label">Username</label>
		<div class="col-sm-4">
			<input type="text" placeholder="Create a username" class="form-control" name="username" id="username" autocomplete="off" value="<?php echo escape(Input::get('username')) ?>">
		</div>
	</div>

	<div class="form-group">
		<label for="name" class="col-sm-2 control-label">Real Name</label>
		<div class="col-sm-4">
			<input type="text" class="form-control" placeholder="Enter your first name" name="name_first" id="name_first" autocomplete="off" value="<?php echo escape(Input::get('name_first')) ?>">
		</div>
		<div class="col-sm-4">
			<input type="text" class="form-control" placeholder="Enter your last name"name="name_last" id="name_last" autocomplete="off" value="<?php echo escape(Input::get('name_last')) ?>">
		</div>
	</div>
	<div class="form-group">
		<label for="email" class="col-sm-2 control-label">Email</label>
		<div class="col-sm-4">
			<input type="text" class="form-control" placeholder="Enter your email" name="email" id="email" autocomplete="off" value="<?php echo escape(Input::get('email')) ?>">
		</div>
	</div>
	<div class="form-group">
		<label for="password_again" class="col-sm-2 control-label">Password</label>
		<div class="col-sm-4">
			<input type="password" placeholder="Enter a password" class="form-control" name="password_again" id="password_again" autocomplete="off">
		</div>
		<div class="col-sm-4">
			<input type="password" placeholder="Re-enter your password"class="form-control" name="password" id="password" autocomplete="off">
		</div>
	</div>
	<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
	<div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <button type="submit" class="btn btn-primary" value="Register">Register</button>
    </div>
  </div>
</form>

<?php
require_once('php/templates/footer.php');