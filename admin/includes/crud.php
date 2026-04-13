<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/activity.php';

// =============================================
// SLIDER CRUD
// =============================================
function get_all_sliders(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT s.*, c.code AS company_name FROM slider_contents s LEFT JOIN companies c ON c.id = s.company_id';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE s.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY s.company_id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_slider_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM slider_contents WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_slider(PDO $pdo, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file'])) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'sliders');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO slider_contents (company_id, title, subtitle, image_url, button_text, button_url, is_active)
         VALUES (:company_id, :title, :subtitle, :image_url, :button_text, :button_url, :is_active)'
    );
    $stmt->execute([
        ':company_id'  => (int) $data['company_id'],
        ':title'       => sanitize_text((string) ($data['title'] ?? '')),
        ':subtitle'    => sanitize_text((string) ($data['subtitle'] ?? '')),
        ':image_url'   => $imageUrl,
        ':button_text' => sanitize_text((string) ($data['button_text'] ?? '')),
        ':button_url'  => sanitize_text((string) ($data['button_url'] ?? '')),
        ':is_active'   => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'SLIDER_CREATED', 'slider_contents', $id);
    return ['success' => true, 'message' => 'สร้างรูปภาพสไลด์สำเร็จ', 'id' => $id];
}

function update_slider(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
{
    // Keep existing image unless a new file is uploaded
    $existing = get_slider_by_id($pdo, $id);
    $imageUrl = $existing['image_url'] ?? '';
    if (!empty($files['image_file']) && ($files['image_file']['error'] ?? 4) === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'sliders');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE slider_contents SET company_id = :company_id, title = :title, subtitle = :subtitle,
         image_url = :image_url, button_text = :button_text, button_url = :button_url,
         is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':company_id'  => (int) $data['company_id'],
        ':title'       => sanitize_text((string) ($data['title'] ?? '')),
        ':subtitle'    => sanitize_text((string) ($data['subtitle'] ?? '')),
        ':image_url'   => $imageUrl,
        ':button_text' => sanitize_text((string) ($data['button_text'] ?? '')),
        ':button_url'  => sanitize_text((string) ($data['button_url'] ?? '')),
        ':is_active'   => !empty($data['is_active']) ? 1 : 0,
        ':id'          => $id,
    ]);
    log_activity($pdo, $adminId, 'SLIDER_UPDATED', 'slider_contents', $id);
    return ['success' => true, 'message' => 'อัปเดตรูปภาพสไลด์สำเร็จ'];
}

function delete_slider(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM slider_contents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'SLIDER_DELETED', 'slider_contents', $id);
    return ['success' => true, 'message' => 'ลบรูปภาพสไลด์สำเร็จ'];
}

