<?php

class User
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $isAdmin;

    public function __construct($id, $username, $email, $password, $isAdmin = false)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->isAdmin  = $isAdmin;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
