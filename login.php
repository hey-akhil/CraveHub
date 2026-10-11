<?php
/**
 * CraveHub - User Login
 */
$pageTitle = 'Login to CraveHub';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: ' . baseUrl('index.php'));
    exit;
}

$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $user = $db->findOne('users', ['email' => $email]);
        if ($user && password_verify($password, $user['password'])) {
            // Success
            $_SESSION['user_id'] = (string)$user['_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['user_address'] = $user['address'] ?? '';
            $_SESSION['user_role'] = $user['role'] ?? 'user';

            if (($user['role'] ?? '') === 'admin') {
                $_SESSION['admin_logged_in'] = true;
            }

            setFlash('success', "Welcome back, {$user['name']}!");
            $redirect = $_SESSION['redirect_after_login'] ?? baseUrl('index.php');
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 3.5rem; padding-bottom: 5rem; max-width: 480px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 2.25rem; backdrop-filter: blur(14px); box-shadow: var(--shadow-md);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="logo-icon" style="margin: 0 auto 1rem; width: 50px; height: 50px; font-size: 1.4rem;">
                <i class="fa-solid fa-fire"></i>
            </div>
            <h2 style="font-size: 1.8rem; font-weight: 800;">Welcome Back</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.3rem;">Sign in to track orders, save addresses and get exclusive discounts.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="toast toast-error" style="position: static; margin-bottom: 1.25rem; width: 100%;">
                <i class="fa-solid fa-triangle-exclamation"></i> <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Quick Demo Credentials Auto-Fill Button -->
        <div style="background: rgba(249, 115, 22, 0.08); border: 1px dashed var(--primary); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
            <div style="font-weight: 700; color: var(--primary); margin-bottom: 0.4rem;">
                <i class="fa-solid fa-bolt"></i> 1-Click Demo Login:
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('rahul@gmail.com', 'user123')" style="font-size: 0.78rem;">
                    Customer: rahul@gmail.com
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('admin@cravehub.com', 'admin123')" style="font-size: 0.78rem;">
                    Admin: admin@cravehub.com
                </button>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="loginEmail">Email Address</label>
                <input type="email" id="loginEmail" name="email" class="form-control" placeholder="name@example.com" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label class="form-label" for="loginPassword" style="margin-bottom: 0;">Password</label>
                    <a href="#" style="font-size: 0.8rem; color: var(--primary);">Forgot Password?</a>
                </div>
                <input type="password" id="loginPassword" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; border-top: 1px solid var(--border-subtle); padding-top: 1.25rem; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account yet? 
            <a href="<?php echo baseUrl('signup.php'); ?>" style="color: var(--primary); font-weight: 700;">Sign Up here</a>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
    showToast('Demo credentials entered!', 'info');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