// =============================================
// PARTNER CRUD
// =============================================
function get_all_partners(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT p.*, c.code AS company_name FROM partners p LEFT JOIN companies c ON c.id = p.company_id';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE p.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY p.company_id, p.partner_order ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_partner_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM partners WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_partner(PDO $pdo, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $logoUrl = sanitize_text((string) ($data['logo_url'] ?? ''));
    if (!empty($files['logo_file'])) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('logo_file', 'partners');
            if ($upload) {
                $logoUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO partners (company_id, name, logo_url, website_url, partner_order, is_active)
         VALUES (:company_id, :name, :logo_url, :website_url, :partner_order, :is_active)'
    );
    $stmt->execute([
        ':company_id'    => (int) $data['company_id'],
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':logo_url'      => $logoUrl,
        ':website_url'   => sanitize_text((string) ($data['website_url'] ?? '')),
        ':partner_order' => (int) ($data['partner_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'PARTNER_CREATED', 'partners', $id);
    return ['success' => true, 'message' => 'เพิ่มข้อมูลพันธมิตรสำเร็จ', 'id' => $id];
}

function update_partner(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
{
    // Keep existing logo unless a new file is uploaded
    $existing = get_partner_by_id($pdo, $id);
    $logoUrl = $existing['logo_url'] ?? '';
    if (!empty($files['logo_file']) && ($files['logo_file']['error'] ?? 4) === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('logo_file', 'partners');
            if ($upload) {
                $logoUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE partners SET company_id = :company_id, name = :name, logo_url = :logo_url,
         website_url = :website_url, partner_order = :partner_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':company_id'    => (int) $data['company_id'],
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':logo_url'      => $logoUrl,
        ':website_url'   => sanitize_text((string) ($data['website_url'] ?? '')),
        ':partner_order' => (int) ($data['partner_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
        ':id'            => $id,
    ]);
    log_activity($pdo, $adminId, 'PARTNER_UPDATED', 'partners', $id);
    return ['success' => true, 'message' => 'อัปเดตข้อมูลพันธมิตรสำเร็จ'];
}

function delete_partner(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM partners WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'PARTNER_DELETED', 'partners', $id);
    return ['success' => true, 'message' => 'ลบข้อมูลพันธมิตรสำเร็จ'];
}

// =============================================
// PRODUCT CRUD (KOCH)
// =============================================
function get_all_products_admin(PDO $pdo, ?int $companyId = null): array
{
    $stmt = $pdo->prepare('SELECT id, name, description, category, image_url, is_active, display_order, created_at, updated_at FROM products ORDER BY display_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_product_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_product(PDO $pdo, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file']) && $files['image_file']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'products');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO products (name, description, category, image_url, display_order, is_active)
         VALUES (:name, :description, :category, :image_url, :display_order, :is_active)'
    );
    $stmt->execute([
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':category'      => sanitize_text((string) ($data['category'] ?? '')),
        ':image_url'     => $imageUrl,
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'PRODUCT_CREATED', 'products', $id);
    return ['success' => true, 'message' => 'เพิ่มข้อมูลสินค้าสำเร็จ', 'id' => $id];
}

function update_product(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
{
    // Keep existing image unless a new file is uploaded
    $existing = get_product_by_id($pdo, $id);
    $imageUrl = $existing['image_url'] ?? '';
    if (!empty($files['image_file']) && ($files['image_file']['error'] ?? 4) === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'products');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE products SET name = :name, description = :description, category = :category,
         image_url = :image_url, display_order = :display_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':category'      => sanitize_text((string) ($data['category'] ?? '')),
        ':image_url'     => $imageUrl,
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
        ':id'            => $id,
    ]);
    log_activity($pdo, $adminId, 'PRODUCT_UPDATED', 'products', $id);
    return ['success' => true, 'message' => 'อัปเดตข้อมูลสินค้าสำเร็จ'];
}

function delete_product(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'PRODUCT_DELETED', 'products', $id);
    return ['success' => true, 'message' => 'ลบข้อมูลสินค้าสำเร็จ'];
}

// =============================================
// TRUCK CARDS CRUD (TNB)
// =============================================
function get_all_truck_cards_admin(PDO $pdo, ?int $companyId = null): array
{
    $stmt = $pdo->prepare('SELECT id, name, description, image_url, capacity, display_order, is_active, created_at, updated_at FROM truck_cards ORDER BY display_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_truck_card_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM truck_cards WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_truck_card(PDO $pdo, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file'])) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'truck-cards');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO truck_cards (name, description, image_url, capacity, display_order, is_active)
         VALUES (:name, :description, :image_url, :capacity, :display_order, :is_active)'
    );
    $stmt->execute([
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':image_url'     => $imageUrl,
        ':capacity'      => sanitize_text((string) ($data['capacity'] ?? '')),
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'TRUCK_CARD_CREATED', 'truck_cards', $id);
    return ['success' => true, 'message' => 'เพิ่มข้อมูลรถขนส่งสำเร็จ', 'id' => $id];
}

function update_truck_card(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file'])) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'truck-cards');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE truck_cards SET name = :name, description = :description, image_url = :image_url,
         capacity = :capacity, display_order = :display_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':image_url'     => $imageUrl,
        ':capacity'      => sanitize_text((string) ($data['capacity'] ?? '')),
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
        ':id'            => $id,
    ]);
    log_activity($pdo, $adminId, 'TRUCK_CARD_UPDATED', 'truck_cards', $id);
    return ['success' => true, 'message' => 'อัปเดตข้อมูลรถขนส่งสำเร็จ'];
}

