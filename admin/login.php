<?php
/**
 * CraveHub - Admin Login Portal
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (isAdmin()) {
    header('Location: ' . baseUrl('admin/index.php'));
    exit;
}

$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $db->findOne('users', ['email' => $email]);
    if ($user && ($user['role'] ?? '') === 'admin' && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = (string)$user['_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = 'admin';

        header('Location: ' . baseUrl('admin/index.php'));
        exit;
    } else {
        $error = 'Invalid Administrator Credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo baseUrl('assets/css/style.css'); ?>">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">

<div style="width: 100%; max-width: 440px; padding: 1.5rem;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 2.5rem; backdrop-filter: blur(14px); box-shadow: var(--shadow-md);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="logo-icon" style="margin: 0 auto 1rem; width: 52px; height: 52px; font-size: 1.4rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 style="font-size: 1.8rem; font-weight: 800;">Admin Portal</h2>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 0.3rem;">Secure management console for food menus & customer orders.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="toast toast-error" style="position: static; margin-bottom: 1.25rem; width: 100%;">
                <i class="fa-solid fa-triangle-exclamation"></i> <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- 1-Click Demo Auto-fill -->
        <div style="background: rgba(249, 115, 22, 0.08); border: 1px dashed var(--primary); border-radius: var(--radius-md); padding: 0.75rem 1rem; margin-bottom: 1.5rem; text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('adminEmail').value='admin@cravehub.com'; document.getElementById('adminPass').value='admin123';" style="font-size: 0.8rem;">
                <i class="fa-solid fa-bolt"></i> Auto-fill (admin@cravehub.com)
            </button>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="adminEmail">Admin Email</label>
                <input type="email" id="adminEmail" name="email" class="form-control" placeholder="admin@cravehub.com" required value="admin@cravehub.com">
            </div>

            <div class="form-group">
                <label class="form-label" for="adminPass">Password</label>
                <input type="password" id="adminPass" name="password" class="form-control" placeholder="••••••••" required value="admin123">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Enter Admin Console
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem;">
            <a href="<?php echo baseUrl('index.php'); ?>" style="color: var(--text-dim); font-size: 0.85rem;">
                <i class="fa-solid fa-arrow-left"></i> Return to Main Website
            </a>
        </div>
    </div>
</div>

</body>
</html>
