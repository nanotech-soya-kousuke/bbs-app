<?php
session_start();
require_once __DIR__ . '/../admin_check.php';

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)){
    http_response_code(403);
    exit('不正なリクエストです');
}

$target_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

try {
    $admin->deleteUser($target_id);
} catch(InvalidArgumentException $e){

}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
header('Location: users.php');
exit;