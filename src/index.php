<?php

require_once('php/templates/header.php');
if(Session::exists('home-success')) {
?>
      <div class="alert alert-success" role="alert">
        <strong>Well done!</strong> <?php echo Session::flash('home-success');?>
      </div>
<?php
}
if(Session::exists('home-danger')) {
?>
      <div class="alert alert-danger" role="alert">
        <strong>Oh snap!</strong> <?php echo Session::flash('home-danger');?>
      </div>
<?php
}
?>

      <div class="jumbotron">
        <h1>XCP <small>Web</small></h1>
        <p>All about XCP.</p>
      </div>

<?php
require_once('php/templates/footer.php');
?>
