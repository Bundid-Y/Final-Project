-- =============================================
-- Database: koch_tnb_system
-- สำหรับระบบจัดการ KOCH & TNB (ออกแบบตาม UI จริง พร้อมใช้งานใน XAMPP)
-- =============================================

-- สร้าง Database
CREATE DATABASE IF NOT EXISTS koch_tnb_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE koch_tnb_system;

-- =============================================
-- ตารางหลัก (Core Tables)
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
    tax_id VARCHAR(50),
    branch_code VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
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
    status ENUM('active', 'inactive', 'suspended', 'pending_verification') DEFAULT 'pending_verification',
    last_login TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255),
    email_verification_expires TIMESTAMP NULL,
    password_reset_token VARCHAR(255),
    password_reset_expires TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    avatar_url VARCHAR(255),
    department VARCHAR(100),
    position VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_company_role (company_id, role),
    INDEX idx_status (status)
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

-- 4. ตาราง Social Login Accounts
CREATE TABLE social_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL, -- 'facebook', 'google', 'line'
    provider_id VARCHAR(255) NOT NULL, -- social media user ID
    provider_email VARCHAR(255),
    provider_data JSON, -- additional data from provider
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider_user (provider, provider_id),
    INDEX idx_user_provider (user_id, provider)
);

-- 5. ตาราง Login Sessions
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_expires (expires_at)
);

-- =============================================
-- ตารางใบเสนอราคา (Quotations) - ตาม UI จริง
-- =============================================

-- 6. ตารางใบเสนอราคา KOCH (จาก koch/main/quotation.php)
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
    brand VARCHAR(100), -- เพิ่มจาก UI
    packaging_type ENUM('Paper Box', 'Wooden Packaging', 'Plastic Packaging', 'Steel Packaging') NOT NULL,
    box_length DECIMAL(10,2),
    box_width DECIMAL(10,2),
    box_height DECIMAL(10,2),
    quantity INT DEFAULT 1,
    special_requirements TEXT,
    status ENUM('pending', 'quoted', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    quoted_price DECIMAL(10,2),
    quoted_by INT, -- admin who quoted
    quoted_at TIMESTAMP NULL,
    approved_by INT, -- admin who approved
    approved_at TIMESTAMP NULL,
    notes TEXT,
    attachment_url VARCHAR(255),
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    expected_delivery_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quoted_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_quotation_number (quotation_number),
    INDEX idx_status_priority (status, priority),
    INDEX idx_created_date (created_at)
);

-- 7. ตารางใบเสนอราคา TNB (จาก tnb/main/quotation.php)
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
    route VARCHAR(255), -- เพิ่มจาก UI (Origin - Destination)
    vehicle_type ENUM('6 Wheel Truck', '10 Wheel Truck', '6 Wheel Trailer', '10 Wheel Trailer', 'Pickup Truck', 'Container Truck') NOT NULL,
    cargo_width DECIMAL(10,2),
    cargo_length DECIMAL(10,2),
    cargo_height DECIMAL(10,2),
    pickup_address TEXT,
    delivery_address TEXT,
    pickup_date DATE,
    delivery_date DATE,
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
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quoted_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_request_number (request_number),
    INDEX idx_tracking (tracking_number),
    INDEX idx_status_priority (status, priority),
    INDEX idx_service_type (service_type)
);

-- =============================================
-- ตารางการขนส่ง TNB (Transportation)
-- =============================================

