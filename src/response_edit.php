<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/model/Response.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$response_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$response    = $response_id > 0 ? Response::findById($response_id) : null;

if (!$response) {
    header('Location: index.php');
    exit;
}

$thread_id = $response->getThreadId();

if (!$response->canEdit((int)$_SESSION['user_id'])) {
    header('Location: thread.php?id=' . $thread_id);
    exit;
}

$content = $response->getContent();
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = '不正なリクエストです。もう一度お試しください。';
    }

    if (empty($errors)) {
        $content = $_POST['content'] ?? '';
        $errors  = Response::validateResponse($content);
    }

    if (empty($errors)) {
        $response->update($content);
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
    <title>コメント編集</title>
</head>

<body>

    <h2>コメント編集</h2>
    <a href="thread.php?id=<?= $thread_id ?>">← スレッドに戻る</a>

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

        <textarea name="content" rows="5" cols="60"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea><br><br>

        <button type="submit">更新する</button>
    </form>

</body>

</html>