function delete_truck_card(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM truck_cards WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'TRUCK_CARD_DELETED', 'truck_cards', $id);
    return ['success' => true, 'message' => 'ลบข้อมูลรถขนส่งสำเร็จ'];
}

// =============================================
// USER MANAGEMENT CRUD
// =============================================
function get_all_users_admin(PDO $pdo, ?int $companyId = null, ?string $role = null, ?string $status = null): array
{
    $sql = 'SELECT u.*, c.code AS company_name, c.code AS company_code FROM users u LEFT JOIN companies c ON c.id = u.company_id WHERE 1=1';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' AND u.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    if ($role !== null && $role !== '') {
        $sql .= ' AND u.role = :role';
        $params[':role'] = $role;
    }
    if ($status !== null && $status !== '') {
        $sql .= ' AND u.status = :status';
        $params[':status'] = $status;
    }
    $sql .= ' ORDER BY u.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function update_user_role(PDO $pdo, int $userId, string $newRole, int $adminId): array
{
    $validRoles = ['super_admin', 'admin', 'user'];
    if (!in_array($newRole, $validRoles, true)) {
        return ['success' => false, 'message' => 'Invalid role.'];
    }
    
    // Prevent editing own role (only super_admin can change anyone's role)
    if ($userId === $adminId) {
        return ['success' => false, 'message' => 'You cannot change your own role.'];
    }
    
    // Only super_admin can assign super_admin role
    $adminStmt = $pdo->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
    $adminStmt->execute([':id' => $adminId]);
    $adminRole = $adminStmt->fetchColumn();
    if ($newRole === 'super_admin' && $adminRole !== 'super_admin') {
        return ['success' => false, 'message' => 'Only Super Admin can assign Super Admin role.'];
    }
    
    $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
    $stmt->execute([':role' => $newRole, ':id' => $userId]);
    log_activity($pdo, $adminId, 'USER_ROLE_CHANGED', 'users', $userId);
    return ['success' => true, 'message' => 'อัปเดตสิทธิ์ผู้ใช้งานสำเร็จ (' . $newRole . ')'];
}

function update_user_status(PDO $pdo, int $userId, string $newStatus, int $adminId): array
{
    $validStatuses = ['active', 'inactive', 'suspended', 'pending_verification'];
    if (!in_array($newStatus, $validStatuses, true)) {
        return ['success' => false, 'message' => 'Invalid status.'];
    }
    
    // Prevent editing own status
    if ($userId === $adminId) {
        return ['success' => false, 'message' => 'You cannot change your own status.'];
    }
    
    $stmt = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $newStatus, ':id' => $userId]);
    log_activity($pdo, $adminId, 'USER_STATUS_CHANGED', 'users', $userId);
    return ['success' => true, 'message' => 'อัปเดตสถานะผู้ใช้งานสำเร็จ (' . $newStatus . ')'];
}

function delete_user_admin(PDO $pdo, int $userId, int $adminId): array
{
    if ($userId === $adminId) {
        return ['success' => false, 'message' => 'Cannot delete yourself.'];
    }
    
    // Soft delete - ไม่ลบข้อมูลจริง แค่เปลี่ยนสถานะ
    $stmt = $pdo->prepare('UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id');
    $stmt->execute([':id' => $userId, ':status' => 'inactive']);
    
    log_activity($pdo, $adminId, 'USER_DELETED', 'users', $userId);
    return ['success' => true, 'message' => 'ลบผู้ใช้งานสำเร็จ'];
}

