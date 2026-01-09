<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'tdhgame');
define('DB_USER', 'tdhadmin');
define('DB_PASS', 'tdhgame20250923');

$DB_CONFIG = [
    "mysql" => [
        "host" => DB_HOST,
        "dbname" => DB_NAME,
        "user" => DB_USER,
        "pass" => DB_PASS,
        "charset" => "utf8mb4"
    ],

    "mongodb" => [
        'host' => '127.0.0.1',
        'port' => '27017',
        'dbname' => 'TDHGame', // 你的数据库名
        'user' => '',          // 如果本地没设密码可为空
        'pass' => '',
    ]
];
