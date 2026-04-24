<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/model/Thread.php';
require_once __DIR__ . '/model/Response.php';

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    exit('不正なリクエストです');
}

$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($type === 'thread') {
    $thread = Thread::findById($id);
    if ($thread && $thread->canEdit((int)$_SESSION['user_id'])) {
        $thread->delete();
    }
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Location: index.php');
    exit;
} elseif ($type === 'response') {
    $response = Response::findById($id);
    if ($response && $response->canEdit((int)$_SESSION['user_id'])) {
        $thread_id = $response->getThreadId();
        $response->delete();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: thread.php?id=' . $thread_id);
        exit;
    }
}

header('Location: index.php');
exit;
