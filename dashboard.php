

<?php
require_once 'includes.php';
// if (isset($_POST['ajax']) && isset($_POST['name'])){
//   $getSelection= $_POST['name'];
//   echo $getSelection;
//   $receivedChilds = childLocs($getSelection);
//   print_r($receivedChilds);
//   $count=0;
//   foreach($receivedChilds as $a => $a_value) {
//     $count=$count+1;
//   }
// echo $count;
//  exit;
// }
 ?>

    <link rel="stylesheet" href="cards.css">
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script>
         $(document).ready(function(){
            $("#myselection").change(function(){      
               $("#myselection option:selected").text();
               var selection = $("#myselection option:selected").text() ;
               //document.write(selection);
               //alert(selection);
               $.ajax({
                   url: 'dashboard_data.php',
                   async: true,
                   type:'post',
                   //dataType: "json",
                   data:{ ajax:1, name:selection},
                   success:function(response){
                       
                       alert(response);
                       var JSONStr = response;
                       var JSONObj = JSON.parse(JSONStr);
                       //console.log(JSONObj);      // Dump all data of the Object in the console
                       //alert(JSONObj[0]["name"])
                       //$("#response").html(response);
                       var i=0;
                      JSONObj.forEach(myFunction);
                      //document.getElementById("response").innerHTML = i;
                      function myFunction(value, index, array) {
                      i++;
                      }
                      var locName = new Array();
                      for (var j=0;j<i;j++){
                          locName[j]=JSONObj[j]["name"];
                      }
                      alert(locName);

                   }
               }) ;
            });
         });

       
      </script>
<style>
      body{
        background-color: white !important;
      }
    </style>

<?php


pageRequiresAuthentication();

$deviceId = $_GET['deviceId'];

if(empty($deviceId)){
  $deviceId = $user['defaultDevice'];
}else{
  if($deviceId != $user['defaultDevice']){
    set_user_default_device($userId,$deviceId);
  }
}

$device = user_device_details($userId,$deviceId);

$currentDialLimit = currentDailLimit($deviceId);
$realPowerLimit = 100;
$reactivePowerLimit = 100;
$apparentPowerLimit = 100;

if($deviceId == 41){
  $currentDialLimit = 450; 
}

if($deviceId == 103){
  $realPowerLimit = 600;
  $reactivePowerLimit = 400;
  $apparentPowerLimit = 600;
}

require_once "header.php";




$locs = user_locations($userId);
if($locs){
  if(!$locs){
  echo '<br /><br />';
  }
  echo '  <form method="post">                         
  <!-- Select Location -->
  <div class="form-group"><div class="input-group">
  <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-cogs"></i></span></div> 
  <select class="form-control" name="deviceId" id="myselection" >';
  echo '<option selected disabled>Please Select Location</option>';
  $locations = locs();
  foreach($locations as $lo){
    /*
    $path = $d['name']." > ";
    */
    
    $path = "";
    //foreach($locations as $l){
      $path = $lo['name'];
      echo '<option '.iif($lo['id']==$deviceId,'selected').' value="?deviceId='.$lo['parent_location_id'].'">'.$path.'</option>';
    //}
    
    
  }
  echo '</select></div></div> </form > 
  ';
}else{
  echo display_alert("There are no devices in your account. ");
}


if(!$device){

  // No Device Selected

}  ?>
<style>
#darkCurrent{
    
  background-color: #008080; 
    border-color: #008080 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
}
#darkVoltage{
    background-color: #f09d61 !important;
    border-color: #f09d61 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
}
#darkRealPower{
    background-color: #e96100 !important;
    border-color: #e96100 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;

}
#darkApparent{
    background-color:  #389C38 !important;
    border-color:  #389C38 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
}
#darkReactive{
    background-color: #FF00FF !important;
    border-color: #FF00FF !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
}
#darkPF{
    background-color:#60bd42 !important;
    border-color:#60bd42 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
}

#wNum{
    font-size:200%;
    font-weight: bold;
    margin-left: 32px;
    margin-top: 31px;
    margin-bottom: 0px !important;
    line-height: 8px !important;
    margin-right:0px;
    
}


.white{
    color: white;
}


