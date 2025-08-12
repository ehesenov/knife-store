<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'knife_store');

// Upload configuration
define('UPLOAD_DIR', 'assets/images/uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Create connection
function getConnection() {
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    
    // Set charset to UTF-8
    $connection->set_charset("utf8mb4");
    
    return $connection;
}

// Function to handle file upload
function uploadImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Fayl ölçüsü çox böyükdür. Maksimum 5MB');
    }
    
    // Check file extension
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        throw new Exception('Yalnız JPG, PNG, GIF, WEBP faylları qəbul edilir');
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0777, true)) {
            throw new Exception('Upload qovluğu yaradıla bilmədi');
        }
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;
    } else {
        throw new Exception('Fayl yüklənərkən xəta baş verdi');
    }
}

// Function to get all products
function getAllProducts($limit = null) {
    $conn = getConnection();
    
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $result = $conn->query($sql);
    $products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $conn->close();
    return $products;
}

// Function to get featured products
function getFeaturedProducts($limit = 8) {
    $conn = getConnection();
    
    $sql = "SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT " . intval($limit);
    $result = $conn->query($sql);
    $products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $conn->close();
    return $products;
}

// Function to get product by ID
function getProductById($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $product = null;
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    
    $stmt->close();
    $conn->close();
    
    return $product;
}

// Function to add new product
function addProduct($name, $price, $discount_price, $image, $description, $category, $subcategory, $rating, $is_featured, $stock_quantity, $all_images) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("INSERT INTO products (name, price, discount_price, image, description, category, subcategory, rating, is_featured, stock_quantity, all_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddssssdbis", $name, $price, $discount_price, $image, $description, $category, $subcategory, $rating, $is_featured, $stock_quantity, $all_images);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Function to update product
function updateProduct($id, $name, $price, $discount_price, $image, $description, $category, $subcategory, $rating, $is_featured, $stock_quantity, $all_images) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, discount_price = ?, image = ?, description = ?, category = ?, subcategory = ?, rating = ?, is_featured = ?, stock_quantity = ?, all_images = ? WHERE id = ?");
    $stmt->bind_param("sddssssdbisi", $name, $price, $discount_price, $image, $description, $category, $subcategory, $rating, $is_featured, $stock_quantity, $all_images, $id);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Function to delete product
function deleteProduct($id) {
    $conn = getConnection();
    
    // First get the product to delete its images
    $product = getProductById($id);
    if ($product) {
        // Delete main image if it's uploaded
        if ($product['image'] && strpos($product['image'], 'uploads/') !== false) {
            if (file_exists($product['image'])) {
                unlink($product['image']);
            }
        }
        
        // Delete all images if they exist
        if (!empty($product['all_images'])) {
            $images = json_decode($product['all_images'], true);
            if ($images) {
                foreach ($images as $img) {
                    if (strpos($img, 'uploads/') !== false && file_exists($img)) {
                        unlink($img);
                    }
                }
            }
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Function to get products by category
function getProductsByCategory($category, $limit = null) {
    $conn = getConnection();
    
    $sql = "SELECT * FROM products WHERE category = ? ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $products;
}

// Function to get products by category and subcategory
function getProductsByCategoryAndSubcategory($category, $subcategory, $limit = null) {
    $conn = getConnection();
    
    $sql = "SELECT * FROM products WHERE category = ? AND subcategory = ? ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $category, $subcategory);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $products = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $products;
}

// Function to add product to cart
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    return true;
}

// Function to remove product from cart
function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    return false;
}

// Function to get cart items
function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $cart_items = [];
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($product_id);
        if ($product) {
            $product['cart_quantity'] = $quantity;
            $cart_items[] = $product;
        }
    }
    
    return $cart_items;
}

// Function to get cart total
function getCartTotal() {
    $cart_items = getCartItems();
    $total = 0;
    
    foreach ($cart_items as $item) {
        $price = $item['discount_price'] ?: $item['price'];
        $total += $price * $item['cart_quantity'];
    }
    
    return $total;
}

// Function to get cart count
function getCartCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}

// Function to clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Function to authenticate admin (simple hardcoded for now)
function authenticateAdmin($username, $password) {
    // For now, hardcoded credentials
    return ($username === 'admin' && $password === 'admin123');
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>