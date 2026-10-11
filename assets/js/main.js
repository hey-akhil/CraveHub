/**
 * CraveHub - Main Frontend Interactions & AJAX Cart
 */

// Toast Notification Manager
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let iconClass = 'fa-info-circle';
    if (type === 'success') iconClass = 'fa-circle-check';
    if (type === 'error') iconClass = 'fa-circle-exclamation';

    toast.innerHTML = `
        <i class="fa-solid ${iconClass}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Open/Close Cart Drawer
function toggleCartDrawer(open = null) {
    const drawer = document.getElementById('cartDrawer');
    const overlay = document.getElementById('cartOverlay');
    if (!drawer || !overlay) return;

    if (open === true || (open === null && !drawer.classList.contains('active'))) {
        drawer.classList.add('active');
        overlay.classList.add('active');
        loadCartDrawerData();
    } else {
        drawer.classList.remove('active');
        overlay.classList.remove('active');
    }
}

// Add Item to Cart (AJAX)
async function addToCart(foodId, qty = 1, options = {}) {
    try {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('food_id', foodId);
        formData.append('qty', qty);
        if (options.notes) formData.append('notes', options.notes);

        const res = await fetch(window.CRAVEHUB_BASE_URL + 'api/cart.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart_count);
            showToast(data.message || 'Item added to cart!', 'success');
            
            // If on cart page, reload or refresh table
            if (window.location.pathname.includes('cart.php')) {
                window.location.reload();
            } else {
                // Update button state on food card if rendered
                updateCardQtyControl(foodId, data.item_qty);
                // Also refresh drawer if open
                if (document.getElementById('cartDrawer')?.classList.contains('active')) {
                    loadCartDrawerData();
                }
            }
        } else {
            showToast(data.error || 'Could not add item to cart', 'error');
        }
    } catch (err) {
        console.error('Add to cart error:', err);
        showToast('Network error, please try again.', 'error');
    }
}

// Update Cart Quantity (AJAX)
async function updateCartQty(foodId, newQty) {
    try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('food_id', foodId);
        formData.append('qty', newQty);

        const res = await fetch(window.CRAVEHUB_BASE_URL + 'api/cart.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            updateCartBadge(data.cart_count);
            updateCardQtyControl(foodId, data.item_qty);

            if (window.location.pathname.includes('cart.php')) {
                window.location.reload();
            } else {
                loadCartDrawerData();
            }
        }
    } catch (err) {
        console.error(err);
    }
}

// Remove Item from Cart
async function removeFromCart(foodId) {
    await updateCartQty(foodId, 0);
    showToast('Item removed from cart', 'info');
}

// Update badge count
function updateCartBadge(count) {
    const badges = document.querySelectorAll('.cart-badge');
    badges.forEach(b => {
        b.textContent = count;
        b.style.display = count > 0 ? 'inline-block' : 'none';
    });
}

// Update Card Qty Controls if present
function updateCardQtyControl(foodId, qty) {
    const wrapper = document.getElementById(`cart-action-${foodId}`);
    if (!wrapper) return;

    if (qty > 0) {
        wrapper.innerHTML = `
            <div class="qty-control">
                <button type="button" class="qty-btn" onclick="updateCartQty('${foodId}', ${qty - 1})">-</button>
                <span class="qty-number">${qty}</span>
                <button type="button" class="qty-btn" onclick="updateCartQty('${foodId}', ${qty + 1})">+</button>
            </div>
        `;
    } else {
        wrapper.innerHTML = `
            <button type="button" class="btn-add-cart" onclick="addToCart('${foodId}', 1)">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        `;
    }
}

// Fetch & Render Drawer Content
async function loadCartDrawerData() {
    const container = document.getElementById('cartDrawerItems');
    const footer = document.getElementById('cartDrawerFooter');
    if (!container) return;

    try {
        const res = await fetch(window.CRAVEHUB_BASE_URL + 'api/cart.php?action=get');
        const data = await res.json();

        if (!data.items || data.items.length === 0) {
            container.innerHTML = `
                <div class="empty-cart-state">
                    <div class="empty-cart-icon"><i class="fa-solid fa-utensils"></i></div>
                    <h4>Your Cart is Empty</h4>
                    <p style="color: var(--text-dim); margin-top: 0.5rem; font-size: 0.9rem;">
                        Looks like you haven't added anything yet. Explore delicious items from our menu!
                    </p>
                    <a href="${window.CRAVEHUB_BASE_URL}menu.php" class="btn btn-primary btn-sm" style="margin-top: 1.25rem;">
                        Browse Menu
                    </a>
                </div>
            `;
            if (footer) footer.style.display = 'none';
            return;
        }

        if (footer) footer.style.display = 'block';

        let html = '';
        data.items.forEach(item => {
            html += `
                <div class="cart-item-row">
                    <img src="${item.image}" alt="${item.title}" class="cart-item-thumb">
                    <div class="cart-item-info">
                        <div class="cart-item-title">${item.title}</div>
                        <div class="cart-item-price">₹${item.price} × ${item.qty} = ₹${item.price * item.qty}</div>
                    </div>
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="updateCartQty('${item.food_id}', ${item.qty - 1})">-</button>
                        <span class="qty-number">${item.qty}</span>
                        <button type="button" class="qty-btn" onclick="updateCartQty('${item.food_id}', ${item.qty + 1})">+</button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;

        // Update totals in drawer footer
        const subtotalEl = document.getElementById('drawerSubtotal');
        const deliveryEl = document.getElementById('drawerDelivery');
        const totalEl = document.getElementById('drawerTotal');

        if (subtotalEl) subtotalEl.textContent = '₹' + data.subtotal.toFixed(2);
        if (deliveryEl) deliveryEl.textContent = data.delivery_fee === 0 ? 'FREE' : '₹' + data.delivery_fee;
        if (totalEl) totalEl.textContent = '₹' + data.total.toFixed(2);

    } catch (err) {
        console.error(err);
    }
}

// Promo Code Apply Handler
async function applyPromoCode(code = null) {
    const input = document.getElementById('promoCodeInput');
    const promoCode = code || (input ? input.value.trim() : '');
    
    if (!promoCode) {
        showToast('Please enter a coupon code', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'apply_coupon');
        formData.append('code', promoCode);

        const res = await fetch(window.CRAVEHUB_BASE_URL + 'api/cart.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 600);
        } else {
            showToast(data.error || 'Invalid promo code', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Failed to apply coupon', 'error');
    }
}

// Remove Coupon
async function removePromoCode() {
    try {
        const formData = new FormData();
        formData.append('action', 'remove_coupon');
        await fetch(window.CRAVEHUB_BASE_URL + 'api/cart.php', { method: 'POST', body: formData });
        showToast('Coupon removed', 'info');
        setTimeout(() => window.location.reload(), 500);
    } catch (err) {
        console.error(err);
    }
}

// Toggle User Profile Dropdown Menu (Click-to-open & stay open)
function toggleUserDropdown(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('userProfileMenu');
    if (menu) {
        menu.classList.toggle('open');
    }
}

// Close User Profile Menu when clicking outside
document.addEventListener('click', (e) => {
    const menu = document.getElementById('userProfileMenu');
    if (menu && !menu.contains(e.target)) {
        menu.classList.remove('open');
    }
});

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    // Quick search filter if on menu page
    const searchInput = document.getElementById('menuSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.food-card');
            cards.forEach(card => {
                const title = card.getAttribute('data-title')?.toLowerCase() || '';
                const cat = card.getAttribute('data-category')?.toLowerCase() || '';
                if (title.includes(query) || cat.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
