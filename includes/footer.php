    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>Motor Oil Warehouse</h5>
                    <p>Your trusted source for high-quality motor oils and lubricants for all vehicle types.</p>
                </div>
                
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="products.php" class="text-white">Products</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php" class="text-white">Dashboard</a></li>
                        <li><a href="orders.php" class="text-white">Orders</a></li>
                        <?php else: ?>
                        <li><a href="login.php" class="text-white">Login</a></li>
                        <li><a href="register.php" class="text-white">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <address>
                        <p><i class="fas fa-map-marker-alt me-2"></i> 123 Oil Street, Warehouse City, WC 12345</p>
                        <p><i class="fas fa-phone me-2"></i> (555) 123-4567</p>
                        <p><i class="fas fa-envelope me-2"></i> info@motoroilwarehouse.com</p>
                    </address>
                </div>
                
                <div class="col-md-3">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                    <div class="mt-3">
                        <p><i class="fas fa-truck me-2"></i> Fast Shipping</p>
                        <p><i class="fas fa-shield-alt me-2"></i> Secure Payments</p>
                    </div>
                </div>
            </div>
            
            <hr class="mt-4">
            
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> Motor Oil Warehouse. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>
                        <a href="#" class="text-white me-3">Privacy Policy</a>
                        <a href="#" class="text-white me-3">Terms of Service</a>
                        <a href="#" class="text-white">Shipping Information</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'cart.php'): ?>
    <script src="assets/js/cart.js"></script>
    <?php endif; ?>
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'checkout.php'): ?>
    <script src="assets/js/checkout.js"></script>
    <?php endif; ?>
</body>
</html>
