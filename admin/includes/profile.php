<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/activity.php';

function get_profile_summary(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.nick_name, u.phone, u.role, u.department, u.position,
                u.avatar_url, u.created_at, u.last_login, c.name AS company_name, c.code AS company_code
         FROM users u
         INNER JOIN companies c ON c.id = u.company_id
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $userId]);

    return $stmt->fetch() ?: null;
}

function update_profile_details(PDO $pdo, int $userId, array $payload): array
{
    $firstName = sanitize_text((string) ($payload['first_name'] ?? ''));
    $lastName = sanitize_text((string) ($payload['last_name'] ?? ''));
    $nickName = sanitize_text((string) ($payload['nick_name'] ?? ''));
    $email = strtolower(sanitize_text((string) ($payload['email'] ?? '')));
    $phone = sanitize_text((string) ($payload['phone'] ?? ''));
    $department = sanitize_text((string) ($payload['department'] ?? ''));
    $position = sanitize_text((string) ($payload['position'] ?? ''));

    $errors = merge_validation_errors(
        validate_required_fields([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
        ], [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'phone' => 'Phone number',
        ]),
        ['email' => validate_email_address($email)],
        ['phone' => validate_phone_number($phone)]
    );

    if ($errors !== []) {
        return ['success' => false, 'message' => reset($errors) ?: 'Validation failed.', 'errors' => $errors];
    }

    $duplicate = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
    $duplicate->execute([
        ':email' => $email,
        ':id' => $userId,
    ]);

    if ($duplicate->fetch()) {
        return ['success' => false, 'message' => 'This email is already used by another account.'];
    }

    $before = get_profile_summary($pdo, $userId);

    $stmt = $pdo->prepare(
        'UPDATE users
         SET first_name = :first_name,
             last_name = :last_name,
             nick_name = :nick_name,
             email = :email,
             phone = :phone,
             department = :department,
             position = :position,
             updated_at = NOW()
         WHERE id = :id'
    );

    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':nick_name' => $nickName !== '' ? $nickName : null,
        ':email' => $email,
        ':phone' => $phone,
        ':department' => $department !== '' ? $department : null,
        ':position' => $position !== '' ? $position : null,
        ':id' => $userId,
    ]);

    $after = get_profile_summary($pdo, $userId);
    log_activity($pdo, $userId, 'PROFILE_UPDATED', 'users', $userId, $before ?: [], $after ?: []);

    return ['success' => true, 'message' => 'Profile updated successfully.', 'profile' => $after];
}

function get_recent_activity_logs(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT action, table_name, record_id, created_at
         FROM activity_logs
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

function get_koch_user_quotations(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT quotation_number, product_type, quoted_price, status, created_at
         FROM koch_quotations
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

function get_tnb_user_quotations(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT request_number, service_type, route, quoted_price, status, tracking_number, created_at
         FROM tnb_quotations
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

function get_tnb_service_history(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT request_number, service_type, route, status, created_at, pickup_time, delivery_time
         FROM tnb_quotations
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}
