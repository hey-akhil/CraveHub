<?php
/**
 * CraveHub - Checkout & Order Creation API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'error' => 'Your cart is empty']);
    exit;
}

// Read inputs
$customerName = trim($_POST['customer_name'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$deliveryAddress = trim($_POST['delivery_address'] ?? '');
$deliveryNotes = trim($_POST['delivery_notes'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? 'upi');

if (empty($customerName) || empty($customerPhone) || empty($deliveryAddress)) {
    echo json_encode(['success' => false, 'error' => 'Please provide complete delivery name, phone, and address.']);
    exit;
}

// Calculate totals
$items = array_values($_SESSION['cart']);
$subtotal = getCartSubtotal();
$deliveryFee = ($subtotal >= FREE_DELIVERY_THRESHOLD) ? 0 : DELIVERY_FEE;

$discount = 0;
$couponCode = null;
if (!empty($_SESSION['applied_coupon'])) {
    $c = $_SESSION['applied_coupon'];
    $couponCode = $c['code'];
    if ($c['type'] === 'percentage') {
        $discount = min($c['max_discount'], ($subtotal * $c['value']) / 100);
    } elseif ($c['type'] === 'flat') {
        $discount = min($subtotal, $c['value']);
    } elseif ($c['type'] === 'shipping') {
        $deliveryFee = 0;
        $discount = $c['value'];
    }
}

$taxable = max(0, $subtotal - $discount);
$tax = round(($taxable * TAX_PERCENT) / 100, 2);
$totalAmount = round($taxable + $deliveryFee + $tax, 2);

// Generate unique order ID
$orderId = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

// Assigned delivery partner simulation
$partners = [
    [
        'name' => 'Rajesh Kumar',
        'phone' => '+91 98231 44556',
        'vehicle' => 'Honda Activa (KA-03-EX-4491)',
        'rating' => 4.9,
        'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80'
    ],
    [
        'name' => 'Vikram Singh',
        'phone' => '+91 98450 11223',
        'vehicle' => 'TVS Jupiter (KA-05-AB-9921)',
        'rating' => 4.8,
        'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80'
    ]
];
$assignedPartner = $partners[array_rand($partners)];

$now = date('Y-m-d H:i:s');
$orderDoc = [
    '_id' => $orderId,
    'user_id' => $_SESSION['user_id'] ?? 'guest_' . bin2hex(random_bytes(4)),
    'customer_name' => $customerName,
    'customer_email' => $customerEmail,
    'customer_phone' => $customerPhone,
    'delivery_address' => $deliveryAddress,
    'delivery_notes' => $deliveryNotes,
    'items' => $items,
    'subtotal' => $subtotal,
    'discount' => $discount,
    'coupon_code' => $couponCode,
    'delivery_fee' => $deliveryFee,
    'tax' => $tax,
    'total_amount' => $totalAmount,
    'payment_method' => $paymentMethod,
    'payment_status' => ($paymentMethod === 'cod') ? 'pending' : 'paid',
    'transaction_id' => 'TXN' . time() . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
    'status' => ORDER_STATUS_PLACED,
    'delivery_partner' => $assignedPartner,
    'status_history' => [
        [
            'status' => ORDER_STATUS_PLACED,
            'time' => $now,
            'note' => 'Order placed successfully via ' . strtoupper($paymentMethod)
        ]
    ],
    'created_at' => $now,
    'updated_at' => $now
];

// Insert into MongoDB
$db->insert('orders', $orderDoc);

// Clear Cart and applied coupons
$_SESSION['cart'] = [];
unset($_SESSION['applied_coupon']);
$_SESSION['latest_order_id'] = $orderId;

echo json_encode([
    'success' => true,
    'message' => 'Order placed successfully!',
    'order_id' => $orderId
]);
exit;
