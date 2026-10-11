<?php
/**
 * CraveHub - Customer Profile Management
 */
$pageTitle = 'My Profile & Saved Addresses';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$user = $db->findById('users', $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!empty($name) && !empty($phone)) {
        $db->update('users', ['_id' => $userId], [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['user_name'] = $name;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_address'] = $address;

        setFlash('success', 'Profile and default delivery address updated successfully!');
        header('Location: ' . baseUrl('profile.php'));
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 5rem; max-width: 600px;">
    <h1 style="font-size: 2.2rem; font-weight: 900; margin-bottom: 1.5rem;">My Profile</h1>

    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 2rem; backdrop-filter: blur(12px);">
        <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.5rem;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.8rem; font-weight: 800;">
                <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
                <h3 style="font-size: 1.3rem; font-weight: 800;"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h3>
                <div style="color: var(--text-dim); font-size: 0.85rem;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                <div style="margin-top: 0.35rem;">
                    <span class="badge-status badge-confirmed" style="font-size: 0.75rem;">Verified Customer</span>
                </div>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address (Read-only)</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Primary Delivery Address</label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Details
                </button>
                <a href="<?php echo baseUrl('my-orders.php'); ?>" class="btn btn-secondary">
                    View Orders
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
