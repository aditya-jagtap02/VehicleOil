<?php
// Database configuration
$dbConfig = [
    'host' => 'db.fr-pari1.bengt.wasmernet.com',
    'user' => '9783744976708000d247959185f9',
    'password' => '068a9783-7449-7869-8000-d1e17dc4ac90',
    'dbname' => 'dbSM4UCuf5LTNSLMKPv4xNif',
    'charset' => 'utf8mb4'
];

function getPDO() {
    global $dbConfig;
    
    static $pdo;
    
    if (!$pdo) {
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
        } catch (PDOException $e) {
            // Log error
            error_log("Database connection failed: " . $e->getMessage());
            
            // Display user-friendly message
            die("Database connection failed. Please try again later or contact support.");
        }
    }
    
    return $pdo;
}

// Initialize database tables if they don't exist
function initDatabase() {
    $pdo = getPDO();
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Create categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            category_id INT NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            min_stock INT NOT NULL DEFAULT 5,
            viscosity VARCHAR(20) NOT NULL,
            type VARCHAR(50) NOT NULL,
            volume DECIMAL(5,2) NOT NULL,
            api_rating VARCHAR(50),
            vehicle_type VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ");
    
    // Create cart table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY user_product (user_id, product_id)
        )
    ");
    
    // Create orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            total_amount DECIMAL(10,2) NOT NULL,
            shipping_address TEXT NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
            notes TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create order_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");
    
    // Insert default categories if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $categories = [
            ['name' => 'Synthetic Oil', 'description' => 'Fully synthetic motor oils for maximum performance and protection.'],
            ['name' => 'Semi-Synthetic Oil', 'description' => 'Blend of synthetic and conventional base oils for balanced performance and value.'],
            ['name' => 'Conventional Oil', 'description' => 'Traditional mineral-based motor oils for standard engine protection.'],
            ['name' => 'High Mileage Oil', 'description' => 'Specially formulated for vehicles with over 75,000 miles.'],
            ['name' => 'Diesel Engine Oil', 'description' => 'Specifically designed for diesel engines.']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->execute([$category['name'], $category['description']]);
        }
    }
    
    // Insert default admin user if no users exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, phone, address, role) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['admin', 'admin@example.com', $passwordHash, 'System Administrator', '555-123-4567', '123 Admin St, Admin City, AC 12345', 'admin']);
    }
    
    // Insert sample products if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $products = [
            [
                'name' => 'Premium Synthetic 5W-30',
                'category_id' => 1,
                'description' => 'High-performance fully synthetic motor oil providing excellent protection against engine wear, even in extreme conditions. Suitable for modern passenger cars and light trucks.',
                'price' => 39.99,
                'quantity' => 100,
                'min_stock' => 20,
                'viscosity' => '5W-30',
                'type' => 'Synthetic',
                'volume' => 5.0,
                'api_rating' => 'SN Plus',
                'vehicle_type' => 'Passenger Car'
            ],
            [
                'name' => 'Semi-Synthetic 10W-40',
                'category_id' => 2,
                'description' => 'Blend of synthetic and conventional base oils providing good engine protection and performance at a value price. Suitable for older vehicles and moderate driving conditions.',
                'price' => 24.99,
                'quantity' => 150,
                'min_stock' => 30,
                'viscosity' => '10W-40',
                'type' => 'Semi-Synthetic',
                'volume' => 4.0,
                'api_rating' => 'SN',
                'vehicle_type' => 'Passenger Car'
            ],
            [
                'name' => 'Conventional 15W-40',
                'category_id' => 3,
                'description' => 'Traditional mineral-based motor oil providing reliable engine protection for older vehicles. Good for standard driving conditions and regular oil change intervals.',
                'price' => 19.99,
                'quantity' => 120,
                'min_stock' => 25,
                'viscosity' => '15W-40',
                'type' => 'Conventional',
                'volume' => 5.0,
                'api_rating' => 'SL',
                'vehicle_type' => 'Passenger Car'
            ],
            [
                'name' => 'High Mileage 10W-30',
                'category_id' => 4,
                'description' => 'Specially formulated for vehicles with over 75,000 miles. Helps reduce oil consumption, prevent leaks, and minimize engine wear in older engines.',
                'price' => 29.99,
                'quantity' => 80,
                'min_stock' => 15,
                'viscosity' => '10W-30',
                'type' => 'High-Mileage',
                'volume' => 5.0,
                'api_rating' => 'SN',
                'vehicle_type' => 'Passenger Car'
            ],
            [
                'name' => 'Diesel Engine Oil 15W-40',
                'category_id' => 5,
                'description' => 'Heavy-duty diesel engine oil designed for modern diesel engines in trucks and commercial vehicles. Provides excellent protection against soot, wear, and deposits.',
                'price' => 44.99,
                'quantity' => 60,
                'min_stock' => 15,
                'viscosity' => '15W-40',
                'type' => 'Diesel',
                'volume' => 5.0,
                'api_rating' => 'CK-4',
                'vehicle_type' => 'Heavy Duty'
            ],
            [
                'name' => 'Synthetic Blend 5W-20',
                'category_id' => 2,
                'description' => 'Semi-synthetic oil blend offering good cold-weather performance and fuel economy. Ideal for newer passenger cars requiring lower viscosity oils.',
                'price' => 27.99,
                'quantity' => 90,
                'min_stock' => 20,
                'viscosity' => '5W-20',
                'type' => 'Semi-Synthetic',
                'volume' => 5.0,
                'api_rating' => 'SN Plus',
                'vehicle_type' => 'Passenger Car'
            ],
            [
                'name' => 'Racing Formula 0W-40',
                'category_id' => 1,
                'description' => 'Premium fully synthetic oil designed for high-performance and racing applications. Provides maximum protection under extreme heat and high RPM conditions.',
                'price' => 59.99,
                'quantity' => 40,
                'min_stock' => 10,
                'viscosity' => '0W-40',
                'type' => 'Synthetic',
                'volume' => 4.0,
                'api_rating' => 'SN',
                'vehicle_type' => 'Performance'
            ],
            [
                'name' => 'Motorcycle Oil 10W-50',
                'category_id' => 1,
                'description' => 'Specialized synthetic oil for high-performance motorcycles. Formulated to protect motorcycle engines operating at high temperatures and RPMs.',
                'price' => 34.99,
                'quantity' => 50,
                'min_stock' => 10,
                'viscosity' => '10W-50',
                'type' => 'Synthetic',
                'volume' => 1.0,
                'api_rating' => 'SN',
                'vehicle_type' => 'Motorcycle'
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, category_id, description, price, quantity, min_stock, viscosity, type, volume, api_rating, vehicle_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($products as $product) {
            $stmt->execute([
                $product['name'],
                $product['category_id'],
                $product['description'],
                $product['price'],
                $product['quantity'],
                $product['min_stock'],
                $product['viscosity'],
                $product['type'],
                $product['volume'],
                $product['api_rating'],
                $product['vehicle_type']
            ]);
        }
    }
}

// Initialize database tables
initDatabase();
?>
