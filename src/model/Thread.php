<?php
require_once __DIR__ . '/Post.php';
require_once __DIR__ . '/Database.php';

class Thread extends Post
{
    private $title;
    private $userName;

    public function __construct($id, $title, $content, $userId, $createdAt, $userName = '')
    {
        parent::__construct($id, $content, $userId, $createdAt);
        $this->title    = $title;
        $this->userName = $userName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function getUserName(): string
    {
        return $this->userName;
    }

    public static function create(int $userId, string $title, string $content): self
    {
        $record = ORM::for_table('threads')->create();
        $record->title   = $title;
        $record->content = $content;
        $record->user_id = $userId;
        $record->save();

        return new self(
            (int)$record->id,
            $title,
            $content,
            $userId,
            $record->created_at
        );
    }

    public static function getAllWithStats(): array
    {
        $rows = ORM::raw_execute(
            "SELECT
                t.id,
                t.title,
                t.content,
                t.user_id,
                t.created_at,
                u.name AS user_name,
                COUNT(r.id) AS response_count
            FROM threads t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN responses r ON r.thread_id = t.id
            GROUP BY t.id, t.title, t.content, t.user_id, t.created_at, u.name
            ORDER BY t.created_at DESC"
        );
        $pdo  = ORM::get_db();
        $rows = $pdo->query(
            "SELECT
                t.id,
                t.title,
                t.content,
                t.user_id,
                t.created_at,
                u.name AS user_name,
                COUNT(r.id) AS response_count
            FROM threads t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN responses r ON r.thread_id = t.id
            GROUP BY t.id, t.title, t.content, t.user_id, t.created_at, u.name
            ORDER BY t.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $threads = [];
        foreach ($rows as $row) {
            $thread = new self(
                (int)$row['id'],
                $row['title'],
                $row['content'],
                (int)$row['user_id'],
                $row['created_at'],
                $row['user_name']
            );
            $thread->responseCount = (int)$row['response_count'];
            $threads[] = $thread;
        }
        return $threads;
    }
    public static function findById(int $id): ?self
    {
        $pdo  = ORM::get_db();
        $stmt = $pdo->prepare(
            "SELECT t.id, t.title, t.content, t.user_id, t.created_at, u.name AS user_name
            FROM threads t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new self(
            (int)$row['id'],
            $row['title'],
            $row['content'],
            (int)$row['user_id'],
            $row['created_at'],
            $row['user_name']
        );
    }
    public $responseCount = 0;
}
