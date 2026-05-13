<?php
require_once 'model/UserManager.php';
session_start();

$name   = '';
$email  = '';
$errors = [];

/**
 * @kanai: #retake
 *     シーケンス図だと、UserManager クラスで行っている処理になります。
 * 　　実装・設計が乖離しているので修正してください。
 * @soya:
 *      UserManagerで処理を行うようにしました。
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = $_POST['name']             ?? '';
    $email            = $_POST['email']            ?? '';
    $password         = $_POST['password']         ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $manager = new UserManager();
    try {
        $manager->register($name, $email, $password, $password_confirm);
        header('Location: login.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $errors = explode("\n", $e->getMessage());
    } catch (RuntimeException $e) {
        $errors[] = $e->getMessage();
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