<?php
/**
 * CraveHub - Terminal Database Quick Inspector
 * Usage: php view_db.php [collection_name]
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();
$status = $db->getStatusInfo();

echo "========================================================\n";
echo "   CRAVEHUB MONGODB DATABASE INSPECTOR (CLI)\n";
echo "========================================================\n";
echo "Engine:   " . $status['engine'] . "\n";
echo "Database: " . $status['database'] . "\n";
echo "Host:     mongodb://127.0.0.1:27017\n";
echo "========================================================\n\n";

$collection = $argv[1] ?? null;

if (!$collection) {
    echo "COLLECTIONS OVERVIEW:\n";
    $collections = ['users', 'foods', 'orders', 'categories', 'coupons'];
    foreach ($collections as $col) {
        $count = $db->count($col);
        printf("  - %-15s : %d documents\n", $col, $count);
    }
    echo "\nTo inspect any collection's documents, run:\n";
    echo "  php view_db.php foods\n";
    echo "  php view_db.php orders\n";
    echo "  php view_db.php users\n";
    echo "========================================================\n";
    exit;
}

$count = $db->count($collection);
echo "INSPECTING COLLECTION: '{$collection}' (Total: {$count})\n";
echo "--------------------------------------------------------\n";

$docs = $db->find($collection, [], ['limit' => 10, 'sort' => ['created_at' => -1]]);
if (empty($docs)) {
    echo "No documents found in {$collection}.\n";
} else {
    foreach ($docs as $i => $doc) {
        echo "DOCUMENT #" . ($i + 1) . " (ID: " . ($doc['_id'] ?? 'N/A') . "):\n";
        echo json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    }
}
echo "========================================================\n";
