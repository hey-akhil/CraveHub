/**
 * CraveHub - Live Order Tracking & Simulation Controller
 */

let trackingInterval = null;

function initOrderTracker(orderId) {
    pollOrderStatus(orderId);
    trackingInterval = setInterval(() => {
        pollOrderStatus(orderId);
    }, 6000);
}

async function pollOrderStatus(orderId) {
    try {
        const res = await fetch(`${window.CRAVEHUB_BASE_URL}api/order_status.php?order_id=${orderId}`);
        const data = await res.json();

        if (data.success && data.order) {
            updateTrackerUI(data.order);
        }
    } catch (err) {
        console.error('Tracker polling error:', err);
    }
}

function updateTrackerUI(order) {
    const status = order.status;
    const steps = ['placed', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'];
    const currentIndex = steps.indexOf(status);

    const progressBar = document.getElementById('trackerProgressBar');
    if (progressBar) {
        let percent = 0;
        if (currentIndex === 1) percent = 25;
        else if (currentIndex === 2) percent = 50;
        else if (currentIndex === 3) percent = 75;
        else if (currentIndex === 4) percent = 100;
        progressBar.style.width = percent + '%';
    }

    steps.forEach((step, idx) => {
        const stepEl = document.getElementById(`step-${step}`);
        if (!stepEl) return;

        stepEl.classList.remove('completed', 'active');
        if (idx < currentIndex) {
            stepEl.classList.add('completed');
        } else if (idx === currentIndex) {
            stepEl.classList.add('active');
        }
    });

    // Update status badge & dynamic message
    const statusBadge = document.getElementById('orderStatusBadge');
    if (statusBadge && order.status_badge) {
        statusBadge.className = `badge-status ${order.status_badge.class}`;
        statusBadge.innerHTML = `<i class="fa-solid ${order.status_badge.icon}"></i> ${order.status_badge.label}`;
    }

    const etaText = document.getElementById('orderEtaText');
    if (etaText) {
        if (status === 'delivered') {
            etaText.innerHTML = '<span style="color: #10b981; font-weight: 800;">Delivered with Love! Bon Appétit!</span>';
        } else if (status === 'out_for_delivery') {
            etaText.innerHTML = '<span style="color: #f97316; font-weight: 800;">Arriving in approx 8 - 12 mins</span>';
        } else if (status === 'preparing') {
            etaText.innerHTML = '<span>Cooking fresh in the kitchen (approx 18 mins)</span>';
        } else if (status === 'confirmed') {
            etaText.innerHTML = '<span>Order accepted by kitchen. Preparing shortly</span>';
        } else {
            etaText.innerHTML = '<span>Order received, awaiting kitchen confirmation</span>';
        }
    }
}

// Quick simulation for presentation/testing: Advance order status
async function advanceSimulation(orderId) {
    try {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('action', 'advance_simulation');

        const res = await fetch(`${window.CRAVEHUB_BASE_URL}api/order_status.php`, {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.success) {
            showToast(`Status updated to: ${data.new_status.toUpperCase()}`, 'success');
            pollOrderStatus(orderId);
        } else {
            showToast(data.message || 'Cannot advance status', 'info');
        }
    } catch (err) {
        console.error(err);
    }
}