// =============================================
// QUOTATION MANAGEMENT
// =============================================
function get_all_koch_quotations(PDO $pdo, ?string $status = null, int $limit = 50): array
{
    $sql = 'SELECT kq.*, u.username FROM koch_quotations kq LEFT JOIN users u ON u.id = kq.user_id WHERE 1=1';
    $params = [];
    if ($status !== null && $status !== '') {
        $sql .= ' AND kq.status = :status';
        $params[':status'] = $status;
    }
    $sql .= ' ORDER BY kq.created_at DESC LIMIT ' . (int) $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_all_tnb_quotations(PDO $pdo, ?string $status = null, int $limit = 50): array
{
    $sql = 'SELECT tq.*, u.username FROM tnb_quotations tq LEFT JOIN users u ON u.id = tq.user_id WHERE 1=1';
    $params = [];
    if ($status !== null && $status !== '') {
        $sql .= ' AND tq.status = :status';
        $params[':status'] = $status;
    }
    $sql .= ' ORDER BY tq.created_at DESC LIMIT ' . (int) $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function update_koch_quotation_status(PDO $pdo, int $id, string $status, ?float $price, int $adminId): array
{
    $stmt = $pdo->prepare('UPDATE koch_quotations SET status = :status, quoted_by = :admin, quoted_at = NOW() WHERE id = :id');
    $stmt->execute([':status' => $status, ':admin' => $adminId, ':id' => $id]);
    log_activity($pdo, $adminId, 'KOCH_QUOTATION_STATUS_CHANGED', 'koch_quotations', $id);
    return ['success' => true, 'message' => 'อัปเดตสถานะใบเสนอราคาสำเร็จ'];
}

function update_tnb_quotation_status(PDO $pdo, int $id, string $status, ?float $price, int $adminId): array
{
    $stmt = $pdo->prepare('UPDATE tnb_quotations SET status = :status, quoted_by = :admin, quoted_at = NOW() WHERE id = :id');
    $stmt->execute([':status' => $status, ':admin' => $adminId, ':id' => $id]);
    log_activity($pdo, $adminId, 'TNB_QUOTATION_STATUS_CHANGED', 'tnb_quotations', $id);
    return ['success' => true, 'message' => 'อัปเดตสถานะคำขอบริการขนส่งสำเร็จ'];
}

// =============================================
// EMAIL TEMPLATES CRUD
// =============================================
function get_all_email_templates(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT et.*, c.code AS company_name FROM email_templates et LEFT JOIN companies c ON c.id = et.company_id WHERE 1=1';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' AND (et.company_id = :cid OR et.company_id IS NULL)';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY et.name ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function create_email_template(PDO $pdo, array $data, int $adminId): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO email_templates (name, subject, html_content, text_content, variables, company_id, is_active)
         VALUES (:name, :subject, :html_content, :text_content, :variables, :company_id, :is_active)'
    );
    $stmt->execute([
        ':name'         => sanitize_text((string) ($data['name'] ?? '')),
        ':subject'      => sanitize_text((string) ($data['subject'] ?? '')),
        ':html_content' => (string) ($data['html_content'] ?? ''),
        ':text_content' => (string) ($data['text_content'] ?? ''),
        ':variables'    => (string) ($data['variables'] ?? ''),
        ':company_id'   => !empty($data['company_id']) ? (int) $data['company_id'] : null,
        ':is_active'    => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'EMAIL_TEMPLATE_CREATED', 'email_templates', $id);
    return ['success' => true, 'message' => 'เพิ่มเทมเพลตอีเมลสำเร็จ', 'id' => $id];
}

function update_email_template(PDO $pdo, int $id, array $data, int $adminId): array
{
    $stmt = $pdo->prepare(
        'UPDATE email_templates SET name = :name, subject = :subject, html_content = :html_content,
         text_content = :text_content, variables = :variables, company_id = :company_id, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':name'         => sanitize_text((string) ($data['name'] ?? '')),
        ':subject'      => sanitize_text((string) ($data['subject'] ?? '')),
        ':html_content' => (string) ($data['html_content'] ?? ''),
        ':text_content' => (string) ($data['text_content'] ?? ''),
        ':variables'    => (string) ($data['variables'] ?? ''),
        ':company_id'   => !empty($data['company_id']) ? (int) $data['company_id'] : null,
        ':is_active'    => !empty($data['is_active']) ? 1 : 0,
        ':id'           => $id,
    ]);
    log_activity($pdo, $adminId, 'EMAIL_TEMPLATE_UPDATED', 'email_templates', $id);
    return ['success' => true, 'message' => 'อัปเดตเทมเพลตอีเมลสำเร็จ'];
}

function delete_email_template(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM email_templates WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'EMAIL_TEMPLATE_DELETED', 'email_templates', $id);
    return ['success' => true, 'message' => 'ลบเทมเพลตอีเมลสำเร็จ'];
}

