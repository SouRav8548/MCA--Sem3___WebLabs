<?php
// inc/auth.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';


function find_user_by_email($email) {
global $pdo;
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
return $stmt->fetch();
}


function create_user($email, $password_hash, $full_name, $verify_token) {
global $pdo;
$stmt = $pdo->prepare('INSERT INTO users (email, password_hash, full_name, verify_token) VALUES (?, ?, ?, ?)');
return $stmt->execute([$email, $password_hash, $full_name, $verify_token]);
}


function verify_user_token($token) {
global $pdo;
$stmt = $pdo->prepare('SELECT id FROM users WHERE verify_token = ? LIMIT 1');
$stmt->execute([$token]);
$row = $stmt->fetch();
if ($row) {
$update = $pdo->prepare('UPDATE users SET verified = 1, verify_token = NULL WHERE id = ?');
$update->execute([$row['id']]);
return true;
}
return false;
}