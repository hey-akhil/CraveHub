<?php
/**
 * CraveHub - Logout Handler
 */
require_once __DIR__ . '/config/config.php';

session_unset();
session_destroy();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['flash_info'] = 'You have been successfully logged out.';
header('Location: ' . baseUrl('index.php'));
exit;
