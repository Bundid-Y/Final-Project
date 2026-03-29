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
    $sql = 'SELECT s.*, c.name AS company_name FROM slider_contents s LEFT JOIN companies c ON c.id = s.company_id';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE s.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY s.company_id, s.slide_order ASC';
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
        'INSERT INTO slider_contents (company_id, title, subtitle, image_url, button_text, button_url, slide_order, is_active)
         VALUES (:company_id, :title, :subtitle, :image_url, :button_text, :button_url, :slide_order, :is_active)'
    );
    $stmt->execute([
        ':company_id'  => (int) $data['company_id'],
        ':title'       => sanitize_text((string) ($data['title'] ?? '')),
        ':subtitle'    => sanitize_text((string) ($data['subtitle'] ?? '')),
        ':image_url'   => $imageUrl,
        ':button_text' => sanitize_text((string) ($data['button_text'] ?? '')),
        ':button_url'  => sanitize_text((string) ($data['button_url'] ?? '')),
        ':slide_order' => (int) ($data['slide_order'] ?? 0),
        ':is_active'   => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'SLIDER_CREATED', 'slider_contents', $id);
    return ['success' => true, 'message' => 'Slider created.', 'id' => $id];
}

function update_slider(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
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
        'UPDATE slider_contents SET company_id = :company_id, title = :title, subtitle = :subtitle,
         image_url = :image_url, button_text = :button_text, button_url = :button_url,
         slide_order = :slide_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':company_id'  => (int) $data['company_id'],
        ':title'       => sanitize_text((string) ($data['title'] ?? '')),
        ':subtitle'    => sanitize_text((string) ($data['subtitle'] ?? '')),
        ':image_url'   => $imageUrl,
        ':button_text' => sanitize_text((string) ($data['button_text'] ?? '')),
        ':button_url'  => sanitize_text((string) ($data['button_url'] ?? '')),
        ':slide_order' => (int) ($data['slide_order'] ?? 0),
        ':is_active'   => !empty($data['is_active']) ? 1 : 0,
        ':id'          => $id,
    ]);
    log_activity($pdo, $adminId, 'SLIDER_UPDATED', 'slider_contents', $id);
    return ['success' => true, 'message' => 'Slider updated.'];
}

function delete_slider(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM slider_contents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'SLIDER_DELETED', 'slider_contents', $id);
    return ['success' => true, 'message' => 'Slider deleted.'];
}

// =============================================
// PARTNER CRUD
// =============================================
function get_all_partners(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT p.*, c.name AS company_name FROM partners p LEFT JOIN companies c ON c.id = p.company_id';
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
    return ['success' => true, 'message' => 'Partner created.', 'id' => $id];
}

function update_partner(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
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
    return ['success' => true, 'message' => 'Partner updated.'];
}

function delete_partner(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM partners WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'PARTNER_DELETED', 'partners', $id);
    return ['success' => true, 'message' => 'Partner deleted.'];
}

// =============================================
// PRODUCT CRUD (KOCH)
// =============================================
function get_all_products_admin(PDO $pdo, ?int $companyId = null): array
{
    $stmt = $pdo->prepare('SELECT id, name, description, category, image_url, price, is_active, display_order, created_at, updated_at FROM products ORDER BY display_order ASC');
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
    return ['success' => true, 'message' => 'Product created.', 'id' => $id];
}

function update_product(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
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
    return ['success' => true, 'message' => 'Product updated.'];
}

function delete_product(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'PRODUCT_DELETED', 'products', $id);
    return ['success' => true, 'message' => 'Product deleted.'];
}

// =============================================
// TRUCK TYPES CRUD (TNB)
// =============================================
function get_all_truck_types_admin(PDO $pdo, ?int $companyId = null): array
{
    $stmt = $pdo->prepare('SELECT id, name, description, image_url, capacity, dimensions, price_range, is_active, display_order, created_at, updated_at FROM truck_types ORDER BY display_order ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_truck_type_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM truck_types WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function create_truck_type(PDO $pdo, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file'])) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'trucks');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO truck_types (name, description, image_url, capacity, dimensions, price_range, display_order, is_active)
         VALUES (:name, :description, :image_url, :capacity, :dimensions, :price_range, :display_order, :is_active)'
    );
    $stmt->execute([
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':image_url'     => $imageUrl,
        ':capacity'      => sanitize_text((string) ($data['capacity'] ?? '')),
        ':dimensions'    => sanitize_text((string) ($data['dimensions'] ?? '')),
        ':price_range'   => sanitize_text((string) ($data['price_range'] ?? '')),
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'TRUCK_TYPE_CREATED', 'truck_types', $id);
    return ['success' => true, 'message' => 'Truck type created.', 'id' => $id];
}

