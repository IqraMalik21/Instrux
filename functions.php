<?php

function redirect($url){
	header("Location: ".$url);
	exit;
}

function refresh(){
	redirect($_SERVER['REQUEST_URI']);
}

function load_template_file($file){
	require_once 'templates/'.TEMPLATE.'/'.$file;
}

function page_exists($page){
	return file_exists("pages/".$page.".php");
}

function show_page($page){
	require_once 'pages/'.$page.".php";
}

function load_page($page){
	if(page_exists($page)){
		show_page($page);
	}else{
		show_page("404");
	}
}

function get_email_verification_code($email){
	global $PDO;
	$sam = $PDO->prepare("SELECT `verification_code` FROM `s` WHERE `email`=:email");
	$sam->execute(array(':email'=>$email));
	if($sam->rowCount()>0){
		return $sam->fetch()['verification_code'];
	}
	return FALSE;
}

function generateRandomString($length = 10){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function set_email_verified($email){
	global $PDO;
	$sam = $PDO->prepare("UPDATE `users` SET email_verified='1' WHERE `email`=:email");
	$sam->execute(array(':email'=>$email));
	return $sam->rowCount();
}

function update_verification_code($email){
	global $PDO;
	$verification_code = generateRandomString(14);
	$sam = $PDO->prepare("UPDATE `users` SET `verification_code`=:verification_code WHERE `email`=:email");
	$sam->execute(array(':email'=>$email,':verification_code'=>$verification_code));
	if($sam->rowCount()>0){
		return $verification_code;
	}
	return FALSE;
}

function update_password($email, $password=''){
	global $PDO;

	if(empty($password)){
		$password = generateRandomString(14);
	}

	$passwordEncrypted = password_encryption($password);
	
	$sam = $PDO->prepare("UPDATE `users` SET `password`=:password WHERE `email`=:email");
	$sam->execute(array(':email'=>$email,':password'=>$passwordEncrypted));
	if($sam->rowCount()>0){
		return $password;
	}
	return FALSE;
}

function send_email_verification($email,$verificationCode){

	$subject = "Verify your Email";
	$link = URL.'/verifyEmail.php?email='.$email.'&code='.$verificationCode;

	$message = '
	Dear User,<br /><br />
	You are required to follow the URL below to verify your email address for your InstruX account:<br />
	<a href="'.$link.'">'.$link.'</a>
	<br /><br />
	Thanks.
	';

	return send_email($email,$subject,$message);
}

function send_password_reset_link($email,$verificationCode){

	$subject = "Reset your Password";
	$link = URL.'/resetPassword.php?email='.$email.'&code='.$verificationCode;

	$message = '
	Dear User,<br /><br />
	As requested, you can follow the URL below to reset your account password:<br />
	<a href="'.$link.'">'.$link.'</a>
	<br /><br />
	Thanks.
	';

	return send_email($email,$subject,$message);
}


function send_email($to,$subject,$message,$fromEmail=DEFAULT_EMAIL,$fromName=DEFAULT_EMAIL_NAME){
	
	// Constants such as SMTP_SERVER defined in config.php
	try{
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = SMTP_SERVER;  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = SMTP_USERNAME;                 // SMTP username
		$mail->Password = SMTP_PASSWORD;                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted

		$mail->Port       = 465;

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->addCustomHeader('MIME-version', "1.0");
        //$mail->addCustomHeader('Content-type', "text/calendar; method=REQUEST; charset=UTF-8");
        $mail->addCustomHeader('From', $fromEmail);
        $mail->addCustomHeader('Reply-To', 'instruxic@gmail.com');
        $mail->addCustomHeader('Content-Transfer-Encoding', "8bit");
        //$mail->addCustomHeader('X-Mailer', "Microsoft Office Outlook 10.0");
        //$mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

		$mail->From = $fromEmail;
		$mail->FromName = $fromName;
		$mail->addAddress($to);   // Add a recipient
		//echo $to;  

		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = strip_tags($message);

		if(!$mail->send()) {
			return 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			//echo 'Message has been sent';
			return TRUE;
		}

	} catch (Exception $e) {
		return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}

	return FALSE;

}

function display_alert($alert,$type="warning"){
	return '<div class="alert alert-'.$type.'">'.$alert.'</div>';
}

function retrieve_array_from_arrays($arrays,$key){
	$array = array();
	foreach($arrays as $a){
		array_push($array,$a[$key]);
	}
	return $array;
}

function column_names($table){
	global $PDO;
	$sam = $PDO->prepare("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".DB_NAME."' AND `TABLE_NAME`=:tableName");
	$sam->execute(array(":tableName"=>$table));
	if($sam->rowCount()>0){
		$array = $sam->fetchAll();
		$columns = array();
		foreach($array as $column){
			array_push($columns,$column['COLUMN_NAME']);
		}
		return $columns;
	}
	return FALSE;
}

function getAllUserTypes($userId){
	global $PDO,$userTypeId;
	if($userTypeId==SUPER_USER){
		$sam = $PDO->query("SELECT * FROM `user_types`");
		if($sam->rowCount()>0){
			return $sam->fetchAll();
		}
	}else{
		return get_all_user_type_children($userTypeId);
	}
	
	return FALSE;	
}
function get_devices_smartfan(){
    global $PDO;
    $sam = $PDO->query("SELECT * FROM `devices` WHERE `location_id` IS NULL");
    return $sam->fetchAll();
}

function get_data_bulk($device_id=0,$limit=0){
    global $PDO;
    $PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    $queryArray = array();
    $query = "SELECT * FROM `data_bulk` data LEFT JOIN `data_batch` batch ON batch.id = data.batch_id";   
    if($device_id>0){
        $query .= " WHERE batch.device_id=:device_id";
        $queryArray[':device_id'] = $device_id;
    }
    $query .= " ORDER BY data.id DESC";    
    if($limit>0){
        $query .= " LIMIT 0,:limit";
        $queryArray[':limit'] = $limit;
    }
    if(!empty($queryArray)){
        $sam = $PDO->prepare($query);
        $sam->execute($queryArray);
    }else{
        $sam = $PDO->query($query);
    }
    if($sam->rowCount()>0){
        return $sam->fetchAll();
    }
    return FALSE;    
}

function getAllUserTypesJoined($userId){
	global $PDO,$userTypeId;
	if($userTypeId==SUPER_USER){
		$sam = $PDO->query("SELECT 
		ut.*,
		put.type_name as parent_type_name,
		u.username as maker,
		GROUP_CONCAT(DISTINCT(m.name) SEPARATOR ', ') as modules,
		GROUP_CONCAT(DISTINCT(p.name) SEPARATOR ', ') as permissions
		FROM `user_types` ut
		LEFT JOIN `user_types` put ON put.id = ut.parent_type_id
		LEFT JOIN `users` u ON ut.user_id = u.id
		LEFT JOIN `user_type_modules` utm ON ut.id = utm.type_id
		LEFT JOIN `modules` m ON m.id = utm.module_id
		LEFT JOIN `user_type_permissions` utp ON ut.id = utp.type_id
		LEFT JOIN `permissions` p ON p.id = utp.permission_id
		GROUP BY ut.id");
	}else{
		
		$sam = $PDO->prepare("SELECT 
		ut.*,
		put.type_name as parent_type_name,
		u.username as maker,
		GROUP_CONCAT(DISTINCT(m.name) SEPARATOR ', ') as modules,
		GROUP_CONCAT(DISTINCT(p.name) SEPARATOR ', ') as permissions
		FROM (
			SELECT *
			FROM user_types
			WHERE parent_type_id = :parent_id
			UNION
			SELECT * 
			FROM user_types
			WHERE parent_type_id IN 
				(SELECT id FROM user_types WHERE parent_type_id = :parent_id)
		) ut
		LEFT JOIN `user_types` put ON put.id = ut.parent_type_id
		LEFT JOIN `users` u ON ut.user_id = u.id
		LEFT JOIN `user_type_modules` utm ON ut.id = utm.type_id
		LEFT JOIN `modules` m ON m.id = utm.module_id
		LEFT JOIN `user_type_permissions` utp ON ut.id = utp.type_id
		LEFT JOIN `permissions` p ON p.id = utp.permission_id
		GROUP BY ut.id");
		$sam->execute(array(':parent_id'=>$userTypeId));
	}
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
}

function data_series_high_chart($data){
	$return = "";
	$elements = sizeof($data);
	$num = 0;
	$defaultInVisible = array('realPowerPhaseR','realPowerPhaseY','realPowerPhaseB',
	'reactivePowerPhaseR','reactivePowerPhaseY','reactivePowerPhaseB');
	$realStyle = array('realPowerPhaseR','realPowerPhaseY','realPowerPhaseB');
	$reactiveStyle = array('reactivePowerPhaseR','reactivePowerPhaseY','reactivePowerPhaseB');
	$apparentStyle = array('apparentPowerPhaseR','apparentPowerPhaseY','apparentPowerPhaseB');
	foreach ($data as $x=>$y) {
		$num++;
		$return .= "
		{
	        name: '".feasible_param($x)."',
	        data: [
	        	".$y."
			]
    	}
		".iif($num!=$elements,",");
	}
return $return;
}
function draw_high_chart($containerId,$series,$min='',$max='',$title='',$yAxisLegend=''){
return "
<div id=\"".$containerId."\"></div>
<script type=\"text/javascript\">
Highcharts.chart('".$containerId."', {
            chart: {
                zoomType: 'x'
            },
            credits: {
            enabled: false
			},
			time: {
				timezoneOffset: -5 * 60
			},
            title: {
                text: '".$title."'
            },
            subtitle: {
                text: document.ontouchstart === undefined ?
                        'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
            },
            xAxis: {
                type: 'datetime'
            },
            yAxis: {
                title: {
                    text: '".$yAxisLegend."'
				},
				resize: {
					enabled: true
				},
				startOnTick: false,
				minPadding: 0.01,
				endOnTick: false,
				maxPadding: 0.01
                ".iif(!empty($max),", max:".$max)."
                ".iif(!empty($min),", min:".$min)."
            },
            legend: {
                enabled: true
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
			},
			
			
            series: ".$series."
        });
</script>
";
}



function cardValues($dev_id){ // to select card Values
	global $PDO;
	$sam = $PDO->prepare("
	SELECT (averageLineLineVoltage), (totalLineCurrent), ROUND((energyValue/1000), 3) AS eV, ROUND((totalRealPower/1000), 3) AS tRP, ROUND((totalApparentPower/1000), 3) AS tAP, 
	ROUND((totalReactivePower/1000),3) AS tReP, powerfactorAverage AS pfA FROM readings WHERE device_id=:devid ORDER BY id DESC LIMIT 1");
	$sam->execute(array(':devid' => $dev_id));
	if($sam->rowCount()>0){
		return $sam->fetchAll(PDO::FETCH_ASSOC);
	}
	return FALSE;
}

function getAllLocations($userId){
	global $PDO;
	$user = getUserDetails($userId);
	if($user['user_type_id']==SUPER_USER) {
		$sam = $PDO->query("SELECT l.*,GROUP_CONCAT(DISTINCT(lu.user_id) SEPARATOR ', ') as users 
		FROM `locations` l 
		LEFT JOIN `location_users` lu ON lu.location_id = l.id
		GROUP BY l.id
		");
	}else{
		$sam = $PDO->prepare("SELECT l.*,GROUP_CONCAT(DISTINCT(lu.user_id) SEPARATOR ', ') as users
		FROM (
			SELECT l1.*
		FROM locations l1
        LEFT JOIN location_users lu1
        ON lu1.location_id = l1.id
		WHERE lu1.user_id = :user_id
		UNION
		SELECT * 
		FROM locations l2
		WHERE l2.parent_location_id IN 
			(SELECT l3.id FROM locations l3
            LEFT JOIN location_users lu3
            ON lu3.location_id = l3.id
            WHERE lu3.user_id = :user_id)
		) l LEFT JOIN `location_users` lu ON lu.location_id = l.id
		GROUP BY l.id
			");
		$sam->execute(array(':user_id'=>$userId));
	}
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function usersOptions($users,$id=''){
    $return = "<option disabled selected>Select User</option>";
    if(is_array($users)){
    foreach($users as $x){
        $selected = "";
        if(!empty($id) && $id===$x["id"]){$selected = "selected";}
        $return .= '<option value="'.$x['id'].'" '.$selected.' >'.$x['username'].' ('.$x['email'].')</option>';
    }}
    return $return;
}

function usersOptionsMulti($users,$usersSelect=''){
	$return = "";
	if(!empty($usersSelect)){$usersSelect = explode(", ",$usersSelect);}
    if(is_array($users)){
    foreach($users as $x){
        $selected = "";
        if(is_array($usersSelect) && in_array($x['id'],$usersSelect)){$selected = "selected";}
        $return .= '<option value="'.$x['id'].'" '.$selected.' >'.$x['username'].' ('.$x['email'].')</option>';
    }}
    return $return;
}

function make_modal($id,$content,$title='',$submitButton='',$deleteButton='',$deleteId=''){
	return '
	
	<!-- Modal-->
	<div id="'.$id.'" tabindex="-1" role="dialog" aria-labelledby="'.$id.'Label" aria-hidden="true" class="modal fade text-left">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="'.$id.'Label" class="modal-title">'.$title.'</h5>
					<button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
				</div>
				<form method="POST">
					<div class="modal-body">                                                                
						
					'.$content.'
														
					</div>
					<div class="modal-footer">
					'.iif(!empty($submitButton),
					'<input type="submit" name="submit" value="'.$submitButton.'" class="btn btn-warning">'
					).'
					
					'.iif(!empty($deleteButton),
					'<a data-dismiss="modal" data-toggle="modal" href="#delete'.$id.'" class="btn btn-danger">Delete</a>
					<button type="button" data-dismiss="modal" class="btn btn-secondary">Cancel</button>',
					'<button type="button" data-dismiss="modal" class="btn btn-secondary">Close</button>'
					).'						                                           
					</div>
				</form>                            
			</div>
		</div>
	</div>
	<!-- Modal-->


	'.iif(!empty($deleteButton),'
	<!-- Delete Modal-->
	<div id="delete'.$id.'" tabindex="-1" role="dialog" aria-labelledby="delete'.$id.'Label" aria-hidden="true" class="modal fade text-left">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 id="delete'.$id.'Label" class="modal-title">Delete '.$title.'</h5>
					<button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
				</div>
				<form method="POST">
					<div class="modal-body">                                                                
						
					Are you sure you want to delete it?
														
					</div>
					<div class="modal-footer">
					<input type="hidden" name="'.$deleteButton.'" value="'.$deleteId.'" class="btn btn-warning">
					<input type="submit" name="submit" value="Delete" class="btn btn-warning">
					<button type="button" data-dismiss="modal" class="btn btn-secondary">Cancel</button>						                                           
					</div>
				</form>                            
			</div>
		</div>
	</div>
	<!-- Delete Modal-->
	').'
	
	';
}

function password_encryption($password){
	return hash('sha256', $password);
}

function check_organization($id){
	global $PDO;
	$sam = $PDO->prepare("SELECT * FROM `locations` WHERE `id`=:id AND `parent_location_id`='0'");
	$sam->execute(array(':id'=>$id));
	if($sam->rowCount()==1){
		return $sam->fetch();
	}
	return FALSE;
}

function add_user($username,$email,$password,$type_id){
	global $PDO;

	$password = password_encryption($password);

	$sam = $PDO->prepare("INSERT INTO `users` SET 
	`username`=:username,
	`email`=:email,
	`password`=:password,
	`user_type_id`=:type_id
	");
	$sam->execute(array(
		':username'=>$username,
		':email'=>$email,
		':password'=>$password,
		':type_id'=>$type_id
	));
	return $PDO->lastInsertId(); // Returns User id
}

function delete_user($user_id){
	global $PDO;
	$sam = $PDO->prepare("DELETE FROM `users` WHERE `id`=:user_id");
	$sam->execute(array(':user_id'=>$user_id));
	return $sam->rowCount();
}

function update_user($user_id,$username,$email,$password,$type_id){
	global $PDO;
	
	if(empty($password)){

		$sam = $PDO->prepare("UPDATE `users` SET 
		`username`=:username,
		`email`=:email,
		`user_type_id`=:type_id
		WHERE `id`=:user_id
		");
		$sam->execute(array(
			':username'=>$username,
			':email'=>$email,
			':type_id'=>$type_id,
			':user_id'=>$user_id
		));

	}else{

		$password = password_encryption($password);

		$sam = $PDO->prepare("UPDATE `users` SET 
		`username`=:username,
		`email`=:email,
		`password`=:password,
		`user_type_id`=:type_id
		WHERE `id`=:user_id
		");
		$sam->execute(array(
			':username'=>$username,
			':email'=>$email,
			':password'=>$password,
			':type_id'=>$type_id,
			':user_id'=>$user_id
		));
	}
	
	return $sam->rowCount(); // Returns User id
}


function duplicate_email($email,$user_id=''){
	global $PDO;
	if(empty($user_id)){
		$sam = $PDO->prepare("SELECT `id` FROM `users` WHERE `email`=:email");
		$sam->execute(array(':email'=>$email));
	}else{
		$sam = $PDO->prepare("SELECT `id` FROM `users` WHERE `email`=:email AND `id`!=:id");
		$sam->execute(array(':email'=>$email,':id'=>$user_id));
	}
	if($sam->rowCount()>0){
		return TRUE;
	}
	return FALSE;
}

function add_user_type($type_name,$user_id,$modules,$permissions,$parent_type_id){
	global $PDO;
	$sam = $PDO->prepare("INSERT INTO `user_types` SET 
	`type_name`=:type_name,
	`parent_type_id`=:parent_type_id,
	`user_id`=:user_id
	");
	$sam->execute(array(
		':type_name'=>$type_name,
		':parent_type_id'=>$parent_type_id,
		':user_id'=>$user_id
	));

	$type_id = $PDO->lastInsertId(); // Returns User Type id

	if($type_id > 0){
		assign_user_type_modules($type_id,$modules);
		assign_user_type_permissions($type_id,$permissions);
		return $type_id;
	}
	
	return FALSE;
}

function add_location($name,$parent_location_id,$users){
	global $PDO;

	$sam = $PDO->prepare("INSERT INTO `locations` SET 
	`name`=:name,
	`parent_location_id`=:parent_location_id");
	$sam->execute(array(
		':name'=>$name,
		':parent_location_id'=>$parent_location_id
	));
	$location_id = $PDO->lastInsertId();
	if($location_id > 0){
		assign_location_users($location_id,$users);
		return $location_id;
	}
	return FALSE;
}

function update_location($location_id,$name,$parent_location_id,$users,$unselected_users=array()){
	global $PDO;

	delete_location_users($location_id,$unselected_users); // Delete only unselected ones by this user (yet they were shown to user)
	assign_location_users($location_id,$users);

	$sam = $PDO->prepare("UPDATE `locations` SET 
	`name`=:name,
	`parent_location_id`=:parent_location_id
	WHERE `id`=:id
	");
	$sam->execute(array(
		':id'=>$location_id,
		':name'=>$name,
		':parent_location_id'=>$parent_location_id
	));
	return $sam->rowCount();
}

function update_user_type($type_id,$type_name,$user_id,$modules,$permissions,$parent_type_id){
	global $PDO;

	delete_user_type_modules($type_id);
	delete_user_type_permissions($type_id);
	assign_user_type_modules($type_id,$modules);
	assign_user_type_permissions($type_id,$permissions);

	$sam = $PDO->prepare("UPDATE `user_types` SET 
	`type_name`=:type_name,
	`parent_type_id`=:parent_type_id,
	`user_id`=:user_id
	WHERE `id`=:type_id
	");
	$sam->execute(array(
		':type_id'=>$type_id,
		':type_name'=>$type_name,
		':parent_type_id'=>$parent_type_id,
		':user_id'=>$user_id
	));
	return $sam->rowCount();
}

function delete_user_type($type_id){
	global $PDO;

	delete_user_type_modules($type_id);
	delete_user_type_permissions($type_id);

	$sam = $PDO->prepare("DELETE FROM `user_types` WHERE `id`=:type_id");
	$sam->execute(array(':type_id'=>$type_id));
	return $sam->rowCount();
}

function delete_location($location_id){
	global $PDO;
	delete_location_users($location_id);
	$sam = $PDO->prepare("DELETE FROM `locations` WHERE `id`=:location_id");
	$sam->execute(array(':location_id'=>$location_id));
	return $sam->rowCount();
}

function delete_location_user($location_id,$user_id){
	global $PDO;
	$sam = $PDO->prepare("DELETE FROM `location_users` WHERE 
	`location_id`=:location_id AND `user_id`=:user_id");
	$sam->execute(array(':location_id'=>$location_id,':user_id'=>$user_id));
	return $sam->rowCount();
}

function delete_location_users($location_id,$unselected_users=''){
	$return = 0;
	$children = get_all_location_children($location_id);

	if(is_array($children)){
		foreach($children as $location_child){
			$return += delete_location_users_query($location_child['id'],$unselected_users);
		}
	}

	$return += delete_location_users_query($location_id,$unselected_users); // Delete the parent
	return $return;
}

function delete_location_users_query($location_id,$unselected_users=''){
	global $PDO;

	if(is_array($unselected_users) && !empty($unselected_users)){
		//$delete = implode(",",$unselected_users);
		$placeHolders = implode(', ', array_fill(0, count($unselected_users), '?'));
		$sam = $PDO->prepare("DELETE FROM `location_users` WHERE `location_id`=? 
		AND `user_id` IN (".$placeHolders.")");

		$sam->bindValue(1, $location_id, PDO::PARAM_INT);
		$num=0; // Using num instead of array index as it can vary
		foreach ($unselected_users as $index => $value) {
			$sam->bindValue($num + 2, $value, PDO::PARAM_INT);
			$num++;
		}
		$sam->execute();
		return $sam->rowCount();

	}elseif($unselected_users===''){
		$sam = $PDO->prepare("DELETE FROM `location_users` WHERE `location_id`=:location_id");
		$sam->execute(array(':location_id'=>$location_id));
		return $sam->rowCount();
	}
	
	return 0;	
}

function delete_user_type_modules($type_id){
	global $PDO;
	$sam = $PDO->prepare("DELETE FROM `user_type_modules` WHERE `type_id`=:type_id");
	$sam->execute(array(':type_id'=>$type_id));
	return $sam->rowCount();
}

function delete_user_type_permissions($type_id){
	global $PDO;
	$sam = $PDO->prepare("DELETE FROM `user_type_permissions` WHERE `type_id`=:type_id");
	$sam->execute(array(':type_id'=>$type_id));
	return $sam->rowCount();
}

function assign_location_users($location_id,$users){
	global $PDO;
	$return = 0;

	$children = get_all_location_children($location_id);

	if(is_array($users)){
		foreach($users as $user_id){
			add_location_user($location_id,$user_id);

			if(is_array($children)){
				foreach($children as $location_child){
					add_location_user($location_child['id'],$user_id);
					$return++;
				}
			}

			$return++;
		}
	}
	return $return;	// Return Number of Modules Assigned
}

function get_all_location_children($parent_id){
	global $PDO;
	$sam = $PDO->prepare("SELECT *
	FROM locations
	WHERE parent_location_id = :parent_id
	UNION
	SELECT * 
	FROM locations
	WHERE parent_location_id IN 
		(SELECT id FROM locations WHERE parent_location_id = :parent_id)");
	$sam->execute(array(':parent_id'=>$parent_id));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function get_all_user_type_children($parent_id){
	global $PDO;
	$sam = $PDO->prepare("SELECT *
	FROM user_types
	WHERE parent_type_id = :parent_id
	UNION
	SELECT * 
	FROM user_types
	WHERE parent_type_id IN 
		(SELECT id FROM user_types WHERE parent_type_id = :parent_id)");
	$sam->execute(array(':parent_id'=>$parent_id));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function add_location_user($location_id,$user_id){
	global $PDO;
	delete_location_user($location_id,$user_id);
	$sam = $PDO->prepare("INSERT INTO `location_users` SET 
	`location_id`=:location_id,
	`user_id`=:user_id
	");
	$sam->execute(array(
		':location_id'=>$location_id,
		':user_id'=>$user_id
	));
}

function assign_user_type_modules($type_id,$modules){
	global $PDO;
	$return = 0;
	if(is_array($modules)){
		foreach($modules as $module_id){
			$sam = $PDO->prepare("INSERT INTO `user_type_modules` SET 
			`type_id`=:type_id,
			`module_id`=:module_id
			");
			$sam->execute(array(
				':type_id'=>$type_id,
				':module_id'=>$module_id
			));
			$return++;
		}
	}
	return $return;	// Return Number of Modules Assigned
}

function assign_user_type_permissions($type_id,$permissions){
	global $PDO;
	$return = 0;
	if(is_array($permissions)){
		foreach($permissions as $permission_id){
			$sam = $PDO->prepare("INSERT INTO `user_type_permissions` SET 
			`type_id`=:type_id,
			`permission_id`=:permission_id
			");
			$sam->execute(array(
				':type_id'=>$type_id,
				':permission_id'=>$permission_id
			));
			$return++;
		}
	}	
	return $return;	// Return Number of Permissions Assigned
}

function getUserDetails($userId){
	global $PDO;
	$sam = $PDO->prepare("SELECT ut.type_name as type_name, u.* FROM `users` u 
	LEFT JOIN `user_types` ut ON ut.id=u.user_type_id
	WHERE u.`id`=:userid");
	$sam->execute(array(':userid'=>$userId));
	if($sam->rowCount()==1){
		return $sam->fetch();
	}
	return FALSE;
}

function getAllUsersWithType($adminRole,$user_id=0){
	global $PDO;
	if($adminRole == SUPER_USER){
		$sam = $PDO->query("SELECT 
		GROUP_CONCAT(DISTINCT(l.name) SEPARATOR ', ') as organization, ut.type_name as type_name, u.* 
		FROM `users` u 
		LEFT JOIN `user_types` ut ON ut.id=u.user_type_id
		LEFT JOIN `location_users` lu ON lu.user_id = u.id
		LEFT JOIN `locations` l ON (l.id=lu.location_id AND l.parent_location_id=0)
		GROUP BY u.id
		");
	}else{
		$sam = $PDO->prepare("SELECT 
		GROUP_CONCAT(DISTINCT(l.name) SEPARATOR ', ') as organization, ut.type_name as type_name, u.* 
		FROM `location_users` lu
		LEFT JOIN `location_users` lu2 ON (lu2.location_id=lu.location_id)
		LEFT JOIN `users` u ON u.id=lu2.user_id
		LEFT JOIN `user_types` ut ON ut.id=u.user_type_id
		LEFT JOIN `locations` l ON (l.id=lu2.location_id AND l.parent_location_id=0)
		WHERE lu.user_id=:user_id AND l.parent_location_id=0
		GROUP BY u.id");
		$sam->execute(array(':user_id'=>$user_id));
	}
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function get_user_authorized_pages($typeId){
	global $PDO;
	$sam = $PDO->prepare("SELECT m.file
	FROM `user_type_modules` utm
	LEFT JOIN `modules` m ON utm.module_id = m.id
	WHERE utm.type_id=:type_id
	");
	$sam->execute(array(':type_id'=>$typeId));
	if($sam->rowCount()>0){
		return $sam->fetchAll(PDO::FETCH_COLUMN);
	}
	return FALSE;
}

function get_user_permissions($typeId){
	global $PDO;
	$sam = $PDO->prepare("SELECT p.name
	FROM `user_type_permissions` utp
	LEFT JOIN `permissions` p ON utp.permission_id = p.id
	WHERE utp.type_id=:type_id
	");
	$sam->execute(array(':type_id'=>$typeId));
	if($sam->rowCount()>0){
		return $sam->fetchAll(PDO::FETCH_COLUMN);
	}
	return FALSE;
}

function is_page_authorized(){
	global $userTypeId;
	$authorizedPages = get_user_authorized_pages($userTypeId);
	$currentPage = basename(strtok($_SERVER["REQUEST_URI"],'?'));

	if(is_array($authorizedPages)){
		if(in_array($currentPage,$authorizedPages)){
			return TRUE;
		}
	}

	// TODO: move the following to database
	// table modules with column allowGuest
	if($currentPage == 'login.php' || $currentPage == 'register.php'){
		return TRUE; // Authorize Login/Registration pages
	}

	// Incase of debug
	if($userTypeId == SUPER_USER){
		return TRUE; // return FALSE when debugging
		$debug = '<button class="btn btn-warning btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">Debug
		</button>
		<div class="collapse" id="collapseExample">
		<div class="card card-body">
		<pre>Current Page: '.$currentPage.'</pre>
		<pre>User Type Id: '.$userTypeId.'</pre>
		<pre>Authorized Pages: '.var_export($authorizedPages, TRUE).'</pre>
		</div>
		<br /><br />
		</div>';
		
		echo $debug;
	}	
	
	return FALSE;
}

function getAllModules(){
	global $PDO;
	$sam = $PDO->query("SELECT * FROM `modules`");
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function getAllPermissions(){
	global $PDO;
	$sam = $PDO->query("SELECT * FROM `permissions`");
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function user_locations($userid){
	global $PDO;
	$sam = $PDO->prepare("SELECT l.* FROM `locations` l LEFT JOIN `location_users` lu ON lu.location_id=l.id
	WHERE lu.`user_id`=:userid");
	$sam->execute(array(':userid'=>$userid));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function user_device_details($userid,$deviceId){
	global $PDO;
	$sam = $PDO->prepare("SELECT DISTINCT(d.id), d.*,l.name as locationName FROM `devices` d 
	LEFT JOIN `device_users` du ON du.device_id=d.id
	LEFT JOIN `location_users` lu ON `lu`.`location_id`=d.`location_id`
	LEFT JOIN `locations` l ON `l`.`id`=d.`location_id`
	WHERE (du.`user_id`=:userid OR lu.`user_id`=:useridd) AND d.id=:deviceId");
	$sam->execute(array(':userid'=>$userid,':useridd'=>$userid,':deviceId'=>$deviceId));
	if($sam->rowCount()==1){
		return $sam->fetch();
	}
	return FALSE;
}

function default_loc_id($defaultDeviceid){
	global $PDO;
	$sam = $PDO->prepare("
	SELECT name FROM locations WHERE id = (SELECT location_id FROM devices WHERE id = :defaultDeviceid)");
	$sam->execute(array(':defaultDeviceid'=>$defaultDeviceid));
	//print_r($sam->fetchAll());
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function locs(){
	global $PDO;
	$sam = $PDO->prepare("
	SELECT * FROM locations WHERE parent_location_id = 0");
	$sam->execute();
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}



function fetch_locations_with_levels($childest){
	global $PDO;
	$sam = $PDO->prepare("
	SELECT *
	FROM (
		SELECT
			@r AS _id,
			(SELECT @r := parent_location_id FROM locations WHERE id = _id) AS parent_location_id,
			@l := @l + 1 AS lvl
		FROM
			(SELECT @r := :childest, @l := 0) vars,
			locations m
		WHERE @r <> 0) T1
	JOIN locations T2
	ON T1._id = T2.id
	ORDER BY T1.lvl DESC
	");
	$sam->execute(array(':childest'=>$childest));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function fetch_locations_and_devices_5_levels($userid){
	global $PDO;
	$sam = $PDO->prepare("
	SELECT 
	l0.id as l0id, l0.name as l0name, d0.id as d0id, d0.dname as d0name,
	l1.id as l1id, l1.name as l1name, d1.id as d1id, d1.dname as d1name,
	l2.id as l2id, l2.name as l2name, d2.id as d2id, d2.dname as d2name,
	l3.id as l3id, l3.name as l3name, d3.id as d3id, d3.dname as d3name,
	l4.id as l4id, l4.name as l4name, d4.id as d4id, d4.dname as d4name

	FROM `location_users` lu

	LEFT JOIN `locations` l0 ON l0.id=lu.location_id
	LEFT JOIN `devices` d0 ON d0.location_id=l0.id

	LEFT JOIN `locations` l1 ON l1.parent_location_id=l0.id
	LEFT JOIN `devices` d1 ON d1.location_id=l1.id

	LEFT JOIN `locations` l2 ON l2.parent_location_id=l1.id
	LEFT JOIN `devices` d2 ON d2.location_id=l2.id

	LEFT JOIN `locations` l3 ON l3.parent_location_id=l2.id
	LEFT JOIN `devices` d3 ON d3.location_id=l3.id

	LEFT JOIN `locations` l4 ON l4.parent_location_id=l3.id
	LEFT JOIN `devices` d4 ON d4.location_id=l4.id

	WHERE lu.user_id = :userid
	");
	$sam->execute(array(':userid'=>$userid));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function user_devices_all_level($childest){
	global $PDO;
	$sam = $PDO->prepare("
	SELECT DISTINCT(d.id), d.* FROM `devices` d 
	LEFT JOIN `device_users` du ON du.device_id=d.id
	LEFT JOIN `location_users` lu ON `lu`.`location_id`=d.`location_id`
	
	LEFT JOIN (SELECT *
	FROM (
		SELECT
			@r AS _id,
			(SELECT @r := l.parent_location_id FROM locations l WHERE l.id = _id) AS parent_location_id,
			@l := @l + 1 AS lvl
		FROM
			(SELECT @r := :childest, @l := 0) vars,
			locations m
		WHERE @r <> 0) T1
	JOIN locations T2
	ON T1._id = T2.id
	ORDER BY T1.lvl DESC) mloc ON `mloc`.`id`=lu.`location_id`

	WHERE du.`user_id`=:userid OR lu.`user_id`=:useridd
	");
	$sam->execute(array(':childest'=>$childest));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function user_devices($userid){
	global $PDO;
	$sam = $PDO->prepare("SELECT DISTINCT(d.id), d.*, l.name 
	FROM `devices` d 
	LEFT JOIN `locations` l ON l.id=d.location_id
	LEFT JOIN `device_users` du ON du.device_id=d.id
	LEFT JOIN `location_users` lu ON `lu`.`location_id`=d.`location_id`
	WHERE du.`user_id`=:userid OR lu.`user_id`=:useridd");
	$sam->execute(array(':userid'=>$userid,':useridd'=>$userid));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function set_user_default_device($userId,$deviceId){
	global $PDO;
	$sam = $PDO->prepare("UPDATE `users` SET `defaultDevice`=:deviceId WHERE `id`=:userId");
	$sam->execute(array(':userId'=>$userId,':deviceId'=>$deviceId));
}

function user_devices_on_location($userId,$locationId){
	global $PDO;
	$sam = $PDO->prepare("SELECT d.* as locationName 
	FROM `devices` d 
	LEFT JOIN `locations` l ON l.`id`=d.`location_id` 
	LEFT JOIN `location_users` lu ON l.`id`=lu.`location_id` 
	LEFT JOIN `device_users` du ON d.`id`=du.`location_id` 
	WHERE `du`.user_id=:userId OR lu.user_id=:userIdd  AND `d`.location_id=:locationId");
	$sam->execute(array(':userId'=>$userId,':userIdd'=>$userId,':locationId'=>$locationId));
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}
	return FALSE;
}

function insert_shift($deviceId,$name,$frequency,$intervalFrom,$intervalTo){
	global $PDO;
	$sam = $PDO->prepare("INSERT INTO `shifts` SET 
	`name`=:name,
	`device_id`=:deviceId,
	`frequency`=:frequency,
	`intervalFrom`=:intervalFrom,
	`intervalTo`=:intervalTo
	");
	$sam->execute(array(
		':name'=>$name,
		':deviceId'=>$deviceId,
		':frequency'=>$frequency,
		':intervalFrom'=>$intervalFrom,
		':intervalTo'=>$intervalTo
	));
}

function device_shifts($deviceId){
	global $PDO;
	$sam = $PDO->prepare("SELECT * FROM `shifts` WHERE `device_id`=:deviceId");
	$sam->execute(array(':deviceId'=>$deviceId));
	return $sam->fetchAll();
}

function delete_shift($shiftId){
	global $PDO;
	$sam = $PDO->prepare("DELETE FROM `shifts` WHERE `id`=:shiftId");
	$sam->execute(array(':shiftId'=>$shiftId));
}

function hour_from_f($time){
	$timeArray = explode(":",$time);
	$hour = $timeArray[0];
	return $hour;
}

function hour_float_from_time($time){
	$timeArray = explode(":",$time);
	$hour = $timeArray[0];
	$minute = $timeArray[1];
	return $hour+($minute/60);
}

function hour_correction_display($time){
	global $timezoneOffset;
	$timeArray = explode(":",$time);
	$hour = $timeArray[0];
	if($hour<$timezoneOffset){$timeArray[0] = sprintf('%02d', $hour);
	return implode(":",$timeArray);}
	else {
	$timeArray[0] = sprintf('%02d', $hour+$timezoneOffset);
	return implode(":",$timeArray);
	}
	
}

function hour_correction_save($time){
	global $timezoneOffset;
	$timeArray = explode(":",$time);
	$hour = $timeArray[0];
	if($hour<$timezoneOffset)
	{$timeArray[0] = sprintf('%02d', $hour);
	return implode(":",$timeArray);}
	else{	$difference = $hour-$timezoneOffset;
	if($difference < 0){$difference = 24 + $difference;}
	$timeArray[0] = sprintf('%02d', $difference);
	return implode(":",$timeArray);}
}

function calculateIntervalSum($deviceId,$frequency,$intervalFrom,$intervalTo,$durationFrom,$durationTo){
	global $PDO;

	switch($frequency){
		
		case "daily":

		if(hour_float_from_time($intervalFrom) > hour_float_from_time($intervalTo)){

			$sam = $PDO->prepare("
				SELECT (MAX(`energyValue`)-MIN(`energyValue`)) as energy FROM `readings` 
				WHERE `device_id`=:deviceId AND `timestamp`>=:durationFrom AND `timestamp`<:durationTo 
				AND TIME(`datetime`) NOT BETWEEN :intervalTo AND :intervalFrom
				GROUP BY YEAR(`datetime`), MONTH(`datetime`), DAY(`datetime`)
			");
			$sam->execute(array(
				':deviceId'=>$deviceId,
				':durationFrom'=>$durationFrom,
				':durationTo'=>$durationTo,
				':intervalFrom'=>$intervalFrom,
				':intervalTo'=>$intervalTo
			));

		}else{

			$sam = $PDO->prepare("
				SELECT (MAX(`energyValue`)-MIN(`energyValue`)) as energy FROM `readings` 
				WHERE `device_id`=:deviceId AND `timestamp`>=:durationFrom AND `timestamp`<:durationTo 
				AND TIME(`datetime`) BETWEEN :intervalFrom AND :intervalTo
				GROUP BY YEAR(`datetime`), MONTH(`datetime`), DAY(`datetime`)
			");
			$sam->execute(array(
				':deviceId'=>$deviceId,
				':durationFrom'=>$durationFrom,
				':durationTo'=>$durationTo,
				':intervalFrom'=>$intervalFrom,
				':intervalTo'=>$intervalTo
			));

		}

		break;


		case "monthly":

		if($intervalFrom > $intervalTo){

			$sam = $PDO->prepare("
				SELECT (MAX(`energyValue`)-MIN(`energyValue`)) as energy FROM `readings` 
				WHERE `device_id`=:deviceId AND `timestamp`>=:durationFrom AND `timestamp`<:durationTo 
				AND DAY(`datetime`) NOT BETWEEN :intervalTo AND :intervalFrom
				GROUP BY YEAR(`datetime`), MONTH(`datetime`)
			");
			$sam->execute(array(
				':deviceId'=>$deviceId,
				':durationFrom'=>$durationFrom,
				':durationTo'=>$durationTo,
				':intervalFrom'=>$intervalFrom,
				':intervalTo'=>$intervalTo
			));

		}else{

			$sam = $PDO->prepare("
				SELECT (MAX(`energyValue`)-MIN(`energyValue`)) as energy FROM `readings` 
				WHERE `device_id`=:deviceId AND `timestamp`>=:durationFrom AND `timestamp`<:durationTo 
				AND DAY(`datetime`) BETWEEN :intervalFrom AND :intervalTo
				GROUP BY YEAR(`datetime`), MONTH(`datetime`)
			");
			$sam->execute(array(
				':deviceId'=>$deviceId,
				':durationFrom'=>$durationFrom,
				':durationTo'=>$durationTo,
				':intervalFrom'=>$intervalFrom,
				':intervalTo'=>$intervalTo
			));

		}

		break;

		default:
		return FALSE;
	}

	$array = $sam->fetchAll();
	return $array;
}

function calculateReactiveFromReal($real,$powerFactor){
	 // kW = kVAr / √( ( 1 - PF2) / PF2 )
	 //$reactive = $real * (sqrt((1-pow($powerFactor,2))/pow($powerFactor,2)));

	 //$powerFactor = cos(atan($reactive/$real));
	 $reactive = $real * tan(acos($powerFactor));

	 return $reactive;
}

function calculatePowerFactor($real,$reactive){
	// = cos(atan(reactive/real))
	if($real!=0){
		$powerFactor = cos(atan($reactive/$real));
	}else{
		$powerFactor = 0;
	}

	return $powerFactor;
}

function calculateSurcharge($realOffPeak, $realOnPeak, $surchageOffPeakRate, $surchageOnPeakRate){
	$surcharge = ($realOffPeak*$surchageOffPeakRate)+($realOnPeak*$surchageOnPeakRate);
	return $surcharge;
}

function calculateFixedCharges($MDI,$fixRate){
	$fixedCharges = $MDI * $fixRate;
	return $fixedCharges;
}

function calculateVariableCharges($realOffPeak,$realOnPeak,$tariffOffPeak,$tariffOnPeak){
	$variableCharges = ($realOffPeak * $tariffOffPeak) + ($realOnPeak * $tariffOnPeak);
	return $variableCharges;
}


function calculatePowerFactorPenalty($powerFactorOffPeak,$fixedCharges){
	if($powerFactorOffPeak<0.9){
		$powerFactorPenalty = (0.9-$powerFactorOffPeak)*2*$fixedCharges;
	}else{
		$powerFactorPenalty = 0;
	}
	return $powerFactorPenalty;
}

function calculateFuelAdjustment($realOnPeak,$realOffPeak,$ISPRRate){
	$fuelAdjustCharges = ($realOnPeak+$realOffPeak)*$ISPRRate;
	return $fuelAdjustCharges;
}

function calculateElectricDuty($variableCharges,$fuelAdjustCharges){
	$electricDuty = ($variableCharges+$fuelAdjustCharges)*0.015;
	return $electricDuty;
}

function calculateGST($fixedCharges,$variableCharges,$powerFactorPenalty,$fuelAdjustCharges,$electricDuty){
	$gst = ($fixedCharges + $variableCharges + $powerFactorPenalty + $fuelAdjustCharges + $electricDuty + 20)*0.17;
	return $gst;
}

function calculateIncomeTax($fixedCharges,$variableCharges,$powerFactorPenalty,$fuelAdjustCharges,$electricDuty,$gst,$ISPA,$incomeTax){
	$incomeTax = ($fixedCharges + $variableCharges + $powerFactorPenalty + $fuelAdjustCharges + $electricDuty + $gst + $ISPA + 20)*$incomeTax;
	return $incomeTax;
}

function calculateTotal($fixedCharges,$variableCharges,$powerFactorPenalty,$fuelAdjustCharges,$electricDuty,$gst,$incomeTax,$surcharge){
	$total = 8+20+120+ $fixedCharges + $variableCharges + $powerFactorPenalty + $fuelAdjustCharges + $electricDuty + $gst + $incomeTax + $surcharge;
	return $total;
}

function calculateAllCharges($realOffPeak,$realOnPeak,$reactiveOnPeak,$reactiveOffPeak,$MDI,$fixRate,$surchageOffPeakRate,$surchageOnPeakRate,$tariffOffPeak,$tariffOnPeak,$ISPA,$ISPRRate,$incomeTax){

	$bill = func_get_args();

	$bill['surcharge'] = calculateSurcharge($realOffPeak, $realOnPeak, $surchageOffPeakRate, $surchageOnPeakRate);
	$bill['powerFactorOnPeak'] = calculatePowerFactor($realOnPeak, $reactiveOnPeak);
	$bill['powerFactorOffPeak'] = calculatePowerFactor($realOffPeak, $reactiveOffPeak);
	$bill['fuelAdjustCharges'] = calculateFuelAdjustment($realOnPeak,$realOffPeak,$ISPRRate);
	$bill['fixedCharges'] = calculateFixedCharges($MDI,$fixRate);
	$bill['variableCharges'] = calculateVariableCharges($realOffPeak,$realOnPeak,$tariffOffPeak,$tariffOnPeak);

	$bill['powerFactorPenalty'] = calculatePowerFactorPenalty($bill['powerFactorOffPeak'],$bill['fixedCharges']);
	$bill['electricDuty'] = calculateElectricDuty($bill['variableCharges'],$bill['fuelAdjustCharges']);
	$bill['gst']=calculateGST($bill['fixedCharges'],$bill['variableCharges'],$bill['powerFactorPenalty'],$bill['fuelAdjustCharges'],$bill['electricDuty']);
	$bill['incomeTax'] = calculateIncomeTax($bill['fixedCharges'],$bill['variableCharges'],$bill['powerFactorPenalty'],$bill['fuelAdjustCharges'],$bill['electricDuty'],$bill['gst'],$ISPA,$incomeTax);
	$bill['total'] = calculateTotal($bill['fixedCharges'],$bill['variableCharges'],$bill['powerFactorPenalty'],$bill['fuelAdjustCharges'],$bill['electricDuty'],$bill['gst'],$bill['incomeTax'],$bill['surcharge']);
	
	return $bill;
}

function onPeakHour($hour,$OnPeakHoursFrom,$OnPeakHoursTo,$timezoneOffset){
	if($hour>=($OnPeakHoursFrom-$timezoneOffset) && $hour<($OnPeakHoursTo-$timezoneOffset)){
		return true;
	}
	return false;
}



function number_shorten($number, $precision = 2, $divisors = null) {

    // Setup default $divisors if not provided
    if (!isset($divisors)) {
        $divisors = array(
            pow(1000, 0) => '', // 1000^0 == 1
            pow(1000, 1) => 'K', // Thousand
            pow(1000, 2) => 'M', // Million
            pow(1000, 3) => 'B', // Billion
            pow(1000, 4) => 'T', // Trillion
            pow(1000, 5) => 'Qa', // Quadrillion
            pow(1000, 6) => 'Qi', // Quintillion
        );    
    }

    // Loop through each $divisor and find the
    // lowest amount that matches
    foreach ($divisors as $divisor => $shorthand) {
        if (abs($number) < ($divisor * 1000)) {
            // We found a match!
            break;
        }
    }

    // We found our match, or there were no matches.
    // Either way, use the last defined value for $divisor.
    return number_format($number / $divisor, $precision) . $shorthand;
}

function iif($condition,$true,$false=''){
	if($condition){
		return $true;
	}else{
		return $false;
	}
}

function update_settings_authority(){
	global $userTypeId;
	$permissions = get_user_permissions($userTypeId);
	if($userTypeId==SUPER_USER || (is_array($permissions) 
	&& in_array('Update Settings Authority',$permissions))){
		return TRUE;
	}
	return FALSE;
}

function pageRequiresAuthentication(){
	if(!checkLoginStatusUser()){
		redirect('login.php');
	}

	if(!empty($_POST)){ // incase there is a form submit

		if(!is_page_authorized() || !update_settings_authority()){
			require_once "includes.php";
			require_once "header.php";
			echo display_alert("Sorry, You are not authorized to make this request as 
			".iif(!is_page_authorized(),'module is not accessible',
			iif(!update_settings_authority(),'update settings authority is revoked for your account'))."
			. 
			Kindly contact your Administrator.");
			require_once "footer.php";
			die();
		}

	}
}

function checkCredentialsUser($userId,$password){
	global $PDO;
	$sam = $PDO->prepare("SELECT `id` FROM `users` WHERE `id`=:userId AND `password`=:password");
	$sam->execute(array(':userId'=>$userId,':password'=>$password));
	if($sam->rowCount()==1){
		return TRUE;
	}
	return FALSE;
}

function feasible_param($parameter){
	return ucfirst(str_replace(array('R', 'Y', 'B'), array('Red', 'Yellow', 'Blue'), implode(' ',preg_split('/(?=[A-Z])/',$parameter))));
}


function checkLoginStatusUser(){

	global $_SESSION, $_COOKIE;

	$status = FALSE;

	if(!isset($_SESSION['user']) || !isset($_SESSION['key'])){
		if(isset($_COOKIE['user']) && isset($_COOKIE['key'])){
			$_SESSION['user'] = $_COOKIE['user'];
			$_SESSION['key'] = $_COOKIE['key'];
		}
	}

	if(isset($_SESSION['user']) && isset($_SESSION['key'])){
		$userId = $_SESSION['user'];
		$password = $_SESSION['key'];

		$status = checkCredentialsUser($userId,$password);
	}

	if($status){
		return TRUE;
	}else{
		logoutUser();
		return FALSE;
	}

}

function logoutUser(){

	session_start();

	global $_SESSION, $_COOKIE;

	$_SESSION['user'] = false;
	$_SESSION['key'] = false;
	setcookie('user',null,-1);
	setcookie('key',null,-1);

	unset($_SESSION['user']);
	unset($_SESSION['key']);
	unset($_COOKIE['user']);
	unset($_COOKIE['key']);

	session_destroy();

}

function get_expense_from_unit($unit){
	return round($unit * 24);
}

function convert_Wh_to_KWh($Wh){
	return $Wh / 1000;
}

function get_kwh_last_month($deviceId){
	global $PDO;
	$month = date("m",time()+18000);
	if($month != "01"){

	$sam = $PDO->prepare("SELECT 
	MAX( energyValue ) AS maxEnergy, 
	MIN( energyValue ) AS minEnergy, 
	(SELECT r2.energyValue
	FROM `readings` r2
	WHERE r2.energyValue IS NOT NULL
	AND MONTH(r2.datetime) = MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(r2.datetime) = YEAR(CURDATE())
	AND `device_id`=:deviceId
	ORDER BY r2.id DESC
	LIMIT 1
	) AS latestEnergy
	FROM `readings`
	WHERE MONTH(datetime) = MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(datetime) = YEAR(CURDATE())
	AND `device_id`=:deviceIdd");
	$sam->execute(array(
		'deviceId'=>$deviceId,
		'deviceIdd'=>$deviceId
	));}

	else{
		$sam = $PDO->prepare("SELECT 
	MAX( energyValue ) AS maxEnergy, 
	MIN( energyValue ) AS minEnergy, 
	(SELECT r2.energyValue
	FROM `readings` r2
	WHERE r2.energyValue IS NOT NULL
	AND MONTH(r2.datetime) = MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(r2.datetime) = YEAR(CURDATE())-1
	AND `device_id`=:deviceId
	ORDER BY r2.id DESC
	LIMIT 1
	) AS latestEnergy
	FROM `readings`
	WHERE MONTH(datetime) = MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(datetime) = YEAR(CURDATE())-1
	AND `device_id`=:deviceIdd");
	$sam->execute(array(
		'deviceId'=>$deviceId,
		'deviceIdd'=>$deviceId
	));
	}
	if($sam->rowCount()==1){
		$row = $sam->fetch();
		if($row['maxEnergy']>$row['latestEnergy']){
			$value = ($row['latestEnergy'] - 0) + ($row['maxEnergy'] - $row['minEnergy']);
		}else{
			$value = $row['maxEnergy'] - $row['minEnergy'];
		}
		return convert_Wh_to_KWh($value);
	}
	return 0;
}

function get_kwh_this_month($deviceId){
	global $PDO;
	$sam = $PDO->prepare("SELECT 
	MAX( energyValue ) AS maxEnergy, 
	MIN( energyValue ) AS minEnergy, 
	(SELECT r2.energyValue
	FROM `readings` r2
	WHERE r2.energyValue IS NOT NULL
	AND MONTH(r2.datetime) = MONTH(CURDATE()) AND YEAR(r2.datetime) = YEAR(CURDATE())
	AND `device_id`=:deviceId
	ORDER BY r2.id DESC
	LIMIT 1
	) AS latestEnergy
	FROM `readings`
	WHERE MONTH(datetime) = MONTH(CURDATE()) AND YEAR(datetime) = YEAR(CURDATE())
	AND `device_id`=:deviceIdd");
	$sam->execute(array(
		'deviceId'=>$deviceId,
		'deviceIdd'=>$deviceId
	));
	if($sam->rowCount()==1){
		$row = $sam->fetch();
		if($row['maxEnergy']>$row['latestEnergy']){
			$value = ($row['latestEnergy'] - 0) + ($row['maxEnergy'] - $row['minEnergy']);
		}else{
			$value = $row['maxEnergy'] - $row['minEnergy'];
		}
		return convert_Wh_to_KWh($value);
	}
	return 0;
}

function engineering_aggregates_query_old($locationId){
	global $PDO;
	$sam = $PDO->prepare("SELECT 
	AVG(r.`averageLineNeutralVoltage`) as voltage,
	SUM(r.`totalLineCurrent`) as current,
	SUM(r.`totalRealPower`) as powerReal,
	SUM(r.`totalReactivePower`) as powerReactive,
	SUM(r.`totalApparentPower`) as powerApparent,
	AVG(r.`powerfactorAverage`) as powerFactor,
	MAX(r.`timestamp`) as lastUpdate
	FROM 
	(
	SELECT MAX(inR.`id`) as ids
	FROM `readings` inR
	LEFT JOIN `locations` inL 
	ON inL.id = inR.location_id
	LEFT JOIN `locations` inL2 
	ON inL2.id = inL.parent_location_id
	WHERE (inR.`location_id` = :locationId 
		OR inL.`id` = :locationId  
		OR inL2.`id` = :locationId 
		OR inL2.`parent_location_id` = :locationId)
	GROUP BY inR.`device_id`
	) maxTable
	LEFT JOIN `readings` r
	ON maxTable.ids = r.id
	");
	$sam->execute(array(':locationId'=>$locationId));
	if($sam->rowCount()>0){
		return $sam->fetch(PDO::FETCH_ASSOC);
	}else{
		return FALSE;
	}	
}

function business_aggregates_query_old($locationId){
	global $PDO;

	// TODO: Get tariff also

	$sam = $PDO->prepare("SELECT 
	(MAX(r.`energyValue`)-MIN(r.`energyValue`)) as wattHours
	FROM 
	(
	SELECT MAX(inR.`id`) as ids
	FROM `readings` inR
	LEFT JOIN `locations` inL 
	ON inL.id = inR.location_id
	LEFT JOIN `locations` inL2 
	ON inL2.id = inL.parent_location_id
	WHERE (inR.`location_id` = :locationId 
		OR inL.`id` = :locationId  
		OR inL2.`id` = :locationId 
		OR inL2.`parent_location_id` = :locationId)
	GROUP BY inR.`device_id`
	) maxTable
	LEFT JOIN `readings` r
	ON maxTable.ids = r.id
	WHERE MONTH(`datetime`) = MONTH(CURRENT_DATE())
	AND YEAR(`datetime`) = YEAR(CURRENT_DATE())
	");
	$sam->execute(array(':locationId'=>$locationId));
	if($sam->rowCount()>0){
		return $sam->fetch(PDO::FETCH_ASSOC);
	}else{
		return FALSE;
	}	
}


function engineering_aggregates_query($locations){
	global $PDO;

	$in  = str_repeat('?,', count($locations) - 1) . '?';
	
	$sam = $PDO->prepare("SELECT 
	AVG(r.`averageLineNeutralVoltage`) as voltage,
	SUM(r.`totalLineCurrent`) as current,
	SUM(r.`totalRealPower`) as powerReal,
	SUM(r.`totalReactivePower`) as powerReactive,
	SUM(r.`totalApparentPower`) as powerApparent,
	AVG(r.`powerfactorAverage`) as powerFactor,
	MAX(r.`timestamp`) as lastUpdate
	FROM 
	(
	SELECT MAX(inR.`id`) as ids
	FROM `readings` inR
	LEFT JOIN `devices` inD 
	ON inD.id = inR.device_id
	WHERE inD.`location_id` IN (".$in.")
	GROUP BY inR.`device_id`
	) maxTable
	LEFT JOIN `readings` r
	ON maxTable.ids = r.id
	");

	$sam->execute($locations);

	if($sam->rowCount()>0){
		return array_merge($sam->fetch(PDO::FETCH_ASSOC),array("debug"=>$in,"locations"=>$locations));
	}else{
		return array_merge(array("rows"=>0),array("debug"=>$in,"locations"=>$locations));
	}
}

function business_aggregates_query($locations){
	global $PDO;

	$in  = str_repeat('?,', count($locations) - 1) . '?';

	// TODO: Get tariff also

	$sam = $PDO->prepare("SELECT 
	(MAX(r.`energyValue`)-MIN(r.`energyValue`)) as wattHours
	FROM `readings` r
	LEFT JOIN `devices` d 
	ON d.id = r.device_id
	WHERE MONTH(r.`datetime`) = MONTH(CURRENT_DATE())
	AND YEAR(r.`datetime`) = YEAR(CURRENT_DATE())
	AND d.`location_id` IN (".$in.")
	GROUP BY r.`device_id`
	");
	$sam->execute($locations);
	if($sam->rowCount()>0){
		$array = $sam->fetchAll(PDO::FETCH_ASSOC);
		$wattHoursSum = 0;
		foreach($array as $a){
			$wattHoursSum += $a['wattHours'];
		}
		return array('wattHours'=>$wattHoursSum);
	}else{
		return FALSE;
	}	
}

function notifalerts($newalert){
	global $PDO;

	$sql = sprintf(
    "INSERT INTO %s (%s) values (%s)",
    "notifalerts",
    implode(", ", array_keys($newalert)),
    ":" . implode(", :", array_keys($newalert))
    );

    $statement = $PDO->prepare($sql);
    $statement->execute($newalert);
  	} 

function expense_calculation($arrayexpense){ //used in calculation of budget in alert_data
  $elements = sizeof($arrayexpense);
  $num = 0;
  $unitsOffset = 0;
  foreach($arrayexpense as $y){
    $num++;

        $y['energyValue'] = $y['energyValue']/1000;

        if($unitsOffset == 0){$unitsOffset = abs($y['energyValue']);}
        
        $unit = round(abs($y['energyValue'])-$unitsOffset);
        $expenseValue = get_expense_from_unit($unit);
        //echo"<script> console.log('expenseValue ".$expenseValue."')</script>";
        ${'unitsValueData'} .='
        ['.round($x['timestamp']*1000,0).', '.$expenseValue.']'.iif($num!=$elements,',');
        
        //array_push($Weeklytime,array($y['timestamp']));
        //array_push($Weeklyenergy,round($y['energyValue']/1000));
        //echo '<tr><td>'.$y['timestamp'].'</td><td>'.$y['energyValue'].'</td><td></td></tr>';
      }
      return $expenseValue;}

function insert_device($newdevice){
	global $PDO;

      $sql = sprintf(
    "INSERT INTO %s (%s) values (%s)",
    "devices",
    implode(", ", array_keys($newdevice)),
    ":" . implode(", :", array_keys($newdevice))
    );

    $statement = $PDO->prepare($sql);
    $statement->execute($newdevice);
	$message = '<div class="alert alert-success">Device added successfully</div>';
	return $message;}

 function getAllTariffs(){
 	global $PDO;

 	$sam = $PDO->prepare("SELECT `id`,`name` FROM `tariffs`");
 	$sam->execute();
	if($sam->rowCount()>0){
		$arraytariff = $sam->fetchAll(PDO::FETCH_ASSOC);
		return $arraytariff;
	}
	else{
		return FALSE;
	}}

	function hour($displaytime){

		if ($displaytime=="24:00") {
			$displaytime="00:00";
		}
		elseif ($displaytime=="24:15") {
			$displaytime="00:15";
		}
		elseif ($displaytime=="24:30") {
			$displaytime="00:30";
		}
		elseif ($displaytime=="24:45") {
			$displaytime="00:45";
		}
		return $displaytime;
	}

function insert_alerts($newalert){
	global $PDO;

	$sql = sprintf(
    "INSERT INTO %s (%s) values (%s)",
    "alerts",
    implode(", ", array_keys($newalert)),
    ":" . implode(", :", array_keys($newalert))
    );

    $statement = $PDO->prepare($sql);
    $statement->execute($newalert);
  	} 

function currentDailLimit($deviceId){
	global $PDO;

	$sam = $PDO->prepare("SELECT `currentlimit` as `currentlimit` FROM `devices` WHERE `id`=:deviceId");
	$sam->execute(array(':deviceId'=>$deviceId));
	$row = $sam->fetch(PDO::FETCH_ASSOC);

	$current=$row["currentlimit"];
	//echo round($current,0); echo "<br/>";
	//echo number_format($current,2);
	return ceil($current);
}

function report_location($locations)
{	global $PDO;

	$samloc=$PDO->prepare("SELECT `id` as `id`, `name` as `name` FROM `locations` WHERE `id`=:locid ");
	$samloc->execute(array(':locid'=>$locations));
	$arrayloc= $samloc->fetchAll(PDO::FETCH_ASSOC);
	if ($samloc->rowCount()>0) {
	$arraylocation=array('Location_Id');
	foreach ($arrayloc as $x) {
		//echo $x['id'];
	array_push($arraylocation, $x['id']);
	}
	array_push($arraylocation,'Location_Name');
	foreach ($arrayloc as $x) {
		//echo $x['id'];
	array_push($arraylocation, $x['name']);
	}
	}
	/*$n=sizeof($arraylocation);
	for ($i=0; $i <=$n ; $i++) { 
	echo $arraylocation[$i];
	}*/

	//$arraylocation=array('Location_Id',$arrayloc['id'],'Location Name',$arrayloc['name']);
	return $arraylocation;
}

function report_device($devices)
{	global $PDO;

	$samloc=$PDO->prepare("SELECT `id` as `id`, `dname` as `name` FROM `devices` WHERE `id`=:devid ");
	$samloc->execute(array(':devid'=>$devices));
	$arrayloc= $samloc->fetchAll(PDO::FETCH_ASSOC);
	if ($samloc->rowCount()>0) {
	$arraylocation=array('Device_Id');
	foreach ($arrayloc as $x) {
		//echo $x['id'];
	array_push($arraylocation, $x['id']);
	}
	array_push($arraylocation,'Device_Name');
	foreach ($arrayloc as $x) {
		//echo $x['id'];
	array_push($arraylocation, $x['name']);
	}
	}
	/*$n=sizeof($arraylocation);
	for ($i=0; $i <=$n ; $i++) { 
	echo $arraylocation[$i];
	}*/

	//$arraylocation=array('Location_Id',$arrayloc['id'],'Location Name',$arrayloc['name']);
	return $arraylocation;
}

function device_on_location_check($dname)
{
	global $PDO;

	$samdev=$PDO->prepare("SELECT `dname` FROM `devices`d LEFT JOIN `locations` l ON d.`location_id` = l.`id` WHERE `dname`=:dname ");
	$samdev->execute(array(':dname'=>$dname));
	$arraydev= $samdev->fetchAll(PDO::FETCH_ASSOC);
	if ($samdev->rowCount()>=1) {
		$x=$samdev->rowCount();
		//if(!empty($arraydev['dname']))
		return $x;
	}
	else{
	//return TRUE;
	}}

	function units(){
		
		return $arrayunits=array('','','','','','','','','(Hz)','(Volts)','(Volts)','(Volts)','(Volts)','(Volts)','(Volts)','(Ampere)','(Ampere)','(Ampere)','','','','(KW)','(KW)','(KW)','(KVAr)','(KVAr)','(KVAr)','(KVA)','(KVA)','(KVA)','(Volts)','(KWh)','','','(Volts)','(Ampere)','(KW)','(KVAr)','(KVA)','(KW)','(KVA)','(Ampere)','(%)','(%)','(%)','(%)','(%)','(%)','(%)','(%)','(%)','(%)','(%)','(%)','(Ampere)','(Ampere)','(Ampere)');
	}

	function send_email_attachment($to,$subject,$message,$filename,$fromEmail=DEFAULT_EMAIL,$fromName=DEFAULT_EMAIL_NAME){
	//$file_name="Instrux-Weekly-Report-17-Location-66-Device-From-2019-10-01-To-2019-10-31.pdf";
	// Constants such as SMTP_SERVER defined in config.php
	try{
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = SMTP_SERVER;  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = SMTP_USERNAME;                 // SMTP username
		$mail->Password = SMTP_PASSWORD;
		$mail->addAttachment(__DIR__ ."/generatedpdf/".$filename.".pdf");                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted

		$mail->Port       = 465;

		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->addCustomHeader('MIME-version', "1.0");
        //$mail->addCustomHeader('Content-type', "text/calendar; method=REQUEST; charset=UTF-8");
        $mail->addCustomHeader('From', $fromEmail);
        $mail->addCustomHeader('Reply-To', 'instruxic@gmail.com');
        $mail->addCustomHeader('Content-Transfer-Encoding', "8bit");
        //$mail->addCustomHeader('X-Mailer', "Microsoft Office Outlook 10.0");
        //$mail->addCustomHeader("Content-class: urn:content-classes:calendarmessage");

		$mail->From = $fromEmail;
		$mail->FromName = $fromName;
		$mail->addAddress($to);   // Add a recipient
		//echo $to;  
		

        $mail->AddReplyTo( 'instruxic@gmail.com', 'InstruX Report' );
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = strip_tags($message);

		if(!$mail->send()) {
			return 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			//echo 'Message has been sent';
			//echo ($message);
			//echo ($filename);
			return TRUE;
		}

	} catch (Exception $e) {
		return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}

	return FALSE;

}
//Insertion of BUDGET ALERTS in database
	function insert_budget_alerts($newalert){
		global $PDO;
	
		$sql = sprintf(
		"INSERT INTO %s (%s) values (%s)",
		"budget_alerts",
		implode(", ", array_keys($newalert)),
		":" . implode(", :", array_keys($newalert))
		);
	
		$statement = $PDO->prepare($sql);
		$statement->execute($newalert);
	} 

//Insertion of VOLTAGE ALERTS in database
	function insert_voltage_alerts($newalert){
		global $PDO;
	
		$sql = sprintf(
		"INSERT INTO %s (%s) values (%s)",
		"voltage_alerts",
		implode(", ", array_keys($newalert)),
		":" . implode(", :", array_keys($newalert))
		);
	
		$statement = $PDO->prepare($sql);
		$statement->execute($newalert);
	} 

//Insertion of CURRENT ALERTS in database
	function insert_current_alerts($newalert){
		global $PDO;
	
		$sql = sprintf(
		"INSERT INTO %s (%s) values (%s)",
		"current_alerts",
		implode(", ", array_keys($newalert)),
		":" . implode(", :", array_keys($newalert))
		);
	
		$statement = $PDO->prepare($sql);
		$statement->execute($newalert);
	} 

//Sending Emails for WEEKLY BUDGET ALERTS
	function email_weekly_budget_alerts($newalert){
	
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		 
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
			
			$subject = "Weekly Budget Alert";
			$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Weekly Budget Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Your weekly electricity consumption has crossed your weekly budget<br/>
										Weekly Expense: Rs.'.number_format($newalert['expenseweekly']).'<br />
										Weekly Budget: Rs.'.number_format($newalert['budgetweekly']).'<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
			$sendmail=send_email($email['email'],$subject,$message);
	
		}
	 }
	
	} 

//Sending Emails for DAILY BUDGET ALERTS
	function email_daily_budget_alerts($newalert){
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
			$subject = "Daily Budget Alert";
			$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Daily Budget Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Your daily electricity consumption has crossed your daily budget<br/>
										Daily Expense: Rs.'.number_format($newalert['expensedaily']).'<br />
										Daily Budget: Rs.'.number_format($newalert['budgetdaily']).'<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
			$sendmail=send_email($email['email'],$subject,$message);
		}
		 }	
	} 

//Sending Emails for MONTHLY BUDGET ALERTS
	function email_monthly_budget_alerts($newalert){
	
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		 
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
			$subject = "Monthly Budget Alert";
			////////////////////
			$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Monthly Budget Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Your monthly electricity consumption has crossed your monthly budget<br/>
										Monthly Expense: Rs.'.number_format($newalert['expensemonthly']).'<br />
										Monthly Budget: Rs.'.number_format($newalert['budgetmonthly']).'<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
			/////////////////
			$sendmail=send_email($email['email'],$subject,$message);}
		}
	} 



//Sending Emails for MINIMUM VOLTAGE ALERTS
	function email_minimum_voltage_alerts($newalert){
	
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		 
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
		$subject = "Voltage Fluctuation Alert";
		$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Voltage Fluctuation Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Voltage is below threshold ('.round($newalert['voltminalert']).' V).<br/>
										Now it is ('.round($newalert['minimumvoltagereal']).' V).<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
////////////////////////////


		$sendmail=send_email($email['email'],$subject,$message);}
		}
	}

//Sending Emails for MAXIMUM VOLTAGE ALERTS
	function email_maximum_voltage_alerts($newalert){
	
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
		$subject = "Voltage Fluctuation Alert";
		$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Voltage Fluctuation Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Voltage has crossed threshold ('.round($newalert['voltmaxalert']).' V).<br/>
										Now it is ('.round($newalert['maximumvoltagereal']).' V).<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
		$sendmail=send_email($email['email'],$subject,$message);}
		}
	}

//Sending Emails for MINIMUM CURRENT ALERTS
	function email_minimum_current_alerts($newalert){
	
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		 
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
		$subject = "Current Fluctuation Alert";
		$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Current Fluctuation Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Current is below threshold ('.round($newalert['currentminalert']).' A).<br/>
										Now it is ('.round($newalert['minimumcurrentreal']).' A).<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
		$sendmail=send_email($email['email'],$subject,$message);}
		}
	}

//Sending Emails for MAXIMUM CURRENT ALERTS
	function email_maximum_current_alerts($newalert){
	
		global $PDO;
		$samemail = $PDO->prepare("SELECT `sub_email` as `email` FROM `notifalerts` WHERE `alert_subscription`='1' AND `device_id`=:deviceId ");
		$samemail->execute(array(':deviceId'=>$newalert['device_id']));
	
		 $emailarray=$samemail->fetchAll(PDO::FETCH_ASSOC);
		 
		 if ($samemail->rowCount()>0){
	
		foreach ($emailarray as $email) {
		$subject = "Current Fluctuation Alert";
		$message='
		<body style="margin: 0; padding: 0;">
		<table border="0" cellpadding="0" cellspacing="0" width="100%"> 
			<tr>
				<td style="padding: 10px 0 30px 0;">
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 24px;" width="75%">
											INSTRUX<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
											<b>Current Fluctuation Alert</b>
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
											Dear User,
											
										</td>
									</tr>
									<tr>
										<td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
										Current has crossed threshold ('.round($newalert['currentmaxalert']).' A).<br/>
										Now it is ('.round($newalert['maximumcurrentreal']).' A).<br /><br /><br/>
		Instrux <br />
		Instrumentation Centre <br />
		NEDUET
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td bgcolor="#404040" style="padding: 30px 30px 30px 30px;">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
											&reg; Instrument Centre, NED UET 2019<br/>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>';
		$sendmail=send_email($email['email'],$subject,$message);}
		}
	}

function get_user_device_details($userid){
	global $PDO;
	$sam = $PDO->prepare("SELECT DISTINCT(d.id), d.*,l.name as locationName FROM `devices` d 
	LEFT JOIN `device_users` du ON du.device_id=d.id
	LEFT JOIN `location_users` lu ON `lu`.`location_id`=d.`location_id`
	LEFT JOIN `locations` l ON `l`.`id`=d.`location_id`
	WHERE (du.`user_id`=:userid OR lu.`user_id`=:useridd)");
	$sam->execute(array(':userid'=>$userid,':useridd'=>$userid));
	if($sam->rowCount()>=1){
		return $sam->fetchAll();
	}
	return FALSE;
}

function update_device($dname,$location_id,$sanctionedLoad,$node_mac,$currentlimit,$editDevice){
	global $PDO;

		$sam = $PDO->prepare("UPDATE `devices` SET 
		`dname`=:dname,
		`location_id`=:location_id,
		`sanctionedLoad`=:sanctionedLoad,
		`node_mac`=:node_mac,
		`currentlimit`=:currentlimit
		WHERE `id`=:id
		");
		$sam->execute(array(
			':dname'=>$dname,
			':location_id'=>$location_id,
			':sanctionedLoad'=>$sanctionedLoad,
			':node_mac'=>$node_mac,
			':currentlimit'=>$currentlimit,
			':id'=>$editDevice
		));
		if( $sam->rowCount()>=1)
	{$message = '<div class="alert alert-success">Device edited successfully</div>';
	return $message;}
	else{ return FALSE;}
	}

function getTariff($id){
    global $PDO;
     $sam = $PDO->prepare("SELECT `name`,`fixedCharges`,`variableChargesOnPeak`,`variableChargesOffPeak`, `incomeTax`, `ISPA`, `ISPRRate` FROM `tariffs` WHERE `id`=:id ");
    $sam->execute(array(':id'=>$id));
    $array = $sam->fetchAll(PDO::FETCH_ASSOC);
if ( $sam->rowCount()>0){
    return $array;
}
}

function getTariffUpdated($id,$from,$to){
    global $PDO;
     $sam = $PDO->prepare("SELECT `fixedCharges`,`variableChargesOnPeak`,`variableChargesOffPeak`, `timestamp` FROM `tariff_time` WHERE `tariff_id`=:id AND `timestamp`>=:timestampFrom AND `timestamp`<=:timestampTo ");
    $sam->execute(array(':id'=>$id, ':timestampFrom'=>$from, ':timestampTo'=>$to));
    $array = $sam->fetchAll(PDO::FETCH_ASSOC);
if ( $sam->rowCount()>0){
    return $array;
}
}

function calculateBill($deviceId,$from,$to,$fixRate,$tariffOffPeak,$tariffOnPeak,$incomeTax,$ISPA,$ISPRRate  )
{
    global $PDO;

$OnPeakHoursFrom = 18;
$OnPeakHoursTo = 22;

    $parameters = array(
    'energyValue',
    'powerfactorAverage',
    'maximumDemandRealPower'
);

$query='';
foreach($parameters as $y){
    $query .= '
    AVG(`'.$y.'`) as `'.$y.'`,
    ';
}

$fullQuery = "SELECT 
".$query."
DAY(`datetime`) as day, HOUR(`datetime`) as hour, AVG(`timestamp`) as `timestamp`
FROM `readings` 
WHERE `device_id`=:deviceId AND `timestamp`>:timestampFrom AND `timestamp`<:timestampTo 
GROUP BY YEAR(`datetime`), MONTH(`datetime`), DAY(`datetime`), HOUR(`datetime`)
";

$sam = $PDO->prepare($fullQuery);

$queryParams = array(':deviceId'=>$deviceId,':timestampFrom'=>$from,':timestampTo'=>$to);

$sam->execute($queryParams);

if($sam->rowCount()>0){

    $message = '';

    $dataArray = $sam->fetchAll(PDO::FETCH_ASSOC);

    $day = 0;
    $energyOffset = 0;
    $num = 0;

    $energyOnPeak = 0;
    $energyOffPeak = 0;
    $totalEnergy = 0;

    $maximumDemandRealPowerSum = 0;
    $powerfactorAverageSum = 0;

    foreach ($dataArray as $x) {

        $num++;

        $maximumDemandRealPowerSum += $x['maximumDemandRealPower'];
        $powerfactorSum += $x['powerfactorAverage'];

        if($energyOffset == 0){$energyOffset = abs($x['energyValue']);}
        $totalEnergy = abs($x['energyValue'])-$energyOffset;

        $onPeakHour = 0;

        if($day == 0){$day = $x['day'];} // Initialize with the day if it's start of loop

        if(onPeakHour($x['hour'],$OnPeakHoursFrom,$OnPeakHoursTo,$timezoneOffset)){
            if($day != $x['day']){
                $onPeakOffset=0; // Reset the offset if it's the other day but starting with on peak hour
                $day = $x['day']; 
            }
        }
        
        if($day == $x['day']){
            // If same day being counted
/*
            echo $x['hour']+$timezoneOffset.'   '.$x['energyValue'].'
';
*/

            if(onPeakHour($x['hour'],$OnPeakHoursFrom,$OnPeakHoursTo,$timezoneOffset)){
                // It's an on peak hour in the same day

                //echo $x['hour'].'   '.$x['energyValue'].' <br />\n';

                if($onPeakOffset==0){$onPeakOffset = abs($x['energyValue']);} 
                // if it's a reset due to different day or off peak hours then set offset to latest energy value so counting starts from that point

                $energyOnPeak += ($x['energyValue'] - $onPeakOffset); // Save the onPeakDifference (overall) from the increemented value minus the offset (last value)
                $onPeakOffset = abs($x['energyValue']); // Update the offset so it counts next increement in next on peak hour

            }else{
                $onPeakOffset=0; // Reset the offset to make sure it doesnt count the difference till next increement since it's offpeak now
            }

        }else{
            $onPeakOffset=0; 
            $day = $x['day']; // Update the next day
        }

    }

    //echo $energyOnPeak;echo "<br/>";

    $energyOffPeak = $totalEnergy - $energyOnPeak;
    $powerfactorAverage = $powerfactorSum/$num;

    $realOffPeak = $energyOffPeak/1000;
    $realOnPeak = $energyOnPeak/1000;
    $reactiveOnPeak = calculateReactiveFromReal($realOnPeak,$powerfactorAverage);
    $reactiveOffPeak = calculateReactiveFromReal($realOffPeak,$powerfactorAverage);
    $MDI = $maximumDemandRealPowerSum/$num; // Average of all real power maximum demand


    $bill = calculateAllCharges(
        $realOffPeak,$realOnPeak,$reactiveOnPeak,$reactiveOffPeak,$MDI,
        $fixRate,$surchageOffPeakRate,$surchageOnPeakRate,$tariffOffPeak,$tariffOnPeak,$ISPA,$ISPRRate,$incomeTax);
    //array_push($bill, $realOffPeak, $realOnPeak, $MDI);
return $bill;

}else{

    $message = '<div class="alert alert-warning">No Data Available for the selected duration. 
    &nbsp; &nbsp; 
    <button class="btn btn-warning btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        Debug
    </button>
    </div>

    <div class="collapse" id="collapseExample">
    <div class="card card-body">
    <pre>'.$fullQuery.'</pre>
    <pre>'.var_export($queryParams, TRUE).'</pre>
    </div>
    <br /><br />
    </div>';

    return $message;

}
}

function pageRequiresAuthenticationNotif(){
    if(!checkLoginStatusUser()){
        redirect('login.php');
    }
}

function fetch_daily_peak_offpeak($deviceId,$from,$to,$type){
	
	global $PDO;
	//echo "<br> Inside function with type value ".$type."<br>";

	$graph=array("date"=>[], "offPeak"=>[], "onPeak"=>[]);

    //query to fetch different peakhours of different months
    $sam = $PDO->query("SELECT group_concat(month) as 'months',peak_time_from,peak_time_to,
    CONCAT(peak_time_from , ' - ', peak_time_to) as 'duration'  
    FROM admin_instrux.peak_time group by peak_time_from");
    $peaks = $sam->fetchAll();

	//query to fetch 'on peak units/day'
	$query = "";
	$w = 0; //flag to add 'OR' in peak energy query 
	$parameter = "";
	foreach ($peaks as $monthspeaks){
        if($w==1){
            $query .= " OR ";
        }
        $query .= "(MONTH(`datetime`) IN (".$monthspeaks['months'].") AND 
                    TIME(`datetime`) BETWEEN '".$monthspeaks['peak_time_from']."' AND '".$monthspeaks['peak_time_to']."')";
        $w=1;
	}
	if($type=='Energy'){
		//echo "<br> Inside type == 'Energy' with type value ".$type."<br>";
		$parameter = "(MAX(`energyValue`)-MIN(`energyValue`))/1000 AS 'energyOnPeak'";
		$offpeakunits=0;
    	$peakunits=0;
	}
	elseif($type=='MaxPower'){
		//echo "<br> Inside type == 'MDI' with type value ".$type."<br>";
		$parameter = "MAX(`totalRealPower`)/1000 AS 'MaxPower on peak'";
		$query1 = "";
		$nw = 0; ////flag to add 'OR' in off peak energy query i.e NOT BETWEEN clause
		foreach ($peaks as $monthspeaks){
			if($nw==1){
				$query1 .= " OR ";
			}
			$query1 .= "(MONTH(`datetime`) IN (".$monthspeaks['months'].") AND 
						TIME(`datetime`) NOT BETWEEN '".$monthspeaks['peak_time_from']."' AND '".$monthspeaks['peak_time_to']."')";
			$nw=1;
		}
	}
	//query to fetch maximum demand off peak
	$sam2 = $PDO->prepare("SELECT DATE_FORMAT(`datetime`, '%e. %b') as date,MONTH(`datetime`) AS 'Month',
	YEAR(`datetime`) AS 'Year', DAY(`datetime`) AS 'Day',
	".$parameter." FROM `readings` 
	WHERE `device_id`=:deviceId AND (`timestamp` >= :durationFrom AND `timestamp` <= :durationTo )
	AND ( ".$query."  ) GROUP BY year(`datetime`),month(`datetime`),DAY(`datetime`)");
	$sam2->execute(array(':deviceId'=>$deviceId,':durationFrom'=>$from,':durationTo'=>$to));
	//$samMDIOff = $sam4->fetchAll();
	//print_r($samMDIOff);
	//echo ' mdi off peak = '.$samMDIOff['max demand off peak'];
	if($type=='Energy'){
		//query to fetch 'total units/day'
		$sam3 = $PDO->prepare("SELECT DATE_FORMAT(`datetime`, '%e. %b') as date,
		MONTH(`datetime`) as 'Month', YEAR(`datetime`) as 'Year', DAY(`datetime`) as 'Day',
		(MAX(`energyValue`)-MIN(`energyValue`))/1000 AS Totalenergy FROM `readings` 
		WHERE `device_id`=:device_id AND  (`timestamp` >= :timefrom AND `timestamp` <= :timeto) 
		GROUP BY year(`datetime`),month(`datetime`),DAY(`datetime`)");
		$sam3->execute(array(':device_id'=>$deviceId,':timefrom'=>$from,':timeto'=>$to));

		if($sam3->rowCount()>0){
			if($sam2->rowCount()>0){
			$peakperday = $sam2->fetchAll();
			$totalperday = $sam3->fetchAll();
			
			foreach($totalperday as $daytotal){
				$flag=0;
				foreach($peakperday as $daypeak){
					if($daytotal['Year']==$daypeak['Year']){
						if($daytotal['Month']==$daypeak['Month']){
							if($daytotal['Day']==$daypeak['Day']){
								array_push($graph['date'],$daypeak['date']);
								$diff = $daytotal['Totalenergy']-$daypeak['energyOnPeak'];
								array_push($graph['offPeak'],$diff);
								array_push($graph['onPeak'],$daypeak['energyOnPeak']);
								$flag=1;
							}
						}
					}
				}
		
				if($flag==0){        
					array_push($graph['date'],$daytotal['date']);
					array_push($graph['offPeak'],$daytotal['Totalenergy']);
					array_push($graph['onPeak'],0);
				}
			}
			//echo "<html><body><table><tr><th>Date</th><th>Off Peak</th><th>On Peak</th></tr>";
			/*for($i=0;$i<count($graph['date']);$i++){
				echo "<tr><td>".$graph['date'][$i]."</td><td>".$graph['offPeak'][$i]."</td><td>".$graph['onPeak'][$i]."</td></tr>";
			}*/
			//echo "</table></body></html>";
			//print_r($onPeak);
			//echo "<br> Energy = ";
			//print_r($graph);
			return $graph;
		}else{
			return FALSE;
		}}else{
			return FALSE;
		}
	}
	
	
	elseif($type=='MaxPower'){
		//query to fetch maximum demand peak
		$sam4 = $PDO->prepare("SELECT DATE_FORMAT(`datetime`, '%e. %b') as date,MONTH(`datetime`) AS 'Month',
		YEAR(`datetime`) AS 'Year', DAY(`datetime`) AS 'Day',MAX(`totalRealPower`)/1000 AS 'MaxPower off peak' FROM `readings` 
		WHERE `device_id`=:deviceId AND (`timestamp` >= :durationFrom AND `timestamp` <= :durationTo )
		AND ( ".$query1." ) GROUP BY YEAR(`datetime`),MONTH(`datetime`),DAY(`datetime`)");
		$sam4->execute(array(':deviceId'=>$deviceId,':durationFrom'=>$from,':durationTo'=>$to));
		//$samMDIPeak = $sam5->fetchAll();
		//print_r($samMDIPeak);
		
		if($sam4->rowCount()>0){
			if($sam2->rowCount()>0){
				$samMaxPowerPeak = $sam2->fetchAll();
				$samMaxPowerOff = $sam4->fetchAll();
		
				foreach($samMaxPowerOff as $MaxPowerOff){
					$flag=0;
					foreach($samMaxPowerPeak as $MaxPowerPeak){
						if($MaxPowerOff['Year']==$MaxPowerPeak['Year']){
							if($MaxPowerOff['Month']==$MaxPowerPeak['Month']){
								if($MaxPowerOff['Day']==$MaxPowerPeak['Day']){
									array_push($graph['date'],$MaxPowerPeak['date']);
									array_push($graph['offPeak'],number_format($MaxPowerOff['MaxPower off peak'],2));
									array_push($graph['onPeak'],number_format($MaxPowerPeak['MaxPower on peak'],2));
									$flag=1;
								}
							}
						}
					}

					if($flag==0){        
						array_push($graph['date'],$MaxPowerOff['date']);
						array_push($graph['offPeak'],number_format($MaxPowerOff['MaxPower off peak'],2));
						array_push($graph['onPeak'],0);
					}
				}
				//echo "<br> MDI = ";
				//print_r($graph);
				return $graph;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}

}

function get_peak_hours(){
	global $PDO;
	$sam = $PDO->query("SELECT group_concat(month) as 'months',peak_time_from,peak_time_to,
    CONCAT(peak_time_from , ' - ', peak_time_to) as 'duration'  
	FROM admin_instrux.peak_time group by peak_time_from");
	if($sam->rowCount()>0){
		return $sam->fetchAll();
	}else{
	return false;}
}

function draw_bar_chart($containerId,$ds,$series,$min='',$max='',$title='',$yAxisLegend='',$color=''){
	//removed this line [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
	//stops loading highcharts since 03-03-2020
	return "
    
    <div id=\"".$containerId."\"></div>
    
    <script type=\"text/javascript\">
    
    Highcharts.chart('".$containerId."', {
		

		colors: ".$color.",
		chart: {
            zoomType: 'x',
            type: 'column'
        },
        credits: {
            enabled: false
        },
        title: {
            text: '".$title."'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
        },
        xAxis: {
            categories: [".$ds."],
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '".$yAxisLegend."'
            }
        },
        plotOptions: {
            column: {
                dataLabels: {
					enabled: true,
					allowOverlap: true,
					style: {
						fontSize: '9px',
						fontWeight: 'normal'
					},
					inside: false,
					rotation: 270,
					align: 'centre'
				}
            },
            area: {
                fillColor: {
                    linearGradient: {
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops: [
                        [0, Highcharts.getOptions().colors[0]],
                        
                    ]
                },
                marker: {
                    radius: 2
                },
                lineWidth: 1,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                },
                threshold: null
            }
        },
        series: [".$series."]
    });
    
    
    </script>
    
    ";
    
}
function echo_peak_hours( $from, $to ){
	$from = strtotime(date("M Y",$from));
	$to = strtotime(date("M Y",$to));
    $current = $from;
    $ret = array();
    $ret[] = date('n',$current);
    while( $current<$to ){       
        $next = date('Y-M-01', $current) . "+1 month";
        $current = strtotime($next);
        $c = date('n',strtotime($next));
         if(!(in_array($c, $ret))){
            $ret[] = $c;
        }
    }
    $months = $ret;
    //print_r($months);
    $es1=rtrim(implode(",", $months), ",");
    global $PDO;
    $p = $PDO->query("SELECT group_concat(`month`) as 'months',`peak_time_from`,`peak_time_to`,
                    CONCAT(`peak_time_from` , ' - ', `peak_time_to`) as 'duration'
                    FROM `peak_time` where `month` in (".$es1.") group by `peak_time_from`");
    return $p->fetchAll();
}

?>