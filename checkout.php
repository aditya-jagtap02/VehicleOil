<?php
// Start session
include_once 'includes/session.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Get user data
$userData = getUserById($_SESSION['user_id']);

// Check if cart is empty
$cartItems = getCartItems($_SESSION['user_id']);
if (empty($cartItems)) {
    // Set flash message
    $_SESSION['flash_message'] = 'Your cart is empty. Add some products before checkout.';
    $_SESSION['flash_class'] = 'warning';
    
    // Redirect to products page
    redirect('products.php');
}

// Calculate cart total
$cartTotal = calculateCartTotal($_SESSION['user_id']);

// Check if any item exceeds available stock
$stockIssue = false;
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        $stockIssue = true;
        break;
    }
}

$errors = [];
$orderPlaced = false;
$orderId = 0;

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate shipping info
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingCity = trim($_POST['shipping_city'] ?? '');
    $shippingState = trim($_POST['shipping_state'] ?? '');
    $shippingZip = trim($_POST['shipping_zip'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    
    // Perform validation
    if (empty($shippingAddress)) {
        $errors['shipping_address'] = 'Shipping address is required';
    }
    
    if (empty($shippingCity)) {
        $errors['shipping_city'] = 'City is required';
    }
    
    if (empty($shippingState)) {
        $errors['shipping_state'] = 'State is required';
    }
    
    if (empty($shippingZip)) {
        $errors['shipping_zip'] = 'ZIP code is required';
    }
    
    if (empty($paymentMethod)) {
        $errors['payment_method'] = 'Payment method is required';
    }
    
    // Additional payment validation based on method
    if ($paymentMethod === 'credit_card') {
        $cardNumber = trim($_POST['card_number'] ?? '');
        $cardExpiry = trim($_POST['card_expiry'] ?? '');
        $cardCvv = trim($_POST['card_cvv'] ?? '');
        $cardName = trim($_POST['card_name'] ?? '');
        
        if (empty($cardNumber)) {
            $errors['card_number'] = 'Card number is required';
        } elseif (!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $cardNumber))) {
            $errors['card_number'] = 'Invalid card number format';
        }
        
        if (empty($cardExpiry)) {
            $errors['card_expiry'] = 'Expiration date is required';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $cardExpiry)) {
            $errors['card_expiry'] = 'Invalid expiration date format (MM/YY)';
        }
        
        if (empty($cardCvv)) {
            $errors['card_cvv'] = 'CVV is required';
        } elseif (!preg_match('/^\d{3,4}$/', $cardCvv)) {
            $errors['card_cvv'] = 'Invalid CVV format';
        }
        
        if (empty($cardName)) {
            $errors['card_name'] = 'Cardholder name is required';
        }
    }
    
    // Re-check stock before finalizing order
    $cartItems = getCartItems($_SESSION['user_id']); // Get fresh cart data
    $stockError = false;
    
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            $stockError = true;
            $errors['stock'] = 'Some items in your cart exceed available stock. Please update your cart.';
            break;
        }
    }
    
    // Process order if no errors
    if (empty($errors)) {
        // Create shipping address string
        $shippingFullAddress = $shippingAddress . ', ' . $shippingCity . ', ' . $shippingState . ' ' . $shippingZip;
        
        // Place order
        $result = placeOrder(
            $_SESSION['user_id'],
            $shippingFullAddress,
            $paymentMethod,
            $cartTotal
        );
        
        if ($result['success']) {
            $orderPlaced = true;
            $orderId = $result['order_id'];
            
            // Clear cart after successful order
            clearCart($_SESSION['user_id']);
        } else {
            $errors['order'] = $result['message'];
        }
    }
}

