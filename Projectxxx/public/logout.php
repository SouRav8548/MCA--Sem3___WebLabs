<?php
// public/logout.php
require __DIR__ . '/../includes/auth.php';
logout_admin();
logout_student();
header('Location: /marksheet-portal/public/index.php');
