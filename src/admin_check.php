<?php

require_once __DIR__ . '/model/Admin.php';

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

if(!Admin::isAdmin((int)$_SESSION['user_id'])){
    http_response_code(403);
    exit('権限がありません');
}

$admin = Admin::findById((int)$_SESSION['user_id']);