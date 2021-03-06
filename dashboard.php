  <link rel="stylesheet" href="cards.css">
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    

<style>
      body{
        background-color: #F7F7F7 !important;
      }
    </style>

<?php
require_once 'includes.php';
require_once "header.php";
pageRequiresAuthentication();




$deviceId = $_GET['deviceId'];

if(empty($deviceId)){
  $deviceId = $user['defaultDevice'];
}else{
  if($deviceId != $user['defaultDevice']){
    set_user_default_device($userId,$deviceId);
  }
}
$card_Value = cardValues($deviceId);
print_r($card_Value);

$cards='<div class="col-md-2"> <center>
              <div class="card mt-4 mx-4  Regular shadow " style="width: 11rem; height:10rem;"  id="voltage">
              <div class="card-body " id="darkVoltage" >
              <h5 class="card-subtitle cardName white" >Average Voltage </h5>
             
              <br>
              <span  class="white " id="wNum" style="margin-top:47px;">'.$card_Value[0]['averageLineLineVoltage'].'<small>V</small></span>
          </div>

 </center>
</div>
<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="current">
<div class="card-body " id="darkCurrent" >
            <h5 class="card-subtitle cardName white" >Average Current</h5>
            <!-- <span class="cardName"><em>Voltage</em></span> -->
            <br>
            <span  class="white " id="wNum" style="margin-top:47px;">'.$card_Value[0]['totalLineCurrent'].'<small>A</small></span>
        </div>
</center>
</div>
<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="realPower">
            <div class="card-body " id="darkRealPower" >
            <h5 class="card-subtitle cardName white" >Real Power Total</h5>
            <!-- <span class="cardName"><em>Voltage</em></span> -->
            <br>
            <span class="white " id="wNum" style="margin-top:47px;">'.$card_Value[0]['tRP'].'<small>KW</small></span>
        </div>

</center>
</div>
<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="reactive">
              <div class="card-body " id="darkReactive" >
              <h5 class="card-subtitle cardName white" >Reactive Power Total</h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span class="white " id="wNum">'.$card_Value[0]['tReP'].'<small>KVAr</small></span>
          </div>

</center>
</div>
<div class="col-md-2"><center>
  <div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="aparent">
                <div class="card-body " id="darkApparent" >
                <h5 class="card-subtitle mx-0 cardName white" >Apparent Power Total</h5>
                <!-- <span class="cardName"><em>Voltage</em></span> -->
                <br>
                <span class="white " id="wNum">'.$card_Value[0]['tAP'].'<small>KVA</small></span>
            </div>
  </center>
  </div>
  <div class="col-md-2"><center>
  <div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem; line-height: 20px"  id="pf">
              <div class="card-body " id="darkPF" >
              <h5 class="card-subtitle mx-0 cardName white" >Average Power Factor</h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span class="white " id="wNum">'.iif($card_Value[0]['pfA']==null, "N/A",$card_Value[0]['pfA'] ).'</span>
          </div>
</center>
  </div>';
  

$default_loc = default_loc_id($deviceId); // select default location from database to display cards

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
  echo '<option selected disabled>'.$default_loc[0]['name'].'</option>';
  $locations = locs();
  foreach($locations as $lo){
    /*
    $path = $d['name']." > ";
    */
    
    $path = "";
    //foreach($locations as $l){
      $path = $lo['name'];
      echo '<option '.iif($lo['id']==$deviceId,'selected').' value='.$lo['id'].'">'.$path.'</option>';
    //}
    
    
  }
  echo '</select></div></div> </form > 
  ';
}else{
  echo display_alert("There are no devices in your account. ");
}


if(!$device){

  // No Device Selected

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





 ?>
<style>

.card:hover{
  transform: scale(1.05);
  transition: all 0.5s ease;
}
#darkCurrent{
    
  background-color: #008080; 
    border-color: #008080 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
    font-family: 'Roboto', sans-serif;
}
#darkVoltage{
    background-color: #f09d61 !important;
    border-color: #f09d61 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
    font-family: 'Roboto', sans-serif;
}
#darkRealPower{
    background-color: #e96100 !important;
    border-color: #e96100 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
    font-family: 'Roboto', sans-serif;

}
#darkApparent{
    background-color:  #389C38 !important;
    border-color:  #389C38 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
    font-family: 'Roboto', sans-serif;
}
#darkReactive{
    background-color: #FF00FF !important;
    border-color: #FF00FF !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
    font-family: 'Roboto', sans-serif;
}
#darkPF{
    background-color:#60bd42 !important;
    border-color:#60bd42 !important;
    box-shadow: 8px 8px 4px 0px rgba(0, 0, 255, .15);
    border-radius: 5px;
    font-family: 'Roboto', sans-serif;
}

#wNum{
    font-size:200%;
    
    margin-left: 4px;
    margin-top: 31px;
    margin-bottom: 0px !important;
    line-height: 8px !important;
    margin-right:0px;
    font-family: 'Roboto', sans-serif;
    
}


.white{
    color: white;
    font-family: 'Roboto', sans-serif !important;
    font-weight: 300;
}


