<?php
session_start();

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>トップページ</title>
</head>

<body>

    <h2>トップページ</h2>

    <?php if ($is_logged_in): ?>
        <p>ログイン中</p>
        <a href="/logout.php">ログアウト</a>
    <?php else: ?>
        <p>ログインしていません</p>
        <a href="/login.php">ログイン</a>
        <a href="/register.php">新規登録</a>
    <?php endif; ?>

</body>

</html>