<?php
// Authentication related functions
include_once 'db.php';

/**
 * Register a new user
 *
 * @param string $username The username
 * @param string $email The email address
 * @param string $password The password
 * @param string $fullName The full name
 * @param string $phone The phone number
 * @param string $address The address
 * @return array Result with success status and message or user_id
 */
function registerUser($username, $email, $password, $fullName, $phone, $address) {
    try {
        $pdo = getPDO();
        
        // Hash the password for security
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, phone, address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$username, $email, $passwordHash, $fullName, $phone, $address]);
        
        return [
            'success' => true,
            'user_id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        // Check for duplicate entry
        if ($e->getCode() == '23000') {
            if (strpos($e->getMessage(), 'username')) {
                return [
                    'success' => false,
                    'message' => 'Username already exists'
                ];
            } elseif (strpos($e->getMessage(), 'email')) {
                return [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
            }
        }
        
        // Log error
        error_log("Registration failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again later.'
        ];
    }
}

/**
 * Attempt to login a user
 *
 * @param string $email The email address
 * @param string $password The password
 * @return array Result with success status and user data or error message
 */
function loginUser($email, $password) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, full_name, role 
            FROM users 
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
    } catch (PDOException $e) {
        // Log error
        error_log("Login failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Login failed. Please try again later.'
        ];
    }
}

/**
 * Check if a username already exists
 *
 * @param string $username The username to check
 * @return bool True if username exists, false otherwise
 */
function isUsernameExists($username) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM users 
            WHERE username = ?
        ");
        
        $stmt->execute([$username]);
        return ($stmt->fetchColumn() > 0);
    } catch (PDOException $e) {
        // Log error
        error_log("Username check failed: " . $e->getMessage());
        
        // Assume username exists in case of error to prevent registration
        return true;
    }
}

/**
 * Check if an email already exists
 *
 * @param string $email The email to check
 * @return bool True if email exists, false otherwise
 */
function isEmailExists($email) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM users 
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        return ($stmt->fetchColumn() > 0);
    } catch (PDOException $e) {
        // Log error
        error_log("Email check failed: " . $e->getMessage());
        
        // Assume email exists in case of error to prevent registration
        return true;
    }
}

/**
 * Update user profile information
 *
 * @param int $userId The user ID
 * @param string $fullName The full name
 * @param string $phone The phone number
 * @param string $address The address
 * @return array Result with success status and message
 */
function updateUserProfile($userId, $fullName, $phone, $address) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, phone = ?, address = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$fullName, $phone, $address, $userId]);
        
        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    } catch (PDOException $e) {
        // Log error
        error_log("Profile update failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Profile update failed. Please try again later.'
        ];
    }
}

/**
 * Change user password
 *
 * @param int $userId The user ID
 * @param string $currentPassword The current password
 * @param string $newPassword The new password
 * @return array Result with success status and message
 */
function changeUserPassword($userId, $currentPassword, $newPassword) {
    try {
        $pdo = getPDO();
        
        // Get current password hash
        $stmt = $pdo->prepare("
            SELECT password 
            FROM users 
            WHERE id = ?
        ");
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }
        
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$newPasswordHash, $userId]);
        
        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    } catch (PDOException $e) {
        // Log error
        error_log("Password change failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Password change failed. Please try again later.'
        ];
    }
}
?>
