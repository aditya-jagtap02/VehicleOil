<?php
// Start session
include_once 'includes/session.php';
include_once 'includes/db.php';
include_once 'includes/functions.php';
include_once 'includes/auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => '',
    'phone' => '',
    'address' => ''
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'confirm_password' => trim($_POST['confirm_password'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? '')
    ];
    
    // Validate inputs
    if (empty($formData['username'])) {
        $errors['username'] = 'Username is required';
    } elseif (strlen($formData['username']) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    } elseif (isUsernameExists($formData['username'])) {
        $errors['username'] = 'Username already exists';
    }
    
    if (empty($formData['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif (isEmailExists($formData['email'])) {
        $errors['email'] = 'Email already exists';
    }
    
    if (empty($formData['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($formData['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($formData['password'] !== $formData['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($formData['full_name'])) {
        $errors['full_name'] = 'Full name is required';
    }
    
    if (empty($formData['phone'])) {
        $errors['phone'] = 'Phone number is required';
    }
    
    if (empty($formData['address'])) {
        $errors['address'] = 'Address is required';
    }
    
    // Register user if no validation errors
    if (empty($errors)) {
        $result = registerUser(
            $formData['username'], 
            $formData['email'], 
            $formData['password'], 
            $formData['full_name'], 
            $formData['phone'], 
            $formData['address']
        );
        
        if ($result['success']) {
            // Set session for flash message
            $_SESSION['flash_message'] = 'Registration successful! You can now login.';
            $_SESSION['flash_class'] = 'success';
            
            // Redirect to login page
            redirect('login.php');
        } else {
            $errors['registration'] = $result['message'];
        }
    }
}

$pageTitle = "Register - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Register</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['registration'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($errors['registration']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="register.php" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                                        id="username" name="username" value="<?php echo htmlspecialchars($formData['username']); ?>">
                                    <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['username']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                        id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['email']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                        id="password" name="password">
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                        id="confirm_password" name="confirm_password">
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                id="full_name" name="full_name" value="<?php echo htmlspecialchars($formData['full_name']); ?>">
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['full_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="phone">Phone Number</label>
                            <input type="text" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['phone']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="address">Address</label>
                            <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                id="address" name="address" rows="3"><?php echo htmlspecialchars($formData['address']); ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['address']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
