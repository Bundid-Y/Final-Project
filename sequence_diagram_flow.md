# KOCH & TNB System - Sequence Diagram Documentation

## 1. User Authentication Flow

### 1.1 Admin Login Flow
```
User -> Admin Login Page: POST credentials
Admin Login Page -> auth.php: find_admin_user_by_identifier()
auth.php -> Database: SELECT users JOIN companies
Database -> auth.php: User data
auth.php -> session.php: set_authenticated_user()
session.php -> Database: INSERT user_sessions
session.php -> Admin Dashboard: Redirect
Admin Dashboard -> User: Admin Interface
```

### 1.2 KOCH/TNB User Login Flow
```
User -> KOCH/TNB Login Page: POST credentials
Login Page -> auth.php: find_user_by_identifier()
auth.php -> Database: SELECT users WHERE company_id
Database -> auth.php: User data
auth.php -> session.php: set_authenticated_user()
session.php -> Database: INSERT user_sessions
session.php -> User Dashboard: Redirect
User Dashboard -> User: User Interface
```

### 1.3 Session Management (Multi-Session Architecture)
```
Request -> session.php: start_app_session()
session.php -> URL Path Detection:
  - /koch/ -> koch_session
  - /tnb/ -> tnb_session  
  - /admin/ -> koch_tnb_session
session.php -> Session Validation
Session Validation -> Application: Continue / Redirect
```

## 2. Quotation Management Flow

### 2.1 KOCH Quotation Creation
```
User -> KOCH Quotation Form: Submit form
Quotation Form -> quotations/handler.php: handle_koch_quotation()
handler.php -> Database: INSERT koch_quotations
handler.php -> activity.php: log_activity()
handler.php -> notifications.php: create_notification()
handler.php -> User: Success message
Admin -> Admin Dashboard: View new quotation
```

### 2.2 TNB Transport Request Creation
```
User -> TNB Quotation Form: Submit form
Quotation Form -> quotations/handler.php: handle_tnb_quotation()
handler.php -> Database: INSERT tnb_quotations
handler.php -> activity.php: log_activity()
handler.php -> notifications.php: create_notification()
handler.php -> User: Success message
Admin -> Admin Dashboard: View new transport request
```

### 2.3 Quotation Status Update
```
Admin -> Admin Dashboard: Update status
Dashboard -> crud.php: update_koch_quotation_status()
crud.php -> Database: UPDATE koch_quotations
crud.php -> activity.php: log_activity()
crud.php -> notifications.php: create_notification()
User -> User Dashboard: View updated status
```

## 3. Content Management Flow

### 3.1 CRUD Operations (Sliders/Partners/Products)
```
Admin -> Admin Dashboard: CRUD Form
Dashboard -> handler.php: POST entity data
handler.php -> crud.php: create/update/delete function
crud.php -> Database: INSERT/UPDATE/DELETE
crud.php -> activity.php: log_activity()
crud.php -> Dashboard: Success/Failure response
Dashboard -> Frontend: Refresh content
```

### 3.2 File Upload Flow
```
Admin -> Upload Form: Select file
Upload Form -> upload.php: validate_and_upload_file()
upload.php -> File System: Save file
upload.php -> Database: (Optional) save_attachment_record()
upload.php -> Admin: File URL response
```

## 4. User Management Flow

### 4.1 User Registration
```
User -> Registration Form: Submit data
Registration Form -> auth.php: register_user()
auth.php -> validation.php: validate_user_data()
auth.php -> Database: INSERT users
auth.php -> activity.php: log_activity()
auth.php -> notifications.php: create_notification()
auth.php -> Login Page: Redirect
```

### 4.2 Profile Update
```
User -> Profile Form: Submit changes
Profile Form -> profile.php: update_profile_details()
profile.php -> validation.php: validate_profile_data()
profile.php -> Database: UPDATE users
profile.php -> activity.php: log_activity()
profile.php -> User: Success message
```

## 5. Notification System Flow

### 5.1 Notification Creation
```
System Event -> notifications.php: create_notification()
notifications.php -> Database: INSERT notifications
notifications.php -> (Optional) Email System: Send email
User -> Dashboard: Check notifications
Dashboard -> notifications.php: get_user_notifications()
notifications.php -> Database: SELECT notifications
Database -> Dashboard: Notification list
```

### 5.2 Notification Read Status
```
User -> Dashboard: View notifications
Dashboard -> notifications.php: mark_notification_read()
notifications.php -> Database: UPDATE notifications SET is_read=1
```

## 6. Activity Logging Flow

```
User Action -> activity.php: log_activity()
activity.php -> Database: INSERT activity_logs
Admin -> Admin Dashboard: View activity logs
Dashboard -> admin.php: latest_admin_activities()
admin.php -> Database: SELECT activity_logs
Database -> Dashboard: Activity list
```

## 7. Export Data Flow

```
Admin -> Export Section: Select export type
Export Section -> export/handler.php: handle_export()
handler.php -> Database: Query data
handler.php -> CSV/Excel: Generate file
handler.php -> User: Download file
handler.php -> activity.php: log_activity()
```

## 8. Company Mode Switching (Admin Dashboard)

```
Admin -> Dashboard: Switch company mode
Dashboard -> Session: Update admin_company_mode
Dashboard -> Database: Reload with company filter
Dashboard -> Frontend: Update UI colors and data
```

## 9. Logout Flow

```
User -> Logout Link: Click logout
Logout Link -> logout.php: Multi-session search
logout.php -> Database: UPDATE user_sessions (deactivate)
logout.php -> Session: Destroy all sessions
logout.php -> Login Page: Redirect with flash message
```

## 10. API Request Flow

### 10.1 CRUD API Requests
```
Frontend -> API Endpoint: POST/AJAX request
API Endpoint -> handler.php: Route to entity handler
handler.php -> crud.php: Execute CRUD operation
crud.php -> Database: Query
handler.php -> Frontend: JSON response
Frontend -> UI: Update interface
```

### 10.2 Authentication API Requests
```
Frontend -> auth API: Login/logout request
Auth API -> auth.php: Process authentication
auth.php -> Session: Manage session state
auth.php -> Frontend: JSON response
```

## Key System Components:

1. **Session Management**: 3 independent sessions (koch_session, tnb_session, admin_session)
2. **Company Separation**: Strict company-based data isolation
3. **Activity Logging**: Comprehensive audit trail for all actions
4. **Notification System**: Real-time user notifications with email integration
5. **Role-Based Access**: Super Admin, Admin, Manager, User permissions
6. **File Upload**: Secure file handling with validation
7. **Export System**: Data export with filtering capabilities

## Security Features:

1. **CSRF Protection**: All forms use CSRF tokens
2. **SQL Injection Prevention**: Prepared statements throughout
3. **XSS Protection**: Output sanitization with h() function
4. **Session Security**: Secure cookies with proper settings
5. **Input Validation**: Comprehensive validation before database operations
6. **Company Isolation**: Users can only access their own company data