// =============================================
// ACTIVITY LOGS WITH FILTERS
// =============================================
function get_activity_logs_filtered(PDO $pdo, array $filters = [], int $limit = 50, int $offset = 0): array
{
    $sql = 'SELECT al.*, u.username, u.first_name, u.last_name, c.code AS company_code, c.code AS company_name
            FROM activity_logs al
            LEFT JOIN users u ON u.id = al.user_id
            LEFT JOIN companies c ON c.id = al.company_id
            WHERE 1=1';
    $params = [];

    if (!empty($filters['date_from'])) {
        $sql .= ' AND al.created_at >= :date_from';
        $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
    }
    if (!empty($filters['date_to'])) {
        $sql .= ' AND al.created_at <= :date_to';
        $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
    }
    if (!empty($filters['user_id'])) {
        $sql .= ' AND al.user_id = :user_id';
        $params[':user_id'] = (int) $filters['user_id'];
    }
    if (!empty($filters['company_id'])) {
        $sql .= ' AND al.company_id = :company_id';
        $params[':company_id'] = (int) $filters['company_id'];
    }
    if (!empty($filters['action'])) {
        $sql .= ' AND al.action = :action';
        $params[':action'] = $filters['action'];
    }
    if (!empty($filters['ip_address'])) {
        $sql .= ' AND al.ip_address LIKE :ip';
        $params[':ip'] = '%' . $filters['ip_address'] . '%';
    }

    $countSql = preg_replace('/^SELECT .+ FROM/', 'SELECT COUNT(*) FROM', $sql, 1);
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $sql .= ' ORDER BY al.created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return [
        'data' => $stmt->fetchAll(),
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
    ];
}

function get_distinct_actions(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT DISTINCT action FROM activity_logs ORDER BY action ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// =============================================
// NOTIFICATION SYSTEM
// =============================================
function create_admin_notification(PDO $pdo, int $userId, string $title, string $message, string $type = 'info', string $priority = 'normal', ?string $relatedTable = null, ?int $relatedId = null, ?int $companyId = null): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, company_id, title, message, type, priority, related_table, related_id)
         VALUES (:user_id, :company_id, :title, :message, :type, :priority, :related_table, :related_id)'
    );
    $stmt->execute([
        ':user_id'       => $userId,
        ':company_id'    => $companyId,
        ':title'         => $title,
        ':message'       => $message,
        ':type'          => $type,
        ':priority'      => $priority,
        ':related_table' => $relatedTable,
        ':related_id'    => $relatedId,
    ]);
    return (int) $pdo->lastInsertId();
}

function notify_admins_login_failed(PDO $pdo, string $username, string $ipAddress, int $attemptCount): void
{
    $threshold = (int) get_setting_value_cached($pdo, 'notification_login_failed_threshold', '3');
    if ($attemptCount < $threshold) {
        return;
    }
    $admins = $pdo->prepare("SELECT id FROM users WHERE role IN ('super_admin', 'admin') AND status = 'active'");
    $admins->execute();
    $adminIds = $admins->fetchAll(PDO::FETCH_COLUMN);
    foreach ($adminIds as $adminId) {
        create_admin_notification(
            $pdo,
            (int) $adminId,
            'การแจ้งเตือนความปลอดภัย: ล็อกอินล้มเหลวหลายครั้ง',
            "ผู้ใช้งาน '{$username}' ล็อกอินล้มเหลว {$attemptCount} ครั้ง จาก IP: {$ipAddress}",
            'warning',
            'high',
            'users',
            null
        );
    }
}

