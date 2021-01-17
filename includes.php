<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ob_start();


session_start();

error_reporting(E_ALL & ~E_NOTICE);

$timestamp = $time = time();
$userId = $userid = $_SESSION['user'];

// Load Composer's autoloader
//require_once 'vendor/autoload.php';

//require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
//require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';

require_once "constants.php";
require_once "config.php";
//require_once "mysqli_connect.php";
require_once "pdo_connection.php";
require_once "functions.php";
require_once "pages.php";


$loginStatus = checkLoginStatusUser();

$currentPage = basename(strtok($_SERVER["REQUEST_URI"],'?'));
$currentPageTitle = $page[$currentPage];

if($loginStatus){
    $userId = $userid = $_SESSION['user'];
    $user = getUserDetails($userId);
    $userTypeId = $user['user_type_id'];
    $userType = $user['type_name'];
}
