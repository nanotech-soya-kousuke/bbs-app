<?php
require_once __DIR__ . '/../lib/idiorm.php';

ORM::configure([
    'connection_string' => 'pgsql:host=db;port=5432;dbname=bbs_app',
    'username'          => 'user',
    'password'          => 'password',
    'driver_options'    => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
]);