function notify_admins_new_quotation(PDO $pdo, string $type, string $quotationNumber, string $customerName): void
{
    $admins = $pdo->prepare("SELECT id FROM users WHERE role IN ('super_admin', 'admin') AND status = 'active'");
    $admins->execute();
    $adminIds = $admins->fetchAll(PDO::FETCH_COLUMN);
    $label = $type === 'koch' ? 'ใบเสนอราคา KOCH' : 'คำขอบริการขนส่ง TNB';
    // Resolve the company_id for the notification
    $companyCode = $type === 'koch' ? 'KOCH' : 'TNB';
    $notifCompanyId = get_company_id_by_code($pdo, $companyCode);
    foreach ($adminIds as $adminId) {
        create_admin_notification(
            $pdo,
            (int) $adminId,
            "{$label} ล่าสุด: {$quotationNumber}",
            "ลูกค้า: {$customerName} ได้ส่ง{$label}เข้ามาใหม่",
            'info',
            'normal',
            $type === 'koch' ? 'koch_quotations' : 'tnb_quotations',
            null,
            $notifCompanyId
        );
    }
}

function get_setting_value_cached(PDO $pdo, string $key, string $default = ''): string
{
    static $cache = [];
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    $stmt = $pdo->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1');
    $stmt->execute([':key' => $key]);
    $val = $stmt->fetchColumn();
    $cache[$key] = $val !== false ? (string) $val : $default;
    return $cache[$key];
}

// =============================================
// SYSTEM SETTINGS CRUD
// =============================================
function get_all_settings(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT * FROM system_settings ORDER BY setting_key ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function update_system_setting(PDO $pdo, string $key, string $value, int $adminId): array
{
    $stmt = $pdo->prepare('UPDATE system_settings SET setting_value = :val WHERE setting_key = :key AND is_editable = 1');
    $stmt->execute([':val' => $value, ':key' => $key]);
    log_activity($pdo, $adminId, 'SETTING_UPDATED', 'system_settings', 0);
    return ['success' => true, 'message' => "อัปเดตการตั้งค่า '{$key}' สำเร็จ"];
}

// =============================================
// RBAC PERMISSION CHECK
// =============================================
function user_has_permission(PDO $pdo, int $userId, string $permissionName, string $minLevel = 'read'): bool
{
    $levels = ['read' => 1, 'write' => 2, 'delete' => 3, 'admin' => 4];
    $stmt = $pdo->prepare('SELECT permission_value FROM user_permissions WHERE user_id = :uid AND permission_name = :pname LIMIT 1');
    $stmt->execute([':uid' => $userId, ':pname' => $permissionName]);
    $val = $stmt->fetchColumn();
    if ($val === false) {
        return false;
    }
    return ($levels[$val] ?? 0) >= ($levels[$minLevel] ?? 0);
}

function get_user_permissions_list(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT permission_name, permission_value FROM user_permissions WHERE user_id = :uid ORDER BY permission_name');
    $stmt->execute([':uid' => $userId]);
    return $stmt->fetchAll();
}

function set_user_permission(PDO $pdo, int $userId, string $permissionName, string $value, int $adminId): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO user_permissions (user_id, permission_name, permission_value) VALUES (:uid, :pname, :pval)
         ON DUPLICATE KEY UPDATE permission_value = :pval2'
    );
    $stmt->execute([':uid' => $userId, ':pname' => $permissionName, ':pval' => $value, ':pval2' => $value]);
    log_activity($pdo, $adminId, 'PERMISSION_UPDATED', 'user_permissions', $userId);
    return ['success' => true, 'message' => "Permission '{$permissionName}' set to '{$value}'."];
}

function remove_user_permission(PDO $pdo, int $userId, string $permissionName, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM user_permissions WHERE user_id = :uid AND permission_name = :pname');
    $stmt->execute([':uid' => $userId, ':pname' => $permissionName]);
    log_activity($pdo, $adminId, 'PERMISSION_REMOVED', 'user_permissions', $userId);
    return ['success' => true, 'message' => "Permission '{$permissionName}' removed."];
}

