<?php

// Samad: 20180124: Overdo with sessions and cookies for a definite Logout

session_start();

$_SESSION['user'] = false;
$_SESSION['key'] = false;
setcookie('user',null,-1);
setcookie('key',null,-1);

unset($_SESSION['user']);
unset($_SESSION['key']);
unset($_COOKIE['user']);
unset($_COOKIE['key']);

session_destroy();

//echo checkLoginStatusUser();

header("Location: login.php");
exit;


?>