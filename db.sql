-- Create database
CREATE DATABASE IF NOT EXISTS knife_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE knife_store;

-- Create products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO products (name, price, image, description, category) VALUES
('Gyuto Bıçaq', 99.00, 'assets/images/products/bicaq-1.jpg', 'Yüksək keyfiyyətli aşpaz bıçağı', 'Aşpaz Bıçaqları'),
('Santoku Bıçaq', 85.00, 'assets/images/products/bicaq-1.jpg', 'Çox məqsədli aşpaz bıçağı', 'Aşpaz Bıçaqları'),
('Nakiri Bıçaq', 75.00, 'assets/images/products/bicaq-1.jpg', 'Tərəvəz doğramaq üçün ideal', 'Tərəvəz bıçaqları'),
('Yanagiba Bıçaq', 120.00, 'assets/images/products/bicaq-1.jpg', 'Sushi üçün peşəkar bıçaq', 'Balıq bıçaqları'),
('Deba Bıçaq', 95.00, 'assets/images/products/bicaq-1.jpg', 'Balıq təmizləmək üçün', 'Balıq bıçaqları'),
('Petty Bıçaq', 45.00, 'assets/images/products/bicaq-1.jpg', 'Kiçik işlər üçün', 'Kiçik Bıçaqlar'),
('Usuba Bıçaq', 110.00, 'assets/images/products/bicaq-1.jpg', 'Nazik tərəvəz doğrama', 'Tərəvəz bıçaqları'),
('Sujihiki Bıçaq', 130.00, 'assets/images/products/bicaq-1.jpg', 'Ət dilimləmək üçün', 'Ət bıçaqları');

-- Create admin users table (for future use)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


-- Enhanced database schema
-- First, add new columns to existing products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS rating DECIMAL(2,1) DEFAULT 0.0;
ALTER TABLE products ADD COLUMN IF NOT EXISTS discount_price DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE products ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE;
ALTER TABLE products ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0;

-- Create uploads directory structure
-- You need to create these folders manually:
-- assets/images/uploads/products/

-- Update the products table structure for better organization
CREATE TABLE IF NOT EXISTS products_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    rating DECIMAL(2,1) DEFAULT 0.0,
    is_featured BOOLEAN DEFAULT FALSE,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- If you want to migrate existing data (run this only once)
-- INSERT INTO products_new (id, name, price, image, description, category, created_at)
-- SELECT id, name, price, image, description, category, created_at FROM products;

-- Then rename tables
-- DROP TABLE products;
-- RENAME TABLE products_new TO products;

-- Or just add columns to existing table (recommended approach):
-- ALTER TABLE products ADD COLUMN IF NOT EXISTS rating DECIMAL(2,1) DEFAULT 0.0;
-- ALTER TABLE products ADD COLUMN IF NOT EXISTS discount_price DECIMAL(10,2) DEFAULT NULL;
-- ALTER TABLE products ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE;
-- ALTER TABLE products ADD COLUMN IF NOT EXISTS stock_quantity INT DEFAULT 0;
-- ALTER TABLE products ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;