-- 8. ตารางรถบรรทุก (Trucks)
CREATE TABLE trucks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    truck_number VARCHAR(50) UNIQUE NOT NULL,
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    truck_type ENUM('Pickup Jumbo', '6 Wheel', '6 Wheel Trailer', '10 Wheel Trailer', 'Trailer Head') NOT NULL,
    capacity DECIMAL(10,2), -- น้ำหนักบรรทุก (ตัน)
    status ENUM('available', 'in_use', 'maintenance', 'retired', 'accident') DEFAULT 'available',
    driver_id INT,
    insurance_expiry DATE,
    registration_expiry DATE,
    last_maintenance DATE,
    next_maintenance DATE,
    fuel_type ENUM('diesel', 'benzene', 'electric') DEFAULT 'diesel',
    fuel_consumption DECIMAL(5,2), -- ลิตร/กิโลเมตร
    current_location VARCHAR(255),
    gps_enabled BOOLEAN DEFAULT FALSE,
    last_service_date DATE,
    next_service_date DATE,
    mileage INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id),
    INDEX idx_truck_number (truck_number),
    INDEX idx_status (status),
    INDEX idx_driver (driver_id),
    INDEX idx_type_status (truck_type, status)
);

-- 9. ตารางพนักงานขับรถ (Drivers)
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- link to users table
    driver_license VARCHAR(50) UNIQUE NOT NULL,
    license_expiry DATE,
    license_type ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    experience_years INT DEFAULT 0,
    accident_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'suspended', 'on_leave') DEFAULT 'active',
    current_truck_id INT,
    home_address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    medical_checkup_date DATE,
    next_medical_checkup DATE,
    hire_date DATE,
    salary DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (current_truck_id) REFERENCES trucks(id),
    INDEX idx_user (user_id),
    INDEX idx_license (driver_license),
    INDEX idx_status (status)
);

-- 10. ตารางการติดตาม (Tracking)
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
    photo_url VARCHAR(255), -- รูปภาพประกอบ
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quotation_id) REFERENCES tnb_quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_quotation (quotation_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_status (status)
);

-- =============================================
-- ตารางเนื้อหาเว็บไซต์ (Website Content)
-- =============================================

-- 11. ตาราง Slider Content (จาก UI จริง)
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
    INDEX idx_language (language_code),
    INDEX idx_active (is_active)
);

-- 12. ตาราง Partners/Logos (จาก UI จริง)
CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(255),
    website_url VARCHAR(255),
    partner_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    partner_type ENUM('customer', 'supplier', 'partner', 'affiliate') DEFAULT 'partner',
    contact_person VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_company_order (company_id, partner_order),
    INDEX idx_type (partner_type)
);

-- 13. ตาราง Products (สำหรับ KOCH - จาก product.php)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('mail', 'corrugated', 'diecut', 'accessory', 'all') DEFAULT 'all',
    image_url VARCHAR(255),
    price DECIMAL(10,2),
    dimensions VARCHAR(100), -- ขนาดสินค้า
    weight DECIMAL(10,2), -- น้ำหนัก
    material VARCHAR(100), -- วัสดุ
    color VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    language_code VARCHAR(5) DEFAULT 'th',
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    min_order_quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_language (language_code),
    INDEX idx_order (display_order),
    INDEX idx_sku (sku),
    INDEX idx_active (is_active)
);

-- 14. ตาราง Truck Types (สำหรับ TNB - จาก UI จริง)
CREATE TABLE truck_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    type_name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    capacity DECIMAL(10,2), -- ความจุ (ตัน)
    weight_limit DECIMAL(10,2), -- น้ำหนักที่รองรับ
    service_price DECIMAL(10,2), -- ราคาบริการ
    service_area VARCHAR(255), -- พื้นที่ให้บริการ
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_company_order (company_id, display_order),
    INDEX idx_active (is_active)
);

-- =============================================
-- ตารางระบบ (System Tables)
-- =============================================

-- 15. ตาราง Activity Logs
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
    INDEX idx_created_at (created_at),
    INDEX idx_action_date (action, created_at)
);

-- 16. ตารางการแจ้งเตือน (Notifications)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'system') DEFAULT 'info',
    related_table VARCHAR(50),
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    is_email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_priority (priority)
);

