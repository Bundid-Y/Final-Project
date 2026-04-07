# KOCH & TNB System - ER Diagram Documentation

## Database Schema Overview

**Database Name:** `koch_tnb_system`
**Total Tables:** 17

## Core Tables

### 1. companies
**Purpose:** Stores company information (KOCH, TNB)
```sql
- id (PK, INT, Auto Increment)
- name (VARCHAR(255), NOT NULL) - Company display name
- code (VARCHAR(10), NOT NULL, UNIQUE) - Company code (KOCH/TNB)
- description (TEXT, NULL) - Company description
- logo_url (VARCHAR(500), NULL) - Company logo file path
- website_url (VARCHAR(500), NULL) - Company website
- is_active (TINYINT(1), DEFAULT 1) - Active status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

**Relationships:**
- One-to-Many with users (company_id)
- One-to-Many with koch_quotations (company_id)
- One-to-Many with tnb_quotations (company_id)
- One-to-Many with activity_logs (company_id)
- One-to-Many with notifications (company_id)

### 2. users
**Purpose:** User authentication and profile data
```sql
- id (PK, INT, Auto Increment)
- company_id (FK to companies.id) - User's company
- username (VARCHAR(100), NOT NULL, UNIQUE) - Login username
- email (VARCHAR(255), NOT NULL, UNIQUE) - Email address
- password_hash (VARCHAR(255), NOT NULL) - Hashed password
- first_name (VARCHAR(100), NULL) - First name
- last_name (VARCHAR(100), NULL) - Last name
- nick_name (VARCHAR(100), NULL) - Display name
- phone (VARCHAR(20), NULL) - Phone number
- role (ENUM('super_admin','admin','manager','user'), DEFAULT 'user') - User role
- is_active (TINYINT(1), DEFAULT 1) - Account status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
- last_login (TIMESTAMP, NULL) - Last login timestamp
```

**Relationships:**
- Many-to-One with companies (company_id)
- One-to-Many with koch_quotations (user_id)
- One-to-Many with tnb_quotations (user_id)
- One-to-Many with activity_logs (user_id)
- One-to-Many with notifications (user_id)
- One-to-Many with user_sessions (user_id)

## Business Logic Tables

### 3. koch_quotations
**Purpose:** KOCH packaging quotation requests
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id) - Customer who requested
- company_id (FK to companies.id) - Always KOCH company
- quotation_number (VARCHAR(50), NOT NULL, UNIQUE) - Quote reference
- first_name (VARCHAR(100), NOT NULL) - Customer first name
- last_name (VARCHAR(100), NOT NULL) - Customer last name
- email (VARCHAR(255), NOT NULL) - Customer email
- phone (VARCHAR(20), NULL) - Customer phone
- company_name (VARCHAR(255), NULL) - Customer company
- product_type (VARCHAR(100), NOT NULL) - Type of packaging
- quantity (INT, NULL) - Order quantity
- specifications (TEXT, NULL) - Special requirements
- status (ENUM('pending','processing','quoted','approved','completed','rejected','cancelled'), DEFAULT 'pending')
- quoted_by (FK to users.id, NULL) - Admin who quoted
- quoted_at (TIMESTAMP, NULL) - When quoted
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

**Relationships:**
- Many-to-One with users (user_id - customer)
- Many-to-One with companies (company_id)
- Many-to-One with users (quoted_by - admin)

### 4. tnb_quotations
**Purpose:** TNB transport service requests
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id) - Customer who requested
- company_id (FK to companies.id) - Always TNB company
- request_number (VARCHAR(50), NOT NULL, UNIQUE) - Request reference
- first_name (VARCHAR(100), NOT NULL) - Customer first name
- last_name (VARCHAR(100), NOT NULL) - Customer last name
- email (VARCHAR(255), NOT NULL) - Customer email
- phone (VARCHAR(20), NULL) - Customer phone
- company_name (VARCHAR(255), NULL) - Customer company
- service_type (ENUM('domestic','international','warehousing','fleet_management'), NOT NULL)
- pickup_address (TEXT, NULL) - Origin address
- delivery_address (TEXT, NULL) - Destination address
- route (VARCHAR(255), NULL) - Transport route description
- cargo_details (TEXT, NULL) - Cargo information
- weight (DECIMAL(10,2), NULL) - Cargo weight
- dimensions (VARCHAR(100), NULL) - Cargo dimensions
- special_requirements (TEXT, NULL) - Special handling needs
- status (ENUM('pending','processing','quoted','approved','in_transit','delivered','completed','rejected','cancelled'), DEFAULT 'pending')
- tracking_number (VARCHAR(100), NULL) - Tracking reference
- quoted_by (FK to users.id, NULL) - Admin who processed
- quoted_at (TIMESTAMP, NULL) - When processed
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

**Relationships:**
- Many-to-One with users (user_id - customer)
- Many-to-One with companies (company_id)
- Many-to-One with users (quoted_by - admin)

## Content Management Tables

### 5. sliders
**Purpose:** Homepage slider content
```sql
- id (PK, INT, Auto Increment)
- company_id (FK to companies.id) - Company-specific slider
- title (VARCHAR(255), NOT NULL) - Slide title
- subtitle (TEXT, NULL) - Slide subtitle
- image_url (VARCHAR(500), NULL) - Background image
- button_text (VARCHAR(100), NULL) - CTA button text
- button_url (VARCHAR(500), NULL) - CTA button link
- display_order (INT, DEFAULT 0) - Sort order
- is_active (TINYINT(1), DEFAULT 1) - Published status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 6. partners
**Purpose:** Partner companies display
```sql
- id (PK, INT, Auto Increment)
- company_id (FK to companies.id) - Owner company
- name (VARCHAR(255), NOT NULL) - Partner name
- description (TEXT, NULL) - Partner description
- logo_url (VARCHAR(500), NULL) - Partner logo
- website_url (VARCHAR(500), NULL) - Partner website
- display_order (INT, DEFAULT 0) - Sort order
- is_active (TINYINT(1), DEFAULT 1) - Published status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 7. products
**Purpose:** Product catalog (KOCH)
```sql
- id (PK, INT, Auto Increment)
- name (VARCHAR(255), NOT NULL) - Product name
- description (TEXT, NULL) - Product description
- category (VARCHAR(100), NULL) - Product category
- image_url (VARCHAR(255), NULL) - Product image
- display_order (INT, DEFAULT 0) - Sort order
- is_active (TINYINT(1), DEFAULT 1) - Published status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 8. featured_products
**Purpose:** Featured products display
```sql
- id (PK, INT, Auto Increment)
- company_id (FK to companies.id) - Owner company
- name (VARCHAR(255), NOT NULL) - Product name
- description (TEXT, NULL) - Product description
- image_url (VARCHAR(500), NULL) - Product image
- display_order (INT, DEFAULT 0) - Sort order
- is_active (TINYINT(1), DEFAULT 1) - Published status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 9. truck_cards
**Purpose:** TNB transport vehicle types
```sql
- id (PK, INT, Auto Increment)
- name (VARCHAR(255), NOT NULL) - Vehicle type name
- description (TEXT, NULL) - Vehicle description
- image_url (VARCHAR(255), NULL) - Vehicle image
- capacity (VARCHAR(100), NULL) - Load capacity
- display_order (INT, DEFAULT 0) - Sort order
- is_active (TINYINT(1), DEFAULT 1) - Published status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