function update_truck_type(PDO $pdo, int $id, array $data, int $adminId, array $files = []): array
{
    // Handle file upload
    $imageUrl = sanitize_text((string) ($data['image_url'] ?? ''));
    if (!empty($files['image_file'])) {
        require_once __DIR__ . '/upload.php';
        try {
            $upload = handle_uploaded_file('image_file', 'trucks');
            if ($upload) {
                $imageUrl = $upload['public_path'];
            }
        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE truck_types SET name = :name, description = :description, image_url = :image_url,
         capacity = :capacity, dimensions = :dimensions, price_range = :price_range,
         display_order = :display_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':name'          => sanitize_text((string) ($data['name'] ?? '')),
        ':description'   => sanitize_text((string) ($data['description'] ?? '')),
        ':image_url'     => $imageUrl,
        ':capacity'      => sanitize_text((string) ($data['capacity'] ?? '')),
        ':dimensions'    => sanitize_text((string) ($data['dimensions'] ?? '')),
        ':price_range'   => sanitize_text((string) ($data['price_range'] ?? '')),
        ':display_order' => (int) ($data['display_order'] ?? 0),
        ':is_active'     => !empty($data['is_active']) ? 1 : 0,
        ':id'            => $id,
    ]);
    log_activity($pdo, $adminId, 'TRUCK_TYPE_UPDATED', 'truck_types', $id);
    return ['success' => true, 'message' => 'Truck type updated.'];
}

function delete_truck_type(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM truck_types WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'TRUCK_TYPE_DELETED', 'truck_types', $id);
    return ['success' => true, 'message' => 'Truck type deleted.'];
}

// =============================================
// BRANCH CRUD
// =============================================
function get_all_branches_admin(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT b.*, c.name AS company_name FROM branches b LEFT JOIN companies c ON c.id = b.company_id';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE b.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY b.company_id, b.display_order ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $branches = $stmt->fetchAll();
    foreach ($branches as &$b) {
        $b['services'] = json_decode((string) ($b['services'] ?? '[]'), true) ?: [];
    }
    return $branches;
}

function get_branch_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM branches WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $branch = $stmt->fetch() ?: null;
    if ($branch) {
        $branch['services'] = json_decode((string) ($branch['services'] ?? '[]'), true) ?: [];
    }
    return $branch;
}

function create_branch(PDO $pdo, array $data, int $adminId): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO branches (company_id, name, name_en, slug, description, address, phone, email, google_maps_url, latitude, longitude, image_url, is_headquarters, services, area_size, staff_count, operating_hours, display_order, is_active)
         VALUES (:company_id, :name, :name_en, :slug, :description, :address, :phone, :email, :google_maps_url, :latitude, :longitude, :image_url, :is_headquarters, :services, :area_size, :staff_count, :operating_hours, :display_order, :is_active)'
    );
    $services = is_array($data['services'] ?? null) ? json_encode($data['services']) : (string) ($data['services'] ?? '[]');
    $stmt->execute([
        ':company_id'      => (int) $data['company_id'],
        ':name'            => sanitize_text((string) ($data['name'] ?? '')),
        ':name_en'         => sanitize_text((string) ($data['name_en'] ?? '')),
        ':slug'            => sanitize_text((string) ($data['slug'] ?? '')),
        ':description'     => sanitize_text((string) ($data['description'] ?? '')),
        ':address'         => sanitize_text((string) ($data['address'] ?? '')),
        ':phone'           => sanitize_text((string) ($data['phone'] ?? '')),
        ':email'           => sanitize_text((string) ($data['email'] ?? '')),
        ':google_maps_url' => (string) ($data['google_maps_url'] ?? ''),
        ':latitude'        => !empty($data['latitude']) ? (float) $data['latitude'] : null,
        ':longitude'       => !empty($data['longitude']) ? (float) $data['longitude'] : null,
        ':image_url'       => sanitize_text((string) ($data['image_url'] ?? '')),
        ':is_headquarters' => !empty($data['is_headquarters']) ? 1 : 0,
        ':services'        => $services,
        ':area_size'       => sanitize_text((string) ($data['area_size'] ?? '')),
        ':staff_count'     => (int) ($data['staff_count'] ?? 0),
        ':operating_hours' => sanitize_text((string) ($data['operating_hours'] ?? 'จันทร์-เสาร์ 08:00-17:00')),
        ':display_order'   => (int) ($data['display_order'] ?? 0),
        ':is_active'       => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'BRANCH_CREATED', 'branches', $id);
    return ['success' => true, 'message' => 'Branch created.', 'id' => $id];
}

