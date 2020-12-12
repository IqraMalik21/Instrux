    <!-- Side Navbar -->
    <nav class="side-navbar">
      <div class="side-navbar-wrapper">
        <!-- Sidebar Header    -->
        <div class="sidenav-header d-flex align-items-center justify-content-center">
          <!-- User Info-->
          <div class="sidenav-header-inner text-center"><img src="assets/img/user-blue.png" alt="person" class="img-fluid rounded-circle">
            <h2 class="h5"><?php echo $user['username']; ?></h2><span><?php echo $user['type_name']; ?></span>
          </div>
          <!-- Small Brand information, appears on minimized sidebar-->
          <div class="sidenav-header-logo"><a href="devices.php" class="brand-small text-center"> <strong>I</strong><strong class="text-primary">X</strong></a></div>
        </div>
        <!-- Sidebar Navigation Menus-->

        <div class="main-menu">
          <h5 class="sidenav-heading">Engineering</h5>
          <ul id="side-main-menu" class="side-menu list-unstyled">
          <li <?php if($currentPage=="dashboard.php")echo'class="active"'; ?>><a href="dashboard.php"><i class="icon-presentation"></i><?php echo $page['dashboard.php']; ?></a></li>                  
          <li <?php if($currentPage=="realtime.php")echo'class="active"'; ?>><a href="realtime.php"><i class="icon-home"></i><?php echo $page['realtime.php']; ?></a></li>
          <li <?php if($currentPage=="trend.php")echo 'class="active"'; ?>><a href="trend.php"><i class="icon-page"></i><?php echo $page['trend.php']; ?></a></li>
          <li <?php if($currentPage=="reports.php")echo 'class="active"'; ?>><a href="reports.php"><i class="icon-grid"></i><?php echo $page['reports.php']; ?></a></li>
            
          
            <!--<li><a href="#exampledropdownDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-interface-windows"></i>Example dropdown </a>
              <ul id="exampledropdownDropdown" class="collapse list-unstyled ">
                <li><a href="#">Page</a></li>
                <li><a href="#">Page</a></li>
                <li><a href="#">Page</a></li>
              </ul>
            </li>
            <li><a href="login.html"> <i class="icon-interface-windows"></i>Login page                             </a></li>
            <li> <a href="#"> <i class="icon-mail"></i>Demo
                <div class="badge badge-warning">6 New</div></a>
            </li>-->
          </ul>
        </div>

        <div class="admin-menu">
          <h5 class="sidenav-heading">Business</h5>
          <ul id="side-admin-menu" class="side-menu list-unstyled"> 
            <!--<li <?php if($currentPage=="forecasts.php")echo'class="active"'; ?>><a href="forecasts.php"> <i class="icon-line-chart"> </i><?php echo $page['forecasts.php']; ?></a></li>-->
            <li <?php if($currentPage=="bi.php")echo'class="active"'; ?>><a href="bi.php"> <i class="icon-flask"> </i><?php echo $page['bi.php']; ?>
            <li <?php if($currentPage=="billing.php")echo'class="active"'; ?>><a href="billing.php"> <i class="fa fa-dollar-sign"> </i><?php echo $page['billing.php']; ?>
                <!--<div class="badge badge-info">New</div>--></a></li>
          </ul>
        </div>

        <div class="admin-menu">
          <h5 class="sidenav-heading">Management</h5>
          <ul id="side-admin-menu" class="side-menu list-unstyled"> 
            <li <?php if($currentPage=="organization.php")echo'class="active"'; ?>><a href="organization.php"> <i class="icon-website"> </i><?php echo $page['organization.php']; ?></a></li>
            <li <?php if($currentPage=="users.php")echo'class="active"'; ?>><a href="users.php"> <i class="icon-user"> </i><?php echo $page['users.php']; ?>
                <!--<div class="badge badge-info">New</div>--></a></li>
          </ul>
        </div>

        <div class="admin-menu">
          <h5 class="sidenav-heading">Misc.</h5>
          <ul id="side-admin-menu" class="side-menu list-unstyled"> 
            <li <?php if($currentPage=="calculator.php")echo'class="active"'; ?>><a href="calculator.php"> <i class="icon-screen"> </i><?php echo $page['calculator.php']; ?></a></li>
          </ul>
        </div>

      </div>
    </nav>