-- 17. ตาราง Email Templates
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    html_content TEXT NOT NULL,
    text_content TEXT,
    variables JSON, -- ตัวแปรที่ใช้ใน template
    company_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    language_code VARCHAR(5) DEFAULT 'th',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_name (name),
    INDEX idx_company (company_id),
    INDEX idx_language (language_code)
);

-- 18. ตาราง System Settings
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    company_id INT, -- NULL for global settings
    is_editable BOOLEAN DEFAULT TRUE,
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_key (setting_key),
    INDEX idx_company (company_id),
    INDEX idx_category (category)
);

-- 19. ตาราง File Attachments
CREATE TABLE file_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    mime_type VARCHAR(100),
    uploaded_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_file_type (file_type)
);

-- 20. ตาราง API Keys (สำหรับระบบ API)
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    permissions JSON, -- list of permissions
    rate_limit_per_hour INT DEFAULT 1000,
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NULL,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_key (api_key),
    INDEX idx_user_active (user_id, is_active)
);

-- 21. ตาราง Backup Logs
CREATE TABLE backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_type ENUM('full', 'incremental', 'database', 'files') NOT NULL,
    backup_path VARCHAR(500),
    backup_size BIGINT,
    status ENUM('started', 'completed', 'failed', 'cancelled') NOT NULL,
    error_message TEXT,
    started_by INT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (started_by) REFERENCES users(id),
    INDEX idx_type_status (backup_type, status),
    INDEX idx_started_at (started_at)
);

-- 22. ตาราง Maintenance Schedule
CREATE TABLE maintenance_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    maintenance_type ENUM('database', 'system', 'security', 'backup', 'update') NOT NULL,
    scheduled_at TIMESTAMP NOT NULL,
    duration_minutes INT DEFAULT 60,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'failed') DEFAULT 'scheduled',
    performed_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id),
    INDEX idx_scheduled_status (scheduled_at, status),
    INDEX idx_type (maintenance_type)
);

-- =============================================
-- Insert Default Data
-- =============================================

-- Insert Companies
INSERT INTO companies (name, code, description, primary_color, secondary_color, tax_id, branch_code) VALUES
('KOCH Packaging and Packing Services Co.,Ltd', 'KOCH', 'บริการบรรจุภัณฑ์ครบวงจรสำหรับอุตสาหกรรมยานยนต์', '#2563eb', '#1e40af', '0105531001234', 'HEAD'),
('TNB Logistics', 'TNB', 'บริการขนส่งและโลจิสติกส์ครบวงจร', '#0d2d6b', '#325662', '0105532005678', 'HEAD');

