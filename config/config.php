<?php
/**
 * CraveHub - Online Food Delivery System
 * Application Configuration & Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Details
define('APP_NAME', 'CraveHub');
define('APP_TAGLINE', 'Crave it. Order it. Love it.');
define('CURRENCY', '₹');
define('DELIVERY_FEE', 40);
define('FREE_DELIVERY_THRESHOLD', 350);
define('TAX_PERCENT', 5); // 5% GST on Restaurant Food

// Order Status Constants
define('ORDER_STATUS_PLACED', 'placed');
define('ORDER_STATUS_CONFIRMED', 'confirmed');
define('ORDER_STATUS_PREPARING', 'preparing');
define('ORDER_STATUS_OUT_FOR_DELIVERY', 'out_for_delivery');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');

// Status Labels and Colors
function getOrderStatusBadge($status) {
    switch ($status) {
        case ORDER_STATUS_PLACED:
            return ['label' => 'Order Placed', 'class' => 'badge-placed', 'icon' => 'fa-receipt', 'percent' => 20];
        case ORDER_STATUS_CONFIRMED:
            return ['label' => 'Confirmed', 'class' => 'badge-confirmed', 'icon' => 'fa-check-circle', 'percent' => 40];
        case ORDER_STATUS_PREPARING:
            return ['label' => 'Cooking in Kitchen', 'class' => 'badge-preparing', 'icon' => 'fa-fire-burner', 'percent' => 65];
        case ORDER_STATUS_OUT_FOR_DELIVERY:
            return ['label' => 'Out for Delivery', 'class' => 'badge-delivery', 'icon' => 'fa-motorcycle', 'percent' => 85];
        case ORDER_STATUS_DELIVERED:
            return ['label' => 'Delivered', 'class' => 'badge-delivered', 'icon' => 'fa-box-circle-check', 'percent' => 100];
        case ORDER_STATUS_CANCELLED:
            return ['label' => 'Cancelled', 'class' => 'badge-cancelled', 'icon' => 'fa-circle-xmark', 'percent' => 0];
        default:
            return ['label' => ucfirst($status), 'class' => 'badge-default', 'icon' => 'fa-clock', 'percent' => 10];
    }
}

// Available Promo Codes
$PROMO_CODES = [
    'TASTY50' => [
        'type' => 'percentage',
        'value' => 50,
        'max_discount' => 120,
        'min_order' => 199,
        'desc' => '50% OFF up to ₹120 on orders above ₹199'
    ],
    'CRAVE100' => [
        'type' => 'flat',
        'value' => 100,
        'max_discount' => 100,
        'min_order' => 399,
        'desc' => 'Flat ₹100 OFF on orders above ₹399'
    ],
    'FREEDEL' => [
        'type' => 'shipping',
        'value' => 40,
        'max_discount' => 40,
        'min_order' => 149,
        'desc' => 'FREE Delivery on orders above ₹149'
    ],
    'FIRSTBITE' => [
        'type' => 'percentage',
        'value' => 20,
        'max_discount' => 150,
        'min_order' => 149,
        'desc' => '20% OFF up to ₹150 for welcome bonus'
    ]
];

// Base URL helper
function baseUrl($path = '') {
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Normalize path if running in root or subfolder
    $base = rtrim($scriptName, '/');
    if ($base === '' || $base === '.') {
        $base = '';
    }
    // Check if script is currently in /admin or /api
    if (substr($base, -6) === '/admin') {
        $base = substr($base, 0, -6);
    } elseif (substr($base, -4) === '/api') {
        $base = substr($base, 0, -4);
    }
    $path = ltrim($path, '/');
    return ($base === '' ? '' : $base) . '/' . $path;
}

// Authentication Helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'Guest User',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? '',
        'address' => $_SESSION['user_address'] ?? ''
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Please log in to continue.';
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? baseUrl('index.php');
        header('Location: ' . baseUrl('login.php'));
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['flash_error'] = 'Administrator authentication required.';
        header('Location: ' . baseUrl('admin/login.php'));
        exit;
    }
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash($type) {
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

// Cart session initializers
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // food_id => ['item' => [...], 'qty' => 1, 'notes' => '']
}

function getCartCount() {
    $count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += ($item['qty'] ?? 1);
        }
    }
    return $count;
}

function getCartSubtotal() {
    $subtotal = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $price = floatval($item['price'] ?? 0);
            $qty = intval($item['qty'] ?? 1);
            $subtotal += ($price * $qty);
        }
    }
    return $subtotal;
}
