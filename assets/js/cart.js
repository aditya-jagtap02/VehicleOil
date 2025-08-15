/**
 * JavaScript for cart page functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Select all quantity buttons
    const qtyButtons = document.querySelectorAll('.qty-btn');
    
    // Add event listener to each button
    qtyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Get item ID and action
            const itemId = this.getAttribute('data-item-id');
            const action = this.getAttribute('data-action');
            
            // Find corresponding input element
            const inputElem = document.querySelector(`input[name="items[${itemId}]"]`);
            
            if (inputElem) {
                let currentQty = parseInt(inputElem.value, 10);
                
                // Update quantity based on action
                if (action === 'increase') {
                    const maxStock = parseInt(this.getAttribute('data-max'), 10) || 9999;
                    if (currentQty < maxStock) {
                        currentQty++;
                    } else {
                        // Show alert if trying to exceed available stock
                        alert(`Sorry, only ${maxStock} items are available in stock.`);
                    }
                } else if (action === 'decrease' && currentQty > 0) {
                    currentQty--;
                }
                
                // Update input value
                inputElem.value = currentQty;
            }
        });
    });
    
    // Handle form submission
    const cartForm = document.getElementById('cart-form');
    if (cartForm) {
        cartForm.addEventListener('submit', function(e) {
            // Check if any quantity is invalid
            const qtyInputs = document.querySelectorAll('.qty-input');
            let isValid = true;
            
            qtyInputs.forEach(function(input) {
                const qty = parseInt(input.value, 10);
                const max = parseInt(input.getAttribute('max'), 10) || 9999;
                
                if (qty > max) {
                    isValid = false;
                    alert(`Some items exceed available stock. Please adjust quantities.`);
                    input.classList.add('is-invalid');
                    e.preventDefault();
                    return false;
                }
            });
            
            if (isValid) {
                // Add loading state to update button
                const updateBtn = cartForm.querySelector('button[type="submit"]');
                if (updateBtn) {
                    updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
                    updateBtn.disabled = true;
                }
            }
        });
    }
    
    // Automatically update cart when quantity reaches zero
    const qtyInputs = document.querySelectorAll('.qty-input');
    qtyInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const qty = parseInt(this.value, 10);
            if (qty === 0) {
                // Ask for confirmation before removing
                if (confirm('Remove this item from your cart?')) {
                    const itemId = this.name.match(/\d+/)[0];
                    window.location.href = `cart.php?remove=${itemId}`;
                } else {
                    // Reset to 1 if user cancels
                    this.value = 1;
                }
            }
        });
    });
});
