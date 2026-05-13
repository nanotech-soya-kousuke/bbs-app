<?php

abstract class Post
{
    protected $id;
    protected $content;
    protected $userId;
    protected $createdAt;

    public function __construct($id, $content, $userId, $createdAt)
    {
        $this->id        = $id;
        $this->content   = $content;
        $this->userId    = $userId;
        $this->createdAt = $createdAt;
    }


    public function getId(): int
    {
        return $this->id;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /*
        @kanai: 
        #retake
        編集できるかのチェック機能ですが、引数はUserオブジェクト1つを受け取る形にするのがよいです。
        そうすることで、呼び出し側もUserオブジェクトを渡すだけで済み、ユーザIDや管理者権限などの内部的な値を把握する必要がなくなります。
        また、今後ユーザに紐づく条件が増えた場合も、引数の変更なく対応することができます。

        @soya:
        変更しました。
    */

    public function canEdit(User $user): bool
    {
        return $user->isAdmin() || $this->userId === $user->getId();
    }
}
