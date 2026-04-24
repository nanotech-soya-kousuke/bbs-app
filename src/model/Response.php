<?php
require_once __DIR__ . '/Post.php';
require_once __DIR__ . '/Database.php';
class Response extends Post
{
    private $userName;
    private $threadId;

    public function __construct($id, $content, $userId, $createdAt, $userName = '', $threadId = 0)
    {
        parent::__construct($id, $content, $userId, $createdAt);
        $this->userName = $userName;
        $this->threadId = $threadId;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public static function validateResponse(string $content): array
    {
        $errors = [];
        if (trim($content) === '') {
            $errors[] = 'コメントを入力してください';
        }
        return $errors;
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

    public function update(string $content): void
    {
        $record = ORM::for_table('responses')->find_one($this->id);
        if (!$record) {
            throw new RuntimeException('レスポンスが見つかりません');
        }
        $record->content = $content;
        $record->save();

        $this->content = $content;
    }

    public function delete(): void
    {
        $record = ORM::for_table('responses')->find_one($this->id);
        if ($record) {
            $record->delete();
        }
    }

    public static function findById(int $id): ?self
    {
        $row = ORM::for_table('responses')
            ->table_alias('r')
            ->select('r.id')
            ->select('r.content')
            ->select('r.user_id')
            ->select('r.created_at')
            ->select('r.thread_id')
            ->select('u.name', 'user_name')
            ->join('users', ['r.user_id', '=', 'u.id'], 'u')
            ->where('r.id', $id)
            ->find_array();

        if (empty($row)) return null;
        $row = $row[0];

        $obj = new self(
            (int)$row['id'],
            $row['content'],
            (int)$row['user_id'],
            $row['created_at'],
            $row['user_name']
        );
        $obj->threadId = (int)$row['thread_id'];
        return $obj;
    }

    public function getThreadId(): int
    {
        return $this->threadId;
    }

    public static function getByThreadId(int $threadId, int $limit = 500): array
    {
        /*
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
        */
        $rows = ORM::for_table('responses')
            ->table_alias('r')
            ->select('r.id')
            ->select('r.content')
            ->select('r.user_id')
            ->select('r.created_at')
            ->select('u.name', 'user_name')
            ->join('users', array('r.user_id', '=', 'u.id'), 'u')
            ->where('r.thread_id', $threadId)
            ->order_by_asc('r.created_at')
            ->limit($limit)
            ->find_array();


        return array_map(fn($row) => new self(
            (int)$row['id'],
            $row['content'],
            (int)$row['user_id'],
            $row['created_at'],
            $row['user_name']
        ), $rows);
    }
}
