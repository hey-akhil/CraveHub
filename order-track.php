<?php
/**
 * CraveHub - Real-Time Order Tracking & Delivery Simulator
 */
$pageTitle = 'Live Order Tracking';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

$orderId = $_GET['order_id'] ?? '';
if (empty($orderId)) {
    header('Location: ' . baseUrl('index.php'));
    exit;
}

$db = getDB();
$order = $db->findById('orders', $orderId);

if (!$order) {
    setFlash('error', 'Order not found or invalid Order ID.');
    header('Location: ' . baseUrl('my-orders.php'));
    exit;
}

$badge = getOrderStatusBadge($order['status']);
$items = $order['items'] ?? [];
$partner = $order['delivery_partner'] ?? [
    'name' => 'Rajesh Kumar',
    'phone' => '+91 98231 44556',
    'vehicle' => 'Honda Activa (KA-03-EX-4491)',
    'rating' => 4.9,
    'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80'
];

$extraScripts = [baseUrl('assets/js/tracker.js')];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    
    <!-- Top Header Bar -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
                <span class="badge-status <?php echo $badge['class']; ?>" id="orderStatusBadge">
                    <i class="fa-solid <?php echo $badge['icon']; ?>"></i> <?php echo $badge['label']; ?>
                </span>
                <span style="font-size: 0.85rem; color: var(--text-dim);">Placed on <?php echo date('M d, Y • h:i A', strtotime($order['created_at'])); ?></span>
            </div>
            <h1 style="font-size: 2.2rem; font-weight: 900;">
                Order #<?php echo htmlspecialchars($orderId); ?>
            </h1>
        </div>

        <!-- Simulation Advance Button (Demo Mode) -->
        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="advanceSimulation('<?php echo $orderId; ?>')">
                <i class="fa-solid fa-forward-step" style="color: var(--primary);"></i> Advance Status (Demo Step)
            </button>
            <a href="<?php echo baseUrl('my-orders.php'); ?>" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-receipt"></i> All Orders
            </a>
        </div>
    </div>

    <!-- Live Status Tracker Card -->
    <div class="tracker-card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
            <div>
                <h4 style="font-size: 1.25rem; font-weight: 800;" id="orderEtaText">
                    Estimated Delivery: 20 - 25 mins
                </h4>
                <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 0.2rem;">
                    Delivering to: <strong style="color: var(--text-main);"><?php echo htmlspecialchars($order['delivery_address']); ?></strong>
                </p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.8rem; color: var(--text-dim);">Paid via <?php echo strtoupper($order['payment_method'] ?? 'UPI'); ?></span>
                <div style="font-size: 1.25rem; font-weight: 900; font-family: var(--font-heading); color: var(--primary);">
                    ₹<?php echo number_format($order['total_amount'], 2); ?>
                </div>
            </div>
        </div>

        <!-- 5-Stage Live Timeline -->
        <?php
            $statuses = ['placed', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'];
            $currentIdx = array_search($order['status'], $statuses);
            if ($currentIdx === false) $currentIdx = 0;
            $percent = 0;
            if ($currentIdx === 1) $percent = 25;
            elseif ($currentIdx === 2) $percent = 50;
            elseif ($currentIdx === 3) $percent = 75;
            elseif ($currentIdx === 4) $percent = 100;
        ?>
        <div class="tracker-timeline">
            <div class="tracker-track-wrapper">
                <div class="tracker-progress-bar" id="trackerProgressBar" style="width: <?php echo $percent; ?>%;"></div>
            </div>

            <!-- Step 1: Placed -->
            <div class="timeline-step <?php echo $currentIdx >= 0 ? ($currentIdx === 0 ? 'active' : 'completed') : ''; ?>" id="step-placed">
                <div class="step-node">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div class="step-label">Order Placed</div>
                <div class="step-time">Received</div>
            </div>

            <!-- Step 2: Confirmed -->
            <div class="timeline-step <?php echo $currentIdx >= 1 ? ($currentIdx === 1 ? 'active' : 'completed') : ''; ?>" id="step-confirmed">
                <div class="step-node">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div class="step-label">Kitchen Accepted</div>
                <div class="step-time">Confirmed</div>
            </div>

            <!-- Step 3: Preparing -->
            <div class="timeline-step <?php echo $currentIdx >= 2 ? ($currentIdx === 2 ? 'active' : 'completed') : ''; ?>" id="step-preparing">
                <div class="step-node">
                    <i class="fa-solid fa-fire-burner"></i>
                </div>
                <div class="step-label">Cooking Food</div>
                <div class="step-time">Fresh & Hot</div>
            </div>

            <!-- Step 4: Out for Delivery -->
            <div class="timeline-step <?php echo $currentIdx >= 3 ? ($currentIdx === 3 ? 'active' : 'completed') : ''; ?>" id="step-out_for_delivery">
                <div class="step-node">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <div class="step-label">On the Way</div>
                <div class="step-time">Express Valet</div>
            </div>

            <!-- Step 5: Delivered -->
            <div class="timeline-step <?php echo $currentIdx >= 4 ? ($currentIdx === 4 ? 'active' : 'completed') : ''; ?>" id="step-delivered">
                <div class="step-node">
                    <i class="fa-solid fa-house-chimney-user"></i>
                </div>
                <div class="step-label">Delivered</div>
                <div class="step-time">Enjoy your Meal</div>
            </div>
        </div>

        <!-- Simulated Live GPS Radar Map Container -->
        <div style="background: #090d16; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); height: 200px; position: relative; overflow: hidden; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center;">
            <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 0); background-size: 24px 24px; opacity: 0.7;"></div>
            
            <!-- Road Route Simulation Line -->
            <div style="position: absolute; width: 60%; height: 4px; background: dashed 2px rgba(249, 115, 22, 0.4); transform: rotate(-8deg);"></div>

            <!-- Restaurant Marker -->
            <div style="position: absolute; left: 18%; display: flex; flex-direction: column; align-items: center;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 0 15px rgba(59,130,246,0.6);">
                    <i class="fa-solid fa-store"></i>
                </div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; margin-top: 0.25rem;">CraveHub Kitchen</span>
            </div>

            <!-- Live Valet Marker with radar pulse -->
            <div style="position: absolute; left: 52%; display: flex; flex-direction: column; align-items: center; z-index: 5;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; box-shadow: 0 0 20px var(--primary-glow); animation: pulseGlow 1.8s infinite;">
                    <i class="fa-solid fa-motorcycle"></i>
                </div>
                <span style="font-size: 0.78rem; font-weight: 800; color: #fff; margin-top: 0.25rem;">Valet on Route</span>
            </div>

            <!-- Customer Destination Marker -->
            <div style="position: absolute; right: 18%; display: flex; flex-direction: column; align-items: center;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 0 15px rgba(16,185,129,0.6);">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; margin-top: 0.25rem;">Your Doorstep</span>
            </div>
        </div>

        <!-- Delivery Partner Information Card -->
        <div class="delivery-partner-card">
            <div class="partner-info">
                <img src="<?php echo htmlspecialchars($partner['photo']); ?>" alt="Partner" class="partner-photo">
                <div>
                    <h5 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 0.2rem;">
                        <?php echo htmlspecialchars($partner['name']); ?>
                    </h5>
                    <div style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 0.3rem;">
                        <?php echo htmlspecialchars($partner['vehicle']); ?>
                    </div>
                    <span class="rating-pill" style="font-size: 0.75rem; padding: 0.15rem 0.5rem;">
                        <i class="fa-solid fa-star"></i> <?php echo number_format($partner['rating'], 1); ?> Valet Rating
                    </span>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <a href="tel:<?php echo htmlspecialchars($partner['phone']); ?>" class="btn btn-secondary btn-sm" onclick="showToast('Calling delivery partner: <?php echo htmlspecialchars($partner['phone']); ?>', 'info')">
                    <i class="fa-solid fa-phone"></i> Call Valet
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Live tracking link copied to clipboard!', 'success')">
                    <i class="fa-solid fa-share-nodes"></i> Share Live Link
                </button>
            </div>
        </div>
    </div>

    <!-- Order Items Breakdown -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.75rem;">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.25rem;">Dishes in this Delivery</h3>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($items as $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <img src="<?php echo htmlspecialchars($item['image'] ?? ''); ?>" alt="" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                        <div>
                            <h5 style="font-size: 0.95rem; font-weight: 700;"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <span style="font-size: 0.8rem; color: var(--text-dim);">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['qty']; ?></span>
                        </div>
                    </div>
                    <div style="font-weight: 800; font-family: var(--font-heading); font-size: 1.05rem;">
                        ₹<?php echo number_format($item['price'] * $item['qty'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initOrderTracker('<?php echo $orderId; ?>');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
