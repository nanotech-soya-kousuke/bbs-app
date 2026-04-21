<?php
require_once __DIR__ . '/../lib/idiorm.php';

/*
    @kanai:
        研修中は問題ありませんが、こういったデータベース接続の設定情報は、コード上にハードコードしないようにしてください。 
        ※Github のコードが流出したタイミングで、リスクがあるため。
        例えば、環境変数や設定ファイルなどから読み込むようにするのが一般的です。
        下記用語について調べておいてください。（特に対応不要）
            キーワード：.env / KMS(鍵管理システム) / Secret Manager など
*/

ORM::configure([
    'connection_string' => 'pgsql:host=db;port=5432;dbname=bbs_app',
    'username'          => 'user',
    'password'          => 'password',
    'driver_options'    => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
]);
