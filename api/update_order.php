<?php
/**
 * CraveHub - Admin Order Status Updater API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Admin authorization required']);
    exit;
}

$db = getDB();

$orderId = trim($_POST['order_id'] ?? '');
$newStatus = trim($_POST['status'] ?? '');

$allowedStatuses = [
    ORDER_STATUS_PLACED,
    ORDER_STATUS_CONFIRMED,
    ORDER_STATUS_PREPARING,
    ORDER_STATUS_OUT_FOR_DELIVERY,
    ORDER_STATUS_DELIVERED,
    ORDER_STATUS_CANCELLED
];

if (empty($orderId) || !in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID or status']);
    exit;
}

$order = $db->findById('orders', $orderId);
if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

$now = date('Y-m-d H:i:s');
$history = $order['status_history'] ?? [];
$history[] = [
    'status' => $newStatus,
    'time' => $now,
    'note' => 'Admin updated status to ' . strtoupper($newStatus)
];

$updatePayload = [
    'status' => $newStatus,
    'status_history' => $history,
    'updated_at' => $now
];

if ($newStatus === ORDER_STATUS_DELIVERED) {
    $updatePayload['payment_status'] = 'paid';
}

$db->update('orders', ['_id' => $orderId], $updatePayload);

echo json_encode([
    'success' => true,
    'message' => "Order #{$orderId} updated to {$newStatus}",
    'badge' => getOrderStatusBadge($newStatus)
]);
exit;
