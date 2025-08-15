<?php
// Start session
include_once 'includes/session.php';
$pageTitle = "Home - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container mt-5">
    <div class="jumbotron">
        <h1 class="display-4">Vehicle Motor Oil Warehouse</h1>
        <p class="lead">Welcome to our comprehensive warehouse management system for vehicle motor oils.</p>
        <hr class="my-4">
        <p>Browse our extensive collection of high-quality motor oils for all types of vehicles.</p>
        <p class="lead">
            <a class="btn btn-primary btn-lg" href="products.php" role="button">Browse Products</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-secondary btn-lg" href="login.php" role="button">Login</a>
                <a class="btn btn-success btn-lg" href="register.php" role="button">Register</a>
            <?php endif; ?>
        </p>
    </div>

    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-oil-can fa-4x mb-3"></i>
                    <h3 class="card-title">Premium Oils</h3>
                    <p class="card-text">Explore our collection of premium synthetic motor oils for optimal engine performance.</p>
                    <a href="products.php?category=premium" class="btn btn-primary">View Premium Oils</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-truck fa-4x mb-3"></i>
                    <h3 class="card-title">Bulk Orders</h3>
                    <p class="card-text">Special pricing available for bulk orders. Perfect for businesses and resellers.</p>
                    <a href="products.php?category=bulk" class="btn btn-primary">Bulk Options</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-car fa-4x mb-3"></i>
                    <h3 class="card-title">Vehicle Specific</h3>
                    <p class="card-text">Find the perfect motor oil specifically formulated for your vehicle make and model.</p>
                    <a href="products.php?category=specific" class="btn btn-primary">Find My Oil</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h2 class="text-center mb-4">Why Choose Our Warehouse?</h2>
        </div>
        <div class="col-md-3">
            <div class="text-center mb-4">
                <i class="fas fa-check-circle fa-3x mb-2 text-success"></i>
                <h4>Quality Guaranteed</h4>
                <p>All our products come with quality assurance and warranty.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center mb-4">
                <i class="fas fa-shipping-fast fa-3x mb-2 text-success"></i>
                <h4>Fast Shipping</h4>
                <p>Quick delivery to your doorstep or business location.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center mb-4">
                <i class="fas fa-tag fa-3x mb-2 text-success"></i>
                <h4>Competitive Prices</h4>
                <p>We offer the best prices in the market with regular discounts.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center mb-4">
                <i class="fas fa-headset fa-3x mb-2 text-success"></i>
                <h4>24/7 Support</h4>
                <p>Our customer service team is always available to assist you.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
