<?php
/**
 * CraveHub - Homepage
 */
$pageTitle = 'Order Food Online - Superfast Delivery';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();
$categories = $db->find('categories');
$bestsellers = $db->find('foods', ['is_bestseller' => true], ['limit' => 8]);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fa-solid fa-sparkles"></i> 50% OFF on Your First 3 Orders • Code: TASTY50
            </div>
            <h1 class="hero-title">
                Savor Every Bite. <br>
                Delivered <span class="gradient-text">Lightning Fast.</span>
            </h1>
            <p class="hero-desc">
                From sizzling artisanal pizzas and loaded burgers to royal dum biryanis and molten desserts — CraveHub brings the city's finest kitchens straight to your doorstep.
            </p>
            <div class="hero-actions">
                <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-primary btn-lg" id="hero-order-now-btn">
                    <i class="fa-solid fa-bag-shopping"></i> Order Now
                </a>
                <a href="<?php echo baseUrl('menu.php?filter=bestseller'); ?>" class="btn btn-secondary btn-lg">
                    <i class="fa-solid fa-fire"></i> Bestsellers
                </a>
            </div>
            <div class="hero-features">
                <div class="hero-feature-item">
                    <div class="feature-icon-box"><i class="fa-solid fa-clock"></i></div>
                    <div class="feature-text">
                        <h5>30 Mins</h5>
                        <p>Superfast Delivery</p>
                    </div>
                </div>
                <div class="hero-feature-item">
                    <div class="feature-icon-box"><i class="fa-solid fa-medal"></i></div>
                    <div class="feature-text">
                        <h5>Top Hygiene</h5>
                        <p>5-Star Kitchens</p>
                    </div>
                </div>
                <div class="hero-feature-item">
                    <div class="feature-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="feature-text">
                        <h5>Live Tracking</h5>
                        <p>GPS Partner Route</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-image-wrapper">
            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=900&auto=format&fit=crop&q=80" 
                 alt="Gourmet Food Feast" class="hero-main-img">
            
            <div class="hero-float-card top">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(16,185,129,0.2); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 1.2rem;">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div>
                    <h6 style="font-size: 0.95rem; font-weight: 800;">4.9 / 5.0 Rating</h6>
                    <p style="font-size: 0.78rem; color: var(--text-dim);">Over 25,000+ Happy Foodies</p>
                </div>
            </div>

            <div class="hero-float-card bottom">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(249,115,22,0.2); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.2rem;">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <div>
                    <h6 style="font-size: 0.95rem; font-weight: 800;">On the Way</h6>
                    <p style="font-size: 0.78rem; color: var(--text-dim);">Rajesh is 1.2 km away</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Food Categories Row -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">What are you craving today?</h2>
                <p class="section-subtitle">Explore dishes by your favorite gourmet categories</p>
            </div>
            <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-outline btn-sm">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="categories-scroll">
            <?php foreach ($categories as $cat): ?>
                <a href="<?php echo baseUrl('menu.php?cat=' . urlencode($cat['slug'])); ?>" class="category-card">
                    <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" class="category-img">
                    <span class="category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Chef's Bestsellers Grid -->
