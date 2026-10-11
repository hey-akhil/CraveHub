<?php
/**
 * CraveHub - Customer Order History
 */
$pageTitle = 'My Order History';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();
$userId = $_SESSION['user_id'] ?? null;

// Find orders for this user or fetch recent if guest
$filter = [];
if ($userId) {
    $filter = ['user_id' => $userId];
}

$orders = $db->find('orders', $filter, ['sort' => ['created_at' => -1]]);

// If empty and not logged in, show sample seeded order so user sees a working demonstration
if (empty($orders) && !$userId) {
    $orders = $db->find('orders', [], ['limit' => 5, 'sort' => ['created_at' => -1]]);
}

// Handle 1-click Re-order
if (isset($_GET['reorder_id'])) {
    $reorder = $db->findById('orders', $_GET['reorder_id']);
    if ($reorder && !empty($reorder['items'])) {
        foreach ($reorder['items'] as $item) {
            $fId = $item['food_id'];
            if (isset($_SESSION['cart'][$fId])) {
                $_SESSION['cart'][$fId]['qty'] += $item['qty'];
            } else {
                $_SESSION['cart'][$fId] = $item;
            }
        }
        setFlash('success', 'All items from order added to cart!');
        header('Location: ' . baseUrl('cart.php'));
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 900;">Order History</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Track live deliveries, view invoices, or re-order your favorite meals</p>
        </div>
        <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Order New Food
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center;">
            <div class="empty-cart-icon"><i class="fa-solid fa-receipt"></i></div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Orders Placed Yet</h3>
            <p style="color: var(--text-dim); margin-bottom: 1.5rem;">You haven't ordered any delicious food yet. Check out our menu!</p>
            <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-primary">Browse Menu</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($orders as $order): ?>
                <?php
                    $orderId = (string)$order['_id'];
                    $badge = getOrderStatusBadge($order['status']);
                    $items = $order['items'] ?? [];
                ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem; backdrop-filter: blur(10px); transition: var(--transition);">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <h3 style="font-size: 1.15rem; font-weight: 800;">Order #<?php echo htmlspecialchars($orderId); ?></h3>
                                <span class="badge-status <?php echo $badge['class']; ?>">
                                    <i class="fa-solid <?php echo $badge['icon']; ?>"></i> <?php echo $badge['label']; ?>
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-dim); margin-top: 0.2rem;">
                                Placed: <?php echo date('D, d M Y • h:i A', strtotime($order['created_at'])); ?>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="text-align: right;">
                                <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase;">Total Amount</div>
                                <div style="font-weight: 900; font-family: var(--font-heading); font-size: 1.25rem; color: var(--text-main);">
                                    ₹<?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                            </div>

                            <a href="<?php echo baseUrl('order-track.php?order_id=' . $orderId); ?>" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-location-crosshairs"></i> Track Live
                            </a>
                            <a href="<?php echo baseUrl('my-orders.php?reorder_id=' . $orderId); ?>" class="btn btn-secondary btn-sm" title="Add all items from this order to cart">
                                <i class="fa-solid fa-rotate-right"></i> Re-Order
                            </a>
                        </div>
                    </div>

                    <!-- Items Preview in this Order -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <?php foreach ($items as $item): ?>
                            <div style="display: flex; align-items: center; gap: 0.6rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.4rem 0.75rem;">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? ''); ?>" alt="" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover;">
                                <span style="font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['qty']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
