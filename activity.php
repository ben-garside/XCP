<?php
require_once('php/templates/header.php');
if(!$user->isLoggedIn()){
    Redirect::to('login.php?nexturl=activity.php');
}
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
//if($test = Activity::initRunning()) {
//  $datetime1 = new DateTime($test);
//  $datetime2 = new DateTime('now');
//  $interval = $datetime1->diff($datetime2);
//  Session::flash('home-danger','The init process is still running, it has been running since ' .$test . ' (That\'s: ' . $interval->format('%R%a days, %H hours, %i minutes') . '.)');
//  Redirect::to('index.php');
//}
?>
<div class="page-header">

<form method="POST" class="form-inline pull-right" id="select_form">
  <div class="form-group">
    <label class="sr-only" for="select_feed">Select feed </label>
    <select id="select_feed" name="feed" class="form-control">
      <option value="0">All feeds</option>
      <?php
      foreach (Activity::getFeeds() as $feed) {
        echo '<option value="' . $feed->feed_id . '">' . $feed->feed_name . '</option>';
      }
      ?>
    </select>
  </div>
  <div class="form-group">
    <label class="sr-only" for="select_Pipeline">Select Pipeline</label>
    <select id="select_Pipeline" name="Pipeline" class="form-control">
      <option value="0">All pipelines</option>
      <?php
      foreach (Activity::getStreams() as $stream) {
        echo '<option value="' . $stream->id . '">' . $stream->name . '</option>';
      }
      ?>
    </select>
  </div>
  <input type="hidden" value="" id="select_act" name="act"/>
  <?php 

   if($user->hasPermission('admin')){
      ?>
        <div class="form-group">
          <label class="sr-only" for="select_Pipeline">Select Pipeline</label>
          <select id="uid" name="uid" class="form-control">
            <option value="<?php echo $user->data()->id; ?>">Just mine</option>
            <option value="0">All Users</option>
          </select>
        </div>
      <?php
    } else {
      ?>
        <input type="hidden" value="<?php echo $user->data()->id; ?>" id="uid" name="uid"/>
      <?php
    }
  ?>
</form>
<h1>Activity Tracker</h1>
</div>


<ul class="nav nav-tabs" id="act_list">

<li  title="Refresh Data" role="presentation" id="refreshButton">
<a style="color:#777777;" href="javascript:void(0)" onclick="getCounts()" ondblclick="refresh()"><i class="fa fa-refresh"></i></a>
</li>

<?php
$act = new Activity();
foreach ($act->getAllActivities() as $key => $value) {
  echo '<li  title="' . $value->FULL_NAME . ' - '. $value->DESCRIPTION . '" class="act_list_item" role="presentation" id="' . str_pad($value->ID, 2, '0', STR_PAD_LEFT) . '">';
  echo '<a href="javascript:void(0)"  onclick="setActivity(' . str_pad($value->ID, 2, '0', STR_PAD_LEFT) . ');"><span class="label label-default">' . $value->SHORT_NAME . '</span>';
  echo ' <span id="b_' . str_pad($value->ID, 2, '0', STR_PAD_LEFT) . '" class="label label-warning" style="display:none;">0</span>';
  echo ' <span id="m_' . str_pad($value->ID, 2, '0', STR_PAD_LEFT) . '" class="label label-success" style="display:none;">0</span></a></li>';
}

?>


</ul>
<BR/>
<div class="testCon">
  <div id="tasks_mine_panel_test" class=" dataTables_processing" style="display:none; position: absolute;">
  <div >Loading...</div>
  </div>
  <div id="tasks_mine_panel" class="panel panel-success">
    <!-- Default panel contents -->
    <div class="panel-heading">Mine</div>
    <!-- Table -->
    <table id="tasks_mine" class="table table-hover ">
      <thead>
        <tr><th>Material</th><th>Identifier</th><th>Type</th><th>Title</th><th>Latest User</th><th>Date</th><th>Page</th><th>Pipeline</th><th>Status</th><th></th></tr>
      </thead>
      <tbody id="tasks_mine_tbody">
      </tbody>
    </table>
  </div>
  <div id="tasks_team_panel" class="panel panel-warning">
    <!-- Default panel contents -->
    <div class="panel-heading">Unclaimed</div>
    <!-- Table -->
    <form>
    <table id="tasks_team" class="table table-hover">
      <thead>
        <tr><th>Material</th><th>Identifier</th><th>Type</th><th>Title</th><th>Latest User</th><th>Date</th><th>Page</th><th>Pipeline</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    </form>
  </div>
</div>

<!-- START modals -->

<div class="modal fade" id="updateData" tabindex="-1" role="dialog" aria-labelledby="updateData">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel"></h4>
      </div>
      <div class="modal-body">
        <form>          <div class="form-group">
            <label for="recipient-name" class="control-label">Recipient:</label>
            <input type="text" class="form-control" id="recipient-name">
          </div>
          <div class="form-group">
            <label for="message-text" class="control-label">Message:</label>
            <textarea class="form-control" id="message-text"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Update</button>
      </div>
    </div>
  </div>
</div>

<?php
echoActiveIfAttributeMatches('act', $value->SHORT_NAME);
require_once('php/templates/footer.php');
?>
<script src="js/activity.js"></script>