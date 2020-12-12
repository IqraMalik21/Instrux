<?php

require_once 'includes.php';
require_once 'header.php';
require_once "footer.php";

$loc = user_locations($userId);
if($loc){
  if(!$loc){
  echo '<br /><br />';
  }
  echo '<!-- Select Location -->
  <div class="form-group"><div class="input-group">
  <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-cogs"></i></span></div>                          
  <select class="form-control" name="deviceId" onchange="location = this.value;">';
  echo '<option selected disabled>Please Select Location</option>';
  foreach($loc as $d){
    /*
    $path = $d['name']." > ";
    */
    $path = "";
    $locations = fetch_locations_with_levels($d['location_id']);
    foreach($locations as $l){
      $path .= $l['name']." > ";
    }
    
    echo '<option '.iif($d['id']==$deviceId,'selected').' value="?deviceId='.$d['id'].'">'.$path.$d['dname'].'</option>';
  }
  echo '</select></div></div>';
}else{
  echo display_alert("There are no devices in your account. ");
}
?>