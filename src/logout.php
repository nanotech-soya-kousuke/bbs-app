<?php
require_once 'model/UserManager.php';
session_start();

$mang = new UserManager();
$mang->logout();
header('Location: login.php');
exit;
