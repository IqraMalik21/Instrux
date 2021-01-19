
<?php

require 'includes.php';
if (isset($_POST['ajax']) && isset($_POST['idVal'])){
    $gotSelection= $_POST['idVal'];
	//echo $gotSelection;
	$fetchedChildLocs = childlocs($gotSelection);
	//print_r($fetchedChildLocs);
	 echo json_encode($fetchedChildLocs);
    exit;
}

function childlocs($getSelection){ // to select child locations
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
