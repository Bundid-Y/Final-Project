-- =============================================
-- Patch: เพิ่มคอลัมน์และตารางที่ขาดหายไป
-- ให้ตรงกับโค้ด PHP ใน admin/includes/
-- =============================================

USE koch_tnb_system;

-- 1. เพิ่มคอลัมน์ที่ขาดในตาราง users
ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(100) DEFAULT NULL AFTER avatar_url;
ALTER TABLE users ADD COLUMN IF NOT EXISTS position VARCHAR(100) DEFAULT NULL AFTER department;
ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0 AFTER position;
ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL DEFAULT NULL AFTER login_attempts;

-- 2. เพิ่มคอลัมน์ is_active ในตาราง companies
ALTER TABLE companies ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER contact_phone;

-- 3. สร้างตาราง user_sessions (ใช้ใน auth.php)
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active TINYINT(1) DEFAULT 1,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_active (user_id, is_active)
);

-- 4. สร้างตาราง file_attachments (ใช้ใน upload.php)
CREATE TABLE IF NOT EXISTS file_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT DEFAULT 0,
    file_type VARCHAR(20),
    mime_type VARCHAR(100),
    uploaded_by INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_table_record (table_name, record_id)
);

-- 5. เพิ่มคอลัมน์ที่ขาดในตาราง koch_quotations
ALTER TABLE koch_quotations ADD COLUMN IF NOT EXISTS brand VARCHAR(100) DEFAULT NULL AFTER weight;
ALTER TABLE koch_quotations ADD COLUMN IF NOT EXISTS packaging_type VARCHAR(100) DEFAULT NULL AFTER brand;

-- 6. เพิ่มคอลัมน์ที่ขาดในตาราง tnb_quotations
ALTER TABLE tnb_quotations ADD COLUMN IF NOT EXISTS route TEXT DEFAULT NULL AFTER delivery_address;
ALTER TABLE tnb_quotations ADD COLUMN IF NOT EXISTS vehicle_type VARCHAR(100) DEFAULT NULL AFTER truck_type;
ALTER TABLE tnb_quotations ADD COLUMN IF NOT EXISTS cargo_width DECIMAL(10,2) DEFAULT NULL AFTER cargo_weight;
ALTER TABLE tnb_quotations ADD COLUMN IF NOT EXISTS cargo_length DECIMAL(10,2) DEFAULT NULL AFTER cargo_width;
ALTER TABLE tnb_quotations ADD COLUMN IF NOT EXISTS cargo_height DECIMAL(10,2) DEFAULT NULL AFTER cargo_length;
ALTER TABLE tnb_quotations ADD COLUMN IF NOT EXISTS priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' AFTER notes;

-- 7. เพิ่มคอลัมน์ priority ในตาราง notifications
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' AFTER is_email_sent;

-- 8. อัปเดต companies ให้มี is_active = 1
UPDATE companies SET is_active = 1 WHERE is_active IS NULL OR is_active = 0;

-- 9. สร้างตาราง social_accounts (สำหรับ Social Login)
CREATE TABLE IF NOT EXISTS social_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider ENUM('facebook', 'google', 'line') NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    provider_email VARCHAR(255),
    provider_name VARCHAR(255),
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider_user (provider, provider_id),
    INDEX idx_user (user_id)
);

-- 10. สร้างตาราง truck_types (สำหรับ TNB content management)
CREATE TABLE IF NOT EXISTS truck_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    capacity VARCHAR(100),
    dimensions VARCHAR(100),
    price_range VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    language_code VARCHAR(5) DEFAULT 'th',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order (display_order),
    INDEX idx_language (language_code)
);

-- 11. สร้างตาราง backup_logs
CREATE TABLE IF NOT EXISTS backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_type ENUM('full', 'incremental', 'database', 'files') NOT NULL,
    file_path VARCHAR(500),
    file_size BIGINT DEFAULT 0,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- 12. สร้างตาราง api_keys
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    permissions JSON,
    is_active TINYINT(1) DEFAULT 1,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_api_key (api_key),
    INDEX idx_user (user_id)
);

-- 13. Insert default truck types for TNB
INSERT IGNORE INTO truck_types (name, description, capacity, is_active, display_order) VALUES
('Pickup Jumbo', 'รถกระบะตู้ทึบ', '1.5 ตัน', 1, 1),
('6 Wheel', 'รถ 6 ล้อ', '5 ตัน', 1, 2),
('6 Wheel Trailer', 'รถ 6 ล้อพ่วง', '10 ตัน', 1, 3),
('10 Wheel Trailer', 'รถ 10 ล้อพ่วง', '15 ตัน', 1, 4),
('Trailer Head', 'หัวลากตู้คอนเทนเนอร์', '25 ตัน', 1, 5);
