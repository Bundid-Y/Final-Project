-- =============================================
-- Migration V2: เพิ่มตารางที่ขาด + ปรับปรุงระบบ
-- สำหรับ koch_tnb_system
-- =============================================

USE koch_tnb_system;

-- =============================================
-- 23. ตารางสาขา (Branches)
-- =============================================
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    slug VARCHAR(100),
    description TEXT,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(100),
    google_maps_url TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    image_url VARCHAR(255),
    is_headquarters BOOLEAN DEFAULT FALSE,
    services JSON, -- ["Domestic Transport","Fleet Management"]
    area_size VARCHAR(100), -- "1,600 ตร.ม."
    staff_count INT DEFAULT 0,
    operating_hours VARCHAR(100) DEFAULT 'จันทร์-เสาร์ 08:00-17:00',
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    INDEX idx_company_order (company_id, display_order),
    INDEX idx_active (is_active),
    INDEX idx_slug (slug)
);

-- =============================================
-- 24. ตารางข้อความติดต่อ (Contact Messages)
-- =============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    replied_by INT,
    replied_at TIMESTAMP NULL,
    reply_message TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (replied_by) REFERENCES users(id),
    INDEX idx_company_status (company_id, status),
    INDEX idx_created_at (created_at),
    INDEX idx_email (email)
);

-- =============================================
-- 25. ตารางผู้รับอีเมลแจ้งเตือน (Email Recipients)
-- =============================================
CREATE TABLE IF NOT EXISTS email_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT,
    event_type VARCHAR(100) NOT NULL, -- 'new_quotation', 'new_registration', 'security_alert', 'contact_message'
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    UNIQUE KEY unique_event_email (event_type, recipient_email),
    INDEX idx_event_active (event_type, is_active),
    INDEX idx_company (company_id)
);

-- =============================================
-- 26. ตารางประวัติการส่งอีเมล (Email Logs)
-- =============================================
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    error_message TEXT,
    related_table VARCHAR(50),
    related_id INT,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES email_templates(id),
    INDEX idx_status (status),
    INDEX idx_recipient (recipient_email),
    INDEX idx_created_at (created_at),
    INDEX idx_related (related_table, related_id)
);

-- =============================================
-- Insert Default Branches - KOCH
-- =============================================
INSERT INTO branches (company_id, name, name_en, slug, description, address, phone, email, is_headquarters, services, area_size, staff_count, display_order) VALUES
(1, 'สาขาบ้านบึง', 'Ban Bueng Branch', 'banbueng', 'ฐานการผลิตหลักสำหรับบรรจุภัณฑ์กระดาษลูกฟูก (Carton Box) ด้วยพนักงานที่เชี่ยวชาญกว่า 120 ท่าน พร้อมรองรับปริมาณการผลิตขนาดใหญ่', '742/5 หมู่ที่ 1 ตำบลหนองไผ่แก้ว อำเภอบ้านบึง จังหวัดชลบุรี 20220', '081-5758823', 'salesteam@koch-packaging.com', TRUE, '["Packaging Services","Carton Box Production","Quality Control"]', '1,600 ตร.ม.', 120, 1),
(1, 'สาขาบ่อวิน', 'Bowin Branch', 'bowin', 'สาขาใหม่ที่ถูกออกแบบมาเพื่อรองรับการขยายตัวในนิคมอุตสาหกรรมบ่อวิน เน้นบริการคลังสินค้าและการจัดการระบบ VMI', '123 นิคมอุตสาหกรรมบ่อวิน จังหวัดชลบุรี', '062-6392499', 'bowin@koch-packaging.com', FALSE, '["Warehouse Operations","VMI Management","WMS System"]', '5,000 ตร.ม.', 50, 2);

