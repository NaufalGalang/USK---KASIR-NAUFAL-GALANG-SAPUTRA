<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'administrator';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../kasir/index.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /kasir/pages/dashboard.php');
        exit;
    }
}
?>