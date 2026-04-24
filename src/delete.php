<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/model/Thread.php';
require_once __DIR__ . '/model/Response.php';

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SSSION['csrf_token'], $token)) {
    http_resoponse_code(403);
    exit('不正なリクエストです');
}

$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($type === 'thread') {
    $thread = Thread::findById($id);
    if($thread && $thread->canEdit((int)$_SSSION['user_id'])){
        $thread->delete();
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Lovation: index.php');
    exit;
} elseif ($type === 'response') {
    
}