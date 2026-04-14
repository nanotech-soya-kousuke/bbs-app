<?php
$dsn = 'pgsql:host=db;port=5432;dbname=bbs_app';
$user = 'user';
$password = 'password';

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_input = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $errors = [];

    if ($name === '') {
        $errors[] = 'ユーザー名は必須です';
    } elseif (preg_match('/^[\s　]+$/u', $name)) {
        $errors[] = 'ユーザー名は空白のみでは登録できません';
    }

    if ($email === '') {
        $errors[] = 'メールアドレスは必須です';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'メールアドレスの形式が不正です';
    }

    if ($password_input === '') {
        $errors[] = 'パスワードは必須です';
    } elseif (strlen($password_input) < 8) {
        $errors[] = 'パスワードは8文字以上にしてください';
    } elseif (trim($password_input) === '') {
        $errors[] = '空白のみのパスワードは使用できません';
    }

    if ($password_input !== $password_confirm) {
        $errors[] = 'パスワードが一致しません';
    }

    if (empty($errors)) {
        $password_hash = password_hash($password_input, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password_hash)
                VALUES (:name, :email, :password_hash)
            ");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => $password_hash
            ]);

            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'このメールアドレスは既に登録されています';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー登録</title>
</head>

<body>

    <h2>ユーザー登録</h2>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="ユーザー名"
            value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><br><br>

        <input type="email" name="email" placeholder="メールアドレス"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"><br><br>

        <input type="password" name="password" placeholder="パスワード"><br><br>

        <input type="password" name="password_confirm" placeholder="パスワード確認"><br><br>

        <button type="submit">登録</button>
    </form>

</body>

</html>