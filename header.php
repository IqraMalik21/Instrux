<?php

require_once "header-head.php";

// Side Nav Bar
require_once "navigation-left.php";
?>

<div class="page">
      
<?php
require_once "navigation-top.php";
?>
      <!-- Breadcrumb-->
      <div class="breadcrumb-holder">
        <div class="container-fluid">
          <ul class="breadcrumb">
          <li class="breadcrumb-item">Dashboard</li>
            <li class="breadcrumb-item active"><?php echo $currentPageTitle; ?></li>
          </ul>
        </div>
      </div>
      
      <section>
        <div class="container-fluid">
          <!-- Page Header-->
          <!--<header> 
            <h1 class="h3 display">Tables            </h1>
          </header>-->
          <br />

<?php 

if(!is_page_authorized()){
  echo display_alert("Sorry, You are not authorized to access this module. 
  Kindly contact your Administrator.");
  require_once "footer.php";
  die();
}

?>