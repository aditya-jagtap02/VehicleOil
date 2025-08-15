<?php
// Start session
include_once 'includes/session.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Process cart update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $cartItems = $_POST['items'] ?? [];
    
    foreach ($cartItems as $itemId => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            // Remove item if quantity is zero or negative
            removeFromCart($_SESSION['user_id'], $itemId);
        } else {
            // Update quantity
            updateCartQuantity($_SESSION['user_id'], $itemId, $quantity);
        }
    }
    
    // Set flash message
    $_SESSION['flash_message'] = 'Cart updated successfully';
    $_SESSION['flash_class'] = 'success';
    
    // Redirect to avoid form resubmission
    redirect('cart.php');
}

// Process item removal
if (isset($_GET['remove'])) {
    $itemId = (int)$_GET['remove'];
    removeFromCart($_SESSION['user_id'], $itemId);
    
    // Set flash message
    $_SESSION['flash_message'] = 'Item removed from cart';
    $_SESSION['flash_class'] = 'success';
    
    // Redirect to avoid repeated removal
    redirect('cart.php');
}

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = calculateCartTotal($_SESSION['user_id']);

$pageTitle = "Shopping Cart - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Your Shopping Cart</h1>
            
            <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_class']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
                // Clear flash message
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_class']);
            endif;
            ?>
            
            <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">
                <i class="fas fa-shopping-cart me-2"></i> Your cart is empty.
                <a href="products.php" class="alert-link">Browse products</a> to add items to your cart.
            </div>
            <?php else: ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Shopping Cart Items</h5>
                </div>
                <div class="card-body">
                    <form action="cart.php" method="post" id="cart-form">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-oil-can fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['viscosity'] . ' | ' . $item['type']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₹<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                        <td>
                                            <div class="input-group" style="max-width: 120px;">
                                                <button class="btn btn-outline-secondary qty-btn" type="button" 
                                                    data-action="decrease" data-item-id="<?php echo $item['product_id']; ?>">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="form-control text-center qty-input" 
                                                    name="items[<?php echo $item['product_id']; ?>]" 
                                                    value="<?php echo htmlspecialchars($item['quantity']); ?>" 
                                                    min="0" max="<?php echo htmlspecialchars($item['stock']); ?>">
                                                <button class="btn btn-outline-secondary qty-btn" type="button" 
                                                    data-action="increase" data-item-id="<?php echo $item['product_id']; ?>" 
                                                    data-max="<?php echo htmlspecialchars($item['stock']); ?>">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <?php if ($item['quantity'] > $item['stock']): ?>
                                            <div class="text-danger small mt-1">
                                                Only <?php echo htmlspecialchars($item['stock']); ?> available
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>₹<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></td>
                                        <td>
                                            <a href="cart.php?remove=<?php echo $item['product_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to remove this item?')">
                                                <i class="fas fa-trash"></i> Remove
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">
                                            <strong>Total:</strong>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <input type="hidden" name="update_cart" value="1">
                        
                        <div class="d-flex justify-content-between mt-3">
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-2"></i> Update Cart
                                </button>
                                <a href="checkout.php" class="btn btn-success ms-2">
                                    <i class="fas fa-shopping-cart me-2"></i> Proceed to Checkout
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
