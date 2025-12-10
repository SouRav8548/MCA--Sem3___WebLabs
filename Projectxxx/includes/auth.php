<?php
// includes/auth.php
session_name((require __DIR__ . '/../config/config.php')['app']['session_name']);
session_start();

function login_admin($id)
{
    $_SESSION['admin_id'] = $id;
}
function logout_admin()
{
    unset($_SESSION['admin_id']);
}

function login_student($id)
{
    $_SESSION['student_id'] = $id;
}
function logout_student()
{
    unset($_SESSION['student_id']);
}

function require_admin()
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: /marksheet-portal/public/login_admin.php');
        exit;
    }
}
function require_student()
{
    if (empty($_SESSION['student_id'])) {
        header('Location: /marksheet-portal/public/login_student.php');
        exit;
    }
}
