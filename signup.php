<?php
/**
 * CraveHub - Customer Registration
 */
$pageTitle = 'Sign Up for CraveHub';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: ' . baseUrl('index.php'));
    exit;
}

$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'All fields marked with an asterisk are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already registered in MongoDB
        $existing = $db->findOne('users', ['email' => $email]);
        if ($existing) {
            $error = 'An account with this email already exists. Please log in.';
        } else {
            $newUser = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => 'user',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $inserted = $db->insert('users', $newUser);

            // Log user in automatically
            $_SESSION['user_id'] = (string)$inserted['_id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_address'] = $address;
            $_SESSION['user_role'] = 'user';

            setFlash('success', "Welcome to CraveHub, {$name}! Use code FIRSTBITE for 20% off.");
            header('Location: ' . baseUrl('menu.php'));
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 3rem; padding-bottom: 5rem; max-width: 540px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 2.25rem; backdrop-filter: blur(14px); box-shadow: var(--shadow-md);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="logo-icon" style="margin: 0 auto 1rem; width: 50px; height: 50px; font-size: 1.4rem;">
                <i class="fa-solid fa-fire"></i>
            </div>
            <h2 style="font-size: 1.8rem; font-weight: 800;">Create an Account</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.3rem;">Join thousands of foodies and order from the best restaurants.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="toast toast-error" style="position: static; margin-bottom: 1.25rem; width: 100%;">
                <i class="fa-solid fa-triangle-exclamation"></i> <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="signupName">Full Name *</label>
                <input type="text" id="signupName" name="name" class="form-control" placeholder="e.g. Priya Patel" required
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="signupEmail">Email Address *</label>
                    <input type="email" id="signupEmail" name="email" class="form-control" placeholder="priya@example.com" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="signupPhone">Phone Number *</label>
                    <input type="tel" id="signupPhone" name="phone" class="form-control" placeholder="9876543210" required
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="signupAddress">Default Delivery Address</label>
                <textarea id="signupAddress" name="address" class="form-control" rows="2" placeholder="House/Flat No., Street, City, Pincode"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="signupPass">Password *</label>
                    <input type="password" id="signupPass" name="password" class="form-control" placeholder="Min. 6 chars" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="signupConfirmPass">Confirm Password *</label>
                    <input type="password" id="signupConfirmPass" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                Create Account & Claim 20% Off
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; border-top: 1px solid var(--border-subtle); padding-top: 1.25rem; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? 
            <a href="<?php echo baseUrl('login.php'); ?>" style="color: var(--primary); font-weight: 700;">Sign in here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
