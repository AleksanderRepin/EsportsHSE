<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}

function is_admin(): bool {
    return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_admin() {

    require_login();

    if (!is_admin()) {
        http_response_code(403);
        echo "Доступ разрешен только администратору.";
        exit;
    }
}
