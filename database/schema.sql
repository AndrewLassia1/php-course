-- Learning Materials Management System Database Schema
-- Created: 2024
-- Description: Database structure for managing educational materials

-- Create database
CREATE DATABASE IF NOT EXISTS learning_materials;
USE learning_materials;

-- Materials table to store learning resources
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('pdf', 'doc', 'docx') NOT NULL,
    file_size BIGINT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    downloads_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_file_type (file_type),
    INDEX idx_price (price),
    INDEX idx_created_at (created_at),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table for authentication
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123 - change in production)
INSERT INTO admin_users (username, password_hash, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- Download logs table for tracking
CREATE TABLE IF NOT EXISTS download_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    INDEX idx_material_id (material_id),
    INDEX idx_downloaded_at (downloaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample materials for demonstration
INSERT INTO materials (title, description, file_path, file_type, price) VALUES 
('Introduction to Programming', 'A comprehensive guide to programming fundamentals covering variables, functions, and basic algorithms.', 'uploads/intro-programming.pdf', 'pdf', 29.99),
('Advanced Web Development', 'Master modern web development techniques including HTML5, CSS3, JavaScript, and responsive design principles.', 'uploads/advanced-web-dev.pdf', 'pdf', 49.99),
('Database Design Principles', 'Learn essential database design concepts, normalization, and best practices for efficient data management.', 'uploads/database-design.doc', 'doc', 39.99),
('Mobile App Development Guide', 'Complete guide to building mobile applications for iOS and Android platforms using modern frameworks.', 'uploads/mobile-app-guide.docx', 'docx', 59.99),
('Data Structures and Algorithms', 'Comprehensive coverage of essential data structures and algorithms with practical examples and exercises.', 'uploads/data-structures.pdf', 'pdf', 44.99);

-- Create indexes for better performance
CREATE INDEX idx_title_search ON materials(title);
CREATE INDEX idx_description_search ON materials(description(100));