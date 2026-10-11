<?php
/**
 * CraveHub - Checkout & Multi-Channel Dummy Payment Page
 */
$pageTitle = 'Secure Checkout & Payment';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: ' . baseUrl('menu.php'));
    exit;
}

$user = getLoggedInUser();
$subtotal = getCartSubtotal();
$deliveryFee = ($subtotal >= FREE_DELIVERY_THRESHOLD) ? 0 : DELIVERY_FEE;

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
    }
}

$taxable = max(0, $subtotal - $discount);
$tax = round(($taxable * TAX_PERCENT) / 100, 2);
$grandTotal = max(0, $taxable + $deliveryFee + $tax);

$extraScripts = [baseUrl('assets/js/checkout.js')];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <!-- Step Indicator -->
    <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; margin-bottom: 2.5rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem; color: var(--accent); font-weight: 700;">
            <i class="fa-solid fa-circle-check"></i> Basket
        </div>
        <div style="width: 50px; height: 2px; background: var(--accent);"></div>
        <div style="display: flex; align-items: center; gap: 0.6rem; color: var(--primary); font-weight: 800;">
            <i class="fa-solid fa-circle-dot"></i> Checkout & Payment
        </div>
        <div style="width: 50px; height: 2px; background: var(--border-subtle);"></div>
        <div style="display: flex; align-items: center; gap: 0.6rem; color: var(--text-dim); font-weight: 600;">
            <i class="fa-regular fa-circle"></i> Live Tracking
        </div>
    </div>

    <form id="checkoutOrderForm" onsubmit="return false;">
        <input type="hidden" name="payment_method" id="paymentMethodInput" value="upi">

        <div class="checkout-grid" style="display: grid; grid-template-columns: 1.45fr 1fr; gap: 2.5rem; align-items: start;">
            <!-- Left Column: Delivery Address & Payment Channel -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                
                <!-- 1. Delivery Details Box -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.75rem;">
                    <h3 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> 1. Delivery Address
                    </h3>

                    <?php if (!isLoggedIn()): ?>
                        <div style="background: rgba(249, 115, 22, 0.1); border: 1px solid var(--border-highlight); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; justify-content: space-between; align-items: center;">
                            <span>Ordering as Guest? You can also log in to auto-fill addresses.</span>
                            <a href="<?php echo baseUrl('login.php'); ?>" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 0.3rem 0.75rem;">Log In</a>
                        </div>
                    <?php endif; ?>

                    <div class="form-two-col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="customerName">Full Name *</label>
                            <input type="text" id="customerName" name="customer_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="e.g. Rahul Sharma">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="customerPhone">Phone Number *</label>
                            <input type="tel" id="customerPhone" name="customer_phone" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. 9876543210">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="customerEmail">Email Address (for Invoice)</label>
                        <input type="email" id="customerEmail" name="customer_email" class="form-control"
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="rahul@example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deliveryAddress">Complete Delivery Address *</label>
                        <textarea id="deliveryAddress" name="delivery_address" class="form-control" rows="3" required placeholder="Flat / House No., Floor, Building Name, Street / Locality, Landmark"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="deliveryNotes">Delivery Instructions (Optional)</label>
                        <input type="text" id="deliveryNotes" name="delivery_notes" class="form-control" placeholder="e.g. Leave with security, call when outside">
                    </div>
                </div>

                <!-- 2. Dummy Payment Gateway Box -->
                <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <h3 style="font-size: 1.3rem; font-weight: 800; display: flex; align-items: center; gap: 0.6rem;">
                            <i class="fa-solid fa-lock" style="color: var(--accent);"></i> 2. Choose Payment Method
                        </h3>
                        <span style="font-size: 0.78rem; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-weight: 700;">
                            <i class="fa-solid fa-shield-halved"></i> 256-Bit SSL Demo Gateway
                        </span>
                    </div>

                    <!-- Payment Navigation Tabs -->
                    <div class="payment-tabs">
                        <button type="button" class="payment-tab-btn active" data-method="upi" onclick="selectPaymentMethod('upi')">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>Instant UPI / QR</span>
                        </button>
                        <button type="button" class="payment-tab-btn" data-method="card" onclick="selectPaymentMethod('card')">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Credit / Debit Card</span>
                        </button>
                        <button type="button" class="payment-tab-btn" data-method="netbanking" onclick="selectPaymentMethod('netbanking')">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Net Banking</span>
                        </button>
                        <button type="button" class="payment-tab-btn" data-method="cod" onclick="selectPaymentMethod('cod')">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                            <span>Cash on Delivery</span>
                        </button>
                    </div>

                    <!-- Method 1: Instant UPI / QR -->
                    <div id="section-upi" class="payment-method-section">
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
                                Scan dynamic QR code with any UPI app (GPay, PhonePe, Paytm, Cred)
                            </p>
                            
                            <!-- Dynamic QR Code Container -->
                            <div style="position: relative; width: 190px; height: 190px; margin: 0 auto 1.25rem; background: #fff; padding: 10px; border-radius: 12px; box-shadow: var(--shadow-md);">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=170x170&data=upi://pay?pa=cravehub@bank&pn=CraveHub&am=<?php echo $grandTotal; ?>&cu=INR" 
                                     alt="Scan to Pay" style="width: 100%; height: 100%; border-radius: 8px;">
                                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary); animation: scanLine 2s infinite ease-in-out;"></div>
                            </div>

                            <div style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 1.5rem;">
                                Amount: <strong style="color: var(--primary); font-size: 1.05rem;">₹<?php echo number_format($grandTotal, 2); ?></strong>
                            </div>

                            <!-- UPI App Pills -->
                            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Opened Google Pay simulator!', 'info')">
                                    <i class="fa-brands fa-google-pay"></i> Google Pay
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('PhonePe selected!', 'info')">
                                    PhonePe
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Paytm selected!', 'info')">
                                    Paytm UPI
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Method 2: Credit / Debit Card -->
                    <div id="section-card" class="payment-method-section" style="display: none;">
                        <!-- Interactive 3D Card Preview -->
                        <div class="simulated-card">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="card-chip"></div>
                                <i class="fa-brands fa-cc-visa" style="font-size: 2.2rem; color: #fff;"></i>
                            </div>
                            <div class="card-number-display" id="displayCardNumber">
                                •••• •••• •••• ••••
                            </div>
                            <div class="card-bottom-row">
                                <div>
                                    <div style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase;">Card Holder</div>
                                    <div class="card-holder-name" id="displayCardName">YOUR NAME HERE</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase;">Expires</div>
                                    <div class="card-expiry-val" id="displayCardExpiry">MM/YY</div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Inputs -->
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Card Number</label>
                                <input type="text" id="cardNumberInput" class="form-control" placeholder="4123 4567 8901 2345" maxlength="19">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Cardholder Name</label>
                                <input type="text" id="cardNameInput" class="form-control" placeholder="Name as on card">
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="text" id="cardExpiryInput" class="form-control" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">CVV / CVC</label>
                                    <input type="password" id="cardCvvInput" class="form-control" placeholder="•••" maxlength="4">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Method 3: Net Banking -->
                    <div id="section-netbanking" class="payment-method-section" style="display: none;">
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">Select your banking institution:</p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem;">
                            <label style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.9rem; text-align: center; cursor: pointer;">
                                <input type="radio" name="bank_select" checked style="margin-bottom: 0.5rem;"><br>
                                <strong style="font-size: 0.85rem;">HDFC Bank</strong>
                            </label>
                            <label style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.9rem; text-align: center; cursor: pointer;">
                                <input type="radio" name="bank_select" style="margin-bottom: 0.5rem;"><br>
                                <strong style="font-size: 0.85rem;">State Bank of India</strong>
                            </label>
                            <label style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.9rem; text-align: center; cursor: pointer;">
                                <input type="radio" name="bank_select" style="margin-bottom: 0.5rem;"><br>
                                <strong style="font-size: 0.85rem;">ICICI Bank</strong>
                            </label>
                            <label style="background: rgba(255,255,255,0.04); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.9rem; text-align: center; cursor: pointer;">
                                <input type="radio" name="bank_select" style="margin-bottom: 0.5rem;"><br>
                                <strong style="font-size: 0.85rem;">Axis Bank</strong>
                            </label>
                        </div>
                    </div>

                    <!-- Method 4: Cash on Delivery -->
                    <div id="section-cod" class="payment-method-section" style="display: none;">
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
                            <div style="width: 55px; height: 55px; border-radius: 50%; background: rgba(16,185,129,0.15); color: #10b981; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem;">Pay Cash on Arrival</h4>
                            <p style="color: var(--text-dim); font-size: 0.88rem; max-width: 400px; margin: 0 auto;">
                                Keep exact change of <strong style="color: var(--primary);">₹<?php echo number_format($grandTotal, 2); ?></strong> ready at delivery. Digital UPI is also accepted at your door by our partner.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Right Column: Order Summary Widget -->
            <div style="position: sticky; top: 90px;">
                <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.75rem; backdrop-filter: blur(10px);">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
                        Order Items (<?php echo count($cart); ?>)
                    </h3>

                    <div style="max-height: 240px; overflow-y: auto; margin-bottom: 1.25rem; padding-right: 0.5rem; display: flex; flex-direction: column; gap: 0.85rem;">
                        <?php foreach ($cart as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover;">
                                    <div>
                                        <div style="font-size: 0.9rem; font-weight: 700; line-height: 1.2;"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-dim);">Qty: <?php echo $item['qty']; ?></div>
                                    </div>
                                </div>
                                <div style="font-weight: 800; font-family: var(--font-heading); font-size: 0.95rem;">
                                    ₹<?php echo number_format($item['price'] * $item['qty'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
                        <div class="cart-summary-line">
                            <span>Item Total</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                            <div class="cart-summary-line" style="color: #10b981;">
                                <span>Discount</span>
                                <span>-₹<?php echo number_format($discount, 2); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="cart-summary-line">
                            <span>Delivery Partner Fee</span>
                            <span><?php echo $deliveryFee === 0 ? '<span style="color: var(--accent);">FREE</span>' : '₹' . number_format($deliveryFee, 2); ?></span>
                        </div>
                        <div class="cart-summary-line">
                            <span>Taxes & Charges (GST 5%)</span>
                            <span>₹<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="cart-summary-line total">
                            <span>Total Payable</span>
                            <span style="color: var(--primary);">₹<?php echo number_format($grandTotal, 2); ?></span>
                        </div>
                    </div>

                    <button type="submit" id="submitOrderBtn" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                        <i class="fa-solid fa-lock"></i> Pay ₹<?php echo number_format($grandTotal, 2); ?> & Place Order
                    </button>

                    <div style="text-align: center; margin-top: 1rem; font-size: 0.78rem; color: var(--text-dim); display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <i class="fa-solid fa-shield-check" style="color: var(--accent);"></i> Encrypted 256-bit Dummy Payment Simulation
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Simulated Processing Modal Overlay -->
<div class="modal-overlay" id="paymentProcessingModal">
    <div class="modal-container" style="max-width: 450px; text-align: center; padding: 2.5rem;">
        <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(249,115,22,0.15); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 2rem; margin: 0 auto 1.5rem;">
            <i class="fa-solid fa-arrows-rotate fa-spin"></i>
        </div>

        <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.5rem;">Processing Payment</h3>
        <p id="paymentProcessStatus" style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.75rem;">
            Connecting to bank network...
        </p>

        <!-- Progress bar -->
        <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 999px; overflow: hidden; margin-bottom: 1.5rem;">
            <div id="paymentProgressFill" style="width: 15%; height: 100%; background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%); transition: width 0.4s ease;"></div>
        </div>

        <div style="font-size: 0.8rem; color: var(--text-dim);">
            Please do not refresh or close this browser window.
        </div>
    </div>
</div>

<style>
@keyframes scanLine {
    0% { top: 0; }
    50% { top: 100%; }
    100% { top: 0; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
