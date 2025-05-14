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

// Initialize variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$totalOrders = getUserOrdersCount($_SESSION['user_id']);
$totalPages = ceil($totalOrders / $perPage);
$isAdmin = ($_SESSION['user_role'] === 'admin');

// Get specific order details if ID is provided
$orderDetails = null;
if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $orderDetails = getOrderDetails($orderId, $isAdmin ? null : $_SESSION['user_id']);
    
    // If admin is updating order status
    if ($isAdmin && isset($_POST['update_status']) && isset($_POST['status'])) {
        $newStatus = $_POST['status'];
        $result = updateOrderStatus($orderId, $newStatus);
        
        if ($result['success']) {
            // Refresh order details
            $orderDetails = getOrderDetails($orderId);
            
            // Set success message
            $_SESSION['flash_message'] = 'Order status updated successfully';
            $_SESSION['flash_class'] = 'success';
        } else {
            // Set error message
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_class'] = 'danger';
        }
    }
}

// Get orders list if not viewing specific order
$orders = [];
if (!$orderDetails) {
    if ($isAdmin) {
        // Admin can see all orders with filtering
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $totalOrders = getAdminOrdersCount($statusFilter, $search);
        $totalPages = ceil($totalOrders / $perPage);
        $orders = getAdminOrders($statusFilter, $search, $page, $perPage);
    } else {
        // Regular user sees only their orders
        $orders = getUserOrders($_SESSION['user_id'], $page, $perPage);
    }
}

$pageTitle = "Orders - Motor Oil Warehouse";
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
                        <a class="nav-link active" href="orders.php">
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
                    <?php if ($isAdmin): ?>
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
                <h1 class="h2">
                    <?php 
                    if ($orderDetails) {
                        echo "Order #" . htmlspecialchars($orderDetails['id']);
                    } else {
                        echo $isAdmin ? "All Orders" : "My Orders";
                    }
                    ?>
                </h1>
                
                <?php if ($isAdmin && !$orderDetails): ?>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="reports.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-chart-bar"></i> Sales Reports
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($orderDetails): ?>
            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order Details</h5>
                        <span class="badge rounded-pill bg-<?php echo getStatusColor($orderDetails['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($orderDetails['status'])); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($orderDetails['id']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($orderDetails['order_date']))); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge rounded-pill bg-<?php echo getStatusColor($orderDetails['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($orderDetails['status'])); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($orderDetails['payment_method'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($orderDetails['full_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($orderDetails['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($orderDetails['phone']); ?></p>
                            <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($orderDetails['shipping_address']); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($isAdmin): ?>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="orders.php?id=<?php echo $orderDetails['id']; ?>" method="post" class="d-flex align-items-center">
                                <label for="status" class="me-2"><strong>Update Status:</strong></label>
                                <select name="status" id="status" class="form-select me-2">
                                    <option value="pending" <?php echo $orderDetails['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $orderDetails['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $orderDetails['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $orderDetails['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $orderDetails['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderDetails['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($item['viscosity'] . ' | ' . $item['type']); ?></div>
                                        </div>
                                    </td>
                                    <td>₹<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>₹<?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'], 2)); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>₹<?php echo htmlspecialchars(number_format($orderDetails['total_amount'], 2)); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <a href="orders.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Orders
                        </a>
                        
                        <?php if ($orderDetails['status'] == 'delivered'): ?>
                        <a href="products.php" class="btn btn-success ms-2">
                            <i class="fas fa-shopping-cart me-2"></i> Buy Again
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($isAdmin): ?>
            <!-- Admin Order Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="orders.php" method="get" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="Order ID, customer name or email" 
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Orders List -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order History</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($orders)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <?php if ($isAdmin): ?>
                                    <th>Customer</th>
                                    <?php endif; ?>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['order_date']))); ?></td>
                                    <?php if ($isAdmin): ?>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                        <div class="small text-muted"><?php echo htmlspecialchars($order['email']); ?></div>
                                    </td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($order['item_count']); ?></td>
                                    <td>₹<?php echo htmlspecialchars(number_format($order['total'], 2)); ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php echo getStatusColor($order['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    Previous
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?>">
                                    Next
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No orders found.
                        <?php if (!$isAdmin): ?>
                        <a href="products.php" class="alert-link">Browse products</a> to place an order.
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
