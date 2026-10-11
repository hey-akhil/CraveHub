<?php
/**
 * CraveHub - Admin Main Dashboard
 */
$pageTitle = 'Dashboard Overview';
$pageHeading = 'Dashboard Analytics';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';

$orders = $db->find('orders', [], ['sort' => ['created_at' => -1]]);
$foods = $db->find('foods');
$users = $db->find('users', ['role' => 'user']);
$categories = $db->find('categories');

// Compute Metrics
$totalOrders = count($orders);
$totalFoodItems = count($foods);
$totalCustomers = count($users);

$totalRevenue = 0;
$pendingOrdersCount = 0;
foreach ($orders as $ord) {
    $totalRevenue += floatval($ord['total_amount'] ?? 0);
    if (in_array($ord['status'] ?? '', [ORDER_STATUS_PLACED, ORDER_STATUS_CONFIRMED, ORDER_STATUS_PREPARING])) {
        $pendingOrdersCount++;
    }
}

// Slice recent 6 orders for the table
$recentOrders = array_slice($orders, 0, 8);
?>

<!-- 4 Key Analytics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h6>Total Revenue</h6>
            <div class="stat-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
            <div style="font-size: 0.78rem; color: #10b981; margin-top: 0.3rem;">
                <i class="fa-solid fa-arrow-trend-up"></i> Lifetime Net Gross
            </div>
        </div>
        <div class="stat-icon primary">
            <i class="fa-solid fa-indian-rupee-sign"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h6>Total Orders</h6>
            <div class="stat-value"><?php echo $totalOrders; ?></div>
            <div style="font-size: 0.78rem; color: var(--primary); margin-top: 0.3rem;">
                <?php echo $pendingOrdersCount; ?> orders in kitchen/delivery
            </div>
        </div>
        <div class="stat-icon green">
            <i class="fa-solid fa-receipt"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h6>Food Menu Items</h6>
            <div class="stat-value"><?php echo $totalFoodItems; ?></div>
            <div style="font-size: 0.78rem; color: var(--text-dim); margin-top: 0.3rem;">
                Across <?php echo count($categories); ?> active categories
            </div>
        </div>
        <div class="stat-icon blue">
            <i class="fa-solid fa-utensils"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h6>Registered Customers</h6>
            <div class="stat-value"><?php echo $totalCustomers; ?></div>
            <div style="font-size: 0.78rem; color: #a855f7; margin-top: 0.3rem;">
                MongoDB Verified Foodies
            </div>
        </div>
        <div class="stat-icon purple">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
</div>

<!-- Pending Kitchen Orders Alert Banner -->
<?php if ($pendingOrdersCount > 0): ?>
    <div style="background: rgba(249, 115, 22, 0.12); border: 1px solid var(--border-highlight); border-radius: var(--radius-md); padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                <i class="fa-solid fa-bell fa-shake"></i>
            </div>
            <div>
                <h5 style="font-size: 1rem; font-weight: 800;">Attention: <?php echo $pendingOrdersCount; ?> Active Orders Need Delivery Update</h5>
                <p style="font-size: 0.82rem; color: var(--text-dim);">Orders are awaiting kitchen preparation or valet assignment.</p>
            </div>
        </div>
        <a href="<?php echo baseUrl('admin/orders.php'); ?>" class="btn btn-primary btn-sm">
            Manage Orders Now <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>

<!-- Recent Orders Data Table -->
<div class="admin-card-table">
    <div class="table-header-bar">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800;">Recent Customer Orders</h3>
            <span style="font-size: 0.8rem; color: var(--text-dim);">Real-time orders stored in MongoDB database</span>
        </div>
        <a href="<?php echo baseUrl('admin/orders.php'); ?>" class="btn btn-secondary btn-sm">
            View All Orders
        </a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Phone / Address</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Live Status</th>
                    <th>Quick Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-dim);">
                            No orders placed yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $ord): ?>
                        <?php
                            $ordId = (string)$ord['_id'];
                            $b = getOrderStatusBadge($ord['status'] ?? 'placed');
                            $itemCount = count($ord['items'] ?? []);
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary); font-family: var(--font-heading);"><?php echo htmlspecialchars($ordId); ?></strong>
                                <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('d M, h:i A', strtotime($ord['created_at'])); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?php echo htmlspecialchars($ord['customer_name'] ?? 'Guest'); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($ord['customer_email'] ?? ''); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($ord['customer_phone'] ?? ''); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-dim); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars($ord['delivery_address'] ?? ''); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge-tag" style="background: rgba(255,255,255,0.06);">
                                    <?php echo $itemCount; ?> item<?php echo $itemCount > 1 ? 's' : ''; ?>
                                </span>
                            </td>
                            <td>
                                <strong style="font-family: var(--font-heading); font-size: 1.05rem;">
                                    ₹<?php echo number_format($ord['total_amount'] ?? 0, 2); ?>
                                </strong>
                            </td>
                            <td>
                                <span style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: <?php echo ($ord['payment_status'] ?? '') === 'paid' ? '#10b981' : '#f59e0b'; ?>;">
                                    ● <?php echo htmlspecialchars($ord['payment_method'] ?? 'COD'); ?> (<?php echo htmlspecialchars($ord['payment_status'] ?? 'pending'); ?>)
                                </span>
                            </td>
                            <td>
                                <!-- Quick Status Changer Dropdown -->
                                <select class="status-select" onchange="updateAdminOrderStatus('<?php echo $ordId; ?>', this.value)">
                                    <option value="placed" <?php echo ($ord['status'] ?? '') === 'placed' ? 'selected' : ''; ?>>Order Placed</option>
                                    <option value="confirmed" <?php echo ($ord['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="preparing" <?php echo ($ord['status'] ?? '') === 'preparing' ? 'selected' : ''; ?>>Preparing in Kitchen</option>
                                    <option value="out_for_delivery" <?php echo ($ord['status'] ?? '') === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                    <option value="delivered" <?php echo ($ord['status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo ($ord['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <a href="<?php echo baseUrl('order-track.php?order_id=' . $ordId); ?>" target="_blank" class="btn btn-secondary btn-sm" title="View Customer Live Tracker">
                                    <i class="fa-solid fa-eye"></i> Track
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