$pageTitle = "Checkout - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container mt-5">
    <?php if ($orderPlaced): ?>
    <!-- Order Success -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i> Order Placed Successfully!</h4>
                </div>
                <div class="card-body text-center">
                    <h5>Thank you for your order!</h5>
                    <p>Your order #<?php echo htmlspecialchars($orderId); ?> has been placed successfully.</p>
                    <p>We will process your order shortly. You can track your order status in your account.</p>
                    
                    <div class="mt-4">
                        <a href="orders.php" class="btn btn-primary">
                            <i class="fas fa-clipboard-list me-2"></i> View My Orders
                        </a>
                        <a href="products.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Checkout Process -->
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Checkout</h1>
            
            <?php if ($stockIssue): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i> Some items in your cart exceed available stock. Please update your cart before proceeding.
            </div>
            <?php endif; ?>
            
            <?php if (isset($errors['order']) || isset($errors['stock'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php 
                    if (isset($errors['order'])) echo htmlspecialchars($errors['order']);
                    if (isset($errors['stock'])) echo htmlspecialchars($errors['stock']);
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Order Summary -->
        <div class="col-md-4 order-md-2 mb-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cartItems as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($item['viscosity'] . ' | Qty: ' . $item['quantity']); ?>
                                </small>
                                <?php if ($item['quantity'] > $item['stock']): ?>
                                <div class="text-danger small">
                                    <i class="fas fa-exclamation-circle"></i> Only <?php echo $item['stock']; ?> available
                                </div>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted">$<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></span>
                        </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <h6 class="my-0">Subtotal</h6>
                            </div>
                            <span>₹<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <h6 class="my-0">Shipping</h6>
                                <small class="text-muted">Standard shipping</small>
                            </div>
                            <span>₹0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <h6 class="my-0">Tax</h6>
                                <small class="text-muted">Based on shipping address</small>
                            </div>
                            <span>₹0.00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <div class="text-success">
                                <h6 class="my-0">Total (INR)</h6>
                            </div>
                            <span class="text-success"><strong>₹<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></strong></span>
                        </li>
                    </ul>
                    
                    <div class="mt-3">
                        <a href="cart.php" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-edit me-2"></i> Edit Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <div class="col-md-8 order-md-1">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Shipping & Payment Information</h5>
                </div>
                <div class="card-body">
                    <form action="checkout.php" method="post" id="checkout-form">
                        <!-- Customer Information -->
                        <h5 class="mb-3">Your Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="customer_name" value="<?php echo htmlspecialchars($userData['full_name']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="customer_phone" value="<?php echo htmlspecialchars($userData['phone']); ?>" disabled>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Shipping Information -->
                        <h5 class="mb-3">Shipping Information</h5>
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Address</label>
                            <input type="text" class="form-control <?php echo isset($errors['shipping_address']) ? 'is-invalid' : ''; ?>" 
                                id="shipping_address" name="shipping_address" value="<?php echo htmlspecialchars($userData['address']); ?>">
                            <?php if (isset($errors['shipping_address'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['shipping_address']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control <?php echo isset($errors['shipping_city']) ? 'is-invalid' : ''; ?>" 
                                    id="shipping_city" name="shipping_city" value="">
                                <?php if (isset($errors['shipping_city'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['shipping_city']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_state" class="form-label">State</label>
                                <input type="text" class="form-control <?php echo isset($errors['shipping_state']) ? 'is-invalid' : ''; ?>" 
                                    id="shipping_state" name="shipping_state" value="">
                                <?php if (isset($errors['shipping_state'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['shipping_state']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="shipping_zip" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control <?php echo isset($errors['shipping_zip']) ? 'is-invalid' : ''; ?>" 
                                    id="shipping_zip" name="shipping_zip" value="">
                                <?php if (isset($errors['shipping_zip'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['shipping_zip']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Payment Method -->
                        <h5 class="mb-3">Payment Method</h5>
                        <div class="my-3">
                            <div class="form-check">
                                <input id="credit_card" name="payment_method" type="radio" class="form-check-input" value="credit_card" checked>
                                <label class="form-check-label" for="credit_card">Credit Card</label>
                            </div>
                            <div class="form-check">
                                <input id="cash_on_delivery" name="payment_method" type="radio" class="form-check-input" value="cash_on_delivery">
                                <label class="form-check-label" for="cash_on_delivery">Cash on Delivery</label>
                            </div>
                        </div>
                        
                        <!-- Credit Card Details -->
                        <div id="credit-card-details">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control <?php echo isset($errors['card_name']) ? 'is-invalid' : ''; ?>" 
                                        id="card_name" name="card_name">
                                    <?php if (isset($errors['card_name'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['card_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="text-muted">Full name as displayed on card</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control <?php echo isset($errors['card_number']) ? 'is-invalid' : ''; ?>" 
                                        id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                                    <?php if (isset($errors['card_number'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['card_number']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="card_expiry" class="form-label">Expiration</label>
                                    <input type="text" class="form-control <?php echo isset($errors['card_expiry']) ? 'is-invalid' : ''; ?>" 
                                        id="card_expiry" name="card_expiry" placeholder="MM/YY">
                                    <?php if (isset($errors['card_expiry'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['card_expiry']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control <?php echo isset($errors['card_cvv']) ? 'is-invalid' : ''; ?>" 
                                        id="card_cvv" name="card_cvv">
                                    <?php if (isset($errors['card_cvv'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['card_cvv']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PayPal Details -->
                        <div id="cash-on-delivery-details" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-money-bill-wave me-2"></i> You will pay the amount in cash when the order is delivered.
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="save_info" name="save_info">
                            <label class="form-check-label" for="save_info">Save this information for next time</label>
                        </div>
                        
                        <input type="hidden" name="place_order" value="1">
                        <button class="btn btn-primary btn-lg w-100" type="submit" <?php echo $stockIssue ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-bag me-2"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>