.cardName{
    text-align: left;
    margin-top: 2px;
    font-size: 17px;
    font-weight: normal;
}
.ChildLocName{
  display: block;
  border: 1px solid #343A40;
  margin: 0 auto;
  font-size: 100%;
  text-align: center;
  font-family: 'Roboto', sans-serif;
  padding: 5 50;
  width: fit-content;
  font-weight:500;

}
.ChildLocName:hover {
color: white;
background-color: #343A40;
transform: scale(1.05);
transition: all 0.5s ease;

}

.packOfCards{
  margin-right: 20;
}
.grey{
  background-color: white;
  margin: 20 auto;
  padding:20 10;
  border-radius: 10px;
  border: 2px solid white;

}

small{
  font-size:15px;
  margin-right:0px;
  font-weight:400;
}

</style>



<div id="response"></div>
  




<div id="displayCard" class="row ">
<div class= "grey Regular shadow"><div class="ChildLocName" id="<?php echo $default_loc[0]['name']?>"><?php echo $default_loc[0]['name']?></div><div class="row justify-content-flex-start packOfCards"><?php echo $cards?></div></div>
</div>


<script>
         $(document).ready(function(){
            $("#myselection").change(function(){   //runs when a change occurs in dropdown   
               $("#myselection option:selected").text();
               var selection = $("#myselection option:selected").val() ; //save selected value 
               
               $.ajax({
                   url: 'dashboard_data.php',
                   async: true,
                   type:'post',
                   //dataType: "json",
                   data:{ ajax:1, idVal:selection},
                   
                   success:function(response){
                       

                       
                       var JSONStr = response;
                       var JSONObj = JSON.parse(JSONStr); //convert JSON string to obj
                       var i=0;
                      if(response.trim()=="false"){  // trim extra character in response and check it
                         document.getElementById('displayCard').innerHTML = "";
                       }
                      else{
                        JSONObj.forEach(()=>{i++}); //count no of rows
                        var locName = new Array();   // save name of child locs to array
                        for (var j=0;j<i;j++){
                            locName[j]=JSONObj[j]["name"];
                        }
                        displayCards(locName); // display cards
                       }
                   }
               }) ;
            });
         });
         function displayCards(locName){
          var cardsSix=`<div class="col-md-2"> <center>
              <div class="card mt-4 mx-4  Regular shadow " style="width: 11rem; height:10rem;"  id="voltage">
              <div class="card-body " id="darkVoltage" >
              <h5 class="card-subtitle cardName white" >Average Voltage </h5>
             
              <br>
              <span  class="white " id="wNum" style="margin-top:47px;">314<small>V</small></span>
          </div>

 </center>
</div>
<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="current">
<div class="card-body " id="darkCurrent" >
            <h5 class="card-subtitle cardName white" >Average Current</h5>
            <!-- <span class="cardName"><em>Voltage</em></span> -->
            <br>
            <span  class="white " id="wNum" style="margin-top:47px;">314<small>A</small></span>
        </div>
</center>
</div>
<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="realPower">
            <div class="card-body " id="darkRealPower" >
            <h5 class="card-subtitle cardName white" >Real Power Total</h5>
            <!-- <span class="cardName"><em>Voltage</em></span> -->
            <br>
            <span class="white " id="wNum" style="margin-top:47px;">314<small>KW</small></span>
        </div>

</center>
</div>
<div class="col-md-2"><center>
<div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="reactive">
              <div class="card-body " id="darkReactive" >
              <h5 class="card-subtitle cardName white" >Reactive Power Total</h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span class="white " id="wNum">314 <small>KVAr</small></span>
          </div>

</center>
</div>
<div class="col-md-2"><center>
  <div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem;"  id="aparent">
                <div class="card-body " id="darkApparent" >
                <h5 class="card-subtitle mx-0 cardName white" >Apparent Power Total</h5>
                <!-- <span class="cardName"><em>Voltage</em></span> -->
                <br>
                <span class="white " id="wNum">314 <small>KVA</small></span>
            </div>
  </center>
  </div>
  <div class="col-md-2"><center>
  <div class="card mt-4 mx-4 border Regular shadow rounded" style="width: 11rem; height:10rem; line-height: 20px"  id="pf">
              <div class="card-body " id="darkPF" >
              <h5 class="card-subtitle mx-0 cardName white" >Average Power Factor</h5>
              <!-- <span class="cardName"><em>Voltage</em></span> -->
              <br>
              <span class="white " id="wNum">314</span>
          </div>
</center>
  </div>`;
  document.getElementById('displayCard').innerHTML = "  ";
  $("#displayCard").empty()
  if(locName.length!==0){
   
    for (var j=0;j<locName.length;j++){
      document.getElementById('displayCard').innerHTML +='<div class= "grey Regular shadow"><div class="ChildLocName" id="'+locName[j]+'">'+locName[j]+'</div><div class="row justify-content-flex-start packOfCards">'+cardsSix+'</div></div>';
    }  
  }
  else{
    document.getElementById('displayCard').innerHTML= "";
    alert("empty");
  }       
}// display function end

</script>

  

  


  






          <?php require_once 'footer.php'; ?>
