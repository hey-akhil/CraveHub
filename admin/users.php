<?php
/**
 * CraveHub - Admin Customer Management
 */
$pageTitle = 'Registered Customers';
$pageHeading = 'Customer Profiles & Activity';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';

$users = $db->find('users', ['role' => 'user'], ['sort' => ['created_at' => -1]]);
$allOrders = $db->find('orders');

// Calculate user statistics
$userStats = [];
foreach ($allOrders as $ord) {
    $uId = $ord['user_id'] ?? null;
    $email = $ord['customer_email'] ?? '';
    
    // Key by user_id or email
    $key = $uId ?: $email;
    if ($key) {
        if (!isset($userStats[$key])) {
            $userStats[$key] = ['count' => 0, 'spent' => 0];
        }
        $userStats[$key]['count']++;
        $userStats[$key]['spent'] += floatval($ord['total_amount'] ?? 0);
    }
}
?>

<div style="margin-bottom: 2rem;">
    <p style="color: var(--text-dim); font-size: 0.9rem;">
        View registered accounts, verify contact details, and track customer lifetime value.
    </p>
</div>

<!-- Users Table -->
<div class="admin-card-table">
    <div class="table-header-bar">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800;">Customer Database</h3>
            <span style="font-size: 0.8rem; color: var(--text-dim);">MongoDB Collection: <code>users</code></span>
        </div>
        <div style="font-size: 0.85rem; color: var(--text-dim);">
            Total Customers: <strong><?php echo count($users); ?></strong>
        </div>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Default Delivery Address</th>
                    <th>Joined On</th>
                    <th>Orders Placed</th>
                    <th>Total Spent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-dim);">
                            No registered customers found yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                            $uId = (string)$u['_id'];
                            $stats = $userStats[$uId] ?? ($userStats[$u['email']] ?? ['count' => 0, 'spent' => 0]);
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; color: #fff;">
                                        <?php echo strtoupper(substr($u['name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong style="font-size: 0.95rem;"><?php echo htmlspecialchars($u['name'] ?? 'Anonymous'); ?></strong>
                                        <div style="font-size: 0.72rem; color: #10b981;">● Active</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem; color: var(--text-muted);"><?php echo htmlspecialchars($u['email'] ?? ''); ?></span>
                            </td>
                            <td>
                                <span style="font-size: 0.88rem;"><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; max-width: 250px; line-height: 1.4; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($u['address'] ?? 'Not provided'); ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; color: var(--text-dim);">
                                    <?php echo date('d M Y', strtotime($u['created_at'] ?? 'now')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-tag" style="background: rgba(249, 115, 22, 0.15); color: var(--primary); font-weight: 800;">
                                    <?php echo $stats['count']; ?> orders
                                </span>
                            </td>
                            <td>
                                <strong style="font-family: var(--font-heading); font-size: 1.05rem; color: #10b981;">
                                    ₹<?php echo number_format($stats['spent'], 2); ?>
                                </strong>
                            </td>
                            <td>
                                <a href="<?php echo baseUrl('admin/orders.php?q=' . urlencode($u['name'])); ?>" class="btn btn-secondary btn-sm" title="View all orders from this customer">
                                    <i class="fa-solid fa-receipt"></i> Orders
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
