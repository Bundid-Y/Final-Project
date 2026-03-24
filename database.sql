-- =============================================
-- Database: koch_tnb_system
-- สำหรับระบบจัดการ KOCH & TNB
-- =============================================

-- สร้าง Database
CREATE DATABASE IF NOT EXISTS koch_tnb_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE koch_tnb_system;

-- =============================================
-- ตารางหลัก
-- =============================================

-- 1. ตารางบริษัท (Companies)
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL, -- 'KOCH', 'TNB'
    description TEXT,
    logo_url VARCHAR(255),
    primary_color VARCHAR(7), -- hex color
    secondary_color VARCHAR(7), -- hex color
    website_url VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. ตารางผู้ใช้ (Users) - ร่วมกันทั้งสองบริษัท
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- bcrypt
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    nick_name VARCHAR(50),
    phone VARCHAR(20),
    role ENUM('super_admin', 'admin', 'manager', 'user') DEFAULT 'user',
    company_id INT NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_company_role (company_id, role)
);

-- 3. ตารางสิทธิ์ผู้ใช้ (User Permissions)
CREATE TABLE user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_name VARCHAR(100) NOT NULL,
    permission_value ENUM('read', 'write', 'delete', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_permission (user_id, permission_name)
);

-- 4. ตารางใบเสนอราคา KOCH (Quotations)
CREATE TABLE koch_quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_number VARCHAR(50) UNIQUE NOT NULL, -- KOCH-2024-0001
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    nick_name VARCHAR(50),
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    product_type ENUM('Engine Parts', 'Body Parts', 'Suspension and Transmission', 'Electrical Parts', 'Car Accessories', 'Other Auto Parts') NOT NULL,
    weight DECIMAL(10,2) NOT NULL,
    box_length DECIMAL(10,2),
    box_width DECIMAL(10,2),
    box_height DECIMAL(10,2),
    quantity INT DEFAULT 1,
    special_requirements TEXT,
    status ENUM('pending', 'quoted', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    quoted_price DECIMAL(10,2),
    quoted_by INT, -- admin who quoted
    quoted_at TIMESTAMP NULL,
    approved_by INT, -- admin who approved
    approved_at TIMESTAMP NULL,
    notes TEXT,
    attachment_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quoted_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_quotation_number (quotation_number)
);

-- 5. ตารางใบเสนอราคา TNB (Transportation Requests)
CREATE TABLE tnb_quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL, -- TNB-2024-0001
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    nick_name VARCHAR(50),
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    service_type ENUM('Container Transport', 'Domestic Transport', 'Import Export', 'Warehouse Shuttle', 'Truck Parking', 'Last Mile Delivery') NOT NULL,
    cargo_weight DECIMAL(10,2) NOT NULL,
    pickup_address TEXT,
    delivery_address TEXT,
    pickup_date DATE,
    delivery_date DATE,
    truck_type VARCHAR(100),
    special_requirements TEXT,
    status ENUM('pending', 'quoted', 'approved', 'assigned', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
    quoted_price DECIMAL(10,2),
    quoted_by INT,
    quoted_at TIMESTAMP NULL,
    approved_by INT,
    approved_at TIMESTAMP NULL,
    driver_id INT,
    truck_id INT,
    tracking_number VARCHAR(100),
    pickup_time TIMESTAMP NULL,
    delivery_time TIMESTAMP NULL,
    notes TEXT,
    attachment_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quoted_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_request_number (request_number),
    INDEX idx_tracking (tracking_number)
);

-- 6. ตารางรถบรรทุก (Trucks) - สำหรับ TNB
CREATE TABLE trucks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    truck_number VARCHAR(50) UNIQUE NOT NULL,
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    truck_type ENUM('Pickup Jumbo', '6 Wheel', '6 Wheel Trailer', '10 Wheel Trailer', 'Trailer Head') NOT NULL,
    capacity DECIMAL(10,2), -- น้ำหนักบรรทุก (ตัน)
    status ENUM('available', 'in_use', 'maintenance', 'retired') DEFAULT 'available',
    driver_id INT,
    insurance_expiry DATE,
    registration_expiry DATE,
    last_maintenance DATE,
    next_maintenance DATE,
    fuel_type ENUM('diesel', 'benzene', 'electric') DEFAULT 'diesel',
    fuel_consumption DECIMAL(5,2), -- ลิตร/กิโลเมตร
    current_location VARCHAR(255),
    gps_enabled BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id),
    INDEX idx_truck_number (truck_number),
    INDEX idx_status (status),
    INDEX idx_driver (driver_id)
);

