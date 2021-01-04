
<?php

require 'includes.php';
if (isset($_POST['ajax']) && isset($_POST['idVal'])){
    $gotSelection= $_POST['idVal'];
	//echo $gotSelection;
	$fetchedChildLocs = childlocs($gotSelection);
	//print_r($fetchedChildLocs);
	 echo json_encode($fetchedChildLocs);
	//
	
	// $keys = array_keys($fetchedChildLocs);
	// for($i = 0; $i < count($keys); $i++) {
    // 	foreach($fetchedChildLocs[$keys[$i]] as $key => $value) {
	// 		echo $value ."<html><body><br></body></html>";
	// 		//echo  nl2br ($value ." \n");
   	// 	}
	// }
    exit;
}

function childlocs($getSelection){
	global $PDO;
	$sam = $PDO->prepare("
	SELECT id, name FROM locations WHERE parent_location_id =:plid;");
	$sam->execute(array(':plid' => $getSelection));
	if($sam->rowCount()>0){
		return $sam->fetchAll(PDO::FETCH_ASSOC);
	}
	return FALSE;
}




?>
