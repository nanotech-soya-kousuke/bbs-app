<?php
session_start();

date_default_timezone_set('Asia/Tokyo');

$dsn = 'pgsql:host=db;port=5432;dbname=bbs_app';
$user = 'user';
$password = 'password';

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$is_logged_in = isset($_SESSION['user_id']);


$stmt = $pdo->query("
    SELECT 
        t.id,
        t.title,
        t.created_at,
        u.name AS user_name,
        COUNT(r.id) AS response_count
    FROM threads t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN responses r ON r.thread_id = t.id
    GROUP BY t.id, u.name
    ORDER BY t.created_at DESC
");

$threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>トップページ</title>
</head>

<body>

    <h2>スレッド一覧</h2>

    <?php if ($is_logged_in): ?>
        <p>ログイン中</p>
        <a href="/logout.php">ログアウト</a>
    <?php else: ?>
        <p>ログインしていません</p>
        <a href="/login.php">ログイン</a>
        <a href="/register.php">新規登録</a>
    <?php endif; ?>

    <hr>

    <a href="/thread_create.php">スレッド作成</a>

    <?php foreach ($threads as $thread): ?>
        <div style="margin-bottom:20px;">
            <h3>
                <a href="thread.php?id=<?= $thread['id'] ?>">
                    <?= htmlspecialchars($thread['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </h3>
            <p>
                投稿者: <?= htmlspecialchars($thread['user_name'], ENT_QUOTES, 'UTF-8') ?><br>
                投稿日時: <?= date('Y/m/d H:i', strtotime($thread['created_at'])) ?><br>
                コメント数: <?= $thread['response_count'] ?>
            </p>
        </div>
    <?php endforeach; ?>

</body>

</html>