-- Insert Default Super Admin
INSERT INTO users (username, email, password_hash, first_name, last_name, role, company_id, status, email_verified, department, position) VALUES
('admin', 'admin@koch-tnb.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'super_admin', 1, 'active', TRUE, 'IT', 'System Administrator'),
('koch_admin', 'admin@koch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KOCH', 'Admin', 'admin', 1, 'active', TRUE, 'Management', 'Administrator'),
('tnb_admin', 'admin@tnb.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'TNB', 'Admin', 'admin', 2, 'active', TRUE, 'Management', 'Administrator');

-- Insert Sample Regular Users
INSERT INTO users (username, email, password_hash, first_name, last_name, role, company_id, status, email_verified, phone, department, position) VALUES
('user1', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User1', 'user', 1, 'active', TRUE, '0812345678', 'Sales', 'Sales Executive'),
('user2', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User2', 'user', 2, 'active', TRUE, '0823456789', 'Operations', 'Logistics Coordinator');

-- Insert Default Permissions
INSERT INTO user_permissions (user_id, permission_name, permission_value) VALUES
(1, 'system.admin', 'admin'),
(1, 'users.manage', 'admin'),
(1, 'quotations.manage', 'admin'),
(1, 'content.manage', 'admin'),
(2, 'users.manage', 'write'),
(2, 'quotations.manage', 'write'),
(2, 'content.manage', 'write'),
(3, 'users.manage', 'write'),
(3, 'quotations.manage', 'write'),
(3, 'content.manage', 'write');

-- Insert Default Email Templates
INSERT INTO email_templates (name, subject, html_content, company_id, language_code) VALUES
('koch_quotation_received', 'KOCH - ได้รับใบเสนอราคาของคุณแล้ว', '<h3>เรียน {first_name} {last_name}</h3><p>ขอบคุณที่สนใจบริการของ KOCH เราได้รับใบเสนอราคา #{quotation_number} แล้ว</p><p>เราจะดำเนินการตรวจสอบและติดต่อกลับภายใน 24 ชั่วโมง</p>', 1, 'th'),
('tnb_quotation_received', 'TNB - ได้รับคำขอบริการของคุณแล้ว', '<h3>เรียน {first_name} {last_name}</h3><p>ขอบคุณที่สนใจบริการของ TNB เราได้รับคำขอ #{request_number} แล้ว</p><p>เราจะดำเนินการตรวจสอบและติดต่อกลับภายใน 24 ชั่วโมง</p>', 2, 'th'),
('email_verification', 'ยืนยันอีเมลของคุณ', '<h3>ยืนยันอีเมลของคุณ</h3><p>กรุณาคลิกลิงก์ด้านล่างเพื่อยืนยันอีเมลของคุณ:</p><p><a href="{verification_url}">ยืนยันอีเมล</a></p>', NULL, 'th'),
('password_reset', 'รีเซ็ตรหัสผ่าน', '<h3>รีเซ็ตรหัสผ่านของคุณ</h3><p>กรุณาคลิกลิงก์ด้านล่างเพื่อรีเซ็ตรหัสผ่าน:</p><p><a href="{reset_url}">รีเซ็ตรหัสผ่าน</a></p>', NULL, 'th');

-- Insert Default System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, category) VALUES
('company_auto_switch', 'true', 'boolean', 'สลับบริษัทอัตโนมัติตามผู้ใช้', 'general'),
('email_smtp_host', 'localhost', 'string', 'SMTP Server', 'email'),
('email_smtp_port', '587', 'number', 'SMTP Port', 'email'),
('email_smtp_username', '', 'string', 'SMTP Username', 'email'),
('email_smtp_password', '', 'string', 'SMTP Password', 'email'),
('email_smtp_encryption', 'tls', 'string', 'SMTP Encryption', 'email'),
('log_retention_days', '90', 'number', 'จำนวนวันที่เก็บ log', 'security'),
('max_file_upload_size', '10485760', 'number', 'ขนาดไฟล์สูงสุด (bytes)', 'general'),
('max_login_attempts', '5', 'number', 'จำนวนครั้งที่พยายาม login สูงสุด', 'security'),
('account_lockout_duration', '30', 'number', 'ระยะเวลาล็อคบัญชี (นาที)', 'security'),
('session_timeout', '24', 'number', 'ระยะเวลาหมดอายุ session (ชั่วโมง)', 'security'),
('password_min_length', '8', 'number', 'ความยาวรหัสผ่านขั้นต่ำ', 'security'),
('require_email_verification', 'true', 'boolean', 'ต้องการยืนยันอีเมล', 'security'),
('enable_social_login', 'true', 'boolean', 'เปิดใช้งาน Social Login', 'authentication'),
('default_language', 'th', 'string', 'ภาษาเริ่มต้น', 'general'),
('timezone', 'Asia/Bangkok', 'string', 'Timezone', 'general'),
('backup_enabled', 'true', 'boolean', 'เปิดใช้งานระบบ backup', 'backup'),
('backup_frequency', 'daily', 'string', 'ความถี่การ backup', 'backup'),
('backup_retention_days', '30', 'number', 'จำนวนวันที่เก็บ backup', 'backup');

-- Insert Sample Slider Content
INSERT INTO slider_contents (company_id, title, subtitle, button_text, button_url, slide_order, language_code) VALUES
(1, 'KOCH Packaging Solutions', 'บริการบรรจุภัณฑ์ครบวงจรสำหรับอุตสาหกรรมยานยนต์', 'ดูบริการของเรา', '/services', 1, 'th'),
(1, 'Quality Packaging', 'คุณภาพสูง มาตรฐานสากล', 'ติดต่อเรา', '/contact', 2, 'th'),
(2, 'TNB Logistics Services', 'บริการขนส่งและโลจิสติกส์ที่เชื่อถือได้', 'ขอใบเสนอราคา', '/quotation', 1, 'th'),
(2, 'Fast Delivery', 'จัดส่งเร็ว ปลอดภัย ตรงเวลา', 'เรียนรู้เพิ่มเติม', '/about', 2, 'th');

-- Insert Sample Partners
INSERT INTO partners (company_id, name, partner_order, partner_type, contact_person, contact_email) VALUES
(1, 'Toyota Motor Thailand', 1, 'customer', 'Mr. Tanaka', 'tanaka@toyota.co.th'),
(1, 'Honda Automobile Thailand', 2, 'customer', 'Ms. Yuki', 'yuki@honda.co.th'),
(2, 'Thai Union Group', 1, 'partner', 'Mr. Smith', 'smith@thaiunion.com'),
(2, 'CP Group', 2, 'partner', 'Ms. Johnson', 'johnson@cpfood.com');

-- Insert Sample Products (KOCH)
INSERT INTO products (name, description, category, price, dimensions, weight, material, sku, stock_quantity, display_order) VALUES
('กล่องกระดาษอุตสาหกรรม', 'กล่องกระดาษสำหรับอุตสาหกรรมยานยนต์ ความทนทานสูง', 'mail', 150.00, '30x20x10 cm', 0.5, 'กระดาษแข็ง', 'KCH-BOX-001', 1000, 1),
('บรรจุภัณฑ์ไม้', 'บรรจุภัณฑ์ไม้คุณภาพสูง ป้องกันการกระแทก', 'corrugated', 280.00, '40x30x15 cm', 2.0, 'ไม้สน', 'KCH-WOOD-002', 500, 2),
('บรรจุภัณฑ์พลาสติก', 'บรรจุภัณฑ์พลาสติกทนทาน กันน้ำ', 'diecut', 95.00, '25x15x8 cm', 0.3, 'PP Plastic', 'KCH-PLA-003', 2000, 3),
('บรรจุภัณฑ์เหล็ก', 'บรรจุภัณฑ์เหล็กแข็งแรง ป้องกันน้ำหนัก', 'accessory', 450.00, '50x40x20 cm', 5.0, 'เหล็ก', 'KCH-STEEL-004', 200, 4);

-- Insert Sample Truck Types (TNB)
INSERT INTO truck_types (company_id, type_name, description, capacity, service_price, display_order) VALUES
(2, 'Pickup Jumbo', 'รถกระบะขนาดใหญ่ เหมาะกับการขนส่งของเล็ก', 1.5, 1200.00, 1),
(2, '6 Wheel', 'รถ 6 ล้อ ความจุปานกลาง', 4.0, 2500.00, 2),
(2, '10 Wheel Trailer', 'รถพ่วง 10 ล้อ ความจุสูง', 15.0, 5500.00, 3),
(2, 'Trailer Head', 'หัวลาก สำหรับงานขนส่งระยะไกล', 25.0, 8500.00, 4);

-- Insert Sample Trucks
INSERT INTO trucks (truck_number, license_plate, truck_type, capacity, status, fuel_type, current_location) VALUES
('TNB-001', 'กข-1234', 'Pickup Jumbo', 1.5, 'available', 'diesel', 'Bangkok'),
('TNB-002', 'กค-5678', '6 Wheel', 4.0, 'available', 'diesel', 'Bangkok'),
('TNB-003', 'กง-9012', '10 Wheel Trailer', 15.0, 'in_use', 'diesel', 'Chonburi'),
('TNB-004', 'กจ-3456', 'Trailer Head', 25.0, 'maintenance', 'diesel', 'Samut Prakan');

-- =============================================
-- Create Views for Easy Reporting
-- =============================================

-- View สำหรับสถิติผู้ใช้
CREATE VIEW user_stats AS
SELECT 
    c.name as company_name,
    u.role,
    u.status,
    COUNT(*) as user_count,
    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_count,
    COUNT(CASE WHEN DATE(u.created_at) = CURDATE() THEN 1 END) as today_count,
    COUNT(CASE WHEN u.last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_last_7_days
FROM users u
JOIN companies c ON u.company_id = c.id
GROUP BY c.name, u.role, u.status;

-- View สำหรับสถิติใบเสนอราคา KOCH
CREATE VIEW koch_quotation_stats AS
SELECT 
    DATE(created_at) as date,
    status,
    priority,
    product_type,
    COUNT(*) as count,
    AVG(quoted_price) as avg_price,
    SUM(CASE WHEN quoted_price IS NOT NULL THEN quoted_price ELSE 0 END) as total_revenue
FROM koch_quotations
GROUP BY DATE(created_at), status, priority, product_type;

-- View สำหรับสถิติการขนส่ง TNB
CREATE VIEW tnb_transportation_stats AS
SELECT 
    DATE(created_at) as date,
    status,
    service_type,
    vehicle_type,
    COUNT(*) as count,
    AVG(quoted_price) as avg_price,
    SUM(CASE WHEN quoted_price IS NOT NULL THEN quoted_price ELSE 0 END) as total_revenue
FROM tnb_quotations
GROUP BY DATE(created_at), status, service_type, vehicle_type;

-- View สำหรับสถิติรถบรรทุก
CREATE VIEW truck_stats AS
SELECT 
    truck_type,
    status,
    COUNT(*) as count,
    AVG(capacity) as avg_capacity,
    COUNT(CASE WHEN status = 'available' THEN 1 END) as available_count
FROM trucks
GROUP BY truck_type, status;

-- View สำหรับ Activity Summary
CREATE VIEW activity_summary AS
SELECT 
    DATE(created_at) as date,
    action,
    COUNT(*) as count,
    COUNT(DISTINCT user_id) as unique_users
FROM activity_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at), action
ORDER BY date DESC, action;

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

-- Procedure สำหรับ login attempt tracking
CREATE PROCEDURE log_login_attempt(
    IN p_username VARCHAR(50),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT,
    IN p_success BOOLEAN
)
BEGIN
    DECLARE user_id INT DEFAULT NULL;
    DECLARE attempt_count INT DEFAULT 0;
    
    -- Get user ID if exists
    SELECT id INTO user_id FROM users WHERE username = p_username;
    
    -- Update login attempts
    IF user_id IS NOT NULL THEN
        UPDATE users 
        SET login_attempts = CASE WHEN p_success = FALSE THEN login_attempts + 1 ELSE 0 END,
            locked_until = CASE WHEN p_success = FALSE AND login_attempts + 1 >= (SELECT CAST(setting_value AS UNSIGNED) FROM system_settings WHERE setting_key = 'max_login_attempts') 
                THEN DATE_ADD(NOW(), INTERVAL (SELECT CAST(setting_value AS UNSIGNED) FROM system_settings WHERE setting_key = 'account_lockout_duration') MINUTE)
                ELSE NULL END
        WHERE id = user_id;
        
        SELECT login_attempts INTO attempt_count FROM users WHERE id = user_id;
    END IF;
    
    -- Log activity
    INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent)
    VALUES (user_id, IF(p_success, 'LOGIN_SUCCESS', 'LOGIN_FAILED'), 'users', user_id, p_ip_address, p_user_agent);
    
    SELECT attempt_count as remaining_attempts;
END //

-- Procedure สำหรับ cleanup old sessions
CREATE PROCEDURE cleanup_old_sessions()
BEGIN
    DELETE FROM user_sessions 
    WHERE expires_at < NOW() OR (is_active = FALSE AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY));
    
    SELECT ROW_COUNT() as sessions_cleaned;
END //

-- Procedure สำหรับ cleanup old logs
CREATE PROCEDURE cleanup_old_logs()
BEGIN
    DECLARE retention_days INT DEFAULT 90;
    
    SELECT CAST(setting_value AS UNSIGNED) INTO retention_days 
    FROM system_settings 
    WHERE setting_key = 'log_retention_days';
    
    DELETE FROM activity_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL retention_days DAY);
    
    SELECT ROW_COUNT() as logs_cleaned;