function update_branch(PDO $pdo, int $id, array $data, int $adminId): array
{
    $services = is_array($data['services'] ?? null) ? json_encode($data['services']) : (string) ($data['services'] ?? '[]');
    $stmt = $pdo->prepare(
        'UPDATE branches SET company_id = :company_id, name = :name, name_en = :name_en, slug = :slug,
         description = :description, address = :address, phone = :phone, email = :email,
         google_maps_url = :google_maps_url, latitude = :latitude, longitude = :longitude,
         image_url = :image_url, is_headquarters = :is_headquarters, services = :services,
         area_size = :area_size, staff_count = :staff_count, operating_hours = :operating_hours,
         display_order = :display_order, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':company_id'      => (int) $data['company_id'],
        ':name'            => sanitize_text((string) ($data['name'] ?? '')),
        ':name_en'         => sanitize_text((string) ($data['name_en'] ?? '')),
        ':slug'            => sanitize_text((string) ($data['slug'] ?? '')),
        ':description'     => sanitize_text((string) ($data['description'] ?? '')),
        ':address'         => sanitize_text((string) ($data['address'] ?? '')),
        ':phone'           => sanitize_text((string) ($data['phone'] ?? '')),
        ':email'           => sanitize_text((string) ($data['email'] ?? '')),
        ':google_maps_url' => (string) ($data['google_maps_url'] ?? ''),
        ':latitude'        => !empty($data['latitude']) ? (float) $data['latitude'] : null,
        ':longitude'       => !empty($data['longitude']) ? (float) $data['longitude'] : null,
        ':image_url'       => sanitize_text((string) ($data['image_url'] ?? '')),
        ':is_headquarters' => !empty($data['is_headquarters']) ? 1 : 0,
        ':services'        => $services,
        ':area_size'       => sanitize_text((string) ($data['area_size'] ?? '')),
        ':staff_count'     => (int) ($data['staff_count'] ?? 0),
        ':operating_hours' => sanitize_text((string) ($data['operating_hours'] ?? 'จันทร์-เสาร์ 08:00-17:00')),
        ':display_order'   => (int) ($data['display_order'] ?? 0),
        ':is_active'       => !empty($data['is_active']) ? 1 : 0,
        ':id'              => $id,
    ]);
    log_activity($pdo, $adminId, 'BRANCH_UPDATED', 'branches', $id);
    return ['success' => true, 'message' => 'Branch updated.'];
}

function delete_branch(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM branches WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'BRANCH_DELETED', 'branches', $id);
    return ['success' => true, 'message' => 'Branch deleted.'];
}

// =============================================
// USER MANAGEMENT CRUD
// =============================================
function get_all_users_admin(PDO $pdo, ?int $companyId = null, ?string $role = null, ?string $status = null): array
{
    $sql = 'SELECT u.*, c.name AS company_name, c.code AS company_code FROM users u LEFT JOIN companies c ON c.id = u.company_id WHERE 1=1';
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
    $validRoles = ['super_admin', 'admin', 'manager', 'user'];
    if (!in_array($newRole, $validRoles, true)) {
        return ['success' => false, 'message' => 'Invalid role.'];
    }
    $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
    $stmt->execute([':role' => $newRole, ':id' => $userId]);
    log_activity($pdo, $adminId, 'USER_ROLE_CHANGED', 'users', $userId);
    return ['success' => true, 'message' => 'User role updated to ' . $newRole . '.'];
}

