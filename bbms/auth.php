<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function require_login() {
    if (empty($_SESSION['admin_id'])) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /bbms/login.php');
        exit;
    }
}
function is_logged_in() {
    return !empty($_SESSION['admin_id']);
}
?>