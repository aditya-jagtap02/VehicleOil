/**
 * JavaScript for checkout page functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const creditCardDetails = document.getElementById('credit-card-details');
    const paypalDetails = document.getElementById('paypal-details');
    const bankTransferDetails = document.getElementById('bank-transfer-details');
    
    // Function to show/hide payment details based on selection
    function togglePaymentDetails() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        // Hide all payment details sections first
        if (creditCardDetails) creditCardDetails.style.display = 'none';
        if (paypalDetails) paypalDetails.style.display = 'none';
        if (bankTransferDetails) bankTransferDetails.style.display = 'none';
        
        // Show the selected payment details section
        if (selectedMethod === 'credit_card' && creditCardDetails) {
            creditCardDetails.style.display = 'block';
        } else if (selectedMethod === 'paypal' && paypalDetails) {
            paypalDetails.style.display = 'block';
        } else if (selectedMethod === 'bank_transfer' && bankTransferDetails) {
            bankTransferDetails.style.display = 'block';
        }
    }
    
    // Add event listeners to payment method radio buttons
    paymentMethods.forEach(function(method) {
        method.addEventListener('change', togglePaymentDetails);
    });
    
    // Initialize payment details visibility
    togglePaymentDetails();
    
    // Credit card form validation and formatting
    const cardNumberInput = document.getElementById('card_number');
    const cardExpiryInput = document.getElementById('card_expiry');
    const cardCvvInput = document.getElementById('card_cvv');
    
    // Format card number with spaces after every 4 digits
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 16) {
                value = value.slice(0, 16);
            }
            
            // Format with spaces after every 4 digits
            const parts = [];
            for (let i = 0; i < value.length; i += 4) {
                parts.push(value.slice(i, i + 4));
            }
            
            this.value = parts.join(' ');
        });
    }
    
    // Format card expiry date (MM/YY)
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4);
            }
            
            if (value.length > 2) {
                this.value = value.slice(0, 2) + '/' + value.slice(2);
            } else {
                this.value = value;
            }
            
            // Validate month (01-12)
            if (value.length >= 2) {
                const month = parseInt(value.slice(0, 2), 10);
                if (month < 1 || month > 12) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            }
        });
    }
    
    // Validate CVV (3-4 digits)
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4);
            }
            this.value = value;
        });
    }
    
    // Form validation on submit
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(event) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            let isValid = true;
            
            // Basic validation for shipping information
            const shippingAddress = document.getElementById('shipping_address');
            const shippingCity = document.getElementById('shipping_city');
            const shippingState = document.getElementById('shipping_state');
            const shippingZip = document.getElementById('shipping_zip');
            
            if (shippingAddress && shippingAddress.value.trim() === '') {
                shippingAddress.classList.add('is-invalid');
                isValid = false;
            } else if (shippingAddress) {
                shippingAddress.classList.remove('is-invalid');
            }
            
            if (shippingCity && shippingCity.value.trim() === '') {
                shippingCity.classList.add('is-invalid');
                isValid = false;
            } else if (shippingCity) {
                shippingCity.classList.remove('is-invalid');
            }
            
            if (shippingState && shippingState.value.trim() === '') {
                shippingState.classList.add('is-invalid');
                isValid = false;
            } else if (shippingState) {
                shippingState.classList.remove('is-invalid');
            }
            
            if (shippingZip && shippingZip.value.trim() === '') {
                shippingZip.classList.add('is-invalid');
                isValid = false;
            } else if (shippingZip) {
                shippingZip.classList.remove('is-invalid');
            }
            
            // Credit card validation
            if (selectedMethod === 'credit_card') {
                const cardName = document.getElementById('card_name');
                
                if (cardName && cardName.value.trim() === '') {
                    cardName.classList.add('is-invalid');
                    isValid = false;
                } else if (cardName) {
                    cardName.classList.remove('is-invalid');
                }
                
                if (cardNumberInput) {
                    // Remove spaces and check length
                    const cardNum = cardNumberInput.value.replace(/\s/g, '');
                    if (cardNum.length < 13 || cardNum.length > 16) {
                        cardNumberInput.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        cardNumberInput.classList.remove('is-invalid');
                    }
                }
                
                if (cardExpiryInput) {
                    // Check format MM/YY
                    const expiryRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
                    if (!expiryRegex.test(cardExpiryInput.value)) {
                        cardExpiryInput.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        // Check if card is expired
                        const parts = cardExpiryInput.value.split('/');
                        const expMonth = parseInt(parts[0], 10);
                        const expYear = parseInt('20' + parts[1], 10);
                        
                        const now = new Date();
                        const currentMonth = now.getMonth() + 1; // getMonth() is 0-based
                        const currentYear = now.getFullYear();
                        
                        if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
                            cardExpiryInput.classList.add('is-invalid');
                            isValid = false;
                        } else {
                            cardExpiryInput.classList.remove('is-invalid');
                        }
                    }
                }
                
                if (cardCvvInput) {
                    // Check CVV length (3-4 digits)
                    if (cardCvvInput.value.length < 3 || cardCvvInput.value.length > 4) {
                        cardCvvInput.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        cardCvvInput.classList.remove('is-invalid');
                    }
                }
            }
            
            if (!isValid) {
                event.preventDefault();
                // Scroll to the first invalid element
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Show error message
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                errorAlert.role = 'alert';
                errorAlert.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Please correct the errors in the form before proceeding.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insert at the top of the form
                checkoutForm.prepend(errorAlert);
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    errorAlert.classList.remove('show');
                    setTimeout(function() {
                        errorAlert.remove();
                    }, 500);
                }, 5000);
            } else {
                // Disable submit button to prevent double submission
                const submitBtn = checkoutForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing Order...';
                }
            }
        });
    }
    
    // Populate city, state from address if saved info is checked
    const saveInfoCheckbox = document.getElementById('save_info');
    if (saveInfoCheckbox) {
        saveInfoCheckbox.addEventListener('change', function() {
            // This would typically pre-fill information from a saved address
            // For now, we'll just add a placeholder behavior
            if (this.checked) {
                // In a real app, this would populate from saved data
                console.log('Save info checked - would auto-fill data');
            }
        });
    }
});
