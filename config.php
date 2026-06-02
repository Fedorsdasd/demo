<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'uchis_rf');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('ADMIN_LOGIN', 'Admin26');
define('ADMIN_PASSWORD', 'Demo20');
define('BASE_URL', '/uchis-rf');

function getDB(): mysqli {
    static $mysqli = null;
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3307);
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $mysqli->set_charset(DB_CHARSET);
    }
    return $mysqli;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/admin_login.php');
        exit;
    }
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
    exit;
}
