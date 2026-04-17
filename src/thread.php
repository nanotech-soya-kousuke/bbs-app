<?php
session_start();

date_default_timezone_set('Asia/Tokyo');

$dsn         = 'pgsql:host=db;port=5432;dbname=bbs_app';
$db_user     = 'user';
$db_password = 'password';

$pdo = new PDO($dsn, $db_user, $db_password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$is_logged_in = isset($_SESSION['user_id']);

$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($thread_id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        t.id,
        t.title,
        t.content,
        t.created_at,
        u.name AS user_name
    FROM threads t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = :id
");

$stmt->execute([':id' => $thread_id]);
$thread = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thread) {
    header('Location: index.php');
    exit;
}

if ($is_logged_in && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_logged_in) {
        header('Location: login.php');
        exit;
    }

    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = '不正なリクエストです。もう一度お試しください。';
    }

    if (empty($errors)) {
        $content = $_POST['content'] ?? '';

        if ($content === '' || trim($content) === '') {
            $errors[] = 'コメントを入力してください';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO responses (thread_id, content, user_id)
            VALUES (:thread_id, :content, :user_id)
        ");
        $stmt->execute([
            ':thread_id' => $thread_id,
            ':content'   => $content,
            ':user_id'   => $_SESSION['user_id'],
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: thread.php?id=' . $thread_id);
        exit;
    }
}


$stmt = $pdo->prepare("
    SELECT
        r.id,
        r.content,
        r.created_at,
        u.name AS user_name
    FROM responses r
    JOIN users u ON r.user_id = u.id
    WHERE r.thread_id = :thread_id
    ORDER BY r.created_at ASC
    LIMIT 500
");
$stmt->execute([':thread_id' => $thread_id]);
$responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($thread['title'], ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>

    <a href="index.php">← スレッド一覧に戻る</a>

    <hr>

    <h2><?= htmlspecialchars($thread['title'], ENT_QUOTES, 'UTF-8') ?></h2>
    <p>
        投稿者: <?= htmlspecialchars($thread['user_name'], ENT_QUOTES, 'UTF-8') ?><br>
        投稿日時: <?= date('Y/m/d H:i', strtotime($thread['created_at'])) ?>
    </p>
    <p><?= nl2br(htmlspecialchars($thread['content'], ENT_QUOTES, 'UTF-8')) ?></p>

    <hr>

    <h3>コメント (<?= count($responses) ?> 件)</h3>

    <?php if (empty($responses)): ?>
        <p>まだコメントはありません。</p>
    <?php else: ?>
        <?php foreach ($responses as $i => $res): ?>
            <div id="res-<?= $res['id'] ?>" style="margin-bottom:16px; padding:8px; border:1px solid #ccc;">
                <strong>
                    <?= $i + 1 ?>番:
                    <?= htmlspecialchars($res['user_name'], ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <span style="color:#888; margin-left:8px;">
                    <?= date('Y/m/d H:i', strtotime($res['created_at'])) ?>
                </span>
                <p><?= nl2br(htmlspecialchars($res['content'], ENT_QUOTES, 'UTF-8')) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr>

    <?php if ($is_logged_in): ?>

        <h3>コメントを投稿する</h3>

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

            <textarea name="content" rows="5" cols="60"
                placeholder="コメントを入力してください"></textarea><br><br>

            <button type="submit">投稿する</button>
        </form>

    <?php else: ?>
        <p>
            コメントするには<a href="login.php">ログイン</a>が必要です。
        </p>
    <?php endif; ?>

</body>

</html>