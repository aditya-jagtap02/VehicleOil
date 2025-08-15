<?php
// Start session
include_once 'includes/session.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';
include_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Get user data
$userData = getUserById($_SESSION['user_id']);

// Get recent orders
$recentOrders = getRecentOrders($_SESSION['user_id'], 5);

// Get inventory alerts for admin users
$inventoryAlerts = [];
if ($_SESSION['user_role'] === 'admin') {
    $inventoryAlerts = getLowStockItems();
}

$pageTitle = "Dashboard - Motor Oil Warehouse";
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
                        <a class="nav-link active" href="dashboard.php">
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
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">
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
                    <?php endif; ?>
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
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="cart.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-shopping-cart"></i> Cart
                        </a>
                        <a href="products.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-shopping-bag"></i> Shop
                        </a>
                    </div>
                </div>
            </div>

            <!-- Welcome message -->
            <div class="alert alert-info">
                <h4>Welcome, <?php echo htmlspecialchars($userData['full_name']); ?>!</h4>
                <p>Here's your motor oil warehouse dashboard. Manage your orders, check inventory, and more from here.</p>
            </div>

            <div class="row mt-4">
                <!-- User info summary -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Profile Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-secondary rounded-circle p-3 me-3">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($userData['username']); ?></h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($userData['email']); ?></p>
                                </div>
                            </div>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($userData['full_name']); ?></p>
                            <p><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($userData['role'])); ?></p>
                            <a href="profile.php" class="btn btn-outline-primary btn-sm">View Profile</a>
                        </div>
                    </div>
                </div>

                <!-- Order summary -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h3"><?php echo getUserOrderCount($_SESSION['user_id']); ?></div>
                                    <div class="small text-muted">Orders</div>
                                </div>
                                <div class="col-4">
                                    <div class="h3"><?php echo getActiveOrderCount($_SESSION['user_id']); ?></div>
                                    <div class="small text-muted">Active</div>
                                </div>
                                <div class="col-4">
                                    <div class="h3"><?php echo getCompletedOrderCount($_SESSION['user_id']); ?></div>
                                    <div class="small text-muted">Completed</div>
                                </div>
                            </div>
                            <hr>
                            <a href="orders.php" class="btn btn-outline-success btn-sm">View Orders</a>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="products.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-search me-2"></i> Browse Products
                                </a>
                                <a href="cart.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-shopping-cart me-2"></i> View Cart
                                </a>
                                <a href="orders.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-history me-2"></i> Order History
                                </a>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                <a href="inventory.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-boxes me-2"></i> Manage Inventory
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent orders section -->
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentOrders)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['order_date']))); ?></td>
                                            <td><?php echo htmlspecialchars($order['item_count']); ?></td>
                                            <td>$<?php echo htmlspecialchars(number_format($order['total'], 2)); ?></td>
                                            <td>
                                                <span class="badge rounded-pill bg-<?php echo getStatusColor($order['status']); ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="orders.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Details
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> You have no recent orders.
                                <a href="products.php" class="alert-link">Browse products</a> to place an order.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($_SESSION['user_role'] === 'admin' && !empty($inventoryAlerts)): ?>
            <!-- Inventory alerts for admin -->
            <div class="row mt-2">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">Low Stock Alerts</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Product Name</th>
                                            <th>Current Stock</th>
                                            <th>Minimum Stock</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inventoryAlerts as $item): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($item['product_id']); ?></td>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td>
                                                <span class="badge rounded-pill bg-danger">
                                                    <?php echo htmlspecialchars($item['quantity']); ?> units
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['min_stock']); ?> units</td>
                                            <td>
                                                <a href="inventory.php?id=<?php echo $item['product_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Update Stock
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