END //

DELIMITER ;

-- =============================================
-- Triggers
-- =============================================

-- Trigger สำหรับ log การเปลี่ยนแปลงข้อมูล KOCH Quotations
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
            'quoted_price', OLD.quoted_price,
            'priority', OLD.priority
        ),
        JSON_OBJECT(
            'status', NEW.status,
            'quoted_price', NEW.quoted_price,
            'priority', NEW.priority
        )
    );
END //
DELIMITER ;

-- Trigger สำหรับ log การเปลี่ยนแปลงข้อมูล TNB Quotations
DELIMITER //
CREATE TRIGGER log_tnb_quotation_changes
AFTER UPDATE ON tnb_quotations
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values)
    VALUES (
        COALESCE(@current_user_id, 0),
        'UPDATE',
        'tnb_quotations',
        NEW.id,
        JSON_OBJECT(
            'status', OLD.status,
            'quoted_price', OLD.quoted_price,
            'priority', OLD.priority
        ),
        JSON_OBJECT(
            'status', NEW.status,
            'quoted_price', NEW.quoted_price,
            'priority', NEW.priority
        )
    );
END //
DELIMITER ;

-- Trigger สำหรับ log การเปลี่ยนแปลงข้อมูลผู้ใช้
DELIMITER //
CREATE TRIGGER log_user_changes
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values)
    VALUES (
        NEW.id,
        'UPDATE',
        'users',
        NEW.id,
        JSON_OBJECT(
            'status', OLD.status,
            'role', OLD.role,
            'email', OLD.email
        ),
        JSON_OBJECT(
            'status', NEW.status,
            'role', NEW.role,
            'email', NEW.email
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
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_users_status_company ON users(status, company_id);
CREATE INDEX idx_quotations_status_created ON koch_quotations(status, created_at);
CREATE INDEX idx_files_table_record ON file_attachments(table_name, record_id);

-- =============================================
-- สรุปโครงสร้างฐานข้อมูล
-- =============================================

-- ตารางหลัก (5): companies, users, user_permissions, social_accounts, user_sessions
-- ตารางใบเสนอราคา (2): koch_quotations, tnb_quotations
-- ตารางการขนส่ง (3): trucks, drivers, tracking_logs
-- ตารางเนื้อหาเว็บ (4): slider_contents, partners, products, truck_types
-- ตารางระบบ (8): activity_logs, notifications, email_templates, system_settings, file_attachments, api_keys, backup_logs, maintenance_schedule

-- รวมทั้งหมด: 22 ตาราง
-- รองรับการใช้งานจริงตาม UI ที่มีอยู่
-- รองรับทั้ง KOCH และ TNB ในฐานข้อมูลเดียว
-- มีระบบสิทธิ์ผู้ใช้และการจัดการครบถ้วน
-- รองรับ Social Login (Facebook, Google, LINE)
-- มีระบบ Session Management ครบถ้วน
-- มีระบบ File Management
-- มีระบบ Backup และ Maintenance
-- มีระบบ API Key Management
-- มีระบบ Security ครบถ้วน (Login Attempts, Account Lockout)
-- มีระบบ Logging และ Monitoring
-- พร้อมใช้งานใน XAMPP ทันที
