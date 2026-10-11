<?php
/**
 * CraveHub - Main Footer Template
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$dbStatus = getDB()->getStatusInfo();
?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Col 1: Brand Info -->
                <div class="footer-col">
                    <div class="brand-logo" style="margin-bottom: 1rem;">
                        <div class="logo-icon"><i class="fa-solid fa-fire"></i></div>
                        <span>Crave<span class="accent">Hub</span></span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; max-width: 320px;">
                        Delivering steaming hot gourmet dishes right to your doorstep in 30 minutes or less. Made with love and the freshest ingredients.
                    </p>
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="#" class="btn btn-secondary btn-sm" style="width: 36px; height: 36px; padding: 0; border-radius: 50%;"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="btn btn-secondary btn-sm" style="width: 36px; height: 36px; padding: 0; border-radius: 50%;"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#" class="btn btn-secondary btn-sm" style="width: 36px; height: 36px; padding: 0; border-radius: 50%;"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="btn btn-secondary btn-sm" style="width: 36px; height: 36px; padding: 0; border-radius: 50%;"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="footer-col">
                    <h4>Explore</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo baseUrl('menu.php'); ?>"><i class="fa-solid fa-angle-right"></i> All Food Menus</a></li>
                        <li><a href="<?php echo baseUrl('menu.php?filter=bestseller'); ?>"><i class="fa-solid fa-angle-right"></i> Chef's Bestsellers</a></li>
                        <li><a href="<?php echo baseUrl('menu.php?filter=veg'); ?>"><i class="fa-solid fa-angle-right"></i> Pure Veg Specials</a></li>
                        <li><a href="<?php echo baseUrl('cart.php'); ?>"><i class="fa-solid fa-angle-right"></i> View Cart & Coupons</a></li>
                        <li><a href="<?php echo baseUrl('my-orders.php'); ?>"><i class="fa-solid fa-angle-right"></i> Track Orders</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care -->
                <div class="footer-col">
                    <h4>Help & Support</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fa-solid fa-phone"></i> +91 (800) 234-5678</a></li>
                        <li><a href="#"><i class="fa-solid fa-envelope"></i> support@cravehub.com</a></li>
                        <li><a href="#"><i class="fa-solid fa-shield-halved"></i> 100% Safe Payments</a></li>
                        <li><a href="#"><i class="fa-solid fa-motorcycle"></i> Express 30m Delivery</a></li>
                        <li><a href="<?php echo baseUrl('admin/login.php'); ?>"><i class="fa-solid fa-lock"></i> Restaurant Admin Portal</a></li>
                    </ul>
                </div>

                <!-- Col 4: Tech Stack & System Status -->
                <div class="footer-col">
                    <h4>Tech Architecture</h4>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.5rem;">
                            <i class="fa-brands fa-php" style="color: #8892be; font-size: 1.4rem;"></i>
                            <span style="font-weight: 700; font-size: 0.9rem;">PHP 8.2 Engine</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-database" style="color: #10b981; font-size: 1.1rem;"></i>
                            <span style="font-weight: 700; font-size: 0.9rem;">MongoDB NoSQL</span>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-dim);">
                            Status: <span style="color: var(--accent); font-weight: 700;">● Active</span> (<?php echo htmlspecialchars($dbStatus['engine']); ?>)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> Inc. All rights reserved.</p>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Main JavaScript -->
    <script src="<?php echo baseUrl('assets/js/main.js'); ?>"></script>
    
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
