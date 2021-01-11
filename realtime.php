<link rel="stylesheet" href="cards.css">
    <style>
      body{
        background-color: #F7F7F7 !important;
      }
    </style>
<?php

require_once 'includes.php';
pageRequiresAuthentication();

$deviceId = $_GET['deviceId'];

print_r(767);
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


$devices = user_devices($userId);
if($devices){
  if(!$device){
  echo '<br /><br />';
  }
  echo '<!-- Select Device -->
  <div class="form-group"><div class="input-group">
  <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-cogs"></i></span></div>                          
  <select class="form-control" name="deviceId" onchange="location = this.value;">';
  echo '<option selected disabled>Please Select Device</option>';
  foreach($devices as $d){
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

if(!$device){

  // No Device Selected

}else{

?>

<style type="text/css">
svg g text{
font-size: 12px;
}
  .badgesSection {
    margin: 24px 4px;
  }
  .badgesSection div {
    padding: 8px 24px;
    width: 100%;
    text-align: center;
    color: #fff;
    font-size: 14px;
    margin: 6px;
    border-radius: 48px;
    text-shadow: 1px 1px 1px #000;
    box-shadow: 1px 1px 1px #000;
  }
  .phaseBlue {
    background: #004cd6;
  }
  .phaseYellow {
    background: #eac300;
    color: #000 !important;
    text-shadow: 1px 1px 1px #fff !important;
  }
  .phaseRed {
    background: #ab0000;
  }
  .valueBox {
    background: #ececec;
    color: #333;
    padding: 12px;
    margin: 8px;
    text-shadow: 1px 1px 1px #fff;
    text-align: center;
    box-shadow: 1px 1px 1px #aaa;
    border-radius: 6px;
  }
  .valueBox span {
    font-weight: bold;
  }
</style>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script type="text/javascript">

var chart;

var optionsVoltage = {
  min: 0,
  max: 300,
  redFrom: 220, redTo: 245, redColor: '#109618',
  greenFrom: 0, greenTo: 300, greenColor: '#dc3912',
  yellowFrom:200, yellowTo: 255,
  minorTicks: 10
};
var optionsCurrent = {
  min: 0,
  max: <?php echo $currentDialLimit; ?>,
  greenFrom: 2, greenTo: <?php echo $currentDialLimit-($currentDialLimit/5); ?>, 
  yellowFrom: <?php echo $currentDialLimit-($currentDialLimit/5); ?>, yellowTo:  <?php echo $currentDialLimit-($currentDialLimit/10); ?>,
  redFrom: <?php echo $currentDialLimit-($currentDialLimit/10); ?>, redTo:  <?php echo $currentDialLimit; ?>,
  minorTicks: 20
};
var optionsRealPower = {
  min: 0,
  max: <?php echo $realPowerLimit; ?>,
  greenFrom: 0, greenTo: <?php echo $realPowerLimit-($realPowerLimit/5); ?>,
  yellowFrom: <?php echo $realPowerLimit-($realPowerLimit/5); ?>, yellowTo: <?php echo $realPowerLimit-($realPowerLimit/10); ?>,
  redFrom: <?php echo $realPowerLimit-($realPowerLimit/10); ?>, redTo:  <?php echo $realPowerLimit; ?>,
  minorTicks: 50
};
var optionsReactivePower = {
  min: 0,
  max: <?php echo $reactivePowerLimit; ?>,
  greenFrom: 0, greenTo: <?php echo $reactivePowerLimit-($reactivePowerLimit/5); ?>,
  yellowFrom: <?php echo $reactivePowerLimit-($reactivePowerLimit/5); ?>, yellowTo: <?php echo $reactivePowerLimit-($reactivePowerLimit/10); ?>,
  redFrom: <?php echo $reactivePowerLimit-($reactivePowerLimit/10); ?>, redTo:  <?php echo $reactivePowerLimit; ?>,
  minorTicks: 50
};
var optionsApparentPower = {
  min: 0,
  max:<?php echo $apparentPowerLimit; ?>,
  greenFrom: 0, greenTo: <?php echo $apparentPowerLimit-($apparentPowerLimit/5); ?>,
  yellowFrom: <?php echo $apparentPowerLimit-($apparentPowerLimit/5); ?>, yellowTo: <?php echo $apparentPowerLimit-($apparentPowerLimit/10); ?>,
  redFrom: <?php echo $apparentPowerLimit-($apparentPowerLimit/10); ?>, redTo:  <?php echo $apparentPowerLimit; ?>,
  minorTicks: 50
};
var optionsPowerFactor = {
  min: -1,
  max: 1,
  greenFrom: 0.9, greenTo: 1, 
  yellowFrom: 0.88, yellowTo: 0.9,
  redFrom: -1, redTo:  0.88,
  minorTicks: 20
};

var interval = 3000;
var date = new Date();
var timestamp = date.getTime();
var numjs = 0;
var durationLastReading;
var durationLastFetch;

var optionsVoltageTHD = {
  seriesType: 'bars',
  legend: null,
  legend: {position: 'none'},
  vAxis: {minValue: '0', maxValue: '10'},
  colors: ['#8c0e0e', '#dd9b00', '#163a89']
};
var optionsCurrentTHD = {
  seriesType: 'bars',
  legend: null,
  legend: {position: 'none'},
  vAxis: {minValue: '0', maxValue: '30'},
  colors: ['#8c0e0e', '#dd9b00', '#163a89']
};
var data;
var newObj = {};
var objVoltTHD = {};
var objCurrentTHD = {};
var chartVoltageTHD;
var chartCurrentTHD;
var chartPowerLine;
var durationSinceLastDeviceReading;
var lastServerTimeStampMS;
var lastDeviceTimeStampMS;
var lastConnectionTimeStampMS;
var optionsLineChart = {
  title: 'Apparent Power (KVA)',
  curveType: 'function',
  legend: { position: 'bottom' },
  vAxis: {
    minValue: '0', maxValue: '10'
  },
  colors: ['#8c0e0e', '#dd9b00', '#163a89','#008000']
};
var objPowerLine = {};

// Load the Visualization API and the piechart package.
google.charts.load('current', {'packages':['gauge','corechart']});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(loadedGoogleCharts);

function loadedGoogleCharts(){

  chartVoltage = new google.visualization.Gauge(document.getElementById("voltage_gauge"));
  chartCurrent = new google.visualization.Gauge(document.getElementById("current_gauge"));
  chartRealPower = new google.visualization.Gauge(document.getElementById("real_power_gauge"));
  chartReactivePower = new google.visualization.Gauge(document.getElementById("reactive_power_gauge"));
  chartApparentPower = new google.visualization.Gauge(document.getElementById("apparent_power_gauge"));
  chartPowerFactor = new google.visualization.Gauge(document.getElementById("power_factor_gauge"));

  chartVoltageTHD = new google.visualization.ComboChart(document.getElementById("voltageTHD"));
  chartCurrentTHD = new google.visualization.ComboChart(document.getElementById("currentTHD"));

  chartPowerLine = new google.visualization.LineChart(document.getElementById("powerLineChart"));

  drawChart();
}

function drawChart() {

  $.ajax({
      url: "realtime_data.php?deviceId=<?php echo $deviceId; ?>",
      dataType: "json",
      async: true,
      success: function (obj) {

        document.getElementById("wholeBlock").style.display = "block";

        document.getElementById("voltagePhaseR").innerHTML=obj.extras.voltagePhaseR+" V";
        document.getElementById("voltagePhaseY").innerHTML=obj.extras.voltagePhaseY+" V";
        document.getElementById("voltagePhaseB").innerHTML=obj.extras.voltagePhaseB+" V";

        document.getElementById("currentPhaseR").innerHTML=obj.extras.currentPhaseR+" A";
        document.getElementById("currentPhaseY").innerHTML=obj.extras.currentPhaseY+" A";
        document.getElementById("currentPhaseB").innerHTML=obj.extras.currentPhaseB+" A";

        document.getElementById("realPowerPhaseR").innerHTML=obj.extras.realPowerPhaseR+" kW";
        document.getElementById("realPowerPhaseY").innerHTML=obj.extras.realPowerPhaseY+" kW";
        document.getElementById("realPowerPhaseB").innerHTML=obj.extras.realPowerPhaseB+" kW";

        document.getElementById("reactivePowerPhaseR").innerHTML=obj.extras.reactivePowerPhaseR+" kVAr";
        document.getElementById("reactivePowerPhaseY").innerHTML=obj.extras.reactivePowerPhaseY+" kVAr";
        document.getElementById("reactivePowerPhaseB").innerHTML=obj.extras.reactivePowerPhaseB+" kVAr";

        document.getElementById("apparentPowerPhaseR").innerHTML=obj.extras.apparentPowerPhaseR+" kVA";
        document.getElementById("apparentPowerPhaseY").innerHTML=obj.extras.apparentPowerPhaseY+" kVA";
        document.getElementById("apparentPowerPhaseB").innerHTML=obj.extras.apparentPowerPhaseB+" kVA";

        document.getElementById("powerfactorPhaseR").innerHTML=obj.extras.powerfactorPhaseR;
        document.getElementById("powerfactorPhaseY").innerHTML=obj.extras.powerfactorPhaseY;
        document.getElementById("powerfactorPhaseB").innerHTML=obj.extras.powerfactorPhaseB;

        document.getElementById("frequencyValue").innerHTML=obj.extras.frequency;
        document.getElementById("energyValue").innerHTML=obj.extras.energyValue;
        document.getElementById("maximumDemandValue").innerHTML=obj.extras.maximumDemandApparentPower;

        date = new Date();
        lastDeviceTimeStampMS = obj.extras.lastReadingTimestamp;
        lastServerTimeStampMS = obj.extras.serverTimestamp;
        lastConnectionTimeStampMS = date.getTime();

        numjs = 0;

        obj.gaugeChartRows.forEach(function(row) {
          newObj = {};
          newObj.cols = obj.gaugeChartCols;
          newObj.rows = [];
          newObj.rows[0] = row;

          switch(numjs){
            case 0:
            data = new google.visualization.DataTable(newObj);
            chartVoltage.draw(data, optionsVoltage);
            break;
            case 1:
            data = new google.visualization.DataTable(newObj);
            chartCurrent.draw(data, optionsCurrent);
            break;
            case 2:
            data = new google.visualization.DataTable(newObj);
            chartRealPower.draw(data, optionsRealPower);
            break;
            case 3:
            data = new google.visualization.DataTable(newObj);
            chartReactivePower.draw(data, optionsReactivePower);
            break;
            case 4:
            data = new google.visualization.DataTable(newObj);
            chartApparentPower.draw(data, optionsApparentPower);
            break;
            case 5:
            data = new google.visualization.DataTable(newObj);
            chartPowerFactor.draw(data, optionsPowerFactor);
            break;
          }

          numjs++;    

        });


        objVoltTHD = {};
        objCurrentTHD = {};
        objVoltTHD.cols = obj.comboChartCols;
        objCurrentTHD.cols = obj.comboChartCols;
        objVoltTHD.rows = [];
        objCurrentTHD.rows = [];
        objVoltTHD.rows[0] = obj.comboChartRows[0];
        objCurrentTHD.rows[0] = obj.comboChartRows[1];

        data = new google.visualization.DataTable(objVoltTHD);
        chartVoltageTHD.draw(data, optionsVoltageTHD);

        data = new google.visualization.DataTable(objCurrentTHD);
        chartCurrentTHD.draw(data, optionsCurrentTHD);

        objPowerLine = {};
        objPowerLine.cols = obj.lineChartCols;
        objPowerLine.rows = [];
        objPowerLine.rows = obj.lineChartRows;

        data = new google.visualization.DataTable(objPowerLine);
        chartPowerLine.draw(data, optionsLineChart);

      }, // Success
      complete: function (data) {

        document.getElementById("loading-chart").style.display = "none";
        // Schedule the next
        setTimeout(drawChart, interval);
      } // Complete

      }); // Ajax

} // drawChart()

var greenTime = 20;
var yellowTime = 120;

var setTime = function() {

    var deviceStatus = document.getElementById('deviceStatus');
    var serverStatus = document.getElementById('serverStatus');
    var connectionStatus = document.getElementById('connectionStatus');
    var deviceStatusOutline = document.getElementById('deviceStatusOutline');
    var serverStatusOutline = document.getElementById('serverStatusOutline');
    var connectionStatusOutline = document.getElementById('connectionStatusOutline');
    var lastUpdated = document.getElementById('lastUpdated');

    console.log("lastDeviceTimeStampMS: "+lastDeviceTimeStampMS);

    durationSinceLastDeviceReading = timeDurationSince(lastDeviceTimeStampMS * 1000);
    lastUpdated.innerHTML = durationSinceLastDeviceReading;

    durationLastDevice = timeSince(lastDeviceTimeStampMS);
    durationLastServer = timeSince(lastServerTimeStampMS);
    durationLastConnection = timeSince(lastConnectionTimeStampMS/1000);

    // console.log("durationLastDevice: "+durationLastDevice+", lastDeviceTimeStampMS: "+lastDeviceTimeStampMS);
    // console.log("durationLastServer: "+durationLastServer+", lastServerTimeStampMS: "+lastServerTimeStampMS);
    // console.log("durationLastConnection: "+durationLastConnection+", lastConnectionTimeStampMS: "+lastConnectionTimeStampMS);

    if(durationLastDevice < greenTime){ // if last reading from device is less than 10 seconds
      deviceStatus.setAttribute('class', 'badge badge-success');
      deviceStatusOutline.setAttribute('title', 'Connected - Last Reading '+timeDurationSeconds(durationLastDevice)+' ago');
    }else if(durationLastDevice < yellowTime){
      deviceStatus.setAttribute('class', 'badge badge-warning');
      deviceStatusOutline.setAttribute('title', 'Waiting - Last Reading '+timeDurationSeconds(durationLastDevice)+' ago');
    }else{
      deviceStatus.setAttribute('class', 'badge badge-danger');
      deviceStatusOutline.setAttribute('title', 'Disconnected - Last Reading '+timeDurationSeconds(durationLastDevice)+' ago');
    }

    if(durationLastServer < greenTime){ // if last reading from server is less than 10 seconds
      serverStatus.setAttribute('class', 'badge badge-success');
      serverStatusOutline.setAttribute('title', 'Connected - Last Successful Connection '+timeDurationSeconds(durationLastServer)+' ago');
    }else if(durationLastServer < yellowTime){
      serverStatus.setAttribute('class', 'badge badge-warning');
      serverStatusOutline.setAttribute('title', 'Waiting - Last Successful Connection '+timeDurationSeconds(durationLastServer)+' ago');
    }else{
      serverStatus.setAttribute('class', 'badge badge-danger');
      serverStatusOutline.setAttribute('title', 'Disconnected - Last Successful Connection '+timeDurationSeconds(durationLastServer)+' ago');
    }

    if(durationLastConnection < greenTime){ // if last reading from server is less than 10 seconds
      connectionStatus.setAttribute('class', 'badge badge-success');
      connectionStatusOutline.setAttribute('title', 'Connected - Last Try '+timeDurationSeconds(durationLastConnection)+' ago');
    }else if(durationLastConnection < yellowTime){
      connectionStatus.setAttribute('class', 'badge badge-warning');
      connectionStatusOutline.setAttribute('title', 'Waiting - Last Try '+timeDurationSeconds(durationLastConnection)+' ago');
    }else{
      connectionStatus.setAttribute('class', 'badge badge-danger');
      connectionStatusOutline.setAttribute('title', 'Disconnected - Last Try '+timeDurationSeconds(durationLastConnection)+' ago');
    }
};

setInterval(setTime, 1000);
</script>

<div id="loading-chart" class="loading-chart">
  <img class="loading-image" src="assets/img/preloader-chart.svg" alt="Loading..." />
</div>

<div id="wholeBlock" style="display: none;">

<div class="row justify-content-center">
  <button type="button" class="col-lg-2 btn btn-outline-dark"><b><?php echo $device['dname']; ?></b></button>&nbsp; &nbsp;
  <button type="button" id="deviceStatusOutline" class="col-lg-2 btn btn-outline-dark">Device Status: <span id="deviceStatus" class="badge badge-success">&nbsp; &nbsp;</span></button>&nbsp; &nbsp;
  <button type="button" id="lastUpdatedOutline" class="col-lg-3 btn btn-outline-dark">Last Updated <span id="lastUpdated" style="font-weight: 700"></span> ago</button>&nbsp; &nbsp;
  <button type="button" id="serverStatusOutline" class="col-lg-2 btn btn-outline-dark">Server Connection: <span id="serverStatus" class="badge badge-success">&nbsp; &nbsp;</span></button>&nbsp; &nbsp;
  <button type="button" id="connectionStatusOutline" class="col-lg-2 btn btn-outline-dark">Synchronization: <span id="connectionStatus" class="badge badge-success">&nbsp; &nbsp;</span></button>
</div>
<br /><br />

<div class="row justify-content-center">
  <div class="col-md-2"><center><div id="voltage_gauge"></div>

  <div class="badgesSection">
    <div class="phaseRed" id="voltagePhaseR"></div>
    <div class="phaseYellow" id="voltagePhaseY"></div>
    <div class="phaseBlue" id="voltagePhaseB"></div>
  </div>
</center>
</div>
  <div class="col-md-2"><center><div id="current_gauge"></div>

  <div class="badgesSection">
    <div class="phaseRed" id="currentPhaseR"></div>
    <div class="phaseYellow" id="currentPhaseY"></div>
    <div class="phaseBlue" id="currentPhaseB"></div>
  </div>
</center>
</div>
  <div class="col-md-2"><center><div id="real_power_gauge"></div>

  <div class="badgesSection">
    <div class="phaseRed" id="realPowerPhaseR"></div>
    <div class="phaseYellow" id="realPowerPhaseY"></div>
    <div class="phaseBlue" id="realPowerPhaseB"></div>
  </div>

</center>
</div>
  
<div class="col-md-2"><center><div id="reactive_power_gauge"></div>

  <div class="badgesSection">
    <div class="phaseRed" id="reactivePowerPhaseR"></div>
    <div class="phaseYellow" id="reactivePowerPhaseY"></div>
    <div class="phaseBlue" id="reactivePowerPhaseB"></div>
  </div>

</center>
</div>
  
<div class="col-md-2"><center><div id="apparent_power_gauge"></div>

  <div class="badgesSection">
    <div class="phaseRed" id="apparentPowerPhaseR"></div>
    <div class="phaseYellow" id="apparentPowerPhaseY"></div>
    <div class="phaseBlue" id="apparentPowerPhaseB"></div>
  </div>

</center>
</div>

  <div class="col-md-2"><center><div id="power_factor_gauge"></div>

  <div class="badgesSection">
    <div class="phaseRed" id="powerfactorPhaseR"></div>
    <div class="phaseYellow" id="powerfactorPhaseY"></div>
    <div class="phaseBlue" id="powerfactorPhaseB"></div>
  </div>

</center>
  </div>
  
</div>


<div class="row">

  <div class="col-md-2"><div id="voltageTHD"></div></div>
  <div class="col-md-2"><div id="currentTHD"></div></div>

  <div class="col-md-4">
    <div class="valueBox">Frequency: <span id="frequencyValue">0</span> Hz</div>
    <div class="valueBox">Energy: <span id="energyValue">0</span> kWh</div>
    <div class="valueBox">Maximum Demand(KW): <span id="maximumDemandValue">0</span></div>
  </div>

  <div class="col-md-4">
    <div id="powerLineChart"></div>
  </div>
  
</div>

</div> <!-- wholeBlock -->


<?php

}

require_once 'footer.php';


?>

