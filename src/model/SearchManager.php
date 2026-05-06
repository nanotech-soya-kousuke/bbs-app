<?php
require_once __DIR__ . '/Thread.php';
require_once __DIR__ . '/Database.php';

class SearchManager
{
    public function searchThreadTitle(string $keyword, int $limit = 10, int $offset = 0): array
    {
        $rows = ORM::for_table('threads')
            ->table_alias('t')
            ->select('t.id')
            ->select('t.title')
            ->select('t.content')
            ->select('t.user_id')
            ->select('t.created_at')
            ->select('u.name', 'user_name')
            ->select_expr('COUNT(r.id)', 'response_count')
            ->join('users', ['t.user_id', '=', 'u.id'], 'u')
            ->left_outer_join('responses', ['r.thread_id', '=', 't.id'], 'r')
            ->where_like('t.title', '%' . $keyword . '%')
            ->group_by('t.id')
            ->group_by('t.title')
            ->group_by('t.content')
            ->group_by('t.user_id')
            ->group_by('t.created_at')
            ->group_by('u.name')
            ->order_by_desc('t.created_at')
            ->limit($limit)
            ->offset($offset)
            ->find_array();

        return $this->buildThreads($rows);
    }

    public function searchThreadUser(string $userName, int $limit = 10, int $offset = 0): array
    {
        $rows = ORM::for_table('threads')
            ->table_alias('t')
            ->select('t.id')
            ->select('t.title')
            ->select('t.content')
            ->select('t.user_id')
            ->select('t.created_at')
            ->select('u.name', 'user_name')
            ->select_expr('COUNT(r.id)', 'response_count')
            ->join('users', ['t.user_id', '=', 'u.id'], 'u')
            ->left_outer_join('responses', ['r.thread_id', '=', 't.id'], 'r')
            ->where_like('u.name', '%' . $userName . '%')
            ->group_by('t.id')
            ->group_by('t.title')
            ->group_by('t.content')
            ->group_by('t.user_id')
            ->group_by('t.created_at')
            ->group_by('u.name')
            ->order_by_desc('t.created_at')
            ->limit($limit)
            ->offset($offset)
            ->find_array();

        return $this->buildThreads($rows);
    }

    private function buildThreads(array $rows): array
    {
        $threads = [];
        foreach ($rows as $row) {
            $thread = new Thread(
                (int)$row['id'],
                $row['title'],
                $row['content'],
                (int)$row['user_id'],
                $row['created_at'],
                $row['user_name']
            );
            $thread->setResponseCount((int)$row['response_count']);
            $threads[] = $thread;
        }
        return $threads;
    }
}
