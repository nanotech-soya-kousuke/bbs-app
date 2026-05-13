<?php
require_once __DIR__ . '/Thread.php';
require_once __DIR__ . '/Database.php';

class SearchManager
{
    public function searchThreadTitle(string $keyword, int $limit = 10, int $offset = 0): array
    {
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
            ->where_like('threads.title', '%' . $keyword . '%')
            ->group_by('threads.id')
            ->group_by('threads.title')
            ->group_by('threads.content')
            ->group_by('threads.user_id')
            ->group_by('threads.created_at')
            ->group_by('users.name')
            ->order_by_desc('threads.created_at')
            ->limit($limit)
            ->offset($offset)
            ->find_array();

        return $this->buildThreads($rows);
    }

    public function searchThreadUser(string $userName, int $limit = 10, int $offset = 0): array
    {
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
            ->where_like('users.name', '%' . $userName . '%')
            ->group_by('threads.id')
            ->group_by('threads.title')
            ->group_by('threads.content')
            ->group_by('threads.user_id')
            ->group_by('threads.created_at')
            ->group_by('users.name')
            ->order_by_desc('threads.created_at')
            ->limit($limit)
            ->offset($offset)
            ->find_array();

        return $this->buildThreads($rows);
    }

    private function buildThreads(array $rows): array
    {
        $threads = [];
        foreach ($rows as $row) {
            $threads[] = new Thread(
                (int)$row['id'],
                $row['title'],
                $row['content'],
                (int)$row['user_id'],
                $row['created_at'],
                $row['user_name'],
                (int)$row['response_count']
            );
        }
        return $threads;
    }
}
