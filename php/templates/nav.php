<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php">XCP 1.2</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
      <?php if($user->isLoggedIn()){ ?>
        <!-- ACTIVITY -->
        <li <?php echoActiveClassIfRequestMatches("activity"); ?>><a href="activity.php">Activity Tracker</a></li>
        
        <!-- ADMIN -->
        <?php if($user->inRole(1)){ ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li <?php echoActiveClassIfRequestMatches("flow"); ?>><a href="users.php" ><i class="fa fa-users fa-fw"></i> Manage Users</a></li>
            </ul>
          </li>  
        <?php } ?>
        <!-- ADMIN END -->

        <!-- REPORTS -->
        <?php if($user->inRole(7)){ ?>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reports <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li <?php echoActiveClassIfRequestMatches("rep"); ?>><a href="rep.php" ><i class="fa fa-bar-chart fa-fw"></i> Test reports</a></li>
            <li <?php echoActiveClassIfRequestMatches("dumpAudit"); ?>><a href="dumpAudit.php"><i class="fa fa-download fa-fw"></i> Dump Audit</a></li>

          </ul>
        </li> 
        <?php } ?>
        <!-- REPORTS END -->

        <!-- MANAGE -->
        <?php if($user->inRole(2)){ ?>
        <li class="dropdown">
            <a id="dLabel" data-toggle="dropdown"  data-target="#" href="#">Manage <span class="caret"></span></a>
            <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">
              <li <?php echoActiveClassIfRequestMatches("flow"); ?>><a href="flow.php" ><i class="fa fa-random fa-fw"></i> Stage Manager</a></li>
              <li <?php echoActiveClassIfRequestMatches("actionmanager"); ?>><a href="actionmanager.php" ><i class="fa fa-table fa-fw"></i> Action Manager</a></li>
              <li <?php echoActiveClassIfRequestMatches("activitymanager"); ?>><a href="activitymanager.php" ><i class="fa fa-fw fa-bolt"></i></i> Activity Manager</a></li>
              <li class="divider"></li>
              <li><a href="addexclution.php" ><i class="fa fa-times"></i> Exclude Content</a></li>
              
            </ul>
        </li>
        <?php } ?>
        <!-- MANAGE END -->
      <?php } ?>
      </ul>

    <?php
    if($user->isLoggedIn()){
      ?>
      <p class="navbar-text navbar-right" style="padding-right: 20px;"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <a href="changepassword.php" class="navbar-link"><?php echo $user->data()->username . " (" . $user->group() . ")"; ?></a> | <a href="logout.php" class="navbar-link">sign out</a></p>
      <?php
    } else {
      ?>
      <p class="navbar-text navbar-right" style="padding-right: 20px;"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <a href="login.php" class="navbar-link">Log In</a></p>
      <?php
    }
    ?>
      <form class="navbar-form navbar-right" role="search" action="search.php">
        <div class="form-group">
          <input type="text" class="form-control" name="term" id="searchInput" placeholder="Search">
        </div>
      </form>
    </div><!--/.nav-collapse -->

  </div><!--/.container-fluid -->
</nav>


