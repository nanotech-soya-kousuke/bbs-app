<?php
require_once 'User.php';
require_once __DIR__ . '/Database.php';

class UserManager
{
    public function __construct() {}

    /**
     * @kanai: 
     * #retake
     * どこからも呼んでいない処理になっています。不要であれば削除してください。
     * @soya:
     * ユーザー登録機能の改修に伴い、機能するようになりました。
     */
    public function register(string $name, string $email, string $password, string $passwordConfirm): User
    {
        $errors = $this->checkValidation($name, $email, $password, $passwordConfirm);
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
                $_SESSION['is_admin'] = (bool)$user->is_admin;
                $_SESSION['username'] = $user->name;
                $_SESSION['email']    = $user->email;
            } else {
                $errors[] = 'メールアドレスまたはパスワードが正しくありません';
            }
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(implode("\n", $errors));
        }
        return new User($user->id, $user->name, $user->email, '', (bool)$user->is_admin);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        return;
    }

    private function _register(string $name, string $email, string $password): User
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $record                = ORM::for_table('users')->create();
            $record->name          = $name;
            $record->email         = $email;
            $record->password_hash = $passwordHash;
            $record->save();
        } catch (PDOException $e) {
            throw new RuntimeException('このメールアドレスは既に登録されています');
        }

        return new User(
            (int)$record->id,
            $name,
            $email,
            '',
            false
        );
    }

    private function checkValidation(string $name, string $email, string $password, string $passwordConfirm = ''): array
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

        if ($passwordConfirm !== '' && $password !== $passwordConfirm) {
            $errors[] = 'パスワードが一致しません';
        }

        return $errors;
    }

    public function checkEditValidation(string $name, string $email): array
    {
        return $this->checkValidation($name, $email, 'dummy_pass_for_edit');
    }
}