-- 7. ตารางพนักงานขับรถ (Drivers) - สำหรับ TNB
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- link to users table
    driver_license VARCHAR(50) UNIQUE NOT NULL,
    license_expiry DATE,
    license_type ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    experience_years INT DEFAULT 0,
    accident_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    current_truck_id INT,
    home_address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    medical_checkup_date DATE,
    next_medical_checkup DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (current_truck_id) REFERENCES trucks(id),
    INDEX idx_user (user_id),
    INDEX idx_license (driver_license),
    INDEX idx_status (status)
);

-- 8. ตารางการติดตาม (Tracking) - สำหรับ TNB
CREATE TABLE tracking_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'at_checkpoint', 'delivered', 'cancelled') NOT NULL,
    location VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    driver_notes TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT, -- driver or admin
    FOREIGN KEY (quotation_id) REFERENCES tnb_quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_quotation (quotation_id),
    INDEX idx_timestamp (timestamp)
);

-- 9. ตาราง Activity Logs
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_action (user_id, action),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);

-- 10. ตารางการแจ้งเตือน (Notifications)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    related_table VARCHAR(50),
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    is_email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- 11. ตาราง Email Templates
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    html_content TEXT NOT NULL,
    text_content TEXT,
    variables JSON, -- ตัวแปรที่ใช้ใน template
    company_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_name (name),
    INDEX idx_company (company_id)
);

-- 12. ตาราง Slider Content (สำหรับหน้าแรก)
CREATE TABLE slider_contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    image_url VARCHAR(255),
    button_text VARCHAR(100),
    button_url VARCHAR(255),
    slide_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    language_code VARCHAR(5) DEFAULT 'th', -- th, en, zh, jp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_company_order (company_id, slide_order),
    INDEX idx_language (language_code)
);

-- 13. ตาราง Partners/Logos
CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(255),
    website_url VARCHAR(255),
    partner_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_company_order (company_id, partner_order)
);

-- 14. ตาราง Products (สำหรับ KOCH)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    image_url VARCHAR(255),
    price DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    language_code VARCHAR(5) DEFAULT 'th',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_language (language_code),
    INDEX idx_order (display_order)
);

-- 15. ตาราง System Settings
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    company_id INT, -- NULL for global settings
    is_editable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_key (setting_key),
    INDEX idx_company (company_id)
);

-- =============================================
-- Insert Default Data
-- =============================================

-- Insert Companies
INSERT INTO companies (name, code, description, primary_color, secondary_color) VALUES
('KOCH Packaging and Packing Services Co.,Ltd', 'KOCH', 'บริการบรรจุภัณฑ์ครบวงจร', '#2563eb', '#1e40af'),
('TNB Logistics', 'TNB', 'บริการขนส่งและโลจิสติกส์', '#0d2d6b', '#325662');