## System Tables

### 10. activity_logs
**Purpose:** Comprehensive audit trail
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id, NULL) - User who performed action
- company_id (FK to companies.id, NULL) - Company context
- company_name (VARCHAR(100), NULL) - Company name snapshot
- action (VARCHAR(100), NOT NULL) - Action type
- table_name (VARCHAR(50), NULL) - Affected table
- record_id (INT, NULL) - Affected record ID
- old_values (LONGTEXT, NULL) - Previous values (JSON)
- new_values (LONGTEXT, NULL) - New values (JSON)
- ip_address (VARCHAR(45), NULL) - User IP
- user_agent (TEXT, NULL) - Browser info
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

**Indexes:**
- INDEX on user_id
- INDEX on company_id  
- INDEX on action
- INDEX on created_at

### 11. notifications
**Purpose:** User notification system
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id, NOT NULL) - Recipient
- company_id (FK to companies.id, NULL) - Company context
- title (VARCHAR(255), NOT NULL) - Notification title
- message (TEXT, NOT NULL) - Notification message
- type (ENUM('info','success','warning','error','system'), DEFAULT 'info')
- related_table (VARCHAR(50), NULL) - Related entity table
- related_id (INT, NULL) - Related entity ID
- is_read (TINYINT(1), DEFAULT 0) - Read status
- is_email_sent (TINYINT(1), DEFAULT 0) - Email sent status
- priority (ENUM('low','normal','high','urgent'), DEFAULT 'normal')
- email_sent_at (TIMESTAMP, NULL) - When email sent
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

**Indexes:**
- INDEX on user_id
- INDEX on company_id
- INDEX on is_read
- INDEX on created_at

### 12. user_sessions
**Purpose:** Active user session tracking
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id, NOT NULL) - Session owner
- session_token (VARCHAR(255), NOT NULL, UNIQUE) - Session identifier
- ip_address (VARCHAR(45), NULL) - Login IP
- user_agent (TEXT, NULL) - Browser info
- is_active (TINYINT(1), DEFAULT 1) - Session status
- expires_at (TIMESTAMP, NOT NULL) - Session expiry
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

**Indexes:**
- INDEX on user_id
- UNIQUE INDEX on session_token
- INDEX on expires_at