// ── Featured Products CRUD ────────────────────────────────────────

function get_all_featured_products_admin(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT fp.*, c.code AS company_name FROM featured_products fp LEFT JOIN companies c ON c.id = fp.company_id';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE fp.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY fp.display_order ASC, fp.id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

function get_active_featured_products(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM featured_products WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
    return $stmt->fetchAll() ?: [];
}

function get_featured_product_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM featured_products WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_featured_product(PDO $pdo, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file']) && $files['image_file']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'featured');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO featured_products (company_id, name, description, image_url, website_url, display_order, is_active)
         VALUES (:company_id, :name, :description, :image_url, :website_url, :display_order, :is_active)'
    );
    $stmt->execute([
        ':company_id'    => (int) $data['company_id'],
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':image_url'     => $imageUrl,
        ':website_url'   => sanitize_text((string) ($data['website_url'] ?? '')),
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'FEATURED_PRODUCT_CREATED', 'featured_products', $id);
    return ['success' => true, 'message' => 'เพิ่มสินค้าแนะนำสำเร็จ', 'id' => $id];
}

function update_featured_product(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file']) && $files['image_file']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'featured');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE featured_products SET company_id = :company_id, name = :name, description = :description,
         image_url = :image_url, website_url = :website_url, display_order = :display_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':company_id'    => (int) $data['company_id'],
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':image_url'     => $imageUrl,
        ':website_url'   => sanitize_text((string) ($data['website_url'] ?? '')),
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
        ':id'            => $id,
    ]);
    log_activity($pdo, $adminId, 'FEATURED_PRODUCT_UPDATED', 'featured_products', $id);
    return ['success' => true, 'message' => 'อัปเดตสินค้าแนะนำสำเร็จ'];
}

function delete_featured_product(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM featured_products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'FEATURED_PRODUCT_DELETED', 'featured_products', $id);
    return ['success' => true, 'message' => 'ลบสินค้าแนะนำสำเร็จ'];
}

// =============================================
// SYSTEM SETTINGS (EMAILS)
// =============================================
function handle_system_settings_emails(PDO $pdo, string $action, array $post, int $adminId): array
{
    if ($action === 'update_admin_emails') {
        $kochId = get_company_id_by_code($pdo, 'KOCH');
        $tnbId = get_company_id_by_code($pdo, 'TNB');
        
        $sql = 'INSERT INTO system_settings (setting_key, setting_value, company_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)';
        $stmt = $pdo->prepare($sql);
        
        if (isset($post['admin_notify_email_koch'])) {
            $stmt->execute(['admin_notify_email_koch', trim((string)$post['admin_notify_email_koch']), $kochId]);
        }
        if (isset($post['admin_notify_email_tnb'])) {
            $stmt->execute(['admin_notify_email_tnb', trim((string)$post['admin_notify_email_tnb']), $tnbId]);
        }
        
        log_activity($pdo, $adminId, 'SETTINGS_UPDATED', 'system_settings', 0, [], ['emails_updated' => true]);
        return ['success' => true, 'message' => 'บันทึกอีเมลแจ้งเตือนสำเร็จ'];
    }

    if ($action === 'update_smtp_config') {
        $sql = 'INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)';
        $stmt = $pdo->prepare($sql);
        
        $fields = ['smtp_host', 'smtp_port', 'smtp_user'];
        foreach ($fields as $field) {
            if (isset($post[$field])) {
                $stmt->execute([$field, trim((string)$post[$field])]);
            }
        }
        
        // Passwords shouldn't be overridden with empty if already set (unless they explicitly clear it)
        if (!empty($post['smtp_pass'])) {
            $stmt->execute(['smtp_pass', trim((string)$post['smtp_pass'])]);
        }
        
        log_activity($pdo, $adminId, 'SETTINGS_UPDATED', 'system_settings', 0, [], ['smtp_updated' => true]);
        return ['success' => true, 'message' => 'บันทึกการตั้งค่า SMTP สำเร็จ'];
    }

    return ['success' => false, 'message' => 'Invalid action for settings.'];
}

