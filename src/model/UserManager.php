<?php
require_once 'User.php';
require_once __DIR__ . '/../lib/idiorm.php';

ORM::configure([
    'connection_string' => 'pgsql:host=db;port=5432;dbname=bbs_app',
    'username'          => 'user',
    'password'          => 'password',
    'driver_options'    => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
]);

class UserManager
{
    public function __construct() {}

    public function register($name, $email, $password): User
    {
        $errors = $this->checkValidation($name, $email, $password);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }
        return $this->_register($name, $email, $password);
    }

    public function login($email, $password): User
    {
        $errors = [];
        if ($email === '' || $password === '') {
            $errors[] = 'メールアドレスまたはパスワードが正しくありません';
        }

        if (empty($errors)) {
            $user = ORM::for_table('users')
                ->where('email', $email)
                ->find_one();

            if ($user && password_verify($password, $user->password_hash)) {
                $_SESSION['user_id'] = $user->id;
            } else {
                $errors[] = 'メールアドレスまたはパスワードが正しくありません';
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }
        return new User($user->id, 'dummy', $user->email, '');
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        return;
    }

    private function _register($name, $email, $password): User
    {
        // idiorm を使ってユーザ保存処理
        return new User(1, $name, $email, $password); // dummy
    }

    private function checkValidation($name, $email, $password): array
    {
        $errors = [];

        if ($name === '') {
            $errors[] = 'ユーザー名は必須です';
        } elseif (preg_match('/^[\s　]+$/u', $name)) {
            $errors[] = 'ユーザー名は空白のみでは登録できません';
        }

        if ($email === '') {
            $errors[] = 'メールアドレスは必須です';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'メールアドレスの形式が不正です';
        }

        if ($password === '') {
            $errors[] = 'パスワードは必須です';
        } elseif (strlen($password) < 8) {
            $errors[] = 'パスワードは8文字以上にしてください';
        } elseif (trim($password) === '') {
            $errors[] = '空白のみのパスワードは使用できません';
        }

        return $errors;
    }
}
