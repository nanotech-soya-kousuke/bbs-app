<?php
session_start();
require_once __DIR__ . '/../admin_check.php';
require_once __DIR__ . '/../model/Database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$users = ORM::for_table('users')->order_by_asc('id')->find_many();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー管理</title>
</head>

<body>

    <h2>ユーザー管理</h2>
    <a href="../index.php">← トップに戻る</a>

    <table border="1" cellpadding="6">
        <tr>
            <th>ID</th>
            <th>ユーザー名</th>
            <th>メールアドレス</th>
            <th>管理者</th>
            <th>登録日時</th>
            <th>操作</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int)$u->id ?></td>
                <td><?= htmlspecialchars($u->name, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u->email, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= $u->is_admin ? '〇' : '' ?></td>
                <td><?= htmlspecialchars($u->created_at, ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="user_edit.php?id=<?= (int)$u->id ?>">編集</a>
                    <?php if ($u->id !== $_SESSION['user_id']): ?>
                        <form method="POST" action="user_delete.php" style="display:inline;" onsubmit="return confirm('このユーザーを削除しますか？')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id" value="<?= (int)$u->id ?>">
                            <button type="submit">削除</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>