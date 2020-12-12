<?php

require_once 'includes.php';
pageRequiresAuthentication();

$deviceId = $_GET['deviceId'];

$sam = $PDO->prepare("SELECT * FROM `readings` WHERE `device_id`=:deviceId ORDER BY `id` DESC LIMIT 1");
$sam->execute(array(':deviceId'=>$deviceId));
$array = $sam->fetch();

if($array['powerfactorAverage'] == null){
  $array['powerfactorAverage'] = ($array['powerfactorPhaseR']+$array['powerfactorPhaseY']+$array['powerfactorPhaseB'])/3;
}


header('Content-Type: application/json');

?>
{
  "gaugeChartCols": [
        {"id":"","label":"Label","pattern":"","type":"string"},
        {"id":"","label":"Value","pattern":"","type":"number"}
      ],
  "gaugeChartRows": [

       {"c":[{"v":"Voltage","f":null},{
       "v":"<?php echo round((($array['averageLineNeutralVoltage'])),2); ?>",
       "f":"<?php echo number_format((($array['averageLineNeutralVoltage'])),0); ?> V"
     }]},
       
       {"c":[{"v":"Current","f":"Current"},{
       "v":"<?php echo round((($array['totalLineCurrent'])),2); ?>",
       "f":"<?php echo number_format((($array['totalLineCurrent'])),2); ?> A"
     }]},

       {"c":[{"v":"Real Power","f":null},{
       "v":"<?php echo round(((($array['totalRealPower']))/1000),2);  ?>",
       "f":"<?php echo number_format(((($array['totalRealPower']))/1000),2); ?> KW"
     }]},

       {"c":[{"v":"Reactive","f":null},{
       "v":"<?php echo round((($array['totalReactivePower'])/1000),2); ?>",
       "f":"<?php echo number_format(((($array['totalReactivePower']))/1000),2); ?> KVAr"
     }]},

       {"c":[{"v":"Apparent","f":null},{
       "v":"<?php echo round((($array['totalApparentPower'])/1000),2); ?>",
       "f":"<?php echo number_format(((($array['totalApparentPower']))/1000),2); ?> KVA"
     }]},

       {"c":[{"v":"P.F.","f":null},{
       "v":"<?php echo round(($array['powerfactorAverage']),2); ?>",
       "f":"<?php echo number_format(($array['powerfactorAverage']),2); ?>"
     }]}

      ],
  "comboChartCols": [
        {"id":"","label":"THD","pattern":"","type":"string"},
        {"id":"","label":"Red Phase","pattern":"","type":"number"},
        {"id":"","label":"Yellow Phase","pattern":"","type":"number"},
        {"id":"","label":"Blue Phase","pattern":"","type":"number"}
      ],
  "comboChartRows": [
       {"c":[{"v":"Voltage THD","f":null},{
        "v":"<?php echo ($array['thdVoltageR']); ?>","f":"<?php echo ($array['thdVoltageR']); ?> %"
      },{
        "v":"<?php echo ($array['thdVoltageY']); ?>","f":"<?php echo ($array['thdVoltageY']); ?> %"
      },{
        "v":"<?php echo ($array['thdVoltageB']); ?>","f":"<?php echo ($array['thdVoltageB']); ?> %"
      }]},

       {"c":[{"v":"Current THD","f":null},{
        "v":"<?php echo ($array['thdCurrentR']); ?>","f":"<?php echo ($array['thdCurrentR']); ?> %"
      },{
        "v":"<?php echo ($array['thdCurrentY']); ?>","f":"<?php echo ($array['thdCurrentY']); ?> %"
      },{
        "v":"<?php echo ($array['thdCurrentB']); ?>","f":"<?php echo ($array['thdCurrentB']); ?> %"
      }]}

      ],
  "lineChartCols": [
        {"id":"","label":"Time","pattern":"","type":"string"},
        {"id":"","label":"R","pattern":"","type":"number"},
        {"id":"","label":"Y","pattern":"","type":"number"},
        {"id":"","label":"B","pattern":"","type":"number"},
        {"id":"","label":"T","pattern":"","type":"number"}
      ],
  "lineChartRows": [


<?php

$sam = $PDO->prepare("SELECT DATE_FORMAT(`datetime`, '%H:%i') as `time`,`apparentPowerPhaseR`,`apparentPowerPhaseY`,`apparentPowerPhaseB` , `totalApparentPower` FROM `readings` WHERE `device_id`=:deviceId ORDER BY `id` DESC LIMIT 7");
$sam->execute(array(':deviceId'=>$deviceId));
$row = $sam->fetchAll();

$num = 0;

$row = array_reverse($row);

foreach ($row as $x) {
  echo '
  {"c":[{"v":"'.$x['time'].'","f":null},{
        "v":"'.abs($x['apparentPowerPhaseR']/1000).'","f":"'.abs($x['apparentPowerPhaseR']/1000).' KVA"
      },{
        "v":"'.abs($x['apparentPowerPhaseY']/1000).'","f":"'.abs($x['apparentPowerPhaseY']/1000).' KVA"
      },{
        "v":"'.abs($x['apparentPowerPhaseB']/1000).'","f":"'.abs($x['apparentPowerPhaseB']/1000).' KVA"
      },{
        "v":"'.abs($x['totalApparentPower']/1000).'","f":"'.abs($x['totalApparentPower']/1000).' KVA"
      }]}
  ';
  $num++;
  if(sizeof($row)!=$num){echo ',';}
}

?>

      ],
  "extras": 
       {

        "lastReadingTimestamp":"<?php echo abs($array['timestamp']); ?>",
        "serverTimestamp":"<?php echo time(); ?>",

        "frequency":"<?php echo abs($array['frequency']); ?>",
        "maximumDemandApparentPower":"<?php echo ($array['maximumDemandApparentPower']); ?>",
        "energyValue":"<?php echo number_format(abs($array['energyValue'])/1000,2); ?>",

        "voltagePhaseR":"<?php echo ($array['voltagePhaseR']); ?>",
       	"voltagePhaseY":"<?php echo ($array['voltagePhaseY']); ?>",
       	"voltagePhaseB":"<?php echo ($array['voltagePhaseB']); ?>",

       	"currentPhaseR":"<?php echo ($array['currentPhaseR']); ?>",
       	"currentPhaseY":"<?php echo ($array['currentPhaseY']); ?>",
       	"currentPhaseB":"<?php echo ($array['currentPhaseB']); ?>",

       	"realPowerPhaseR":"<?php echo round(($array['realPowerPhaseR'])/1000,2); ?>",
       	"realPowerPhaseY":"<?php echo round(($array['realPowerPhaseY'])/1000,2); ?>",
       	"realPowerPhaseB":"<?php echo round(($array['realPowerPhaseB'])/1000,2); ?>",

        "reactivePowerPhaseR":"<?php echo round(($array['reactivePowerPhaseR'])/1000,2); ?>",
        "reactivePowerPhaseY":"<?php echo round(($array['reactivePowerPhaseY'])/1000,2); ?>",
        "reactivePowerPhaseB":"<?php echo round(($array['reactivePowerPhaseB'])/1000,2); ?>",

        "apparentPowerPhaseR":"<?php echo round(($array['apparentPowerPhaseR'])/1000,2); ?>",
        "apparentPowerPhaseY":"<?php echo round(($array['apparentPowerPhaseY'])/1000,2); ?>",
        "apparentPowerPhaseB":"<?php echo round(($array['apparentPowerPhaseB'])/1000,2); ?>",

        "powerfactorPhaseR":"<?php echo round($array['powerfactorPhaseR']+0,2); ?>",
        "powerfactorPhaseY":"<?php echo round($array['powerfactorPhaseY']+0,2); ?>",
        "powerfactorPhaseB":"<?php echo round($array['powerfactorPhaseB']+0,2); ?>"
   		}
      
}