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

// Set default date range (last 30 days)
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

// Get date range from form if submitted
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
}

// Get report type
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'sales';

// Get report data based on type
switch ($reportType) {
    case 'products':
        $reportData = getProductSalesReport($startDate, $endDate, 10);
        break;
    case 'inventory':
        $reportData = getInventoryValueReport();
        break;
    case 'sales':
    default:
        $reportData = getSalesReport($startDate, $endDate);
        break;
}

// Calculate totals for sales report
$totalOrders = 0;
$totalRevenue = 0;
if ($reportType === 'sales') {
    foreach ($reportData as $data) {
        $totalOrders += $data['order_count'];
        $totalRevenue += $data['total_sales'];
    }
}

// Format dates for display
$formattedStartDate = date('M j, Y', strtotime($startDate));
$formattedEndDate = date('M j, Y', strtotime($endDate));

$pageTitle = "Reports - Motor Oil Warehouse";
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
                        <a class="nav-link" href="inventory.php">
                            <i class="fas fa-boxes me-2"></i>
                            Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">
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
                <h1 class="h2">Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-clipboard-list"></i> View Orders
                        </a>
                        <a href="inventory.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-boxes"></i> Inventory
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Options -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Report Options</h5>
                </div>
                <div class="card-body">
                    <form action="reports.php" method="get" class="row g-3">
                        <div class="col-md-4">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type">
                                <option value="sales" <?php echo $reportType == 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                                <option value="products" <?php echo $reportType == 'products' ? 'selected' : ''; ?>>Product Sales</option>
                                <option value="inventory" <?php echo $reportType == 'inventory' ? 'selected' : ''; ?>>Inventory Value</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 date-range <?php echo $reportType == 'inventory' ? 'd-none' : ''; ?>">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                value="<?php echo $startDate; ?>">
                        </div>
                        
                        <div class="col-md-3 date-range <?php echo $reportType == 'inventory' ? 'd-none' : ''; ?>">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                value="<?php echo $endDate; ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Report Content -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <?php 
                        switch ($reportType) {
                            case 'products':
                                echo "Top Selling Products";
                                break;
                            case 'inventory':
                                echo "Inventory Value Report";
                                break;
                            case 'sales':
                            default:
                                echo "Sales Report";
                                break;
                        }
                        ?>
                        <?php if ($reportType !== 'inventory'): ?>
                        <small class="ms-2">(<?php echo $formattedStartDate; ?> - <?php echo $formattedEndDate; ?>)</small>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($reportType === 'sales'): ?>
                    <!-- Sales Report Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Orders</h5>
                                    <p class="h2 mb-0"><?php echo $totalOrders; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Revenue</h5>
                                    <p class="h2 mb-0">$<?php echo number_format($totalRevenue, 2); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Average Order Value</h5>
                                    <p class="h2 mb-0">$<?php echo $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sales Report Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData)): ?>
                                    <?php foreach ($reportData as $data): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($data['date'])); ?></td>
                                        <td><?php echo $data['order_count']; ?></td>
                                        <td>$<?php echo number_format($data['total_sales'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No sales data available for the selected date range.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php elseif ($reportType === 'products'): ?>
                    <!-- Product Sales Report -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Viscosity</th>
                                    <th>Type</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData)): ?>
                                    <?php foreach ($reportData as $data): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($data['name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($data['viscosity']); ?></td>
                                        <td><?php echo htmlspecialchars($data['type']); ?></td>
                                        <td><?php echo $data['total_quantity']; ?> units</td>
                                        <td>$<?php echo number_format($data['total_revenue'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No product sales data available for the selected date range.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php elseif ($reportType === 'inventory'): ?>
                    <!-- Inventory Value Report -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Products</th>
                                    <th>Total Items</th>
                                    <th>Inventory Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalProducts = 0;
                                $totalItems = 0;
                                $totalValue = 0;
                                
                                if (!empty($reportData)):
                                    foreach ($reportData as $data):
                                        $totalProducts += $data['product_count'];
                                        $totalItems += $data['total_items'];
                                        $totalValue += $data['total_value'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($data['category_name']); ?></td>
                                    <td><?php echo $data['product_count']; ?></td>
                                    <td><?php echo $data['total_items']; ?> units</td>
                                    <td>$<?php echo number_format($data['total_value'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td><strong><?php echo $totalProducts; ?></strong></td>
                                    <td><strong><?php echo $totalItems; ?> units</strong></td>
                                    <td><strong>$<?php echo number_format($totalValue, 2); ?></strong></td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No inventory data available.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="#" class="btn btn-primary" onclick="window.print();">
                            <i class="fas fa-print me-2"></i> Print Report
                        </a>
                        <a href="#" class="btn btn-outline-secondary ms-2" id="exportCSV">
                            <i class="fas fa-file-csv me-2"></i> Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Show/hide date range based on report type selection
document.getElementById('report_type').addEventListener('change', function() {
    const dateRangeFields = document.querySelectorAll('.date-range');
    if (this.value === 'inventory') {
        dateRangeFields.forEach(field => field.classList.add('d-none'));
    } else {
        dateRangeFields.forEach(field => field.classList.remove('d-none'));
    }
});

// Simple CSV export function
document.getElementById('exportCSV').addEventListener('click', function(e) {
    e.preventDefault();
    
    const table = document.querySelector('.table');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Clean the text content to handle commas and quotes
            let data = cols[j].textContent.replace(/(\r\n|\n|\r)/gm, '').trim();
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // Create CSV file and download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'report-<?php echo $reportType; ?>-<?php echo date('Y-m-d'); ?>.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
});
</script>

<?php include_once 'includes/footer.php'; ?>
