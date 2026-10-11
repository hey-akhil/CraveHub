<?php
/**
 * CraveHub - Admin Food Menu Management (CRUD)
 */
$pageTitle = 'Food Menu Catalog';
$pageHeading = 'Manage Food Menus';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';

$categoryFilter = $_GET['cat'] ?? '';
$searchFilter = trim($_GET['q'] ?? '');

$filter = [];
if (!empty($categoryFilter)) {
    $filter['category_slug'] = $categoryFilter;
}
if (!empty($searchFilter)) {
    $filter['title'] = ['$regex' => $searchFilter, '$options' => 'i'];
}

$foods = $db->find('foods', $filter, ['sort' => ['title' => 1]]);
$categories = $db->find('categories');
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <p style="color: var(--text-dim); font-size: 0.9rem;">
            Add, update pricing, customize images, or toggle stock availability for your dishes.
        </p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openFoodModal()">
        <i class="fa-solid fa-plus"></i> Add New Dish
    </button>
</div>

<!-- Filters Bar -->
<div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
    <form action="" method="GET" style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap;">
        <input type="text" name="q" class="form-control" placeholder="Search dish by name..." style="max-width: 320px;" value="<?php echo htmlspecialchars($searchFilter); ?>">
        
        <select name="cat" class="form-control" style="max-width: 220px;" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?php echo htmlspecialchars($c['slug']); ?>" <?php echo $categoryFilter === $c['slug'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <?php if (!empty($searchFilter) || !empty($categoryFilter)): ?>
            <a href="<?php echo baseUrl('admin/foods.php'); ?>" class="btn btn-outline btn-sm">Clear</a>
        <?php endif; ?>
    </form>

    <div style="font-size: 0.85rem; color: var(--text-dim);">
        Total: <strong><?php echo count($foods); ?></strong> dishes
    </div>
</div>

<!-- Foods Table -->
<div class="admin-card-table">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Dish</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Diet</th>
                    <th>Spicy / Prep</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($foods)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-dim);">
                            No dishes found matching criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($foods as $food): ?>
                        <?php
                            $foodId = (string)$food['_id'];
                            $isAvail = $food['is_available'] !== false;
                            $foodJson = htmlspecialchars(json_encode($food), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr id="food-row-<?php echo $foodId; ?>">
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <img src="<?php echo htmlspecialchars($food['image']); ?>" alt="" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;">
                                    <div>
                                        <div style="font-weight: 800; font-size: 0.95rem;"><?php echo htmlspecialchars($food['title']); ?></div>
                                        <?php if (!empty($food['is_bestseller'])): ?>
                                            <span class="badge-tag badge-bestseller" style="font-size: 0.68rem; padding: 0.15rem 0.4rem;">Bestseller</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);"><?php echo htmlspecialchars($food['category'] ?? ''); ?></span>
                            </td>
                            <td>
                                <strong style="font-family: var(--font-heading); font-size: 1.05rem;">₹<?php echo number_format($food['price'], 0); ?></strong>
                                <?php if (!empty($food['original_price']) && $food['original_price'] > $food['price']): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-dim); text-decoration: line-through;">₹<?php echo number_format($food['original_price'], 0); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="veg-indicator <?php echo ($food['is_veg'] ?? true) ? 'veg' : 'nonveg'; ?>" 
                                     style="position: static; display: inline-flex;"
                                     title="<?php echo ($food['is_veg'] ?? true) ? 'Veg' : 'Non-Veg'; ?>">
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.82rem; color: var(--text-main);"><?php echo htmlspecialchars($food['spicy_level'] ?? 'Mild'); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-dim);"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($food['prep_time'] ?? '20m'); ?></div>
                            </td>
                            <td>
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="checkbox" onchange="toggleFoodAvailability('<?php echo $foodId; ?>', this.checked)" <?php echo $isAvail ? 'checked' : ''; ?>>
                                    <span style="font-size: 0.82rem; font-weight: 700; color: <?php echo $isAvail ? '#10b981' : 'var(--secondary)'; ?>;">
                                        <?php echo $isAvail ? 'In Stock' : 'Sold Out'; ?>
                                    </span>
                                </label>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick='openFoodModal(<?php echo $foodJson; ?>)' title="Edit Dish">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="deleteFoodItem('<?php echo $foodId; ?>', '<?php echo addslashes($food['title']); ?>')" style="color: var(--secondary);" title="Delete Dish">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Add / Edit Dish -->
<div class="modal-overlay" id="foodModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="foodModalTitle" style="font-size: 1.25rem; font-weight: 800;">Add New Dish</h3>
            <button type="button" class="close-drawer-btn" onclick="closeFoodModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="foodForm" onsubmit="submitFoodForm(event)">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="food_id" id="foodIdInput" value="">

            <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Dish Title *</label>
                    <input type="text" id="foodTitleInput" name="title" class="form-control" placeholder="e.g. Sizzling Paneer Tikka Pizza" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Category *</label>
                        <select id="foodCategoryInput" name="category" class="form-control" required>
                            <option value="Pizzas">Pizzas</option>
                            <option value="Burgers & Wraps">Burgers & Wraps</option>
                            <option value="Biryani & Rice">Biryani & Rice</option>
                            <option value="North Indian">North Indian</option>
                            <option value="Asian & Momos">Asian & Momos</option>
                            <option value="Desserts & Shakes">Desserts & Shakes</option>
                            <option value="Coolers & Drinks">Coolers & Drinks</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Selling Price (₹) *</label>
                        <input type="number" id="foodPriceInput" name="price" class="form-control" placeholder="299" step="1" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Original Price (₹) (Optional)</label>
                        <input type="number" id="foodOriginalPriceInput" name="original_price" class="form-control" placeholder="399" step="1">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Preparation Time</label>
                        <input type="text" id="foodPrepTimeInput" name="prep_time" class="form-control" placeholder="20-25 mins" value="20 mins">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Spicy Level</label>
                        <select id="foodSpicyInput" name="spicy_level" class="form-control">
                            <option value="Mild">Mild</option>
                            <option value="Medium">Medium</option>
                            <option value="Hot">Hot</option>
                            <option value="Extra Hot">Extra Hot</option>
                            <option value="Sweet">Sweet</option>
                            <option value="Refreshing">Refreshing</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Image URL</label>
                        <input type="url" id="foodImageInput" name="image" class="form-control" placeholder="https://images.unsplash.com/...">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Description & Ingredients</label>
                    <textarea id="foodDescInput" name="description" class="form-control" rows="3" placeholder="Describe the ingredients, flavor profile, and toppings..."></textarea>
                </div>

                <div style="display: flex; gap: 1.5rem; border-top: 1px solid var(--admin-border); padding-top: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" id="foodIsVegInput" name="is_veg" value="1">
                        <span>Pure Vegetarian</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" id="foodBestsellerInput" name="is_bestseller" value="1">
                        <span>Mark as Bestseller</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" id="foodAvailableInput" name="is_available" value="1" checked>
                        <span>Available in Stock</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFoodModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Dish to Menu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function submitFoodForm(e) {
    e.preventDefault();
    const form = document.getElementById('foodForm');
    const formData = new FormData(form);

    try {
        const res = await fetch('../api/food_actions.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            closeFoodModal();
            setTimeout(() => window.location.reload(), 600);
        } else {
            showToast(data.error || 'Failed to save food', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Server error while saving dish', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
