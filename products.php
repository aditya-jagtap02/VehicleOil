<?php
// Start session
include_once 'includes/session.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'name_asc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

// Get products
$products = getProducts($search, $category, $sort, $page, $perPage);
$totalProducts = getProductsCount($search, $category);
$totalPages = ceil($totalProducts / $perPage);

// Get all categories for filter
$categories = getAllCategories();

// Process add to cart
$addedToCart = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login if not logged in
        redirect('login.php');
    }
    
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Add to cart
    if ($quantity > 0) {
        addToCart($_SESSION['user_id'], $productId, $quantity);
        $addedToCart = true;
    }
}

$pageTitle = "Products - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container mt-4">
    <?php if ($addedToCart): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Item added to your cart.
        <a href="cart.php" class="alert-link">View Cart</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">Motor Oil Products</h1>
            
            <!-- Search and filters -->
            <div class="card">
                <div class="card-body">
                    <form action="products.php" method="get" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="Search by name, viscosity, or description" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                    <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="products.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products display -->
    <div class="row">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <div class="text-center p-3">
                        <i class="fas fa-oil-can fa-4x text-primary"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($product['viscosity'] . ' | ' . $product['category_name']); ?></p>
                        <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 80) . '...'); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0">₹<?php echo htmlspecialchars(number_format($product['price'] * 3, 2)); ?></span>
                            <?php if ($product['quantity'] > 0): ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <form action="products.php" method="post">
                            <div class="input-group mb-2">
                                <input type="number" class="form-control" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" 
                                    value="1" <?php echo $product['quantity'] > 0 ? '' : 'disabled'; ?>>
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="btn btn-primary" <?php echo $product['quantity'] > 0 ? '' : 'disabled'; ?>>
                                    <i class="fas fa-shopping-cart"></i> Add
                                </button>
                            </div>
                        </form>
                        <a href="#" class="btn btn-outline-secondary btn-sm w-100" 
                           data-bs-toggle="modal" data-bs-target="#productModal<?php echo $product['id']; ?>">
                            View Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product Details Modal -->
            <div class="modal fade" id="productModal<?php echo $product['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-oil-can fa-6x text-primary mb-3"></i>
                                    <div class="mt-3">
                                        <span class="h4 text-primary">₹<?php echo htmlspecialchars(number_format($product['price'] * 3, 2)); ?></span>
                                        <?php if ($product['quantity'] > 0): ?>
                                            <p class="text-success"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['quantity']; ?> available)</p>
                                        <?php else: ?>
                                            <p class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h5>Product Details</h5>
                                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                                    
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Viscosity:</strong> <?php echo htmlspecialchars($product['viscosity']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Type:</strong> <?php echo htmlspecialchars($product['type']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Volume:</strong> <?php echo htmlspecialchars($product['volume']); ?> Liters
                                        </div>
                                        <div class="col-6">
                                            <strong>API Rating:</strong> <?php echo htmlspecialchars($product['api_rating']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>For Vehicle Type:</strong> <?php echo htmlspecialchars($product['vehicle_type']); ?>
                                        </div>
                                    </div>
                                    
                                    <form action="products.php" method="post" class="mt-4">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" 
                                                value="1" <?php echo $product['quantity'] > 0 ? '' : 'disabled'; ?>>
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="add_to_cart" value="1">
                                            <button type="submit" class="btn btn-primary" <?php echo $product['quantity'] > 0 ? '' : 'disabled'; ?>>
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No products found. Please try a different search or filter.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="products.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                            Previous
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="products.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="products.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                            Next
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>
