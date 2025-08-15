<?php
// General utility functions for the application
include_once 'db.php';

/**
 * Redirect to a specific page
 *
 * @param string $page The page to redirect to
 * @return void
 */
function redirect($page) {
    header("Location: $page");
    exit;
}

/**
 * Get user data by ID
 *
 * @param int $userId The user ID
 * @return array|false User data or false if not found
 */
function getUserById($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT id, username, email, full_name, phone, address, role, created_at 
            FROM users 
            WHERE id = ?
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        // Log error
        error_log("Get user failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all product categories
 *
 * @return array Categories
 */
function getAllCategories() {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->query("
            SELECT id, name, description 
            FROM categories 
            ORDER BY name ASC
        ");
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get categories failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get products with filtering and pagination
 *
 * @param string $search Search term
 * @param string $category Category ID
 * @param string $sort Sort order
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array Products
 */
function getProducts($search = '', $category = '', $sort = 'name_asc', $page = 1, $perPage = 12) {
    try {
        $pdo = getPDO();
        
        $params = [];
        $sql = "
            SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE 1=1
        ";
        
        // Add search condition
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.viscosity LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add category condition
        if (!empty($category)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        // Add sorting
        switch ($sort) {
            case 'name_desc':
                $sql .= " ORDER BY p.name DESC";
                break;
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.created_at DESC";
                break;
            default: // name_asc
                $sql .= " ORDER BY p.name ASC";
                break;
        }
        
        // Add pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get products failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of products with filters applied
 *
 * @param string $search Search term
 * @param string $category Category ID
 * @return int Total count
 */
function getProductsCount($search = '', $category = '') {
    try {
        $pdo = getPDO();
        
        $params = [];
        $sql = "
            SELECT COUNT(*) 
            FROM products p 
            WHERE 1=1
        ";
        
        // Add search condition
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.viscosity LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add category condition
        if (!empty($category)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get products count failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get product by ID
 *
 * @param int $productId The product ID
 * @return array|false Product data or false if not found
 */
function getProductById($productId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        
        $stmt->execute([$productId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        // Log error
        error_log("Get product failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Add item to user's cart
 *
 * @param int $userId User ID
 * @param int $productId Product ID
 * @param int $quantity Quantity
 * @return bool Success status
 */
function addToCart($userId, $productId, $quantity) {
    try {
        $pdo = getPDO();
        
        // Check if product already in cart
        $stmt = $pdo->prepare("
            SELECT id, quantity 
            FROM cart 
            WHERE user_id = ? AND product_id = ?
        ");
        
        $stmt->execute([$userId, $productId]);
        $cartItem = $stmt->fetch();
        
        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + $quantity;
            
            $stmt = $pdo->prepare("
                UPDATE cart 
                SET quantity = ? 
                WHERE id = ?
            ");
            
            $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            // Insert new cart item
            $stmt = $pdo->prepare("
                INSERT INTO cart (user_id, product_id, quantity) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([$userId, $productId, $quantity]);
        }
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Add to cart failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update cart item quantity
 *
 * @param int $userId User ID
 * @param int $productId Product ID
 * @param int $quantity New quantity
 * @return bool Success status
 */
function updateCartQuantity($userId, $productId, $quantity) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            UPDATE cart 
            SET quantity = ? 
            WHERE user_id = ? AND product_id = ?
        ");
        
        $stmt->execute([$quantity, $userId, $productId]);
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Update cart failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove item from cart
 *
 * @param int $userId User ID
 * @param int $productId Product ID
 * @return bool Success status
 */
function removeFromCart($userId, $productId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            DELETE FROM cart 
            WHERE user_id = ? AND product_id = ?
        ");
        
        $stmt->execute([$userId, $productId]);
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Remove from cart failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Clear all items from user's cart
 *
 * @param int $userId User ID
 * @return bool Success status
 */
function clearCart($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            DELETE FROM cart 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Clear cart failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get cart items for a user
 *
 * @param int $userId User ID
 * @return array Cart items with product details
 */
function getCartItems($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, p.name, p.price, p.viscosity, p.type, p.quantity as stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get cart items failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Calculate total cost of items in cart
 *
 * @param int $userId User ID
 * @return float Cart total
 */
function calculateCartTotal($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT SUM(c.quantity * p.price) as total 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        // Log error
        error_log("Calculate cart total failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Place a new order
 *
 * @param int $userId User ID
 * @param string $shippingAddress Shipping address
 * @param string $paymentMethod Payment method
 * @param float $totalAmount Total order amount
 * @return array Result with success status and order ID or error message
 */
function placeOrder($userId, $shippingAddress, $paymentMethod, $totalAmount) {
    try {
        $pdo = getPDO();
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, shipping_address, payment_method, total_amount) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$userId, $shippingAddress, $paymentMethod, $totalAmount]);
        $orderId = $pdo->lastInsertId();
        
        // Get cart items
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();
        
        // Insert order items and update inventory
        $stmtOrderItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmtUpdateProduct = $pdo->prepare("
            UPDATE products 
            SET quantity = quantity - ? 
            WHERE id = ? AND quantity >= ?
        ");
        
        foreach ($cartItems as $item) {
            // Insert order item
            $stmtOrderItem->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Update product inventory
            $stmtUpdateProduct->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);
            
            // Check if inventory update was successful
            if ($stmtUpdateProduct->rowCount() == 0) {
                // Product is out of stock, rollback transaction
                $pdo->rollBack();
                
                return [
                    'success' => false,
                    'message' => 'Some products are out of stock. Please update your cart.'
                ];
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'success' => true,
            'order_id' => $orderId
        ];
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        
        // Log error
        error_log("Place order failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Order placement failed. Please try again later.'
        ];
    }
}

/**
 * Get recent orders for a user
 *
 * @param int $userId User ID
 * @param int $limit Maximum number of orders to return
 * @return array Orders
 */
function getRecentOrders($userId, $limit = 5) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT o.id as order_id, o.order_date, o.total_amount as total, o.status,
                   COUNT(oi.id) as item_count
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.order_date DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $limit]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get recent orders failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total number of orders for a user
 *
 * @param int $userId User ID
 * @return int Order count
 */
function getUserOrderCount($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get order count failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get count of active orders for a user
 *
 * @param int $userId User ID
 * @return int Active order count
 */
function getActiveOrderCount($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE user_id = ? AND status IN ('pending', 'processing', 'shipped')
        ");
        
        $stmt->execute([$userId]);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get active order count failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get count of completed orders for a user
 *
 * @param int $userId User ID
 * @return int Completed order count
 */
function getCompletedOrderCount($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE user_id = ? AND status = 'delivered'
        ");
        
        $stmt->execute([$userId]);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get completed order count failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get total amount spent by a user
 *
 * @param int $userId User ID
 * @return float Total amount
 */
function getUserTotalSpent($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT SUM(total_amount) 
            FROM orders 
            WHERE user_id = ? AND status != 'cancelled'
        ");
        
        $stmt->execute([$userId]);
        
        return (float)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get total spent failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get CSS class for order status
 *
 * @param string $status Order status
 * @return string CSS class
 */
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Get products with low stock
 *
 * @return array Low stock products
 */
function getLowStockItems() {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->query("
            SELECT id as product_id, name, quantity, min_stock 
            FROM products 
            WHERE quantity <= min_stock 
            ORDER BY quantity ASC
        ");
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get low stock items failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get inventory with filtering and pagination
 *
 * @param string $search Search term
 * @param string $category Category ID
 * @param string $sort Sort order
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array Inventory items
 */
function getInventory($search = '', $category = '', $sort = 'name_asc', $page = 1, $perPage = 15) {
    try {
        $pdo = getPDO();
        
        $params = [];
        $sql = "
            SELECT p.*, c.name as category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE 1=1
        ";
        
        // Add search condition
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add category condition
        if (!empty($category)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        // Add sorting
        switch ($sort) {
            case 'name_desc':
                $sql .= " ORDER BY p.name DESC";
                break;
            case 'quantity_asc':
                $sql .= " ORDER BY p.quantity ASC";
                break;
            case 'quantity_desc':
                $sql .= " ORDER BY p.quantity DESC";
                break;
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            default: // name_asc
                $sql .= " ORDER BY p.name ASC";
                break;
        }
        
        // Add pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get inventory failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of inventory items with filters applied
 *
 * @param string $search Search term
 * @param string $category Category ID
 * @return int Total count
 */
function getInventoryCount($search = '', $category = '') {
    try {
        $pdo = getPDO();
        
        $params = [];
        $sql = "
            SELECT COUNT(*) 
            FROM products p 
            WHERE 1=1
        ";
        
        // Add search condition
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Add category condition
        if (!empty($category)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get inventory count failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Update product inventory
 *
 * @param int $productId Product ID
 * @param int $quantity New quantity
 * @param int $minStock New minimum stock level
 * @return array Result with success status and message
 */
function updateProductInventory($productId, $quantity, $minStock) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            UPDATE products 
            SET quantity = ?, min_stock = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$quantity, $minStock, $productId]);
        
        return [
            'success' => true,
            'message' => 'Inventory updated successfully'
        ];
    } catch (PDOException $e) {
        // Log error
        error_log("Update inventory failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Inventory update failed. Please try again later.'
        ];
    }
}

/**
 * Add new product
 *
 * @param string $name Product name
 * @param int $categoryId Category ID
 * @param string $description Description
 * @param float $price Price
 * @param int $quantity Initial quantity
 * @param int $minStock Minimum stock level
 * @param string $viscosity Viscosity rating
 * @param string $type Oil type
 * @param float $volume Volume in liters
 * @param string $apiRating API rating
 * @param string $vehicleType Vehicle type
 * @return array Result with success status and message or product ID
 */
function addNewProduct($name, $categoryId, $description, $price, $quantity, $minStock, $viscosity, $type, $volume, $apiRating, $vehicleType) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, category_id, description, price, quantity, min_stock, 
                viscosity, type, volume, api_rating, vehicle_type
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name, $categoryId, $description, $price, $quantity, $minStock, 
            $viscosity, $type, $volume, $apiRating, $vehicleType
        ]);
        
        return [
            'success' => true,
            'product_id' => $pdo->lastInsertId(),
            'message' => 'Product added successfully'
        ];
    } catch (PDOException $e) {
        // Log error
        error_log("Add product failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Failed to add product. Please try again later.'
        ];
    }
}

/**
 * Get detailed order information
 *
 * @param int $orderId Order ID
 * @param int $userId User ID (for security check)
 * @return array|false Order details or false if not found or unauthorized
 */
function getOrderDetails($orderId, $userId = null) {
    try {
        $pdo = getPDO();
        
        // Build query based on whether a user ID is provided (for security)
        if ($userId) {
            $stmt = $pdo->prepare("
                SELECT o.*, u.full_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ? AND o.user_id = ?
            ");
            
            $stmt->execute([$orderId, $userId]);
        } else {
            // Admin view (no user restriction)
            $stmt = $pdo->prepare("
                SELECT o.*, u.full_name, u.email, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?
            ");
            
            $stmt->execute([$orderId]);
        }
        
        $order = $stmt->fetch();
        
        if (!$order) {
            return false;
        }
        
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.viscosity, p.type 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll();
        
        return $order;
    } catch (PDOException $e) {
        // Log error
        error_log("Get order details failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all orders for a user with pagination
 *
 * @param int $userId User ID
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array Orders
 */
function getUserOrders($userId, $page = 1, $perPage = 10) {
    try {
        $pdo = getPDO();
        
        $offset = ($page - 1) * $perPage;
        
        $stmt = $pdo->prepare("
            SELECT o.id as order_id, o.order_date, o.total_amount as total, o.status, o.shipping_address,
                   COUNT(oi.id) as item_count
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.order_date DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$userId, $perPage, $offset]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get user orders failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of orders for a user
 *
 * @param int $userId User ID
 * @return int Order count
 */
function getUserOrdersCount($userId) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT id) 
            FROM orders 
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get user orders count failed: " . $e->getMessage());
        return 0;
    }
}

/**
 * Update order status
 *
 * @param int $orderId Order ID
 * @param string $status New status
 * @return array Result with success status and message
 */
function updateOrderStatus($orderId, $status) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([$status, $orderId]);
        
        return [
            'success' => true,
            'message' => 'Order status updated successfully'
        ];
    } catch (PDOException $e) {
        // Log error
        error_log("Update order status failed: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Update failed. Please try again later.'
        ];
    }
}

/**
 * Get sales report data by date range
 *
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @return array Sales data
 */
function getSalesReport($startDate, $endDate) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT 
                DATE(o.order_date) as date,
                COUNT(DISTINCT o.id) as order_count,
                SUM(o.total_amount) as total_sales
            FROM orders o
            WHERE o.order_date BETWEEN ? AND ? AND o.status != 'cancelled'
            GROUP BY DATE(o.order_date)
            ORDER BY date ASC
        ");
        
        $stmt->execute([$startDate, $endDate]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get sales report failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product sales report data
 *
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @param int $limit Maximum number of products to return
 * @return array Product sales data
 */
function getProductSalesReport($startDate, $endDate, $limit = 10) {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.name,
                p.viscosity,
                p.type,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.order_date BETWEEN ? AND ? AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_revenue DESC
            LIMIT ?
        ");
        
        $stmt->execute([$startDate, $endDate, $limit]);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get product sales report failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get inventory value report
 *
 * @return array Inventory value data
 */
function getInventoryValueReport() {
    try {
        $pdo = getPDO();
        
        $stmt = $pdo->query("
            SELECT 
                c.name as category_name,
                COUNT(p.id) as product_count,
                SUM(p.quantity) as total_items,
                SUM(p.quantity * p.price) as total_value
            FROM products p
            JOIN categories c ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY total_value DESC
        ");
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get inventory value report failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get orders for admin with filtering and pagination
 *
 * @param string $status Filter by status
 * @param string $search Search term
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array Orders
 */
function getAdminOrders($status = '', $search = '', $page = 1, $perPage = 20) {
    try {
        $pdo = getPDO();
        
        $params = [];
        $sql = "
            SELECT o.id as order_id, o.order_date, o.total_amount as total, o.status,
                   u.id as user_id, u.full_name, u.email,
                   COUNT(oi.id) as item_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            WHERE 1=1
        ";
        
        // Add status filter
        if (!empty($status)) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        
        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (o.id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.order_date DESC";
        
        // Add pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log error
        error_log("Get admin orders failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of orders for admin with filters
 *
 * @param string $status Filter by status
 * @param string $search Search term
 * @return int Order count
 */
function getAdminOrdersCount($status = '', $search = '') {
    try {
        $pdo = getPDO();
        
        $params = [];
        $sql = "
            SELECT COUNT(DISTINCT o.id)
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE 1=1
        ";
        
        // Add status filter
        if (!empty($status)) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }
        
        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (o.id LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Log error
        error_log("Get admin orders count failed: " . $e->getMessage());
        return 0;
    }
}
?>
