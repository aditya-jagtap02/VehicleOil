<?php
// Start session
include_once 'includes/session.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';
include_once 'includes/auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    redirect('login.php');
}

$errors = [];
$success = false;
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'name_asc';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;

// Process inventory update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $updateProductId = (int)$_POST['product_id'];
    $newQuantity = (int)$_POST['quantity'];
    $minStock = (int)$_POST['min_stock'];
    
    // Validate inputs
    if ($newQuantity < 0) {
        $errors['quantity'] = 'Quantity cannot be negative';
    }
    
    if ($minStock < 0) {
        $errors['min_stock'] = 'Minimum stock cannot be negative';
    }
    
    // Update inventory if no validation errors
    if (empty($errors)) {
        $result = updateProductInventory($updateProductId, $newQuantity, $minStock);
        
        if ($result['success']) {
            $success = true;
        } else {
            $errors['update'] = $result['message'];
        }
    }
}

// Process new product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Get form data
    $name = trim($_POST['name']);
    $categoryId = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $minStock = (int)$_POST['min_stock'];
    $viscosity = trim($_POST['viscosity']);
    $type = trim($_POST['type']);
    $volume = (float)$_POST['volume'];
    $apiRating = trim($_POST['api_rating']);
    $vehicleType = trim($_POST['vehicle_type']);
    
    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Product name is required';
    }
    
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a valid category';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    if ($price <= 0) {
        $errors['price'] = 'Price must be greater than zero';
    }
    
    if ($quantity < 0) {
        $errors['quantity'] = 'Quantity cannot be negative';
    }
    
    if ($minStock < 0) {
        $errors['min_stock'] = 'Minimum stock cannot be negative';
    }
    
    if (empty($viscosity)) {
        $errors['viscosity'] = 'Viscosity is required';
    }
    
    if (empty($type)) {
        $errors['type'] = 'Oil type is required';
    }
    
    if ($volume <= 0) {
        $errors['volume'] = 'Volume must be greater than zero';
    }
    
    // Add product if no validation errors
    if (empty($errors)) {
        $result = addNewProduct(
            $name, $categoryId, $description, $price, $quantity, $minStock, 
            $viscosity, $type, $volume, $apiRating, $vehicleType
        );
        
        if ($result['success']) {
            $success = true;
        } else {
            $errors['add_product'] = $result['message'];
        }
    }
}

// Get product data for editing
$productData = null;
if ($productId > 0) {
    $productData = getProductById($productId);
    if (!$productData) {
        $errors['product'] = 'Product not found';
    }
}

// Get inventory data
$inventory = getInventory($search, $category, $sort, $page, $perPage);
$totalItems = getInventoryCount($search, $category);
$totalPages = ceil($totalItems / $perPage);

// Get all categories for filter and form
$categories = getAllCategories();

