<?php
/**
 * CraveHub - Admin Header & Sidebar Template
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

requireAdmin();

$db = getDB();
$dbStatus = $db->getStatusInfo();
$adminUser = getLoggedInUser();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Admin Portal' : 'Admin Portal - ' . APP_NAME; ?></title>
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Base Stylesheet & Admin Stylesheet -->
    <link rel="stylesheet" href="<?php echo baseUrl('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo baseUrl('assets/css/admin.css'); ?>">

    <script>
        window.CRAVEHUB_BASE_URL = '<?php echo baseUrl(); ?>';
    </script>
</head>
<body>

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <div class="logo-icon" style="width: 38px; height: 38px; font-size: 1.1rem;"><i class="fa-solid fa-fire"></i></div>
            <div>
                <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 900; line-height: 1;">
                    Crave<span class="accent">Hub</span>
                </div>
                <span style="font-size: 0.72rem; color: var(--primary); font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                    Admin Panel
                </span>
            </div>
        </div>

        <ul class="admin-nav-list">
            <li class="admin-nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <a href="<?php echo baseUrl('admin/index.php'); ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">
                <a href="<?php echo baseUrl('admin/orders.php'); ?>">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Orders Control</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $currentPage === 'foods.php' ? 'active' : ''; ?>">
                <a href="<?php echo baseUrl('admin/foods.php'); ?>">
                    <i class="fa-solid fa-burger"></i>
                    <span>Food Menus</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <a href="<?php echo baseUrl('admin/users.php'); ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $currentPage === 'coupons.php' ? 'active' : ''; ?>">
                <a href="<?php echo baseUrl('admin/coupons.php'); ?>">
                    <i class="fa-solid fa-tags"></i>
                    <span>Promo Codes</span>
                </a>
            </li>
            <li class="admin-nav-item <?php echo $currentPage === 'db_viewer.php' ? 'active' : ''; ?>">
                <a href="<?php echo baseUrl('admin/db_viewer.php'); ?>" style="color: #10b981;">
                    <i class="fa-solid fa-database"></i>
                    <span>Database Explorer</span>
                </a>
            </li>

            <hr style="border: 0; border-top: 1px solid var(--admin-border); margin: 0.5rem 0.5rem;">

            <li class="admin-nav-item">
                <a href="<?php echo baseUrl('index.php'); ?>" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>View Customer Site</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a href="<?php echo baseUrl('admin/logout.php'); ?>" style="color: var(--secondary);">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>

        <div class="admin-sidebar-footer">
            <div style="font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.3rem;">
                Database Engine:
            </div>
            <div style="font-size: 0.82rem; font-weight: 700; color: #10b981; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-database"></i> <?php echo htmlspecialchars($dbStatus['engine']); ?>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Top Sticky Bar -->
        <header class="admin-topbar">
            <div>
                <h2 style="font-size: 1.4rem; font-weight: 800;"><?php echo isset($pageHeading) ? htmlspecialchars($pageHeading) : 'Admin Console'; ?></h2>
            </div>

            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <!-- MongoDB Status Pill -->
                <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-full); padding: 0.35rem 0.85rem; font-size: 0.8rem; font-weight: 700; color: #10b981; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <span>MongoDB <?php echo $dbStatus['connected'] ? '127.0.0.1:27017' : 'Store Active'; ?></span>
                </div>

                <!-- Admin Profile Pill -->
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; color: #fff;">
                        A
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.88rem; line-height: 1.2;">Admin Manager</div>
                        <div style="font-size: 0.75rem; color: var(--text-dim);">Super Admin</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-body">
