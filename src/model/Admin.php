<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/UserManager.php';

class Admin extends User
{
    public function updateUser(int $targetUserId, string $name, string $email): void
    {
        $manager = new UserManager();
        $errors  = $manager->checkEditValidation($name, $email);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }

        $record = ORM::for_table('users')->find_one($targetUserId);
        if (!$record) {
            throw new RuntimeException('ユーザーが見つかりません');
        }
        $record->name  = $name;
        $record->email = $email;
        $record->save();
    }

    public function deleteUser(int $targetUserId): void
    {
        if ($targetUserId === $this->getId()) {
            throw new InvalidArgumentException('自分自身は削除できません');
        }
        $record = ORM::for_table('users')->find_one($targetUserId);
        if ($record) {
            $record->delete();
        }
    }

    public function updatePost(Post $post, string $content): void
    {
        $post->update($content);
    }

    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    /**
     * @kanai: 
     *  #retake
     *  static メソッドとして外部からUserId を受け取る形になっています。
     *  (おそらく、セッションからユーザIDを取得して呼び出す現状の実装の都合かと思います)
     * 
     *  これはこれで動作的に問題ないですが、外部から呼び出す場合に、必ず内部的な値であるユーザIDを呼び出し側で把握する必要があり、
     *  オブジェクト指向的な観点からすると、使いにくいケースがでてきそうです。
     * 
     *  is_admin は Userのプロパティになるので、User.isAdmin() とするのが自然かと思います。
     *   ・Userクラスに isAdmin プロパティを追加
     * 　　　・初期化のタイミングでisAdminを設定
     * 　・Userクラスの isAdmin() メソッドでプロパティを返すようにする
     * 
     *  @soya:
     *  isAdminをUserクラスでの処理に変更しました
     */

    /**
     * @kanai: #retake
     *          ID指定でユーザを検索、取得するメソッドですが、管理者ユーザでなければ null を返す仕様になっています。
     * 　　　　　この仕様自体は問題ないですが、メソッド名が findById だと、ID指定でユーザを検索する一般的なメソッドのように見えてしまいます。
     * 　　　　　管理者ユーザでなければ null を返す仕様が分かるようなメソッド名になっているとよさそうです。
     * 　　　　　findAdminById など。 そうでなければ、最低限メソッドのコメントに仕様の表記があるとよいです。
     * 
     * @soya:
     * findById()を廃止しました
     * 
     */
}