-- Insert Default Super Admin
INSERT INTO users (username, email, password_hash, first_name, last_name, role, company_id) VALUES
('admin', 'admin@koch-tnb.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'super_admin', 1);

-- Insert Default Email Templates
INSERT INTO email_templates (name, subject, html_content, company_id) VALUES
('koch_quotation_received', 'KOCH - ได้รับใบเสนอราคาของคุณแล้ว', '<h3>เรียน {first_name} {last_name}</h3><p>ขอบคุณที่สนใจบริการของ KOCH เราได้รับใบเสนอราคา #{quotation_number} แล้ว</p>', 1),
('tnb_quotation_received', 'TNB - ได้รับคำขอบริการของคุณแล้ว', '<h3>เรียน {first_name} {last_name}</h3><p>ขอบคุณที่สนใจบริการของ TNB เราได้รับคำขอ #{request_number} แล้ว</p>', 2);

-- Insert Default System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('company_auto_switch', 'true', 'boolean', 'สลับบริษัทอัตโนมัติตามผู้ใช้'),
('email_smtp_host', 'localhost', 'string', 'SMTP Server'),
('email_smtp_port', '587', 'number', 'SMTP Port'),
('log_retention_days', '90', 'number', 'จำนวนวันที่เก็บ log'),
('max_file_upload_size', '10485760', 'number', 'ขนาดไฟล์สูงสุด (bytes)');

-- =============================================
-- Create Views for Easy Reporting
-- =============================================

-- View สำหรับสถิติผู้ใช้
CREATE VIEW user_stats AS
SELECT 
    c.name as company_name,
    u.role,
    COUNT(*) as user_count,
    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_count,
    COUNT(CASE WHEN DATE(u.created_at) = CURDATE() THEN 1 END) as today_count
FROM users u
JOIN companies c ON u.company_id = c.id
GROUP BY c.name, u.role;

-- View สำหรับสถิติใบเสนอราคา KOCH
CREATE VIEW koch_quotation_stats AS
SELECT 
    DATE(created_at) as date,
    status,
    COUNT(*) as count,
    AVG(quoted_price) as avg_price
FROM koch_quotations
GROUP BY DATE(created_at), status;

-- View สำหรับสถิติการขนส่ง TNB
CREATE VIEW tnb_transportation_stats AS
SELECT 
    DATE(created_at) as date,
    status,
    service_type,
    COUNT(*) as count,
    AVG(quoted_price) as avg_price
FROM tnb_quotations
GROUP BY DATE(created_at), status, service_type;

-- =============================================
-- Stored Procedures
-- =============================================

DELIMITER //

-- Procedure สำหรับสร้างเลขที่ใบเสนอราคา KOCH
CREATE PROCEDURE generate_koch_quotation_number()
BEGIN
    DECLARE next_num INT;
    DECLARE quotation_no VARCHAR(50);
    
    SELECT COUNT(*) + 1 INTO next_num 
    FROM koch_quotations 
    WHERE YEAR(created_at) = YEAR(CURDATE());
    
    SET quotation_no = CONCAT('KOCH-', YEAR(CURDATE()), '-', LPAD(next_num, 4, '0'));
    SELECT quotation_no;
END //

-- Procedure สำหรับสร้างเลขที่คำขอ TNB
CREATE PROCEDURE generate_tnb_request_number()
BEGIN
    DECLARE next_num INT;
    DECLARE request_no VARCHAR(50);
    
    SELECT COUNT(*) + 1 INTO next_num 
    FROM tnb_quotations 
    WHERE YEAR(created_at) = YEAR(CURDATE());
    
    SET request_no = CONCAT('TNB-', YEAR(CURDATE()), '-', LPAD(next_num, 4, '0'));
    SELECT request_no;
END //

-- Procedure สำหรับสร้าง tracking number
CREATE PROCEDURE generate_tracking_number()
BEGIN
    DECLARE tracking_no VARCHAR(100);
    
    SET tracking_no = CONCAT('TNB', DATE_FORMAT(NOW(), '%Y%m%d'), FLOOR(RAND() * 10000));
    SELECT tracking_no;
END //

DELIMITER ;

-- =============================================
-- Triggers
-- =============================================

-- Trigger สำหรับ log การเปลี่ยนแปลงข้อมูล
DELIMITER //
CREATE TRIGGER log_koch_quotation_changes
AFTER UPDATE ON koch_quotations
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values)
    VALUES (
        COALESCE(@current_user_id, 0),
        'UPDATE',
        'koch_quotations',
        NEW.id,
        JSON_OBJECT(
            'status', OLD.status,
            'quoted_price', OLD.quoted_price
        ),
        JSON_OBJECT(
            'status', NEW.status,
            'quoted_price', NEW.quoted_price
        )
    );
END //
DELIMITER ;

-- =============================================
-- Indexes สำหรับ Performance
-- =============================================

-- Composite indexes สำหรับการค้นหาที่ซับซ้อน
CREATE INDEX idx_koch_quotations_user_status_date ON koch_quotations(user_id, status, created_at);
CREATE INDEX idx_tnb_quotations_user_status_date ON tnb_quotations(user_id, status, created_at);
CREATE INDEX idx_activity_logs_user_date ON activity_logs(user_id, created_at);
CREATE INDEX idx_notifications_user_unread_date ON notifications(user_id, is_read, created_at);

-- =============================================
-- Security Considerations
-- =============================================

-- สร้าง Database User สำหรับแต่ละบริษัท (แนะนำสำหรับ production)
-- CREATE USER 'koch_user'@'localhost' IDENTIFIED BY 'secure_password';
-- CREATE USER 'tnb_user'@'localhost' IDENTIFIED BY 'secure_password';
-- GRANT SELECT, INSERT, UPDATE ON koch_tnb_system.* TO 'koch_user'@'localhost';
-- GRANT SELECT, INSERT, UPDATE ON koch_tnb_system.* TO 'tnb_user'@'localhost';

-- =============================================
-- Notes สำหรับการติดตั้ง
-- =============================================

/*
1. สร้าง database: CREATE DATABASE koch_tnb_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
2. นำเข้า SQL นี้: mysql -u root -p koch_tnb_system < database.sql
3. ตั้งค่า connection ใน PHP:
   - Host: localhost
   - Database: koch_tnb_system
   - Charset: utf8mb4
4. อย่าลืมเปลี่ยนรหัสผ่าน admin หลังจากติดตั้ง
5. ตั้งค่า SMTP สำหรับการส่งอีเมล
6. ตั้งค่า folder permissions สำหรับ upload files
*/
