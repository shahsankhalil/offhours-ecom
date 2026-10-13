// OffHour Watch Store - Interactive Main Script
document.addEventListener('DOMContentLoaded', () => {
    console.log('OffHour Main Script Initialized.');

    // 1. Confirm deletion of item from cart
    const deleteButtons = document.querySelectorAll('.btn-remove-item');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const confirmed = confirm('Are you sure you want to remove this watch from your cart?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });

    // 2. Checkout Form Submission Loading Effect
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (e) => {
            const btnSubmit = checkoutForm.querySelector('.btn-place-order');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Processing Order...';
                btnSubmit.style.opacity = '0.7';
            }
        });
    }

    // 3. Simple Card Hover micro-interaction logging (Optional)
    const cards = document.querySelectorAll('.watch-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.borderColor = 'var(--primary-color)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.borderColor = 'var(--border-color)';
        });
    });
});
