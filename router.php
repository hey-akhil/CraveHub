<?php
/**
 * CraveHub - Cloud Web Server Router
 * Handles clean routing for PHP built-in server on cloud hosting (Railway / Render / Cloud Run)
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $path;

// Serve static assets and specific files directly
if ($path !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Handle directory indices (e.g. / or /admin or /admin/)
if (is_dir($filePath)) {
    $indexPath = rtrim($filePath, '/') . '/index.php';
    if (file_exists($indexPath)) {
        require $indexPath;
        return;
    }
}

// Fallback to main index.php
require __DIR__ . '/index.php';
