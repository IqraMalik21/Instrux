

<?php

require 'includes.php';
if (isset($_POST['ajax']) && isset($_POST['name'])){
    $gotSelection= $_POST['name'];
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
	SELECT name FROM locations WHERE parent_location_id =(SELECT id FROM locations WHERE name = :lid);");
	$sam->execute(array(':lid' => $getSelection));
	if($sam->rowCount()>0){
		return $sam->fetchAll(PDO::FETCH_ASSOC);
	}
	return FALSE;
}




?>
