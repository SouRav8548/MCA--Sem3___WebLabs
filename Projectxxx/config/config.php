<?php
// config/config.php
// Copy from config.example.php and update values.
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
        'csrf_key' => 'a_long_random_secret_here',
    ],
];
