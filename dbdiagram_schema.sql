// KOCH System - dbdiagram.io Schema (KOCH Only)
// Copy and paste into https://dbdiagram.io/
// Total: 13 Tables (KOCH company only)

Table companies {
  id int [PK, increment]
  name varchar(255) [not null]
  code varchar(10) [not null, unique]
  description text [null]
  logo_url varchar(500) [null]
  website_url varchar(500) [null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table users {
  id int [PK, increment]
  company_id int [not null, note: 'FK to companies.id']
  username varchar(100) [not null, unique]
  email varchar(255) [not null, unique]
  password_hash varchar(255) [not null]
  first_name varchar(100) [null]
  last_name varchar(100) [null]
  nick_name varchar(100) [null]
  phone varchar(20) [null]
  role enum('super_admin', 'admin', 'manager', 'user') [default: 'user', not null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
  last_login timestamp [null]
}

Table system_settings {
  id int [PK, increment]
  setting_key varchar(100) [not null, unique]
  setting_value text [null]
  description text [null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table koch_quotations {
  id int [PK, increment]
  user_id int [not null, note: 'FK to users.id (customer)']
  company_id int [not null, note: 'FK to companies.id (always KOCH)']
  product_id int [null, note: 'FK to products.id']
  quotation_number varchar(50) [not null, unique]
  first_name varchar(100) [not null]
  last_name varchar(100) [not null]
  email varchar(255) [not null]
  phone varchar(20) [null]
  company_name varchar(255) [null]
  product_type varchar(100) [not null]
  quantity int [null]
  specifications text [null]
  status enum('pending', 'processing', 'quoted', 'approved', 'completed', 'rejected', 'cancelled') [default: 'pending', not null]
  quoted_by int [null, note: 'FK to users.id (admin)']
  quoted_at timestamp [null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table sliders {
  id int [PK, increment]
  company_id int [not null, note: 'FK to companies.id']
  title varchar(255) [not null]
  subtitle text [null]
  image_url varchar(500) [null]
  button_text varchar(100) [null]
  button_url varchar(500) [null]
  display_order int [default: 0, not null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table partners {
  id int [PK, increment]
  company_id int [not null, note: 'FK to companies.id']
  name varchar(255) [not null]
  description text [null]
  logo_url varchar(500) [null]
  website_url varchar(500) [null]
  display_order int [default: 0, not null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table products {
  id int [PK, increment]
  name varchar(255) [not null]
  description text [null]
  category varchar(100) [null, note: 'mail, corrugated, diecut, accessory']
  image_url varchar(255) [null]
  display_order int [default: 0, not null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table featured_products {
  id int [PK, increment]
  company_id int [not null, note: 'FK to companies.id']
  name varchar(255) [not null]
  description text [null]
  image_url varchar(500) [null]
  display_order int [default: 0, not null]
  is_active tinyint(1) [default: 1, not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table contact_messages {
  id int [PK, increment]
  company_id int [null, note: 'FK to companies.id']
  name varchar(255) [not null]
  email varchar(255) [not null]
  phone varchar(20) [null]
  subject varchar(255) [not null]
  message text [not null]
  status enum('new', 'read', 'replied', 'closed') [default: 'new', not null]
  ip_address varchar(45) [null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

Table notifications {
  id int [PK, increment]
  user_id int [not null, note: 'FK to users.id']
  company_id int [null, note: 'FK to companies.id']
  title varchar(255) [not null]
  message text [not null]
  type enum('info', 'success', 'warning', 'error', 'system') [default: 'info', not null]
  related_table varchar(50) [null]
  related_id int [null]
  is_read tinyint(1) [default: 0, not null]
  is_email_sent tinyint(1) [default: 0, not null]
  priority enum('low', 'normal', 'high', 'urgent') [default: 'normal', not null]
  email_sent_at timestamp [null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
}

Table activity_logs {
  id int [PK, increment]
  user_id int [null, note: 'FK to users.id']
  company_id int [null, note: 'FK to companies.id']
  company_name varchar(100) [null]
  action varchar(100) [not null]
  table_name varchar(50) [null]
  record_id int [null]
  old_values longtext [null, note: 'JSON format']
  new_values longtext [null, note: 'JSON format']
  ip_address varchar(45) [null]
  user_agent text [null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
}

Table user_sessions {
  id int [PK, increment]
  user_id int [not null, note: 'FK to users.id']
  session_token varchar(255) [not null, unique]
  ip_address varchar(45) [null]
  user_agent text [null]
  is_active tinyint(1) [default: 1, not null]
  expires_at timestamp [not null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
}

Table user_permissions {
  id int [PK, increment]
  user_id int [not null, note: 'FK to users.id']
  permission_key varchar(100) [not null]
  permission_value text [null]
  created_at timestamp [default: `CURRENT_TIMESTAMP`, not null]
  updated_at timestamp [default: `CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`, not null]
}

// ==================== RELATIONSHIPS ====================

// Companies to Users
Ref: companies.id - users.company_id

// Companies to KOCH Quotations
Ref: companies.id - koch_quotations.company_id

// Companies to Sliders
Ref: companies.id - sliders.company_id

// Companies to Partners
Ref: companies.id - partners.company_id

// Companies to Featured Products
Ref: companies.id - featured_products.company_id

// Companies to Activity Logs
Ref: companies.id - activity_logs.company_id

// Companies to Notifications
Ref: companies.id - notifications.company_id

// Users to KOCH Quotations (customer)
Ref: users.id - koch_quotations.user_id

// Users to KOCH Quotations (quoted_by admin)
Ref: users.id - koch_quotations.quoted_by

// Users to Activity Logs
Ref: users.id - activity_logs.user_id

// Users to Notifications
Ref: users.id - notifications.user_id

// Users to User Sessions
Ref: users.id - user_sessions.user_id

// Users to User Permissions
Ref: users.id - user_permissions.user_id

// Companies to Contact Messages
Ref: companies.id - contact_messages.company_id
