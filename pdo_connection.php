<?php

ob_start();
	try { 

	$str = sprintf('mysql:host=%s;dbname=%s',DB_HOST,DB_NAME);
	$PDO = new PDO($str, DB_USER, DB_PASS);
	$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$PDO->exec("SET time_zone='Asia/Karachi';");

	}
	catch (Exception $e) {
	if ( DEBUG_MODE ) {
	die('Error: ' . $e->getMessage());
	}
	}


?>