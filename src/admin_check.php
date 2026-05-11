<?php

require_once __DIR__ . '/model/Admin.php';

if(!isset($_SESSION['user_id'])){
    header('Location: ../login.php');
    exit;
}


/**
 * @kanai: #retake
 *          Admin::isAdmin, Admin::findById は内部的には同じクエリを実行しています。
 * 　　　　　Admin クラスで指摘した点を踏まえ 下記のようにするのがよさそうです。
 *           $user = User::findById((int)$_SESSION['user_id']);
 *           if($user && !$user->isAdmin()){ 
 *              // 管理者でない
 *           }
 * 
 */

if(!Admin::isAdmin((int)$_SESSION['user_id'])){
    http_response_code(403);
    exit('権限がありません');
}

$admin = Admin::findById((int)$_SESSION['user_id']);