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
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate inputs
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // Attempt login if no validation errors
    if (empty($errors)) {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // Set session variables
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['user_email'] = $result['email'];
            $_SESSION['user_role'] = $result['role'];
            
            // Redirect to dashboard
            redirect('dashboard.php');
        } else {
            $errors['login'] = $result['message'];
        }
    }
}

$pageTitle = "Login - Motor Oil Warehouse";
include_once 'includes/header.php';
include_once 'includes/nav.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Login</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($errors['login'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($errors['login']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="post">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['email']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                id="password" name="password">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['password']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p>Don't have an account? <a href="register.php">Register</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
