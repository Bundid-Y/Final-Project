<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/activity.php';

function get_profile_summary(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.nick_name, u.phone, u.role,
                u.created_at, u.last_login, c.name AS company_name, c.code AS company_code
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
             updated_at = NOW()
         WHERE id = :id'
    );

    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':nick_name' => $nickName !== '' ? $nickName : null,
        ':email' => $email,
        ':phone' => $phone,
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
        'SELECT quotation_number, product_type, status, created_at
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
        'SELECT request_number, service_type, route, status, tracking_number, created_at
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

function get_user_notifications(PDO $pdo, int $userId, int $limit = 20): array
{
    $stmt = $pdo->prepare(
        'SELECT id, title, message, type, is_read, priority, created_at
         FROM notifications
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

function get_unread_notification_count(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0');
    $stmt->execute([':user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function mark_notification_read(PDO $pdo, int $notificationId, int $userId): void
{
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
}

function get_user_login_sessions(PDO $pdo, int $userId, int $limit = 10): array
{
    $stmt = $pdo->prepare(
        'SELECT session_token, ip_address, user_agent, is_active, expires_at, created_at
         FROM user_sessions
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . (int) $limit
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetchAll();
}

function get_koch_quotation_stats(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
            SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) AS quoted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN status = 'rejected' OR status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
         FROM koch_quotations
         WHERE user_id = :user_id"
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetch() ?: ['total' => 0, 'pending' => 0, 'processing' => 0, 'quoted' => 0, 'approved' => 0, 'completed' => 0, 'cancelled' => 0];
}

function get_tnb_quotation_stats(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS processing,
            SUM(CASE WHEN status = 'in_transit' THEN 1 ELSE 0 END) AS in_transit,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
         FROM tnb_quotations
         WHERE user_id = :user_id"
    );
    $stmt->execute([':user_id' => $userId]);

    return $stmt->fetch() ?: ['total' => 0, 'pending' => 0, 'processing' => 0, 'in_transit' => 0, 'delivered' => 0, 'completed' => 0, 'cancelled' => 0];
}

function change_user_password(PDO $pdo, int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array
{
    if ($newPassword !== $confirmPassword) {
        return ['success' => false, 'message' => 'New password confirmation does not match.'];
    }

    $passwordError = validate_password_rules($newPassword);
    if ($passwordError !== null) {
        return ['success' => false, 'message' => $passwordError];
    }

    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($currentPassword, (string) $hash)) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }

    $update = $pdo->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
    $update->execute([':password_hash' => password_hash($newPassword, PASSWORD_BCRYPT), ':id' => $userId]);

    log_activity($pdo, $userId, 'PASSWORD_CHANGED', 'users', $userId);

    return ['success' => true, 'message' => 'Password changed successfully.'];
}
