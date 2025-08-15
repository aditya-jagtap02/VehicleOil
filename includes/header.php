<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Motor Oil Warehouse'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<?php
// Check for session timeout message
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Session Expired!</strong> Your session has expired. Please log in again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

// Display flash message if set
if (isset($_SESSION['flash_message'])) {
    $class = isset($_SESSION['flash_class']) ? $_SESSION['flash_class'] : 'info';
    echo '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['flash_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    
    // Clear flash message
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_class']);
}
?>
