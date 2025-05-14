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

$errors = [];
$success = false;

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate inputs
    if (empty($fullName)) {
        $errors['full_name'] = 'Full name is required';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }
    
    if (empty($address)) {
        $errors['address'] = 'Address is required';
    }
    
    // Update profile if no validation errors
    if (empty($errors)) {
        $result = updateUserProfile($_SESSION['user_id'], $fullName, $phone, $address);
        
        if ($result['success']) {
            $success = true;
            $userData = getUserById($_SESSION['user_id']); // Refresh user data
        } else {
            $errors['update'] = $result['message'];
        }
    }
}

// Process password change form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Get form data
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    // Validate inputs
    if (empty($currentPassword)) {
        $errors['current_password'] = 'Current password is required';
    }
    
    if (empty($newPassword)) {
        $errors['new_password'] = 'New password is required';
    } elseif (strlen($newPassword) < 6) {
        $errors['new_password'] = 'New password must be at least 6 characters';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // Change password if no validation errors
    if (empty($errors)) {
        $result = changeUserPassword($_SESSION['user_id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $success = true;
        } else {
            $errors['password_change'] = $result['message'];
        }
    }
}

$pageTitle = "Profile - Motor Oil Warehouse";
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
                        <a class="nav-link active" href="profile.php">
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
                <h1 class="h2">User Profile</h1>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i> Profile updated successfully!
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profile details -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($errors['update'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($errors['update']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <form action="profile.php" method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled>
                                    <div class="form-text">Username cannot be changed</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                                    <div class="form-text">Email cannot be changed</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                        id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name']); ?>">
                                    <?php if (isset($errors['full_name'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['full_name']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                        id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone']); ?>">
                                    <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['phone']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                        id="address" name="address" rows="3"><?php echo htmlspecialchars($userData['address']); ?></textarea>
                                    <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['address']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" class="form-control" id="role" value="<?php echo ucfirst(htmlspecialchars($userData['role'])); ?>" disabled>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Member Since</label>
                                    <input type="text" class="form-control" id="created_at" 
                                        value="<?php echo htmlspecialchars(date('F d, Y', strtotime($userData['created_at']))); ?>" disabled>
                                </div>
                                
                                <input type="hidden" name="update_profile" value="1">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change password -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($errors['password_change'])): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($errors['password_change']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <form action="profile.php" method="post">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                                        id="current_password" name="current_password">
                                    <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['current_password']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                        id="new_password" name="new_password">
                                    <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['new_password']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                        id="confirm_password" name="confirm_password">
                                    <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <input type="hidden" name="change_password" value="1">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account statistics -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">Account Statistics</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Orders
                                    <span class="badge bg-primary rounded-pill"><?php echo getUserOrderCount($_SESSION['user_id']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Active Orders
                                    <span class="badge bg-warning rounded-pill"><?php echo getActiveOrderCount($_SESSION['user_id']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Completed Orders
                                    <span class="badge bg-success rounded-pill"><?php echo getCompletedOrderCount($_SESSION['user_id']); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Spent
                                    <span class="badge bg-info rounded-pill">$<?php echo number_format(getUserTotalSpent($_SESSION['user_id']), 2); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
