<?php
require_once 'php/templates/header.php';

?>
<div class="page-header">
<h1><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Login</h1>
</div>
<?php
if(Session::exists('login-error')) {
?>
      <div class="alert alert-success" role="alert">
        <?php echo Session::flash('login-error');?>
      </div>
<?php
}
if(Input::exists()) {
	if(Token::check(Input::get('token'))) {

		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'username' => array('required' => true),
			'password' => array('required' => true)
		));

		if($validation->passed()) {
			$user = new User();

			$remember = (Input::get('remember') === 'on') ? true : false;
			$login = $user->login(Input::get('username'), Input::get('password'), $remember);

			if($login) {
				if(Input::get('nexturl')){
					Redirect::to(Input::get('nexturl'));
				} else {
					Redirect::to('index.php');
				}
			} else {
				?>
				<div class="alert alert-warning alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<strong>Warning!</strong> Invalid Login.
				</div>
				<?php
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
<div class="row">
  <div class="col-md-6 col-md-offset-3">
	<form class="form-signin" action="" method="post">
        <label for="inputEmail" class="sr-only">User name</label>
        <input type="text" name="username" class="form-control" placeholder="User name" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <input type="hidden" name="token" value="<?php echo Token::generate(); ?>">
        <div class="checkbox">
          <label>
            <input type="checkbox" name="remember" id="remember" value="remember"> Remember me
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>
	</div>
</div>

<?php
require_once('php/templates/footer.php');