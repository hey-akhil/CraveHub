<?php
/**
 * CraveHub - Admin Promo Codes & Coupons
 */
$pageTitle = 'Promo Codes & Discounts';
$pageHeading = 'Coupons & Discounts';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';

$coupons = $db->find('coupons');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coupon') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $type = trim($_POST['type'] ?? 'percentage');
    $value = floatval($_POST['value'] ?? 0);
    $maxDiscount = floatval($_POST['max_discount'] ?? 0);
    $minOrder = floatval($_POST['min_order'] ?? 0);
    $desc = trim($_POST['desc'] ?? '');

    if (!empty($code) && $value > 0) {
        $db->insert('coupons', [
            '_id' => 'coupon_' . strtolower($code),
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'max_discount' => $maxDiscount,
            'min_order' => $minOrder,
            'desc' => $desc
        ]);
        setFlash('success', "Coupon '{$code}' added successfully!");
        header('Location: ' . baseUrl('admin/coupons.php'));
        exit;
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <p style="color: var(--text-dim); font-size: 0.9rem;">
        Active customer discount coupons and cart savings codes.
    </p>
    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('couponFormCard').style.display='block'">
        <i class="fa-solid fa-plus"></i> Create New Coupon
    </button>
</div>

<!-- Add Coupon Form Card (Hidden by default) -->
<div id="couponFormCard" style="display: none; background: var(--admin-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 1.75rem; margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h4 style="font-size: 1.15rem; font-weight: 800;">Create Discount Coupon</h4>
        <button type="button" class="close-drawer-btn" onclick="document.getElementById('couponFormCard').style.display='none'">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <form method="POST" action="">
        <input type="hidden" name="action" value="add_coupon">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Coupon Code (Uppercase)</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. MONSOON30" required style="text-transform: uppercase;">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Discount Type</label>
                <select name="type" class="form-control">
                    <option value="percentage">Percentage (%)</option>
                    <option value="flat">Flat Amount (₹)</option>
                    <option value="shipping">Free Delivery (Fee Waiver)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Discount Value</label>
                <input type="number" name="value" class="form-control" placeholder="e.g. 50" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Max Discount Cap (₹)</label>
                <input type="number" name="max_discount" class="form-control" placeholder="e.g. 150">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Min Order Value (₹)</label>
                <input type="number" name="min_order" class="form-control" placeholder="e.g. 199">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Offer Description</label>
            <input type="text" name="desc" class="form-control" placeholder="e.g. 50% OFF up to ₹150 on orders above ₹199">
        </div>

        <button type="submit" class="btn btn-primary">
            Save Coupon
        </button>
    </form>
</div>

<!-- Coupons Table -->
<div class="admin-card-table">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount Structure</th>
                    <th>Min. Order</th>
                    <th>Max. Discount</th>
                    <th>Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($coupons)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-dim);">No coupons defined.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($coupons as $coup): ?>
                        <tr>
                            <td>
                                <strong style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--primary); letter-spacing: 0.05em;">
                                    <?php echo htmlspecialchars($coup['code']); ?>
                                </strong>
                            </td>
                            <td>
                                <span style="font-weight: 700;">
                                    <?php echo $coup['type'] === 'percentage' ? $coup['value'] . '% OFF' : 'Flat ₹' . $coup['value'] . ' OFF'; ?>
                                </span>
                            </td>
                            <td>₹<?php echo number_format($coup['min_order'] ?? 0, 0); ?></td>
                            <td><?php echo !empty($coup['max_discount']) ? '₹' . number_format($coup['max_discount'], 0) : 'No limit'; ?></td>
                            <td>
                                <span style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($coup['desc'] ?? ''); ?></span>
                            </td>
                            <td>
                                <span class="badge-status badge-delivered" style="font-size: 0.75rem;">Active</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
