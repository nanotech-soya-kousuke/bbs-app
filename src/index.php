<?php
session_start();
date_default_timezone_set('Asia/Tokyo');

require_once __DIR__ . '/model/Thread.php';

$is_logged_in = isset($_SESSION['user_id']);
$threads      = Thread::getAllWithStats();
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
                <a href="thread.php?id=<?= $thread->getId() ?>">
                    <?= htmlspecialchars($thread->getTitle(), ENT_QUOTES, 'UTF-8') ?>
                </a>
            </h3>
            <p>
                投稿者: <?= htmlspecialchars($thread->getAuthorName(), ENT_QUOTES, 'UTF-8') ?><br>
                投稿日時: <?= date('Y/m/d H:i', strtotime($thread->getCreatedAt())) ?><br>
                コメント数: <?= $thread->responseCount ?>
            </p>
        </div>
    <?php endforeach; ?>

</body>

</html>