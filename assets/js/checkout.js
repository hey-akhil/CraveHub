/**
 * CraveHub - Checkout & Multi-Channel Dummy Payment Simulation
 */

let selectedPaymentMethod = 'upi';

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    
    // Update active tab buttons
    document.querySelectorAll('.payment-tab-btn').forEach(btn => {
        if (btn.getAttribute('data-method') === method) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update form sections
    document.querySelectorAll('.payment-method-section').forEach(section => {
        if (section.id === `section-${method}`) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });

    const hiddenInput = document.getElementById('paymentMethodInput');
    if (hiddenInput) hiddenInput.value = method;
}

// Interactive Credit Card updates
function initCardLivePreview() {
    const numInput = document.getElementById('cardNumberInput');
    const nameInput = document.getElementById('cardNameInput');
    const expInput = document.getElementById('cardExpiryInput');

    const numDisplay = document.getElementById('displayCardNumber');
    const nameDisplay = document.getElementById('displayCardName');
    const expDisplay = document.getElementById('displayCardExpiry');

    if (numInput && numDisplay) {
        numInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 16);
            let formatted = val.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = formatted;
            numDisplay.textContent = formatted || '•••• •••• •••• ••••';
        });
    }

    if (nameInput && nameDisplay) {
        nameInput.addEventListener('input', (e) => {
            nameDisplay.textContent = e.target.value.toUpperCase() || 'YOUR NAME HERE';
        });
    }

    if (expInput && expDisplay) {
        expInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (val.length >= 2) {
                val = val.substring(0, 2) + '/' + val.substring(2);
            }
            e.target.value = val;
            expDisplay.textContent = val || 'MM/YY';
        });
    }
}

// Process Dummy Payment
async function processOrderPayment(e) {
    e.preventDefault();

    const form = document.getElementById('checkoutOrderForm');
    if (!form) return;

    // Basic validation
    const name = form.customer_name.value.trim();
    const phone = form.customer_phone.value.trim();
    const address = form.delivery_address.value.trim();

    if (!name || !phone || !address) {
        showToast('Please fill in your delivery name, phone and address.', 'error');
        return;
    }

    const modal = document.getElementById('paymentProcessingModal');
    const statusText = document.getElementById('paymentProcessStatus');
    const progressFill = document.getElementById('paymentProgressFill');

    if (modal) modal.classList.add('active');

    const steps = [
        { text: 'Connecting to Secure Payment Gateway...', percent: '25%' },
        { text: `Authorizing transaction via ${selectedPaymentMethod.toUpperCase()}...`, percent: '65%' },
        { text: 'Verifying with Bank & Restaurant...', percent: '90%' },
        { text: 'Payment Approved! Confirming Order...', percent: '100%' }
    ];

    let currentStep = 0;
    const interval = setInterval(async () => {
        if (currentStep < steps.length) {
            if (statusText) statusText.textContent = steps[currentStep].text;
            if (progressFill) progressFill.style.width = steps[currentStep].percent;
            currentStep++;
        } else {
            clearInterval(interval);
            
            // Send Order Creation AJAX
            try {
                const formData = new FormData(form);
                formData.append('payment_method', selectedPaymentMethod);

                const response = await fetch(window.CRAVEHUB_BASE_URL + 'api/checkout.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success && result.order_id) {
                    if (statusText) statusText.innerHTML = '<span style="color: #10b981; font-weight: 800;"><i class="fa-solid fa-circle-check"></i> Order Placed Successfully! Redirecting...</span>';
                    setTimeout(() => {
                        window.location.href = window.CRAVEHUB_BASE_URL + 'order-track.php?order_id=' + result.order_id;
                    }, 1000);
                } else {
                    if (modal) modal.classList.remove('active');
                    showToast(result.error || 'Failed to complete order. Please retry.', 'error');
                }
            } catch (err) {
                console.error(err);
                if (modal) modal.classList.remove('active');
                showToast('Payment gateway error. Please try again.', 'error');
            }
        }
    }, 700);
}

document.addEventListener('DOMContentLoaded', () => {
    initCardLivePreview();
    const checkoutForm = document.getElementById('checkoutOrderForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', processOrderPayment);
    }
});