function update_user_status(PDO $pdo, int $userId, string $newStatus, int $adminId): array
{
    $validStatuses = ['active', 'inactive', 'suspended', 'pending_verification'];
    if (!in_array($newStatus, $validStatuses, true)) {
        return ['success' => false, 'message' => 'Invalid status.'];
    }
    $stmt = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $newStatus, ':id' => $userId]);
    log_activity($pdo, $adminId, 'USER_STATUS_CHANGED', 'users', $userId);
    return ['success' => true, 'message' => 'User status updated to ' . $newStatus . '.'];
}

function delete_user_admin(PDO $pdo, int $userId, int $adminId): array
{
    if ($userId === $adminId) {
        return ['success' => false, 'message' => 'Cannot delete yourself.'];
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    log_activity($pdo, $adminId, 'USER_DELETED', 'users', $userId);
    return ['success' => true, 'message' => 'User deleted.'];
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
    $stmt = $pdo->prepare('UPDATE koch_quotations SET status = :status, quoted_price = :price, quoted_by = :admin, quoted_at = NOW() WHERE id = :id');
    $stmt->execute([':status' => $status, ':price' => $price, ':admin' => $adminId, ':id' => $id]);
    log_activity($pdo, $adminId, 'KOCH_QUOTATION_STATUS_CHANGED', 'koch_quotations', $id);
    return ['success' => true, 'message' => 'Quotation status updated.'];
}

function update_tnb_quotation_status(PDO $pdo, int $id, string $status, ?float $price, int $adminId): array
{
    $stmt = $pdo->prepare('UPDATE tnb_quotations SET status = :status, quoted_price = :price, quoted_by = :admin, quoted_at = NOW() WHERE id = :id');
    $stmt->execute([':status' => $status, ':price' => $price, ':admin' => $adminId, ':id' => $id]);
    log_activity($pdo, $adminId, 'TNB_QUOTATION_STATUS_CHANGED', 'tnb_quotations', $id);
    return ['success' => true, 'message' => 'Transport request status updated.'];
}

// =============================================
// EMAIL RECIPIENTS CRUD
// =============================================
function get_all_email_recipients(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT er.*, c.name AS company_name FROM email_recipients er LEFT JOIN companies c ON c.id = er.company_id WHERE 1=1';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' AND er.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY er.event_type, er.recipient_name ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function create_email_recipient(PDO $pdo, array $data, int $adminId): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO email_recipients (company_id, event_type, recipient_email, recipient_name, is_active)
         VALUES (:company_id, :event_type, :recipient_email, :recipient_name, :is_active)'
    );
    $stmt->execute([
        ':company_id'       => !empty($data['company_id']) ? (int) $data['company_id'] : null,
        ':event_type'       => sanitize_text((string) ($data['event_type'] ?? '')),
        ':recipient_email'  => sanitize_text((string) ($data['recipient_email'] ?? '')),
        ':recipient_name'   => sanitize_text((string) ($data['recipient_name'] ?? '')),
        ':is_active'        => !empty($data['is_active']) ? 1 : 0,
    ]);
    $id = (int) $pdo->lastInsertId();
    log_activity($pdo, $adminId, 'EMAIL_RECIPIENT_CREATED', 'email_recipients', $id);
    return ['success' => true, 'message' => 'Email recipient added.', 'id' => $id];
}

function update_email_recipient(PDO $pdo, int $id, array $data, int $adminId): array
{
    $stmt = $pdo->prepare(
        'UPDATE email_recipients SET company_id = :company_id, event_type = :event_type,
         recipient_email = :recipient_email, recipient_name = :recipient_name, is_active = :is_active WHERE id = :id'
    );
    $stmt->execute([
        ':company_id'       => !empty($data['company_id']) ? (int) $data['company_id'] : null,
        ':event_type'       => sanitize_text((string) ($data['event_type'] ?? '')),
        ':recipient_email'  => sanitize_text((string) ($data['recipient_email'] ?? '')),
        ':recipient_name'   => sanitize_text((string) ($data['recipient_name'] ?? '')),
        ':is_active'        => !empty($data['is_active']) ? 1 : 0,
        ':id'               => $id,
    ]);
    log_activity($pdo, $adminId, 'EMAIL_RECIPIENT_UPDATED', 'email_recipients', $id);
    return ['success' => true, 'message' => 'Email recipient updated.'];
}

