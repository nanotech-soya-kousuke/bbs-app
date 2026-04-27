<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/model/Thread.php';
require_once __DIR__ . '/model/Admin.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$isAdmin = Admin::isAdmin((int)$_SESSION['user_id']);
$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$thread = $thread_id > 0 ? Thread::findById($thread_id) : null;

if (!$thread) {
    header('Location: index.php');
    exit;
}

if (!$thread->canEdit((int)$_SESSION['user_id'], $isAdmin)) {
    header('Location: thread.php?id' . $thread_id);
    exit;
}

$title = $thread->getTitle();
$content = $thread->getContent();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = '不正なリクエストです。もう一度お試しください。';
    }

    if (empty($errors)) {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $errors = Thread::validate($title, $content);
    }

    if (empty($errors)) {
        $thread->update($title, $content);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: thread.php?id=' . $thread_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>スレッド編集</title>
</head>

<body>

    <h2>スレッド編集</h2>
    <a href="thread.php?id=<?= $thread_id ?>">← スレッドに戻る</a>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <label for="title">タイトル(200文字以内)</label><br>
        <input type="text" id="title" name="title" maxlength="200" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
            style="width:400px;"><br><br>
        <label for="content">本文</label><br>
        <textarea id="content" name="content" rows="10" cols="50"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea><br><br>

        <button type="submit">更新する</button>
    </form>
</body>

</html>