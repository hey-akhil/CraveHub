<?php
/**
 * CraveHub - Admin Order Management
 */
$pageTitle = 'Manage Customer Orders';
$pageHeading = 'Live Customer Orders';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';

$statusFilter = $_GET['status'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');

$filter = [];
if (!empty($statusFilter)) {
    $filter['status'] = $statusFilter;
}
if (!empty($searchQuery)) {
    $filter['$or'] = [
        ['_id' => ['$regex' => $searchQuery, '$options' => 'i']],
        ['customer_name' => ['$regex' => $searchQuery, '$options' => 'i']],
        ['customer_phone' => ['$regex' => $searchQuery, '$options' => 'i']]
    ];
}

$orders = $db->find('orders', $filter, ['sort' => ['created_at' => -1]]);
?>

<!-- Filter Pills -->
<div style="display: flex; gap: 0.6rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="<?php echo baseUrl('admin/orders.php'); ?>" class="btn <?php echo empty($statusFilter) ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        All Orders
    </a>
    <a href="<?php echo baseUrl('admin/orders.php?status=placed'); ?>" class="btn <?php echo $statusFilter === 'placed' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        Order Placed
    </a>
    <a href="<?php echo baseUrl('admin/orders.php?status=confirmed'); ?>" class="btn <?php echo $statusFilter === 'confirmed' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        Confirmed
    </a>
    <a href="<?php echo baseUrl('admin/orders.php?status=preparing'); ?>" class="btn <?php echo $statusFilter === 'preparing' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        Preparing
    </a>
    <a href="<?php echo baseUrl('admin/orders.php?status=out_for_delivery'); ?>" class="btn <?php echo $statusFilter === 'out_for_delivery' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        Out for Delivery
    </a>
    <a href="<?php echo baseUrl('admin/orders.php?status=delivered'); ?>" class="btn <?php echo $statusFilter === 'delivered' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        Delivered
    </a>
    <a href="<?php echo baseUrl('admin/orders.php?status=cancelled'); ?>" class="btn <?php echo $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
        Cancelled
    </a>
</div>

<!-- Search Bar -->
<div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.75rem; flex: 1; max-width: 450px;">
        <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>"><?php endif; ?>
        <input type="text" name="q" class="form-control" placeholder="Search by Order ID, Customer, or Phone..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
    </form>
    <div style="font-size: 0.85rem; color: var(--text-dim);">
        Showing <strong><?php echo count($orders); ?></strong> orders
    </div>
</div>

<!-- Orders Table -->
<div class="admin-card-table">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Order & Time</th>
                    <th>Customer Details</th>
                    <th>Delivery Address</th>
                    <th>Items Summary</th>
                    <th>Payment</th>
                    <th>Total</th>
                    <th>Status Control</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-dim);">
                            No orders found matching the filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                            $orderId = (string)$order['_id'];
                            $items = $order['items'] ?? [];
                            $orderJson = htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary); font-family: var(--font-heading);"><?php echo htmlspecialchars($orderId); ?></strong>
                                <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo date('d M, h:i A', strtotime($order['created_at'])); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 700;"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($order['customer_phone'] ?? ''); ?></div>
                                <?php if (!empty($order['customer_email'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; max-width: 200px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?>
                                </div>
                                <?php if (!empty($order['delivery_notes'])): ?>
                                    <div style="font-size: 0.72rem; color: var(--primary); margin-top: 0.2rem;">
                                        Note: <?php echo htmlspecialchars($order['delivery_notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; max-width: 220px;">
                                    <?php foreach ($items as $item): ?>
                                        <div>• <?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['qty']; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($order['payment_method'] ?? 'UPI'); ?>
                                </div>
                                <span style="font-size: 0.75rem; color: <?php echo ($order['payment_status'] ?? '') === 'paid' ? '#10b981' : '#f59e0b'; ?>; font-weight: 700;">
                                    ● <?php echo strtoupper($order['payment_status'] ?? 'pending'); ?>
                                </span>
                            </td>
                            <td>
                                <strong style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--text-main);">
                                    ₹<?php echo number_format($order['total_amount'] ?? 0, 2); ?>
                                </strong>
                            </td>
                            <td>
                                <select class="status-select" onchange="updateAdminOrderStatus('<?php echo $orderId; ?>', this.value)">
                                    <option value="placed" <?php echo ($order['status'] ?? '') === 'placed' ? 'selected' : ''; ?>>Placed</option>
                                    <option value="confirmed" <?php echo ($order['status'] ?? '') === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="preparing" <?php echo ($order['status'] ?? '') === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="out_for_delivery" <?php echo ($order['status'] ?? '') === 'out_for_delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                    <option value="delivered" <?php echo ($order['status'] ?? '') === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo ($order['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <a href="<?php echo baseUrl('order-track.php?order_id=' . $orderId); ?>" target="_blank" class="btn btn-secondary btn-sm" title="Live Customer View">
                                    <i class="fa-solid fa-eye"></i> View
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