<section class="section" style="background: rgba(17, 24, 39, 0.4); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container">
        <div class="section-header">
            <div>
                <div style="color: var(--primary); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.3rem;">
                    <i class="fa-solid fa-fire"></i> Most Popular Right Now
                </div>
                <h2 class="section-title">Chef's Signature Dishes</h2>
            </div>
            <div style="display: flex; gap: 0.6rem;">
                <a href="<?php echo baseUrl('menu.php?filter=veg'); ?>" class="btn btn-secondary btn-sm">
                    <span style="color: var(--veg-color); font-weight: 900;">●</span> Pure Veg
                </a>
                <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-primary btn-sm">
                    Full Menu
                </a>
            </div>
        </div>

        <div class="food-grid">
            <?php foreach ($bestsellers as $food): ?>
                <?php
                    $foodId = (string)$food['_id'];
                    $inCartQty = $_SESSION['cart'][$foodId]['qty'] ?? 0;
                ?>
                <div class="food-card" data-id="<?php echo $foodId; ?>">
                    <div class="food-card-img-wrapper">
                        <img src="<?php echo htmlspecialchars($food['image']); ?>" alt="<?php echo htmlspecialchars($food['title']); ?>" class="food-card-img" loading="lazy">
                        
                        <div class="food-badge-overlay">
                            <?php if (!empty($food['is_bestseller'])): ?>
                                <span class="badge-tag badge-bestseller"><i class="fa-solid fa-star"></i> Bestseller</span>
                            <?php endif; ?>
                            <?php if (!empty($food['original_price']) && $food['original_price'] > $food['price']): ?>
                                <?php $pct = round((($food['original_price'] - $food['price']) / $food['original_price']) * 100); ?>
                                <span class="badge-tag badge-discount"><?php echo $pct; ?>% OFF</span>
                            <?php endif; ?>
                        </div>

                        <div class="veg-indicator <?php echo ($food['is_veg'] ?? true) ? 'veg' : 'nonveg'; ?>" 
                             title="<?php echo ($food['is_veg'] ?? true) ? 'Pure Vegetarian' : 'Non-Vegetarian'; ?>">
                        </div>

                        <div class="prep-time-badge">
                            <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($food['prep_time'] ?? '20m'); ?>
                        </div>
                    </div>

                    <div class="food-card-body">
                        <div class="food-card-category"><?php echo htmlspecialchars($food['category'] ?? ''); ?></div>
                        <h3 class="food-card-title">
                            <a href="<?php echo baseUrl('food-details.php?id=' . $foodId); ?>">
                                <?php echo htmlspecialchars($food['title']); ?>
                            </a>
                        </h3>
                        <p class="food-card-desc"><?php echo htmlspecialchars($food['description'] ?? ''); ?></p>
                        
                        <div class="food-card-meta">
                            <span class="rating-pill">
                                <i class="fa-solid fa-star"></i> <?php echo number_format($food['rating'] ?? 4.8, 1); ?>
                            </span>
                            <span style="font-size: 0.8rem; color: var(--text-dim);">
                                <?php echo htmlspecialchars($food['spicy_level'] ?? 'Mild'); ?> spice
                            </span>
                        </div>

                        <div class="food-card-footer">
                            <div class="price-box">
                                <span class="price-current">₹<?php echo number_format($food['price'], 0); ?></span>
                                <?php if (!empty($food['original_price']) && $food['original_price'] > $food['price']): ?>
                                    <span class="price-original">₹<?php echo number_format($food['original_price'], 0); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="cart-action-wrapper" id="cart-action-<?php echo $foodId; ?>">
                                <?php if ($inCartQty > 0): ?>
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="updateCartQty('<?php echo $foodId; ?>', <?php echo $inCartQty - 1; ?>)">-</button>
                                        <span class="qty-number"><?php echo $inCartQty; ?></span>
                                        <button type="button" class="qty-btn" onclick="updateCartQty('<?php echo $foodId; ?>', <?php echo $inCartQty + 1; ?>)">+</button>
                                    </div>
                                <?php else: ?>
                                    <button type="button" class="btn-add-cart" onclick="addToCart('<?php echo $foodId; ?>', 1)">
                                        <i class="fa-solid fa-plus"></i> Add
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Irresistible Promo Coupons Showcase -->
<section class="section">
    <div class="container">
        <div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.15) 0%, rgba(239, 68, 68, 0.08) 100%); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2.5rem; position: relative; overflow: hidden;">
            <div style="max-width: 600px; position: relative; z-index: 2;">
                <span class="badge-tag badge-bestseller" style="margin-bottom: 0.75rem; display: inline-block;">
                    EXCLUSIVE DEALS
                </span>
                <h2 style="font-size: 2.2rem; margin-bottom: 0.75rem;">Big Cravings, Bigger Discounts!</h2>
                <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 1.5rem;">
                    Unlock irresistible discount codes at checkout and save up to ₹150 instantly on your favorite dishes.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <div style="background: rgba(17, 24, 39, 0.9); border: 1.5px dashed var(--primary); border-radius: var(--radius-md); padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase;">Flat 50% Off</div>
                            <div style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 900; color: var(--primary);">TASTY50</div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="applyPromoCode('TASTY50')">
                            Apply
                        </button>
                    </div>

                    <div style="background: rgba(17, 24, 39, 0.9); border: 1.5px dashed #a855f7; border-radius: var(--radius-md); padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase;">Flat ₹100 Off</div>
                            <div style="font-family: var(--font-heading); font-size: 1.2rem; font-weight: 900; color: #c084fc;">CRAVE100</div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" style="border-color: #a855f7; color: #c084fc;" onclick="applyPromoCode('CRAVE100')">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
