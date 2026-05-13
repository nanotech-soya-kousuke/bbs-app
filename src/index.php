<?php
session_start();
date_default_timezone_set('Asia/Tokyo');

require_once __DIR__ . '/model/Thread.php';
require_once __DIR__ . '/model/SearchManager.php';
require_once __DIR__ . '/model/Admin.php';

/*
 * @kanai: #tips 
 *        必須というほどでもないですが、この手の多くのパラメータを受け取る必要のある処理では、ファイルの先頭にパラメータのコメントがあると親切です。
 * 　　　　例えば、$search_type と $search_keyword については、どのような値が入るのか、どのような処理に影響するのかなどをコメントで説明しておくと、コードを読む人が理解しやすくなります。
 *        以下のようなコメントがあるとよいです。
 * 
 * @param string search_type 検索タイプ（'title' または 'user'）
 * @param string search_keyword 検索キーワード
 * @param int page 表示するページ番号
 */

$is_logged_in = isset($_SESSION['user_id']);
$is_admin     = $is_logged_in && (bool)$_SESSION['is_admin'];

$limit  = 10;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$search_type    = $_GET['search_type'] ?? '';
$search_keyword = trim($_GET['keyword'] ?? '');

$threads       = [];
$is_searching  = $search_keyword !== '' && in_array($search_type, ['title', 'user'], true);

if ($is_searching) {
    $manager = new SearchManager();
    if ($search_type === 'title') {
        $threads = $manager->searchThreadTitle($search_keyword, $limit, $offset);
    } else {
        $threads = $manager->searchThreadUser($search_keyword, $limit, $offset);
    }
} else {
    $threads = Thread::getAllWithResponseCount($limit, $offset);
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>トップページ</title>
</head>

<body>

    <h2>スレッド一覧</h2>

    <?php if ($is_logged_in): ?>
        <p>ログイン中</p>
        <a href="/logout.php">ログアウト</a>
        <?php if ($is_admin): ?>
            <a href="/admin/users.php">管理画面</a>
        <?php endif; ?>
    <?php else: ?>
        <p>ログインしていません</p>
        <a href="/login.php">ログイン</a>
        <a href="/register.php">新規登録</a>
    <?php endif; ?>

    <hr>

    <form method="GET">
        <select name="search_type">
            <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>スレッド名</option>
            <option value="user" <?= $search_type === 'user'  ? 'selected' : '' ?>>ユーザー名</option>
        </select>
        <input type="text" name="keyword"
            value="<?= htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="キーワードを入力">
        <button type="submit">検索</button>
        <?php if ($is_searching): ?>
            <a href="index.php">検索をクリア</a>
        <?php endif; ?>
    </form>

    <?php if ($is_searching): ?>
        <p>
            「<?= htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8') ?>」の検索結果:
            <?= count($threads) === 0 ? 'スレッドが見つかりませんでした' : count($threads) . '件' ?>
        </p>
    <?php endif; ?>

    <hr>

    <a href="/thread_create.php">スレッド作成</a>

    <?php foreach ($threads as $thread): ?>
        <div style="margin-bottom:20px;">
            <h3>
                <a href="thread.php?id=<?= $thread->getId() ?>">
                    <?= htmlspecialchars($thread->getTitle(), ENT_QUOTES, 'UTF-8') ?>
                </a>
            </h3>
            <p>
                投稿者: <?= htmlspecialchars($thread->getAuthorName(), ENT_QUOTES, 'UTF-8') ?><br>
                投稿日時: <?= date('Y/m/d H:i', strtotime($thread->getCreatedAt())) ?><br>
                コメント数: <?= $thread->getResponseCount() ?>
            </p>
        </div>
    <?php endforeach; ?>

    <hr>

    <div>
        <?php
        $paging_params = $is_searching
            ? '&search_type=' . urlencode($search_type) . '&keyword=' . urlencode($search_keyword)
            : '';
        ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 . $paging_params ?>">← 前のページ</a>
        <?php endif; ?>
        <?php if (count($threads) === $limit): ?>
            <a href="?page=<?= $page + 1 . $paging_params ?>">次のページ →</a>
        <?php endif; ?>
    </div>

</body>

</html>