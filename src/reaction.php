<?php
session_start();

if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['error' => 'ログインが必要です']);
    exit;
}

require_once __DIR__ . '/model/Reaction.php';

header('Content-Type: application/json');

$post_type = $_POST['post_type'] ?? '';
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$type = $_POST['type'] ?? 'good';

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)){
    http_response_code(403);
    echo json_encode(['error' => '不正なリクエストです']);
    exit;
}

if(!in_array($post_type, ['thread','response'], true) || $post_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'パラメータが不正です']);
    exit;
}

$added = Reaction::toggle($post_type, $post_id, (int)$_SESSION['user_id'], $type);
$count = Reaction::countByPost($post_type, $post_id, $type);

echo json_encode([
    'reacted' => $added,
    'count' => $count,
]);