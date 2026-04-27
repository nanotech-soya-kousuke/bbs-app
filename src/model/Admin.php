<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Database.php';

class Admin extends User
{
    public function updateUser(int $targetUserId, string $name, string $email): void
    {
        $record = ORM::for_table('users')->find_one($targetUserId);
        if (!$record) {
            throw new RuntimeException('ユーザーが見つかりません');
        }
        $record->name = $name;
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

    public static function isAdmin(int $userId): bool
    {
        $record = ORM::for_table('users')->find_one($userId);
        return $record && (bool)$record->is_admin;
    }

    public static function findById(int $userId): ?self
    {
        $record = ORM::for_table('users')->find_one($userId);
        if (!$record || !$record->is_admin) {
            return null;
        }
        return new self(
            (int)$record->id,
            $record->name,
            $record->email,
            ''
        );
    }
}
