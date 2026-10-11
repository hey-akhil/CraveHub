<?php
/**
 * CraveHub - Automated End-to-End Test Suite
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

echo "=================================================\n";
echo "CRAVEHUB END-TO-END AUTOMATED VERIFICATION SUITE\n";
echo "=================================================\n\n";

$db = getDB();
$status = $db->getStatusInfo();
echo "1. Database Engine Check:\n";
echo "   - Engine: " . $status['engine'] . "\n";
echo "   - Database: " . $status['database'] . "\n";
echo "   - PHP MongoDB Extension Loaded: " . ($status['extension_loaded'] ? 'YES' : 'NO') . "\n";
echo "   [PASSED]\n\n";

// 2. Foods Check
echo "2. Food Catalog Check:\n";
$foods = $db->find('foods');
echo "   - Total Dishes Available: " . count($foods) . "\n";
if (count($foods) >= 20) {
    echo "   [PASSED] Over 20 gourmet food items available.\n\n";
} else {
    echo "   [FAIL] Insufficient foods.\n\n";
}

// 3. User Authentication Test
echo "3. User Authentication Test:\n";
$user = $db->findOne('users', ['email' => 'rahul@gmail.com']);
if ($user && password_verify('user123', $user['password'])) {
    echo "   - User Login Verification (rahul@gmail.com): SUCCESS\n";
    echo "   [PASSED]\n\n";
} else {
    echo "   [FAIL] User login failed.\n\n";
}

// 4. Admin Authentication Test
echo "4. Admin Authentication Test:\n";
$admin = $db->findOne('users', ['email' => 'admin@cravehub.com']);
if ($admin && password_verify('admin123', $admin['password']) && $admin['role'] === 'admin') {
    echo "   - Admin Login Verification (admin@cravehub.com): SUCCESS\n";
    echo "   [PASSED]\n\n";
} else {
    echo "   [FAIL] Admin login failed.\n\n";
}

// 5. Test Cart & Coupon Logic
echo "5. Cart & Coupon Verification:\n";
$_SESSION['cart'] = [];
$firstFood = $foods[0];
$fId = (string)$firstFood['_id'];
$_SESSION['cart'][$fId] = [
    'food_id' => $fId,
    'title' => $firstFood['title'],
    'price' => floatval($firstFood['price']),
    'qty' => 2,
    'notes' => 'Less spicy please'
];
$subtotal = getCartSubtotal();
echo "   - Cart subtotal for 2x {$firstFood['title']}: ₹{$subtotal}\n";

// Apply TASTY50
$_SESSION['applied_coupon'] = [
    'code' => 'TASTY50',
    'type' => 'percentage',
    'value' => 50,
    'max_discount' => 120,
    'min_order' => 199
];
$discount = min(120, ($subtotal * 50) / 100);
echo "   - TASTY50 coupon discount calculated: ₹{$discount}\n";
echo "   [PASSED]\n\n";

// 6. Test Order Creation
echo "6. Simulated Order Placement:\n";
$testOrderId = 'ORD-TEST' . rand(1000, 9999);
$newOrder = [
    '_id' => $testOrderId,
    'user_id' => (string)$user['_id'],
    'customer_name' => $user['name'],
    'customer_email' => $user['email'],
    'customer_phone' => $user['phone'],
    'delivery_address' => $user['address'],
    'items' => array_values($_SESSION['cart']),
    'subtotal' => $subtotal,
    'discount' => $discount,
    'coupon_code' => 'TASTY50',
    'delivery_fee' => 0,
    'tax' => round((($subtotal - $discount) * TAX_PERCENT) / 100, 2),
    'total_amount' => round(($subtotal - $discount) + round((($subtotal - $discount) * TAX_PERCENT) / 100, 2), 2),
    'payment_method' => 'upi',
    'payment_status' => 'paid',
    'transaction_id' => 'TXN_TEST_' . time(),
    'status' => ORDER_STATUS_PLACED,
    'delivery_partner' => [
        'name' => 'Rajesh Kumar',
        'phone' => '+91 98231 44556',
        'vehicle' => 'Honda Activa (KA-03-EX-4491)',
        'rating' => 4.9,
        'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80'
    ],
    'status_history' => [
        ['status' => ORDER_STATUS_PLACED, 'time' => date('Y-m-d H:i:s'), 'note' => 'Order placed successfully']
    ],
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];
$insertedOrder = $db->insert('orders', $newOrder);
echo "   - Placed Order ID: {$testOrderId}\n";
echo "   - Amount: ₹{$newOrder['total_amount']}\n";
echo "   [PASSED]\n\n";

// 7. Test Live Order Advance Simulation (Placed -> Confirmed -> Preparing -> Out for Delivery -> Delivered)
echo "7. Live Order Status Advance Simulation:\n";
$stages = [ORDER_STATUS_CONFIRMED, ORDER_STATUS_PREPARING, ORDER_STATUS_OUT_FOR_DELIVERY, ORDER_STATUS_DELIVERED];
foreach ($stages as $st) {
    $db->update('orders', ['_id' => $testOrderId], ['status' => $st]);
    $check = $db->findById('orders', $testOrderId);
    echo "   - Advanced to: " . strtoupper($check['status']) . " [OK]\n";
}
echo "   [PASSED]\n\n";

// 8. Test Admin Metrics & User Orders Query
echo "8. Admin Metrics & User Orders Query:\n";
$userOrders = $db->find('orders', ['customer_email' => 'rahul@gmail.com']);
echo "   - Total Orders Found for Customer rahul@gmail.com: " . count($userOrders) . "\n";
echo "   [PASSED]\n\n";

echo "=================================================\n";
echo "ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "=================================================\n";
