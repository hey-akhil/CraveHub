<?php
/**
 * CraveHub - Admin Logout Handler
 */
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['admin_logged_in']);
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
}

header('Location: ' . baseUrl('admin/login.php'));
exit;
