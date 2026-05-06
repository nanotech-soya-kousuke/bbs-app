<?php
require_once __DIR__ . '/Database.php';

class Reaction
{
    private $id;
    private $postType;
    private $postId;
    private $userId;
    private $type;
    private $createdAt;

    public function __construct($id, $postType, $postId, $userId, $type, $createdAt)
    {
        $this->id = $id;
        $this->postType = $postType;
        $this->postId = $postId;
        $this->userId = $userId;
        $this->type = $type;
        $this->createdAt = $createdAt;
    }

    public static function toggle(string $postType, int $postId, int $userId, string $type = 'good'): bool
    {
        $existing = ORM::for_table('reactions')
            ->where('post_type', $postType)
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->find_one();

        if ($existing) {
            $existing->delete();
            return false;
        }

        $record = ORM::for_table('reactions')->create();
        $record->post_type = $postType;
        $record->post_id = $postId;
        $record->user_id = $userId;
        $record->type = $type;
        $record->save();
        return true;
    }

    public static function countByPost(string $postType, int $postId, string $type = 'good'): int
    {
        return ORM::for_table('reactions')
            ->where('post_type', $postType)
            ->where('post_id', $postId)
            ->where('type', $type)
            ->count();
    }

    public static function hasReacted(string $postType, int $postId, int $userId, string $type = 'good'): bool
    {
        return (bool) ORM::for_table('reactions')
            ->where('post_type', $postType)
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->find_one();
    }

    public static function getByPostIds(string $postType, array $postIds, int $userId): array
    {
        if (empty($postIds)) {
            return [];
        }

        $rows = ORM::for_table('reactions')
            ->where('post_type', $postType)
            ->where_in('post_id', $postIds)
            ->find_array();

        $result = [];
        foreach ($rows as $row) {
            $key = $postType . '_' . $row['post_id'];
            $type = $row['type'];
            if (!isset($result[$key][$type])) {
                $result[$key][$type] = ['count' => 0, 'reacted' => false];
            }
            $result[$key][$type]['count']++;
            if ((int)$row['user_id'] === $userId) {
                $result[$key][$type]['reacted'] = true;
            }
        }
        return $result;
    }
}
