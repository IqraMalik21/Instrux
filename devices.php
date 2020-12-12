<?php 

require_once "includes.php";
pageRequiresAuthentication();

if(isset($_GET['location'])){
	$locationId = $_GET['location'];
	$devices = user_devices_on_location($userId,$locationId);
}else{
	$devices = user_devices($userId);
}


function deviceview($id,$dname,$location) {
		echo '
		<div class="col-lg-3">
				<div class="card border-light bg-light mb-3" style="max-width: 20rem;">
		  <div class="card-header text-center">'.$dname.'</div>
		  <div class="card-body">

		    <a href="realtime.php?deviceId='.$id.'"><div class="btn btn-info form-control">Real-Time Monitoring</div></a><br /><br />
		    <!--<a href="logs.php?meter='.$id.'"><div class="btn btn-outline-secondary form-control">Data Log</div></a><br /><br />-->
				<a href="trend.php?meter='.$id.'"><div class="btn btn-outline-secondary form-control">Charts</div></a><br /><br />
				
		    <a href="billing.php?meter='.$id.'"><div class="btn btn-warning form-control">Billing</div></a><br /><br />
		    <a href="bi.php?meter='.$id.'"><div class="btn btn-success form-control">Business Intelligence</div></a><br /><br />

		    <!--<a class="btn btn-warning" href="edit.php?type=device&id='.$id.'"><span class="fa fa-edit"></span></a>
		    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteDeviceModal'.$id.'"><i class="fa fa-trash"></i></button>-->
		  </div>
		  <!--
		  <div class="card-footer">
		      <small class="text-muted">Last updated 3 mins ago</small>
		    </div>-->
		</div></div>


		<div class="modal fade" id="deleteDeviceModal'.$id.'" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="deleteModalLabel">Delete this Device?</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
			        Are you sure you want to delete this device ('.$dname.') in present location?
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			        <a type="button" class="btn btn-danger" href="delete.php?type=device&id='.$id.'"><i class="fa fa-trash"></i> Delete</a>
			      </div>
			    </div>
			  </div>
			</div>

			';
		
}


require_once 'header.php';

?>	

<div class="row">

<?php

if($devices){
	foreach ($devices as $x) {
		deviceview($x['id'],$x['dname'],$x['locationName']);
	}
}

?>

</div>

<?php

require_once "footer.php";

?>