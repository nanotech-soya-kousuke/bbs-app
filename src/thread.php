<?php
session_start();
date_default_timezone_set('Asia/Tokyo');

require_once __DIR__ . '/model/Thread.php';
require_once __DIR__ . '/model/Response.php';
require_once __DIR__ . '/model/Admin.php';
require_once __DIR__ . '/model/Reaction.php';

$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($thread_id <= 0) {
    header('Location: index.php');
    exit;
}

$thread = Thread::findById($thread_id);
if (!$thread) {
    header('Location: index.php');
    exit;
}

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && Admin::isAdmin((int)$_SESSION['user_id']);

if ($is_logged_in && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$content = '';

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
        $errors  = Response::validateResponse($content);
    }

    if (empty($errors)) {
        Response::create($_SESSION['user_id'], $thread_id, $content);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: thread.php?id=' . $thread_id);
        exit;
    }
}

$responses = Response::getByThreadId($thread_id);

$session_user_id = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

$thread_reaction_count = Reaction::countByPost('thread', $thread_id);
$thread_reacted = $is_logged_in && Reaction::hasReacted('thread', $thread_id, $session_user_id);

$response_ids = array_map(fn($r) => $r->getId(), $responses);
$response_reactions = Reaction::getByPostIds('response', $response_ids, $session_user_id);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($thread->getTitle(), ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>

    <a href="index.php">← スレッド一覧に戻る</a>
    <hr>

    <h2><?= htmlspecialchars($thread->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>

    <?php if ($is_logged_in && $thread->canEdit((int)$_SESSION['user_id'], $is_admin)): ?>
        <a href="thread_edit.php?id=<?= $thread->getId() ?>">編集</a>
        <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('このスレッドを削除しますか？')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="type" value="thread">
            <input type="hidden" name="id" value="<?= $thread->getId() ?>">
            <button type="submit">削除</button>
        </form>
    <?php endif; ?>

    <p>
        投稿者: <?= htmlspecialchars($thread->getAuthorName(), ENT_QUOTES, 'UTF-8') ?><br>
        投稿日時: <?= date('Y/m/d H:i', strtotime($thread->getCreatedAt())) ?>
    </p>

    <p><?= nl2br(htmlspecialchars($thread->getContent(), ENT_QUOTES, 'UTF-8')) ?></p>

    <div class="reaction" data-post-type="thread" data-post-id="<?= $thread->getId() ?>" data-type="good">
        <button class="reaction-btn <?= $thread_reacted ? 'reacted' : '' ?>" <?= $is_logged_in ? '' : 'disabled' ?>>
            👍 <span class="reaction-count">
                <?= $thread_reaction_count ?>
            </span>
        </button>
    </div>

    <hr>

    <h3>コメント (<?= count($responses) ?> 件)</h3>

    <?php if (empty($responses)): ?>
        <p>まだコメントはありません。</p>
    <?php else: ?>
        <?php foreach ($responses as $i => $res): ?>
            <div id="res-<?= $res->getId() ?>" style="margin-bottom:16px; padding:8px; border:1px solid #ccc;">
                <strong>
                    <?= $i + 1 ?>番:
                    <?= htmlspecialchars($res->getUserName(), ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <span style="color:#888; margin-left:8px;">
                    <?= date('Y/m/d H:i', strtotime($res->getCreatedAt())) ?>
                </span>
                <p><?= nl2br(htmlspecialchars($res->getContent(), ENT_QUOTES, 'UTF-8')) ?></p>
                <?php if ($is_logged_in && $res->canEdit((int)$_SESSION['user_id'], $is_admin)): ?>
                    <a href="response_edit.php?id=<?= $res->getId() ?>">編集</a>
                    <form method="POST" action="delete.php" style="display:inline;"
                        onsubmit="return confirm('このコメントを削除しますか？')">
                        <input type="hidden" name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="type" value="response">
                        <input type="hidden" name="id" value="<?= $res->getId() ?>">
                        <button type="submit">削除</button>
                    </form>
                <?php endif; ?>
                <?php
                $res_key = 'response_' . $res->getId();
                $res_count = $response_reactions[$res_key]['good']['count'] ?? 0;
                $res_reacted = $response_reactions[$res_key]['good']['reacted'] ?? false;
                ?>
                <div class="reaction" data-post-type="response" data-post-id="<?= $res->getId() ?>" data-type="good">
                    <button class="reaction-btn <?= $res_reacted ? 'reacted' : '' ?>"
                        <?= $is_logged_in ? '' : 'disabled' ?>>
                        👍 <span class="reaction-count"><?= $res_count ?></span>
                    </button>
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
                    placeholder="コメントを入力してください"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></textarea><br><br>
                <button type="submit">投稿する</button>
            </form>

        <?php else: ?>
            <p>コメントするには<a href="login.php">ログイン</a>が必要です。</p>
        <?php endif; ?>
        <?php if ($is_logged_in): ?>
            <script>
                document.querySelectorAll('.reaction').forEach(function(wrapper) {
                    wrapper.querySelector('.reaction-btn').addEventListener('click', function() {
                        const btn = this;
                        const postType = wrapper.dataset.postType;
                        const postId = wrapper.dataset.postId;
                        const type = wrapper.dataset.type;

                        fetch('reaction.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams({
                                    csrf_token: '<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>',
                                    post_type: postType,
                                    post_id: postId,
                                    type: type,
                                })
                            })
                            .then(function(res) {
                                return res.json();
                            })
                            .then(function(data) {
                                btn.querySelector('.reaction-count').textContent = data.count;
                                btn.classList.toggle('reacted', data.reacted);
                            });
                    });
                });
            </script>
        <?php endif; ?>
</body>

</html>