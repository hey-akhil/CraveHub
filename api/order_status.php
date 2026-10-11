<?php
/**
 * CraveHub - Order Status & Live Tracker API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$db = getDB();

$orderId = $_REQUEST['order_id'] ?? '';
if (empty($orderId)) {
    echo json_encode(['success' => false, 'error' => 'Order ID required']);
    exit;
}

$order = $db->findById('orders', $orderId);
if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

// Action: Advance Simulation (Placed -> Confirmed -> Preparing -> Out for Delivery -> Delivered)
if (isset($_POST['action']) && $_POST['action'] === 'advance_simulation') {
    $sequence = [
        ORDER_STATUS_PLACED => ORDER_STATUS_CONFIRMED,
        ORDER_STATUS_CONFIRMED => ORDER_STATUS_PREPARING,
        ORDER_STATUS_PREPARING => ORDER_STATUS_OUT_FOR_DELIVERY,
        ORDER_STATUS_OUT_FOR_DELIVERY => ORDER_STATUS_DELIVERED
    ];

    $current = $order['status'];
    if (isset($sequence[$current])) {
        $next = $sequence[$current];
        $now = date('Y-m-d H:i:s');
        
        $history = $order['status_history'] ?? [];
        $notes = [
            ORDER_STATUS_CONFIRMED => 'Kitchen accepted your order and started planning',
            ORDER_STATUS_PREPARING => 'Chef is preparing your fresh meal with hygienic safety',
            ORDER_STATUS_OUT_FOR_DELIVERY => 'Valet picked up your meal and is on the way!',
            ORDER_STATUS_DELIVERED => 'Order successfully handed over to customer. Enjoy!'
        ];

        $history[] = [
            'status' => $next,
            'time' => $now,
            'note' => $notes[$next] ?? 'Status updated'
        ];

        $db->update('orders', ['_id' => $orderId], [
            'status' => $next,
            'status_history' => $history,
            'updated_at' => $now
        ]);

        echo json_encode([
            'success' => true,
            'new_status' => $next
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Order is already delivered or in final state.'
        ]);
        exit;
    }
}

// Default: Return current order details
$order['status_badge'] = getOrderStatusBadge($order['status']);

echo json_encode([
    'success' => true,
    'order' => $order
]);
exit;
