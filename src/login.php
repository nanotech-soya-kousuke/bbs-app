<?php
require_once 'model/UserManager.php';
session_start();

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';

    $mang = new UserManager();
    try {
        $user = $mang->login($_POST['email'] ?? '', $_POST['password'] ?? '');
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $errors = explode("\n", $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
</head>

<body>

    <a href="index.php">← スレッド一覧に戻る</a>

    <h2>ログイン</h2>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="メールアドレス"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"><br><br>

        <input type="password" name="password" placeholder="パスワード"><br><br>

        <button type="submit">ログイン</button>
    </form>

</body>

</html>