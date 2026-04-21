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

    /*
        @kanai:
            メソッド名について、getUserName はユーザー名を取得するメソッドであることはわかりますが、少しあいまいです。
            作成者なのか、最終更新者なのか など。具体的な名前を付けるようにしてみてください。
            例)
                getUserName -> getAuthorName / getOwnerName  など
     */
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

    /*
        @kanai:
            全スレッドを取得するメソッドになるかと思いますが、スレッド件数が増えると
            　・クライアント側
                ・全スレッドを一括表示するため、表示に時間がかかる
            　・サーバ側
                ・スレッド情報取得処理(SQL実行)に時間がかかる
                ・全スレッドをメモリに一旦載せているため、メモリ使用量が増える

            のパフォーマンス上のボトルネックになることが見込まれます。
            そのため、ページング機能に対応するか、取得上限件数を指定できるようにしてください。

        @kanai:
            メソッド名がイマイチ不明瞭です。
            Stats が何を表すのかがわからないため、もう少し具体的な名前にしてください。
    
    */
    public static function getAllWithStats(): array
    {
        /*
            @kanai:
                同じクエリを2回実行している。不具合？
        */
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
        
        /*
            @kanai:
                PDO を取得して、PDOを介して 直接SQLを実行していますが、ORMのraw_queryを使うようにしてください。
                基本的にプロジェクトではORMを使う方針なので、ORMを使わないコードは極力書かないようにしてください。（統一感）

            // 実装例（動作未確認）
            $rows = ORM::for_table('threads')
                ->raw_query(
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
                )
                ->find_many();

            foreach ($rows as $row) {
                $thread = new self(
                    $row->id,
                    $row->title,
                    $row->content,
                    $row->user_id,
                    $row->created_at,
                    $row->user_name
                );
                $thread->responseCount = $row->response_count;
                $threads[] = $thread;
            }
        */


        /*
             @kanai:
                また、今回のような複雑なクエリは、raw_query でOKですが、簡単なクエリの場合はORMのクエリビルダを使うようにしてください。
                可読性が上がりますし、SQLインジェクションのリスクも減ります。

                例)
                $rows = ORM::for_table('threads')
                    ->join('users', ['threads.user_id', '=', 'users.id'])
                    ->left_outer_join('responses', ['responses.thread_id', '=', 'threads.id'])
                    ->select('threads.id')
                    ->select('threads.title')
                    ->select('threads.content')
                    ->select('threads.user_id')
                    ->select('threads.created_at')
                    ->select('users.name', 'user_name')
                    ->select_expr('COUNT(responses.id)', 'response_count')
                    ->group_by('threads.id')
                    ->group_by('threads.title')
                    ->group_by('threads.content')
                    ->group_by('threads.user_id')
                    ->group_by('threads.created_at')
                    ->group_by('users.name')
                    ->order_by_desc('threads.created_at')
                    ->find_many();
        */
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
        
        /*
            kanai: 
                単純なSQLなので、ORM(idiorm) のクエリビルダーで取得してみてください。
                SQLインジェクション対策にプリペアードステートメントを使えてるのはGOODです。
        */

        $pdo  = ORM::get_db();
        $stmt = $pdo->prepare(
            "SELECT t.id, t.title, t.content, t.user_id, t.created_at, u.name AS user_name
            FROM threads t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        /* 
            kanai: 
                コーディング規約は別途準備中ですが、基本的にif分の波括弧は省略しない方針でお願いします。
                if(!row) {
                    return null;
                }
        */
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

    /*
        @kanai:
            スレッド毎のレスポンス件数のプロパティかと思いますが、public だと クラス外から書き換えられる可能性があるため。
            private が望ましいケースだと思います。
            private にしたうえで、他プロパティと同じく getResponseCount を用意するのがよいです。
    */
    public $responseCount = 0;
}