.cardName{
    text-align: left;
    margin-top: 2px;
    font-size:17px;

}


.card{    
}
small{
  font-size:15px;
  margin-right:0px;
  font-weight:bold;
}

</style>





          
<?php 


$cards = array('<div class="col-md-2"> <center>
  <div class="card mt-4 mx-4  Regular shadow " style="width: 11rem; height:10rem;"  id="voltage">
              <div class="card-body " id="darkVoltage" >
              <h5 class="card-subtitle cardName white" >Average Voltage </h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span  class="white " id="wNum" style="margin-top:47px;">314 <small>V</small></span>
          </div>

 </center>
</div>','<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="current">
<div class="card-body " id="darkCurrent" >
            <h5 class="card-subtitle cardName white" >Average Current</h5>
            <!-- <span class="cardName"><em>Voltage</em></span> -->
            <br>
            <span  class="white " id="wNum" style="margin-top:47px;">314 <small>A</small></span>
        </div>
</center>
</div>','<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="realPower">
            <div class="card-body " id="darkRealPower" >
            <h5 class="card-subtitle cardName white" >Real Power Total</h5>
            <!-- <span class="cardName"><em>Voltage</em></span> -->
            <br>
            <span class="white " id="wNum" style="margin-top:47px;">314 <small>KW</small></span>
        </div>

</center>
</div>','<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="reactive">
              <div class="card-body " id="darkReactive" >
              <h5 class="card-subtitle cardName white" >Reactive Power Total</h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span class="white " id="wNum">314 <small>KVAr</small></span>
          </div>

</center>
</div>',

  '<div class="col-md-2"><center>
  <div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="aparent">
                <div class="card-body " id="darkApparent" >
                <h5 class="card-subtitle mx-0 cardName white" >Apparent Power Total</h5>
                <!-- <span class="cardName"><em>Voltage</em></span> -->
                <br>
                <span class="white " id="wNum">314 <small>KVA</small></span>
            </div>
  </center>
  </div>
  ','
  <div class="col-md-2"><center>
  <div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem; line-height: 20px"  id="pf">
              <div class="card-body " id="darkPF" >
              <h5 class="card-subtitle mx-0 cardName white" >Average Power Factor</h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span class="white " id="wNum">314</span>
          </div>
</center>
  </div>'
  ); 
  
  $row = array(2,4,7)
  
  ?>




<!-- Cards -->


<div id="response">efdfd</div>
  
<?php 


for ($x = 0; $x < count($row); $x++) {
  echo '<div class="row justify-content-flex-start">';
  for ($y = 0; $y < $row[$x]; $y++) {
    echo $cards[$y%6];

    
  }
  echo '</div>';

  
}
?>
  


  




<!-- <div class="cardSection container">
          <div class="serviceCard row justify-content-left">
            
            <div class="card mt-4 mx-4 border rounded" style="width: 10rem; height:9rem; vertical-align: middle; ">
              <div class="card-body " >

              <div class="cardText">314</div>
                
              </div>
            </div>
            <div class="card col col-md-2 mt-4 mx-4 border rounded" style="width: 10rem; height:9rem; vertical-align: middle;">
              <div class="card-body " >

              <div class="cardText">314</div>
                
              </div>
            </div>
            <div class="card col col-md-2 mt-4 mx-4 border rounded" style="width: 10rem; height:9rem; vertical-align: middle;">
              <div class="card-body " >

              <div class="cardText">314</div>
                
              </div>
            </div>
            <div class="card col col-md-2 mt-4 mx-4 border rounded" style="width: 10rem; height:9rem; vertical-align: middle;">
              <div class="card-body " >

              <div class="cardText">314</div>
                
              </div>
            </div>
            <div class="card col col-md-2 mt-4 mx-3 border rounded" style="width: 10rem; height:9rem; vertical-align: middle;">
              <div class="card-body " >

              <div class="cardText">314</div>
                
              </div>
            </div>
            <div class="card col col-md-2 mt-4 mx-4 border rounded" style="width: 10rem; height:9rem; vertical-align: middle;">
              <div class="card-body " >

              <div class="cardText">314</div>
                
              </div>
            </div>

        
          </div>
</div> -->
<!-- Cards -->

          <?php require_once 'footer.php'; ?>
