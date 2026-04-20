<?php
require_once __DIR__ . '/Post.php';
require_once __DIR__ . '/Database.php';
class Response extends Post
{
    private $userName;
    public function __construct($id, $content, $userId, $createdAt, $userName = '')
    {
        parent::__construct($id, $content, $userId, $createdAt);
        $this->userName = $userName;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public static function create(int $userId, int $threadId, string $content): self
    {
        $record            = ORM::for_table('responses')->create();
        $record->thread_id = $threadId;
        $record->content   = $content;
        $record->user_id   = $userId;
        $record->save();

        return new self(
            (int)$record->id,
            $content,
            $userId,
            $record->created_at
        );
    }

    public static function getByThreadId(int $threadId, int $limit = 500): array
    {
        $pdo  = ORM::get_db();
        $stmt = $pdo->prepare(
            "SELECT
                r.id,
                r.content,
                r.user_id,
                r.created_at,
                u.name AS user_name
            FROM responses r
            JOIN users u ON r.user_id = u.id
            WHERE r.thread_id = :thread_id
            ORDER BY r.created_at ASC
            LIMIT :limit"
        );
        $stmt->bindValue(':thread_id', $threadId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',     $limit,    PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new self(
            (int)$row['id'],
            $row['content'],
            (int)$row['user_id'],
            $row['created_at'],
            $row['user_name']
        ), $rows);
    }
}
