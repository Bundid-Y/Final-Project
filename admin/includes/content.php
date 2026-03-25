<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function get_active_sliders(PDO $pdo, int $companyId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, title, subtitle, image_url, button_text, button_url, slide_order
         FROM slider_contents
         WHERE company_id = :company_id AND is_active = 1
         ORDER BY slide_order ASC'
    );
    $stmt->execute([':company_id' => $companyId]);
    return $stmt->fetchAll();
}

function get_active_partners(PDO $pdo, int $companyId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, name, logo_url, website_url, partner_order
         FROM partners
         WHERE company_id = :company_id AND is_active = 1
         ORDER BY partner_order ASC'
    );
    $stmt->execute([':company_id' => $companyId]);
    return $stmt->fetchAll();
}

function get_active_products(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT id, name, description, category, image_url, price, display_order
         FROM products
         WHERE is_active = 1
         ORDER BY display_order ASC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_active_truck_types(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT id, name, description, image_url, capacity, dimensions, price_range, display_order
         FROM truck_types
         WHERE is_active = 1
         ORDER BY display_order ASC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_active_branches(PDO $pdo, int $companyId): array
{
    $stmt = $pdo->prepare(
        'SELECT id, name, name_en, slug, description, address, phone, email,
                google_maps_url, latitude, longitude, image_url, is_headquarters,
                services, area_size, staff_count, operating_hours, display_order
         FROM branches
         WHERE company_id = :company_id AND is_active = 1
         ORDER BY display_order ASC'
    );
    $stmt->execute([':company_id' => $companyId]);
    $branches = $stmt->fetchAll();
    foreach ($branches as &$b) {
        $b['services'] = json_decode((string) ($b['services'] ?? '[]'), true) ?: [];
    }
    return $branches;
}

function get_company_info(PDO $pdo, string $code): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, name, code, description, logo_url, primary_color, secondary_color,
                website_url, contact_email, contact_phone, address
         FROM companies
         WHERE code = :code AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([':code' => strtoupper($code)]);
    return $stmt->fetch() ?: null;
}

function get_company_id_by_code(PDO $pdo, string $code): int
{
    $company = get_company_info($pdo, $code);
    return $company ? (int) $company['id'] : 0;
}