function delete_email_recipient(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM email_recipients WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'EMAIL_RECIPIENT_DELETED', 'email_recipients', $id);
    return ['success' => true, 'message' => 'Email recipient deleted.'];
}

// =============================================
// EMAIL TEMPLATES CRUD
// =============================================
function get_all_email_templates(PDO $pdo, ?int $companyId = null): array
{
    $sql = 'SELECT et.*, c.name AS company_name FROM email_templates et LEFT JOIN companies c ON c.id = et.company_id WHERE 1=1';
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
    return ['success' => true, 'message' => 'Email template created.', 'id' => $id];
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
    return ['success' => true, 'message' => 'Email template updated.'];
}

function delete_email_template(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM email_templates WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'EMAIL_TEMPLATE_DELETED', 'email_templates', $id);
    return ['success' => true, 'message' => 'Email template deleted.'];
}

// =============================================
// CONTACT MESSAGES
// =============================================
function get_all_contact_messages(PDO $pdo, ?int $companyId = null, ?string $status = null): array
{
    $sql = 'SELECT cm.*, c.name AS company_name FROM contact_messages cm LEFT JOIN companies c ON c.id = cm.company_id WHERE 1=1';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' AND cm.company_id = :cid';
        $params[':cid'] = $companyId;
    }
    if ($status !== null && $status !== '') {
        $sql .= ' AND cm.status = :status';
        $params[':status'] = $status;
    }
    $sql .= ' ORDER BY cm.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function reply_contact_message(PDO $pdo, int $id, string $replyMessage, int $adminId): array
{
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied', reply_message = :reply, replied_by = :admin, replied_at = NOW() WHERE id = :id");
    $stmt->execute([':reply' => $replyMessage, ':admin' => $adminId, ':id' => $id]);
    log_activity($pdo, $adminId, 'CONTACT_REPLIED', 'contact_messages', $id);
    return ['success' => true, 'message' => 'Reply sent.'];
}

// =============================================
// ACTIVITY LOGS WITH FILTERS
// =============================================
function get_activity_logs_filtered(PDO $pdo, array $filters = [], int $limit = 50, int $offset = 0): array
{
    $sql = 'SELECT al.*, u.username, u.first_name, u.last_name, c.code AS company_code
            FROM activity_logs al
            LEFT JOIN users u ON u.id = al.user_id
            LEFT JOIN companies c ON c.id = u.company_id
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
        $sql .= ' AND u.company_id = :company_id';
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

    $countSql = str_replace('SELECT al.*, u.username, u.first_name, u.last_name, c.code AS company_code', 'SELECT COUNT(*)', $sql);
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
function create_admin_notification(PDO $pdo, int $userId, string $title, string $message, string $type = 'info', string $priority = 'normal', ?string $relatedTable = null, ?int $relatedId = null): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, type, priority, related_table, related_id)
         VALUES (:user_id, :title, :message, :type, :priority, :related_table, :related_id)'
    );
    $stmt->execute([
        ':user_id'       => $userId,
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
            'Security Alert: Multiple Failed Logins',
            "User '{$username}' failed login {$attemptCount} times from IP: {$ipAddress}",
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
    $label = $type === 'koch' ? 'KOCH Quotation' : 'TNB Transport Request';
    foreach ($adminIds as $adminId) {
        create_admin_notification(
            $pdo,
            (int) $adminId,
            "New {$label}: {$quotationNumber}",
            "Customer: {$customerName} submitted a new {$label}.",
            'info',
            'normal',
            $type === 'koch' ? 'koch_quotations' : 'tnb_quotations',
            null
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
    return ['success' => true, 'message' => "Setting '{$key}' updated."];
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
    $sql = 'SELECT fp.*, c.name AS company_name FROM featured_products fp LEFT JOIN companies c ON c.id = fp.company_id';
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
    return ['success' => true, 'message' => 'Featured product created.', 'id' => $id];
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
    return ['success' => true, 'message' => 'Featured product updated.'];
}

function delete_featured_product(PDO $pdo, int $id, int $adminId): array
{
    $stmt = $pdo->prepare('DELETE FROM featured_products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    log_activity($pdo, $adminId, 'FEATURED_PRODUCT_DELETED', 'featured_products', $id);
    return ['success' => true, 'message' => 'Featured product deleted.'];
}