### 13. user_stats
**Purpose:** User statistics aggregation
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id, NOT NULL) - User reference
- company_id (FK to companies.id, NULL) - Company context
- total_logins (INT, DEFAULT 0) - Login count
- total_quotations (INT, DEFAULT 0) - Quotation count
- last_activity (TIMESTAMP, NULL) - Last activity timestamp
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 14. user_permissions
**Purpose:** Extended user permissions (future use)
```sql
- id (PK, INT, Auto Increment)
- user_id (FK to users.id, NOT NULL) - User reference
- permission_key (VARCHAR(100), NOT NULL) - Permission identifier
- permission_value (TEXT, NULL) - Permission value
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 15. system_settings
**Purpose:** System-wide configuration
```sql
- id (PK, INT, Auto Increment)
- setting_key (VARCHAR(100), NOT NULL, UNIQUE) - Setting name
- setting_value (TEXT, NULL) - Setting value
- description (TEXT, NULL) - Setting description
- is_active (TINYINT(1), DEFAULT 1) - Setting status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 16. email_templates
**Purpose:** Email notification templates
```sql
- id (PK, INT, Auto Increment)
- company_id (FK to companies.id, NULL) - Company-specific template
- template_name (VARCHAR(100), NOT NULL) - Template identifier
- subject (VARCHAR(255), NOT NULL) - Email subject
- html_content (TEXT, NULL) - HTML email body
- text_content (TEXT, NULL) - Plain text email body
- is_active (TINYINT(1), DEFAULT 1) - Template status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 17. slider_contents
**Purpose:** Additional slider content (legacy)
```sql
- id (PK, INT, Auto Increment)
- slider_id (FK to sliders.id, NOT NULL) - Parent slider
- content_type (VARCHAR(50), NOT NULL) - Content type
- content_value (TEXT, NULL) - Content value
- display_order (INT, DEFAULT 0) - Sort order
- is_active (TINYINT(1), DEFAULT 1) - Content status
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

### 18. activity_summary
**Purpose:** Pre-calculated activity statistics
```sql
- id (PK, INT, Auto Increment)
- company_id (FK to companies.id, NULL) - Company context
- date_summary (DATE, NOT NULL) - Summary date
- total_users (INT, DEFAULT 0) - User count
- total_quotations (INT, DEFAULT 0) - Quotation count
- total_activities (INT, DEFAULT 0) - Activity count
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)
```

## Key Relationships Summary

### Company-Based Data Isolation
- **KOCH Data:** koch_quotations, products (KOCH-specific)
- **TNB Data:** tnb_quotations, truck_cards (TNB-specific)
- **Shared Data:** users, activity_logs, notifications, system_settings

### Foreign Key Relationships
1. **companies** ← users (company_id)
2. **companies** ← koch_quotations (company_id)
3. **companies** ← tnb_quotations (company_id)
4. **companies** ← sliders (company_id)
5. **companies** ← partners (company_id)
6. **companies** ← featured_products (company_id)
7. **companies** ← activity_logs (company_id)
8. **companies** ← notifications (company_id)
9. **companies** ← user_stats (company_id)
10. **companies** ← email_templates (company_id)

11. **users** ← koch_quotations (user_id)
12. **users** ← tnb_quotations (user_id)
13. **users** ← activity_logs (user_id)
14. **users** ← notifications (user_id)
15. **users** ← user_sessions (user_id)
16. **users** ← user_stats (user_id)
17. **users** ← user_permissions (user_id)

18. **users** ← koch_quotations (quoted_by)
19. **users** ← tnb_quotations (quoted_by)

## Data Flow Patterns

### 1. User Registration
```
users → user_sessions → activity_logs → notifications
```

### 2. Quotation Creation
```
users → koch_quotations/tnb_quotations → activity_logs → notifications
```

### 3. Admin Actions
```
users (admin) → activity_logs → notifications → (affected tables)
```

### 4. Content Management
```
users (admin) → sliders/partners/products → activity_logs
```

## Index Strategy

### Performance Critical Indexes
- **users**: username, email, company_id
- **koch_quotations**: user_id, company_id, status, created_at
- **tnb_quotations**: user_id, company_id, status, created_at
- **activity_logs**: user_id, company_id, action, created_at
- **notifications**: user_id, company_id, is_read, created_at
- **user_sessions**: user_id, session_token, expires_at

### Unique Constraints
- users.username
- users.email
- koch_quotations.quotation_number
- tnb_quotations.request_number
- user_sessions.session_token
- system_settings.setting_key

## Data Integrity Rules

### Business Rules
1. **Company Isolation**: Users can only access data from their company
2. **Role Hierarchy**: super_admin > admin > manager > user
3. **Quotation Numbers**: Must be unique per company
4. **Session Management**: One active session per user per company
5. **Activity Logging**: All significant actions must be logged

### Referential Integrity
- All foreign keys reference valid parent records
- Cascading deletes are NOT used (preserve data integrity)
- Soft deletes used where applicable (is_active flags)

## Security Considerations

### Sensitive Data
- **users.password_hash**: Hashed using modern algorithms
- **user_sessions.session_token**: Cryptographically secure
- **activity_logs.ip_address**: Privacy considerations

### Access Control
- Row-level security through company_id filtering
- Column-level security through role-based views
- Audit trail through activity_logs table

This ER diagram provides a complete foundation for the KOCH & TNB dual-company system with proper data isolation, comprehensive logging, and scalable architecture.