$pageTitle = "Inventory Management - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-basket me-2"></i>
                            Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-oil-can me-2"></i>
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i>
                            Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>
                            Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="includes/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Inventory Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus me-1"></i> Add New Product
                    </button>
                </div>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Operation completed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($errors['update']) || isset($errors['add_product']) || isset($errors['product'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php 
                    if (isset($errors['update'])) echo htmlspecialchars($errors['update']);
                    if (isset($errors['add_product'])) echo htmlspecialchars($errors['add_product']);
                    if (isset($errors['product'])) echo htmlspecialchars($errors['product']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- Search and filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="inventory.php" method="get" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="Search by product name or description" value="<?php echo htmlspecialchars($search); ?>">
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
                                <option value="quantity_asc" <?php echo $sort == 'quantity_asc' ? 'selected' : ''; ?>>Quantity (Low to High)</option>
                                <option value="quantity_desc" <?php echo $sort == 'quantity_desc' ? 'selected' : ''; ?>>Quantity (High to Low)</option>
                                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="inventory.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Product Form (if editing) -->
            <?php if ($productData): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Editing: <?php echo htmlspecialchars($productData['name']); ?></h5>
                </div>
                <div class="card-body">
                    <form action="inventory.php" method="post" class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_name" value="<?php echo htmlspecialchars($productData['name']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="edit_category" value="<?php echo htmlspecialchars($productData['category_name']); ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_price" class="form-label">Price ($)</label>
                            <input type="text" class="form-control" id="edit_price" value="<?php echo htmlspecialchars(number_format($productData['price'], 2)); ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" 
                                id="edit_quantity" name="quantity" value="<?php echo htmlspecialchars($productData['quantity']); ?>" min="0">
                            <?php if (isset($errors['quantity'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['quantity']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_min_stock" class="form-label">Minimum Stock</label>
                            <input type="number" class="form-control <?php echo isset($errors['min_stock']) ? 'is-invalid' : ''; ?>" 
                                id="edit_min_stock" name="min_stock" value="<?php echo htmlspecialchars($productData['min_stock']); ?>" min="0">
                            <?php if (isset($errors['min_stock'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['min_stock']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_viscosity" class="form-label">Viscosity</label>
                            <input type="text" class="form-control" id="edit_viscosity" value="<?php echo htmlspecialchars($productData['viscosity']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_type" class="form-label">Oil Type</label>
                            <input type="text" class="form-control" id="edit_type" value="<?php echo htmlspecialchars($productData['type']); ?>" disabled>
                        </div>
                        
                        <input type="hidden" name="product_id" value="<?php echo $productData['id']; ?>">
                        <input type="hidden" name="update_inventory" value="1">
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">Update Inventory</button>
                            <a href="inventory.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Inventory Table -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Inventory List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Viscosity</th>
                                    <th>Price</th>
                                    <th>Current Stock</th>
                                    <th>Min Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($inventory)): ?>
                                    <?php foreach ($inventory as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['viscosity']); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['min_stock']); ?></td>
                                        <td>
                                            <?php if ($item['quantity'] <= 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($item['quantity'] <= $item['min_stock']): ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">In Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="inventory.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                data-bs-target="#viewModal<?php echo $item['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- View Product Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $item['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Product Details: <?php echo htmlspecialchars($item['name']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Product ID:</strong> <?php echo htmlspecialchars($item['id']); ?></p>
                                                            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($item['name']); ?></p>
                                                            <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category_name']); ?></p>
                                                            <p><strong>Viscosity:</strong> <?php echo htmlspecialchars($item['viscosity']); ?></p>
                                                            <p><strong>Oil Type:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
                                                            <p><strong>Volume:</strong> <?php echo htmlspecialchars($item['volume']); ?> Liters</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>API Rating:</strong> <?php echo htmlspecialchars($item['api_rating']); ?></p>
                                                            <p><strong>Vehicle Type:</strong> <?php echo htmlspecialchars($item['vehicle_type']); ?></p>
                                                            <p><strong>Price:</strong> $<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></p>
                                                            <p><strong>Current Stock:</strong> <?php echo htmlspecialchars($item['quantity']); ?> units</p>
                                                            <p><strong>Minimum Stock:</strong> <?php echo htmlspecialchars($item['min_stock']); ?> units</p>
                                                            <p><strong>Created:</strong> <?php echo htmlspecialchars(date('F d, Y', strtotime($item['created_at']))); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>Description:</h6>
                                                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="inventory.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No products found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="inventory.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                                    Previous
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="inventory.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="inventory.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&sort=<?php echo urlencode($sort); ?>">
                                    Next
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="inventory.php" method="post" id="addProductForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                id="name" name="name" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>" 
                                id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['category_id']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                                id="price" name="price" min="0.01" required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['price']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Initial Stock</label>
                            <input type="number" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" 
                                id="quantity" name="quantity" min="0" required>
                            <?php if (isset($errors['quantity'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['quantity']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="min_stock" class="form-label">Minimum Stock</label>
                            <input type="number" class="form-control <?php echo isset($errors['min_stock']) ? 'is-invalid' : ''; ?>" 
                                id="min_stock" name="min_stock" min="0" required>
                            <?php if (isset($errors['min_stock'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['min_stock']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="viscosity" class="form-label">Viscosity (e.g., 5W-30)</label>
                            <input type="text" class="form-control <?php echo isset($errors['viscosity']) ? 'is-invalid' : ''; ?>" 
                                id="viscosity" name="viscosity" required>
                            <?php if (isset($errors['viscosity'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['viscosity']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Oil Type</label>
                            <select class="form-select <?php echo isset($errors['type']) ? 'is-invalid' : ''; ?>" 
                                id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="Synthetic">Synthetic</option>
                                <option value="Semi-Synthetic">Semi-Synthetic</option>
                                <option value="Conventional">Conventional</option>
                                <option value="High-Mileage">High-Mileage</option>
                            </select>
                            <?php if (isset($errors['type'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['type']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="volume" class="form-label">Volume (Liters)</label>
                            <input type="number" step="0.1" class="form-control <?php echo isset($errors['volume']) ? 'is-invalid' : ''; ?>" 
                                id="volume" name="volume" min="0.1" required>
                            <?php if (isset($errors['volume'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['volume']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="api_rating" class="form-label">API Rating</label>
                            <input type="text" class="form-control" id="api_rating" name="api_rating">
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <select class="form-select" id="vehicle_type" name="vehicle_type">
                                <option value="">Select Vehicle Type</option>
                                <option value="Passenger Car">Passenger Car</option>
                                <option value="Light Truck">Light Truck</option>
                                <option value="Heavy Duty">Heavy Duty</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="All Vehicles">All Vehicles</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                id="description" name="description" rows="3" required></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['description']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <input type="hidden" name="add_product" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addProductForm" class="btn btn-primary">Add Product</button>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
