<?php
// config/config.example.php
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'marks_portal',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'http://localhost/marksheet-portal',
        'session_name' => 'marks_sess',
        'csrf_key' => 'change_this_secret_key',
    ],
];
