<?php
/**
 * CraveHub - Food Item Details Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$db = getDB();
$foodId = $_GET['id'] ?? '';

if (empty($foodId)) {
    header('Location: ' . baseUrl('menu.php'));
    exit;
}

$food = $db->findById('foods', $foodId);
if (!$food) {
    header('Location: ' . baseUrl('menu.php'));
    exit;
}

$pageTitle = $food['title'];
$related = $db->find('foods', [
    'category' => $food['category'],
    '_id' => ['$ne' => $food['_id']]
], ['limit' => 4]);

$inCartQty = $_SESSION['cart'][(string)$food['_id']]['qty'] ?? 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <a href="<?php echo baseUrl('index.php'); ?>">Home</a>
        <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
        <a href="<?php echo baseUrl('menu.php'); ?>">Menu</a>
        <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
        <a href="<?php echo baseUrl('menu.php?cat=' . urlencode($food['category_slug'] ?? '')); ?>"><?php echo htmlspecialchars($food['category'] ?? ''); ?></a>
        <i class="fa-solid fa-angle-right" style="font-size: 0.7rem;"></i>
        <span style="color: var(--text-main); font-weight: 600;"><?php echo htmlspecialchars($food['title']); ?></span>
    </div>

    <div class="food-details-grid" style="display: grid; grid-template-columns: 1fr 1.15fr; gap: 3rem; margin-bottom: 3.5rem;">
        <!-- Left: Image Box -->
        <div style="position: relative;">
            <img src="<?php echo htmlspecialchars($food['image']); ?>" 
                 alt="<?php echo htmlspecialchars($food['title']); ?>" 
                 style="width: 100%; height: 420px; object-fit: cover; border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-md);">
            
            <div class="veg-indicator <?php echo ($food['is_veg'] ?? true) ? 'veg' : 'nonveg'; ?>" 
                 style="position: absolute; top: 16px; right: 16px; width: 28px; height: 28px; border-radius: 6px;">
            </div>

            <?php if (!empty($food['is_bestseller'])): ?>
                <span class="badge-tag badge-bestseller" style="position: absolute; top: 16px; left: 16px; font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                    <i class="fa-solid fa-star"></i> Bestseller
                </span>
            <?php endif; ?>
        </div>

        <!-- Right: Dish Info & Add to Cart -->
        <div>
            <div style="color: var(--primary); font-weight: 800; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                <?php echo htmlspecialchars($food['category'] ?? ''); ?>
            </div>
            <h1 style="font-size: 2.3rem; font-weight: 900; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($food['title']); ?></h1>

            <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                <span class="rating-pill" style="font-size: 0.95rem; padding: 0.25rem 0.75rem;">
                    <i class="fa-solid fa-star"></i> <?php echo number_format($food['rating'] ?? 4.8, 1); ?>
                </span>
                <span style="font-size: 0.85rem; color: var(--text-dim);">(<?php echo number_format($food['rating_count'] ?? 150); ?> verified foodie ratings)</span>
                <span style="color: var(--text-dim);">•</span>
                <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($food['prep_time'] ?? '20m'); ?> prep</span>
                <span style="color: var(--text-dim);">•</span>
                <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fa-solid fa-pepper-hot"></i> <?php echo htmlspecialchars($food['spicy_level'] ?? 'Mild'); ?> spice</span>
            </div>

            <div style="display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.5rem;">
                <span style="font-size: 2.2rem; font-weight: 900; font-family: var(--font-heading); color: var(--text-main);">
                    ₹<?php echo number_format($food['price'], 0); ?>
                </span>
                <?php if (!empty($food['original_price']) && $food['original_price'] > $food['price']): ?>
                    <span style="font-size: 1.2rem; color: var(--text-dim); text-decoration: line-through;">
                        ₹<?php echo number_format($food['original_price'], 0); ?>
                    </span>
                    <span class="badge-tag badge-discount">
                        Save ₹<?php echo number_format($food['original_price'] - $food['price'], 0); ?>
                    </span>
                <?php endif; ?>
            </div>

            <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; margin-bottom: 2rem;">
                <?php echo nl2br(htmlspecialchars($food['description'] ?? '')); ?>
            </p>

            <!-- Special Chef Instructions / Customization Notes -->
            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label" for="chefNotes"><i class="fa-solid fa-pen-to-square"></i> Special Cooking Instructions (Optional):</label>
                <input type="text" id="chefNotes" class="form-control" placeholder="e.g. Less spicy, extra sauce, no onions">
            </div>

            <!-- Action Controls -->
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary btn-lg" onclick="addWithNotes('<?php echo (string)$food['_id']; ?>')">
                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart • ₹<?php echo number_format($food['price'], 0); ?>
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="toggleCartDrawer(true)">
                    View Cart (<?php echo getCartCount(); ?>)
                </button>
            </div>

            <!-- Guarantee Badges -->
            <div style="display: flex; gap: 2rem; margin-top: 2.5rem; border-top: 1px solid var(--border-subtle); padding-top: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-solid fa-shield-halved" style="color: var(--accent); font-size: 1.1rem;"></i>
                    <span>100% Fresh & Hygienic</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-solid fa-box-check" style="color: var(--primary); font-size: 1.1rem;"></i>
                    <span>Tamper-proof Packaging</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Dishes -->
    <?php if (!empty($related)): ?>
        <div style="border-top: 1px solid var(--border-subtle); padding-top: 3rem;">
            <h3 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 1.5rem;">More from <?php echo htmlspecialchars($food['category'] ?? ''); ?></h3>
            <div class="food-grid">
                <?php foreach ($related as $rel): ?>
                    <?php $relId = (string)$rel['_id']; ?>
                    <div class="food-card">
                        <div class="food-card-img-wrapper" style="height: 170px;">
                            <img src="<?php echo htmlspecialchars($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" class="food-card-img">
                        </div>
                        <div class="food-card-body">
                            <h4 class="food-card-title">
                                <a href="<?php echo baseUrl('food-details.php?id=' . $relId); ?>">
                                    <?php echo htmlspecialchars($rel['title']); ?>
                                </a>
                            </h4>
                            <div class="food-card-footer">
                                <span class="price-current">₹<?php echo number_format($rel['price'], 0); ?></span>
                                <button type="button" class="btn-add-cart" onclick="addToCart('<?php echo $relId; ?>', 1)">
                                    <i class="fa-solid fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function addWithNotes(foodId) {
    const notes = document.getElementById('chefNotes')?.value.trim() || '';
    addToCart(foodId, 1, { notes: notes });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
