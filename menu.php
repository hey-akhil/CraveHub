<?php
/**
 * CraveHub - Full Food Menu Catalog
 */
$pageTitle = 'Explore Our Delicious Menu';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();
$categories = $db->find('categories');

// Read filters from query params
$selectedCategory = $_GET['cat'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$filterType = $_GET['filter'] ?? ''; // 'veg', 'nonveg', 'bestseller'
$sortBy = $_GET['sort'] ?? 'popular';

$filter = [];
if (!empty($selectedCategory)) {
    $filter['category_slug'] = $selectedCategory;
}
if (!empty($searchQuery)) {
    $filter['$or'] = [
        ['title' => ['$regex' => $searchQuery, '$options' => 'i']],
        ['category' => ['$regex' => $searchQuery, '$options' => 'i']],
        ['description' => ['$regex' => $searchQuery, '$options' => 'i']]
    ];
}
if ($filterType === 'veg') {
    $filter['is_veg'] = true;
} elseif ($filterType === 'nonveg') {
    $filter['is_veg'] = false;
} elseif ($filterType === 'bestseller') {
    $filter['is_bestseller'] = true;
}

$options = [];
if ($sortBy === 'price_asc') {
    $options['sort'] = ['price' => 1];
} elseif ($sortBy === 'price_desc') {
    $options['sort'] = ['price' => -1];
} elseif ($sortBy === 'rating') {
    $options['sort'] = ['rating' => -1];
}

$foods = $db->find('foods', $filter, $options);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">
    <!-- Page Header & Filters -->
    <div style="margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 2.4rem; font-weight: 900;">Explore Menu</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">
                    Showing <?php echo count($foods); ?> mouthwatering dishes available for immediate delivery
                </p>
            </div>

            <!-- Sort By Selector -->
            <form action="" method="GET" style="display: flex; align-items: center; gap: 0.6rem;">
                <?php if ($selectedCategory): ?><input type="hidden" name="cat" value="<?php echo htmlspecialchars($selectedCategory); ?>"><?php endif; ?>
                <?php if ($filterType): ?><input type="hidden" name="filter" value="<?php echo htmlspecialchars($filterType); ?>"><?php endif; ?>
                <?php if ($searchQuery): ?><input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>"><?php endif; ?>

                <label for="sortSelector" style="font-size: 0.85rem; color: var(--text-dim); font-weight: 700;">Sort By:</label>
                <select name="sort" id="sortSelector" class="sort-select-box" onchange="this.form.submit()">
                    <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                    <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                    <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </form>
        </div>

        <!-- Category Pills -->
        <div style="display: flex; gap: 0.6rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
            <a href="<?php echo baseUrl('menu.php'); ?>" 
               class="btn <?php echo empty($selectedCategory) && empty($filterType) ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                All Items
            </a>
            <a href="<?php echo baseUrl('menu.php?filter=veg'); ?>" 
               class="btn <?php echo $filterType === 'veg' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                <span style="color: var(--veg-color);">●</span> Pure Veg
            </a>
            <a href="<?php echo baseUrl('menu.php?filter=nonveg'); ?>" 
               class="btn <?php echo $filterType === 'nonveg' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                <span style="color: var(--nonveg-color);">▲</span> Non-Veg
            </a>
            <a href="<?php echo baseUrl('menu.php?filter=bestseller'); ?>" 
               class="btn <?php echo $filterType === 'bestseller' ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                <i class="fa-solid fa-fire"></i> Bestsellers
            </a>

            <?php foreach ($categories as $c): ?>
                <a href="<?php echo baseUrl('menu.php?cat=' . urlencode($c['slug'])); ?>" 
                   class="btn <?php echo $selectedCategory === $c['slug'] ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                    <i class="fa-solid <?php echo htmlspecialchars($c['icon'] ?? 'fa-bowl-food'); ?>"></i>
                    <?php echo htmlspecialchars($c['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Empty State if no food items match -->
    <?php if (empty($foods)): ?>
        <div style="text-align: center; padding: 4rem 1rem; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle);">
            <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"><i class="fa-solid fa-utensils"></i></div>
            <h3>No Dishes Found</h3>
            <p style="color: var(--text-dim); margin: 0.5rem auto 1.5rem; max-width: 400px;">
                We couldn't find any dishes matching your selected filters. Try searching with different keywords or clear your filters.
            </p>
            <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-primary btn-sm">
                Clear Filters & Show All
            </a>
        </div>
    <?php else: ?>
        <!-- Food Items Grid -->
        <div class="food-grid">
            <?php foreach ($foods as $food): ?>
                <?php
                    $foodId = (string)$food['_id'];
                    $inCartQty = $_SESSION['cart'][$foodId]['qty'] ?? 0;
                    $isAvailable = $food['is_available'] ?? true;
                ?>
                <div class="food-card" data-title="<?php echo htmlspecialchars($food['title']); ?>" data-category="<?php echo htmlspecialchars($food['category'] ?? ''); ?>" style="<?php echo !$isAvailable ? 'opacity: 0.6;' : ''; ?>">
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
                                <?php echo htmlspecialchars($food['spicy_level'] ?? 'Mild'); ?>
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
                                <?php if (!$isAvailable): ?>
                                    <span style="font-size: 0.8rem; color: var(--secondary); font-weight: 700;">Sold Out</span>
                                <?php elseif ($inCartQty > 0): ?>
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
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