-- =============================================
-- Insert Default Branches - TNB
-- =============================================
INSERT INTO branches (company_id, name, name_en, slug, description, address, phone, email, is_headquarters, services, area_size, display_order) VALUES
(2, 'สาขาบางแสน (สำนักงานใหญ่)', 'Bangsaen Branch (HQ)', 'bangsaen', 'ศูนย์กลางการบริหารจัดการการขนส่งภายในประเทศ เป็นสำนักงานใหญ่ที่รวมศูนย์บัญชาการและประสานงานทุกสาขา', '18/2 หมู่ที่ 5 ตำบลเหมือง อำเภอเมืองชลบุรี จังหวัดชลบุรี 20130', '081-5758823', 'wachira.o@tnb-logistics.com', TRUE, '["Domestic Transport","Fleet Management","HQ Operations"]', NULL, 1),
(2, 'สาขาแหลมฉบัง', 'Laem Chabang Branch', 'laemchabang', 'ให้บริการจัดจองตู้คอนเทนเนอร์และพื้นที่ฝากวางตู้ (Container Drop Yard) เชื่อมต่อท่าเรือแหลมฉบังโดยตรง', 'ท่าเรือแหลมฉบัง จังหวัดชลบุรี', '062-6392499', 'laemchabang@tnb-logistics.com', FALSE, '["Container Yard","Import/Export","Port Linkage"]', NULL, 2),
(2, 'สาขาบางกะดี', 'Bangkadi Branch', 'bangkadi', 'เชี่ยวชาญการให้บริการรถ Shuttle รับ-ส่งสินค้าระหว่างคลังสินค้าและการจัดการตู้คอนเทนเนอร์', 'นิคมอุตสาหกรรมบางกะดี จังหวัดปทุมธานี', '081-5758823', 'bangkadi@tnb-logistics.com', FALSE, '["Shuttle Service","WH to WH","Container Mgmt"]', NULL, 3),
(2, 'สาขาลาดกระบัง', 'Latkrabang Branch', 'latkrabang', 'ศูนย์กระจายสินค้าและลานจอดรถขนาด 9,000 ตร.ม. ตั้งอยู่ใกล้กับ ICD เพื่อความรวดเร็วในการขนส่ง', 'ลาดกระบัง กรุงเทพฯ', '062-6392499', 'latkrabang@tnb-logistics.com', FALSE, '["Distribution Center","9,000 sqm Yard","Near ICD"]', '9,000 ตร.ม.', 4);

-- =============================================
-- Insert Default Email Recipients
-- =============================================
INSERT INTO email_recipients (company_id, event_type, recipient_email, recipient_name) VALUES
(1, 'new_quotation', 'salesteam@koch-packaging.com', 'KOCH Sales Team'),
(1, 'new_registration', 'admin@koch-packaging.com', 'KOCH Admin'),
(1, 'security_alert', 'admin@koch-packaging.com', 'KOCH Admin'),
(1, 'contact_message', 'salesteam@koch-packaging.com', 'KOCH Sales Team'),
(2, 'new_quotation', 'wachira.o@tnb-logistics.com', 'TNB Sales Team'),
(2, 'new_registration', 'admin@tnb-logistics.com', 'TNB Admin'),
(2, 'security_alert', 'admin@tnb-logistics.com', 'TNB Admin'),
(2, 'contact_message', 'wachira.o@tnb-logistics.com', 'TNB Sales Team');

-- =============================================
-- เพิ่ม System Settings สำหรับ Email Recipients
-- =============================================
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('notification_login_failed_threshold', '3', 'number', 'จำนวนครั้ง login ผิดก่อนแจ้งเตือน admin'),
('notification_enabled', 'true', 'boolean', 'เปิดใช้งานระบบแจ้งเตือน'),
('email_notification_enabled', 'true', 'boolean', 'เปิดใช้งานการแจ้งเตือนทางอีเมล'),
('email_from_name', 'KOCH & TNB System', 'string', 'ชื่อผู้ส่งอีเมล'),
('email_from_address', 'noreply@koch-tnb.com', 'string', 'อีเมลผู้ส่ง')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- =============================================
-- เพิ่ม Email Templates สำหรับแจ้งเตือน
-- =============================================
INSERT INTO email_templates (name, subject, html_content, company_id, language_code) VALUES
('security_login_failed', 'แจ้งเตือนความปลอดภัย: Login ผิดพลาดหลายครั้ง', '<h3>แจ้งเตือนความปลอดภัย</h3><p>ผู้ใช้ <strong>{username}</strong> พยายามเข้าสู่ระบบผิดพลาด {attempt_count} ครั้ง</p><p>IP: {ip_address}</p><p>เวลา: {timestamp}</p><p>กรุณาตรวจสอบกิจกรรมที่น่าสงสัย</p>', NULL, 'th'),
('new_user_registered', 'มีผู้ใช้ลงทะเบียนใหม่', '<h3>ผู้ใช้ลงทะเบียนใหม่</h3><p>Username: {username}</p><p>Email: {email}</p><p>บริษัท: {company_name}</p><p>วันที่: {created_at}</p>', NULL, 'th'),
('contact_received', 'มีข้อความติดต่อใหม่', '<h3>ข้อความติดต่อใหม่</h3><p>จาก: {name} ({email})</p><p>หัวข้อ: {subject}</p><p>ข้อความ: {message}</p>', NULL, 'th')
ON DUPLICATE KEY UPDATE subject = VALUES(subject);
