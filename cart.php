<?php
/**
 * CraveHub - Full Cart Page
 */
$pageTitle = 'Your Shopping Cart';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$cart = $_SESSION['cart'] ?? [];
$subtotal = getCartSubtotal();
$deliveryFee = ($subtotal >= FREE_DELIVERY_THRESHOLD || $subtotal === 0) ? 0 : DELIVERY_FEE;

$discount = 0;
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
if ($appliedCoupon) {
    if ($subtotal >= ($appliedCoupon['min_order'] ?? 0)) {
        if ($appliedCoupon['type'] === 'percentage') {
            $discount = min($appliedCoupon['max_discount'], ($subtotal * $appliedCoupon['value']) / 100);
        } elseif ($appliedCoupon['type'] === 'flat') {
            $discount = min($subtotal, $appliedCoupon['value']);
        } elseif ($appliedCoupon['type'] === 'shipping') {
            $deliveryFee = 0;
            $discount = $appliedCoupon['value'];
        }
    } else {
        unset($_SESSION['applied_coupon']);
        $appliedCoupon = null;
    }
}

$taxable = max(0, $subtotal - $discount);
$tax = round(($taxable * TAX_PERCENT) / 100, 2);
$grandTotal = max(0, $taxable + $deliveryFee + $tax);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
    <h1 style="font-size: 2.2rem; font-weight: 900; margin-bottom: 1.75rem;">Your Food Basket</h1>

    <?php if (empty($cart)): ?>
        <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 4rem 2rem; text-align: center; max-width: 600px; margin: 0 auto;">
            <div class="empty-cart-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Your basket is empty</h2>
            <p style="color: var(--text-dim); margin-bottom: 2rem;">
                Good food is always just a few clicks away! Explore our hand-crafted menu and satisfy your hunger.
            </p>
            <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-utensils"></i> Browse Delicious Dishes
            </a>
        </div>
    <?php else: ?>
        <div class="cart-grid" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2.5rem; align-items: start;">
            <!-- Left: Cart Items List -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem; backdrop-filter: blur(10px);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; margin-bottom: 1.25rem;">
                    <h3 style="font-size: 1.2rem; font-weight: 800;">
                        Selected Items (<?php echo count($cart); ?>)
                    </h3>
                    <a href="<?php echo baseUrl('menu.php'); ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-plus"></i> Add More Food
                    </a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <?php foreach ($cart as $foodId => $item): ?>
                        <div class="cart-item-row" style="padding-bottom: 1.25rem;">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="cart-item-thumb" style="width: 80px; height: 80px;">
                            
                            <div class="cart-item-info">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div class="veg-indicator <?php echo ($item['is_veg'] ?? true) ? 'veg' : 'nonveg'; ?>" style="position: static; width: 18px; height: 18px;"></div>
                                    <h4 class="cart-item-title" style="font-size: 1.05rem; margin-bottom: 0;">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h4>
                                </div>
                                <div class="cart-item-price" style="margin-top: 0.35rem;">
                                    ₹<?php echo number_format($item['price'], 0); ?> each
                                </div>
                                <?php if (!empty($item['notes'])): ?>
                                    <div style="font-size: 0.78rem; color: var(--primary); margin-top: 0.25rem;">
                                        <i class="fa-solid fa-note-sticky"></i> Note: <?php echo htmlspecialchars($item['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="qty-control" style="margin-right: 1.5rem;">
                                <button type="button" class="qty-btn" onclick="updateCartQty('<?php echo $foodId; ?>', <?php echo $item['qty'] - 1; ?>)">-</button>
                                <span class="qty-number"><?php echo $item['qty']; ?></span>
                                <button type="button" class="qty-btn" onclick="updateCartQty('<?php echo $foodId; ?>', <?php echo $item['qty'] + 1; ?>)">+</button>
                            </div>

                            <div style="text-align: right; min-width: 85px;">
                                <div style="font-weight: 800; font-family: var(--font-heading); font-size: 1.15rem; color: var(--text-main);">
                                    ₹<?php echo number_format($item['price'] * $item['qty'], 0); ?>
                                </div>
                                <button type="button" onclick="removeFromCart('<?php echo $foodId; ?>')" style="background: none; border: none; color: var(--secondary); font-size: 0.8rem; cursor: pointer; margin-top: 0.25rem;">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Free Delivery Meter -->
                <?php if ($subtotal < FREE_DELIVERY_THRESHOLD): ?>
                    <div style="margin-top: 1.5rem; background: rgba(249, 115, 22, 0.1); border: 1px solid var(--border-highlight); border-radius: var(--radius-md); padding: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fa-solid fa-motorcycle" style="color: var(--primary); font-size: 1.3rem;"></i>
                        <div style="font-size: 0.88rem;">
                            Add items worth <strong>₹<?php echo number_format(FREE_DELIVERY_THRESHOLD - $subtotal, 0); ?></strong> more to unlock <strong>FREE Delivery!</strong>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 1.5rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fa-solid fa-circle-check" style="color: var(--accent); font-size: 1.3rem;"></i>
                        <div style="font-size: 0.88rem; color: #10b981; font-weight: 700;">
                            Congratulations! Your order qualifies for FREE Delivery.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Promo & Bill Summary -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Promo Code Box -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-ticket" style="color: var(--primary);"></i> Apply Promo Code
                    </h4>

                    <?php if ($appliedCoupon): ?>
                        <div style="background: rgba(16, 185, 129, 0.12); border: 1.5px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 0.9rem 1.1rem; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <span style="font-weight: 900; color: #10b981; letter-spacing: 0.05em;"><?php echo htmlspecialchars($appliedCoupon['code']); ?></span>
                                <div style="font-size: 0.8rem; color: var(--text-dim);">Coupon applied successfully!</div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="removePromoCode()" style="color: var(--secondary); font-size: 0.78rem;">
                                Remove
                            </button>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="promoCodeInput" class="form-control" placeholder="Enter coupon code (e.g. TASTY50)" style="text-transform: uppercase;">
                            <button type="button" class="btn btn-primary" onclick="applyPromoCode()">
                                Apply
                            </button>
                        </div>
                        <div style="margin-top: 0.85rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="badge-tag" style="background: rgba(255,255,255,0.05); cursor: pointer;" onclick="applyPromoCode('TASTY50')">
                                TASTY50 (50% Off)
                            </span>
                            <span class="badge-tag" style="background: rgba(255,255,255,0.05); cursor: pointer;" onclick="applyPromoCode('CRAVE100')">
                                CRAVE100 (₹100 Off)
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bill Details Box -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.25rem;">Bill Summary</h4>

                    <div class="cart-summary-line">
                        <span>Item Subtotal</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <?php if ($discount > 0): ?>
                        <div class="cart-summary-line" style="color: #10b981;">
                            <span>Promo Discount (<?php echo htmlspecialchars($appliedCoupon['code']); ?>)</span>
                            <span>-₹<?php echo number_format($discount, 2); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="cart-summary-line">
                        <span>Delivery Fee</span>
                        <span><?php echo $deliveryFee === 0 ? '<span style="color: var(--accent);">FREE</span>' : '₹' . number_format($deliveryFee, 2); ?></span>
                    </div>

                    <div class="cart-summary-line">
                        <span>Govt. Taxes (GST <?php echo TAX_PERCENT; ?>%)</span>
                        <span>₹<?php echo number_format($tax, 2); ?></span>
                    </div>

                    <div class="cart-summary-line total">
                        <span>To Pay</span>
                        <span style="color: var(--primary);">₹<?php echo number_format($grandTotal, 2); ?></span>
                    </div>

                    <div style="margin-top: 1.75rem;">
                        <a href="<?php echo baseUrl('checkout.php'); ?>" class="btn btn-primary btn-block btn-lg" id="proceed-checkout-btn">
                            Proceed to Checkout <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
