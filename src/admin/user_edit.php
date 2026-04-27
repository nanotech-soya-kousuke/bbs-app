<?php
session_start();
require_once __DIR__ . '/../admin_check.php';
require_once __DIR__ . '/../model/Database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$target_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$target = $target_id > 0 ? ORM::for_table('users')->find_one($target_id) : null;

if (!$target) {
    header('Location: users.php');
    exit;
}

$name = $target->name;
$email = $target->email;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = '不正なリクエストです';
    }

    if (empty($errors)) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '') {
            $errors[] = 'ユーザー名を入力してください';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '有効なメールアドレスを入力してください';
        }
    }

        if (empty($errors)) {
        try {
            $admin->updateUser($target_id, $name, $email);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: users.php');
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー編集</title>
</head>

<body>
    <h2>ユーザー編集</h2>
    <a href="users.php">← ユーザー一覧に戻る</a>

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

        <label>ユーザー名<br>
            <input type="text" name="name"
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
        </label><br><br>

        <label>メールアドレス<br>
            <input type="email" name="email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        </label><br><br>

        <button type="submit">更新する</button>
    </form>

</body>

</html>