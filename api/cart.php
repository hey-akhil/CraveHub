<?php
/**
 * CraveHub - Cart API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$db = getDB();
$action = $_REQUEST['action'] ?? 'get';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        $foodId = $_POST['food_id'] ?? '';
        $qty = max(1, intval($_POST['qty'] ?? 1));
        $notes = trim($_POST['notes'] ?? '');

        if (empty($foodId)) {
            echo json_encode(['success' => false, 'error' => 'Food ID is required']);
            exit;
        }

        $food = $db->findById('foods', $foodId);
        if (!$food) {
            echo json_encode(['success' => false, 'error' => 'Item not found']);
            exit;
        }

        if (isset($_SESSION['cart'][$foodId])) {
            $_SESSION['cart'][$foodId]['qty'] += $qty;
            if (!empty($notes)) {
                $_SESSION['cart'][$foodId]['notes'] = $notes;
            }
        } else {
            $_SESSION['cart'][$foodId] = [
                'food_id' => $food['_id'],
                'title' => $food['title'],
                'price' => floatval($food['price']),
                'image' => $food['image'] ?? '',
                'is_veg' => $food['is_veg'] ?? true,
                'qty' => $qty,
                'notes' => $notes
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => "Added {$food['title']} to cart!",
            'cart_count' => getCartCount(),
            'item_qty' => $_SESSION['cart'][$foodId]['qty']
        ]);
        exit;

    case 'update':
        $foodId = $_POST['food_id'] ?? '';
        $qty = intval($_POST['qty'] ?? 0);

        if (empty($foodId)) {
            echo json_encode(['success' => false, 'error' => 'Food ID is required']);
            exit;
        }

        if ($qty <= 0) {
            unset($_SESSION['cart'][$foodId]);
            $itemQty = 0;
        } else {
            if (isset($_SESSION['cart'][$foodId])) {
                $_SESSION['cart'][$foodId]['qty'] = $qty;
                $itemQty = $qty;
            } else {
                $itemQty = 0;
            }
        }

        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount(),
            'item_qty' => $itemQty
        ]);
        exit;

    case 'apply_coupon':
        $code = strtoupper(trim($_POST['code'] ?? ''));
        if (empty($code)) {
            echo json_encode(['success' => false, 'error' => 'Please enter coupon code']);
            exit;
        }

        global $PROMO_CODES;
        $coupon = $PROMO_CODES[$code] ?? null;

        // Also check database for dynamically added coupons
        if (!$coupon) {
            $dbCoupon = $db->findOne('coupons', ['code' => $code]);
            if ($dbCoupon) $coupon = $dbCoupon;
        }

        if (!$coupon) {
            echo json_encode(['success' => false, 'error' => 'Invalid or expired coupon code']);
            exit;
        }

        $subtotal = getCartSubtotal();
        if ($subtotal < ($coupon['min_order'] ?? 0)) {
            echo json_encode([
                'success' => false,
                'error' => "Minimum order amount of ₹{$coupon['min_order']} required for this coupon"
            ]);
            exit;
        }

        $_SESSION['applied_coupon'] = [
            'code' => $code,
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'max_discount' => $coupon['max_discount'] ?? 0,
            'min_order' => $coupon['min_order'] ?? 0
        ];

        echo json_encode(['success' => true, 'message' => "Coupon '{$code}' applied successfully!"]);
        exit;

    case 'remove_coupon':
        unset($_SESSION['applied_coupon']);
        echo json_encode(['success' => true, 'message' => 'Coupon removed']);
        exit;

    case 'get':
    default:
        $items = array_values($_SESSION['cart'] ?? []);
        $subtotal = getCartSubtotal();
        $deliveryFee = ($subtotal >= FREE_DELIVERY_THRESHOLD || $subtotal === 0) ? 0 : DELIVERY_FEE;

        // Calculate discount
        $discount = 0;
        if (!empty($_SESSION['applied_coupon'])) {
            $c = $_SESSION['applied_coupon'];
            if ($subtotal >= ($c['min_order'] ?? 0)) {
                if ($c['type'] === 'percentage') {
                    $discount = min($c['max_discount'], ($subtotal * $c['value']) / 100);
                } elseif ($c['type'] === 'flat') {
                    $discount = min($subtotal, $c['value']);
                } elseif ($c['type'] === 'shipping') {
                    $deliveryFee = 0;
                    $discount = $c['value'];
                }
            } else {
                unset($_SESSION['applied_coupon']);
            }
        }

        $taxableAmount = max(0, $subtotal - $discount);
        $tax = round(($taxableAmount * TAX_PERCENT) / 100, 2);
        $total = max(0, $taxableAmount + $deliveryFee + $tax);

        echo json_encode([
            'items' => $items,
            'cart_count' => getCartCount(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => round($discount, 2),
            'applied_coupon' => $_SESSION['applied_coupon'] ?? null,
            'tax' => $tax,
            'total' => round($total, 2)
        ]);
        exit;
}
