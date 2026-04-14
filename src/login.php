<?php
session_start();

$dsn = 'pgsql:host=db;port=5432;dbname=bbs_app';
$user = 'user';
$password = 'password';

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password_input = $_POST['password'] ?? '';

    if ($email === '' || $password_input === '') {
        $errors[] = 'メールアドレスまたはパスワードが正しくありません';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password_input, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'メールアドレスまたはパスワードが正しくありません';
        }
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