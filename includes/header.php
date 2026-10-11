<?php
/**
 * CraveHub - Main Header Template
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$cartCount = getCartCount();
$user = getLoggedInUser();
$flashSuccess = getFlash('success');
$flashError = getFlash('error');
$flashInfo = getFlash('info');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' . APP_NAME : APP_NAME . ' - ' . APP_TAGLINE; ?></title>
    <meta name="description" content="Order food online from CraveHub. Superfast delivery, hot delicious meals, live order tracking, and irresistible offers.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍔</text></svg>">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Theme Stylesheet -->
    <link rel="stylesheet" href="<?php echo baseUrl('assets/css/style.css'); ?>">

    <script>
        window.CRAVEHUB_BASE_URL = '<?php echo baseUrl(); ?>';
    </script>
</head>
<body>

    <!-- Top Sticky Navbar -->
    <header class="navbar">
        <div class="container nav-container">
            <!-- Brand Logo -->
            <a href="<?php echo baseUrl('index.php'); ?>" class="brand-logo" id="brand-logo-link">
                <div class="logo-icon"><i class="fa-solid fa-fire"></i></div>
                <span>Crave<span class="accent">Hub</span></span>
            </a>

            <!-- Search Bar -->
            <div class="nav-search">
                <form action="<?php echo baseUrl('menu.php'); ?>" method="GET" class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="q" id="navSearchInput" class="search-input" 
                           placeholder="Search delicious pizzas, burgers, biryanis..." 
                           value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </form>
            </div>

            <!-- Navigation Links & Actions -->
            <nav class="nav-links">
                <a href="<?php echo baseUrl('menu.php'); ?>" class="nav-link" id="nav-menu-link">
                    <i class="fa-solid fa-utensils"></i> <span>Explore Menu</span>
                </a>

                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo baseUrl('my-orders.php'); ?>" class="nav-link" id="nav-orders-link">
                        <i class="fa-solid fa-clock-rotate-left"></i> <span>My Orders</span>
                    </a>
                <?php endif; ?>

                <!-- Cart Button with Slide Drawer Toggle -->
                <button type="button" class="cart-btn-nav" id="cartToggleBtn" onclick="toggleCartDrawer()">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Cart</span>
                    <span class="cart-badge" style="<?php echo $cartCount > 0 ? '' : 'display: none;'; ?>"><?php echo $cartCount; ?></span>
                </button>

                <!-- User Authentication Menu -->
                <?php if (isLoggedIn()): ?>
                    <div class="user-profile-menu" id="userProfileMenu">
                        <div class="user-avatar-btn" id="userAvatarBtn" onclick="toggleUserDropdown(event)">
                            <i class="fa-solid fa-user-circle" style="font-size: 1.25rem; color: var(--primary);"></i>
                            <span><?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?></span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem; color: var(--text-dim);"></i>
                        </div>
                        <div class="user-dropdown">
                            <a href="<?php echo baseUrl('profile.php'); ?>">
                                <i class="fa-solid fa-user-gear"></i> My Profile
                            </a>
                            <a href="<?php echo baseUrl('my-orders.php'); ?>">
                                <i class="fa-solid fa-receipt"></i> Order History
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo baseUrl('admin/index.php'); ?>" style="color: var(--primary);">
                                    <i class="fa-solid fa-shield-halved"></i> Admin Panel
                                </a>
                            <?php endif; ?>
                            <hr style="border: 0; border-top: 1px solid var(--border-subtle); margin: 0.4rem 0;">
                            <a href="<?php echo baseUrl('logout.php'); ?>" style="color: var(--secondary);">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="display: flex; gap: 0.6rem; align-items: center;">
                        <a href="<?php echo baseUrl('login.php'); ?>" class="btn btn-secondary btn-sm" id="nav-login-btn">
                            Login
                        </a>
                        <a href="<?php echo baseUrl('signup.php'); ?>" class="btn btn-primary btn-sm" id="nav-signup-btn">
                            Sign Up
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Slide-out Cart Drawer -->
    <div class="cart-drawer-overlay" id="cartOverlay" onclick="toggleCartDrawer(false)"></div>
    <div class="cart-drawer" id="cartDrawer">
        <div class="cart-drawer-header">
            <h3><i class="fa-solid fa-bag-shopping" style="color: var(--primary);"></i> Your Cart</h3>
            <button type="button" class="close-drawer-btn" onclick="toggleCartDrawer(false)">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="cart-drawer-body" id="cartDrawerItems">
            <!-- Dynamic Cart Items Loaded via JS -->
            <div class="empty-cart-state">
                <div class="empty-cart-icon"><i class="fa-solid fa-spinner fa-spin"></i></div>
                <p>Loading your cart...</p>
            </div>
        </div>

        <div class="cart-drawer-footer" id="cartDrawerFooter" style="display: none;">
            <div class="cart-summary-line">
                <span>Subtotal</span>
                <span id="drawerSubtotal">₹0.00</span>
            </div>
            <div class="cart-summary-line">
                <span>Delivery Fee</span>
                <span id="drawerDelivery" style="color: var(--accent);">FREE</span>
            </div>
            <div class="cart-summary-line total">
                <span>Grand Total</span>
                <span id="drawerTotal" style="color: var(--primary);">₹0.00</span>
            </div>
            <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem;">
                <a href="<?php echo baseUrl('cart.php'); ?>" class="btn btn-secondary btn-block">View Full Cart</a>
                <a href="<?php echo baseUrl('checkout.php'); ?>" class="btn btn-primary btn-block">Checkout</a>
            </div>
        </div>
    </div>

    <!-- Flash Message Banner -->
    <?php if ($flashSuccess || $flashError || $flashInfo): ?>
        <div class="container" style="margin-top: 1.25rem;">
            <?php if ($flashSuccess): ?>
                <div class="toast toast-success" style="position: static; margin-bottom: 0.75rem; pointer-events: auto;">
                    <i class="fa-solid fa-circle-check"></i> <span><?php echo htmlspecialchars($flashSuccess); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="toast toast-error" style="position: static; margin-bottom: 0.75rem; pointer-events: auto;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <span><?php echo htmlspecialchars($flashError); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($flashInfo): ?>
                <div class="toast toast-info" style="position: static; margin-bottom: 0.75rem; pointer-events: auto;">
                    <i class="fa-solid fa-info-circle"></i> <span><?php echo htmlspecialchars($flashInfo); ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content Container Wrapper -->
    <main style="flex: 1;">
