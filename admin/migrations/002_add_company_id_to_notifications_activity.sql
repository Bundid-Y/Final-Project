-- Migration: Add company_id to notifications and activity_logs
-- Purpose: Enable company-based data isolation for multi-tenant dashboard
-- Date: 2026-03-29

ALTER TABLE notifications ADD COLUMN company_id INT DEFAULT NULL AFTER user_id;
ALTER TABLE notifications ADD INDEX idx_notif_company (company_id);

ALTER TABLE activity_logs ADD COLUMN company_id INT DEFAULT NULL AFTER user_id;
ALTER TABLE activity_logs ADD INDEX idx_actlog_company (company_id);
