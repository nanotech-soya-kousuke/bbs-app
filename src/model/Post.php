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
}
