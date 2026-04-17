<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$dsn = 'pgsql:host=db;port=5432;dbname=bbs_app';
$db_user = 'user';
$db_password = 'password';

$pdo = new PDO($dsn, $db_user, $db_password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$title = '';
$content = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = '不正なリクエストです。もう一度お試しください。';
    }

    if (empty($errors)) {
        $title   = $_POST['title']   ?? '';
        $content = $_POST['content'] ?? '';

        if ($title === '') {
            $errors[] = 'タイトルは必須です';
        } elseif (mb_strlen($title, 'UTF-8') > 200) {
            $errors[] = 'タイトルは200文字以内で入力してください';
        }

        if ($content === '') {
            $errors[] = '本文は必須です';
        }
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO threads (title, content, user_id)
            VALUES (:title, :content, :user_id)
        ");
        $stmt->execute([
            ':title'   => $title,
            ':content' => $content,
            ':user_id' => $_SESSION['user_id'],
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>スレッド作成</title>
</head>

<body>

    <h2>スレッド作成</h2>

    <a href="index.php">← 一覧に戻る</a>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <label for="title">タイトル（200文字以内）</label><br>
        <input type="text" id="title" name="title" maxlength="200"
            value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
            style="width:400px;"><br><br>

        <label for="content">本文</label><br>
        <textarea id="content" name="content" rows="10" cols="50"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea><br><br>

        <button type="submit">スレッドを作成する</button>
    </form>

</body>

</html>