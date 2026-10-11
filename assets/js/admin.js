/**
 * CraveHub - Admin Management Scripts
 */

// Instant Status Updater
async function updateAdminOrderStatus(orderId, newStatus) {
    try {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', newStatus);

        const res = await fetch('../api/update_order.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showToast(`Order #${orderId} updated to ${newStatus.toUpperCase()}`, 'success');
        } else {
            showToast(data.error || 'Failed to update order status', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Network error while updating status', 'error');
    }
}

// Open Food Modal (for Add or Edit)
function openFoodModal(foodData = null) {
    const modal = document.getElementById('foodModal');
    const form = document.getElementById('foodForm');
    const titleEl = document.getElementById('foodModalTitle');

    if (!modal || !form) return;

    form.reset();

    if (foodData) {
        titleEl.textContent = 'Edit Food Item';
        document.getElementById('foodIdInput').value = foodData._id || '';
        document.getElementById('foodTitleInput').value = foodData.title || '';
        document.getElementById('foodCategoryInput').value = foodData.category || '';
        document.getElementById('foodPriceInput').value = foodData.price || '';
        document.getElementById('foodOriginalPriceInput').value = foodData.original_price || '';
        document.getElementById('foodImageInput').value = foodData.image || '';
        document.getElementById('foodPrepTimeInput').value = foodData.prep_time || '20 mins';
        document.getElementById('foodSpicyInput').value = foodData.spicy_level || 'Mild';
        document.getElementById('foodDescInput').value = foodData.description || '';
        document.getElementById('foodIsVegInput').checked = foodData.is_veg === true || foodData.is_veg === 'true';
        document.getElementById('foodBestsellerInput').checked = foodData.is_bestseller === true || foodData.is_bestseller === 'true';
        document.getElementById('foodAvailableInput').checked = foodData.is_available !== false && foodData.is_available !== 'false';
    } else {
        titleEl.textContent = 'Add New Dish';
        document.getElementById('foodIdInput').value = '';
        document.getElementById('foodAvailableInput').checked = true;
    }

    modal.classList.add('active');
}

function closeFoodModal() {
    const modal = document.getElementById('foodModal');
    if (modal) modal.classList.remove('active');
}

// Toggle food availability (In Stock / Out of Stock)
async function toggleFoodAvailability(foodId, isAvailable) {
    try {
        const formData = new FormData();
        formData.append('action', 'toggle_stock');
        formData.append('food_id', foodId);
        formData.append('is_available', isAvailable ? '1' : '0');

        const res = await fetch('../api/food_actions.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showToast(`Item availability updated`, 'success');
        } else {
            showToast(data.error || 'Failed to toggle availability', 'error');
        }
    } catch (err) {
        console.error(err);
    }
}

// Delete food item
async function deleteFoodItem(foodId, foodTitle) {
    if (!confirm(`Are you sure you want to delete "${foodTitle}"? This cannot be undone.`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('food_id', foodId);

        const res = await fetch('../api/food_actions.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showToast('Item deleted successfully', 'success');
            const row = document.getElementById(`food-row-${foodId}`);
            if (row) row.remove();
        } else {
            showToast(data.error || 'Failed to delete item', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Server error while deleting item', 'error');
    }
}
