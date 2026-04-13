<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/activity.php';

function get_company_by_slug(PDO $pdo, string $companySlug): ?array
{
    $code = company_code_from_slug($companySlug);

    $stmt = $pdo->prepare('SELECT id, name, code FROM companies WHERE code = :code AND is_active = 1 LIMIT 1');
    $stmt->execute([':code' => $code]);

    return $stmt->fetch() ?: null;
}

function find_admin_user_by_identifier(PDO $pdo, string $identifier): ?array
{
    $stmt = $pdo->prepare(
        "SELECT u.*, c.code AS company_code, c.name AS company_name
         FROM users u
         INNER JOIN companies c ON c.id = u.company_id
         WHERE (u.username = :ident_user OR u.email = :ident_email)
           AND u.role IN ('super_admin', 'admin')
         LIMIT 1"
    );
    $stmt->execute([
        ':ident_user' => $identifier,
        ':ident_email' => $identifier,
    ]);

    return $stmt->fetch() ?: null;
}

function get_setting_value(PDO $pdo, string $key, mixed $default = null): mixed
{
    $stmt = $pdo->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :setting_key LIMIT 1');
    $stmt->execute([':setting_key' => $key]);
    $value = $stmt->fetchColumn();

    return $value !== false ? $value : $default;
}

function find_user_by_identifier(PDO $pdo, string $identifier, int $companyId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.*, c.code AS company_code, c.name AS company_name
         FROM users u
         INNER JOIN companies c ON c.id = u.company_id
         WHERE (u.username = :ident_user OR u.email = :ident_email)
           AND u.company_id = :company_id
         LIMIT 1'
    );
    $stmt->execute([
        ':ident_user' => $identifier,
        ':ident_email' => $identifier,
        ':company_id' => $companyId,
    ]);

    return $stmt->fetch() ?: null;
}

function find_user_by_email(PDO $pdo, string $email, int $companyId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.*, c.code AS company_code, c.name AS company_name
         FROM users u
         INNER JOIN companies c ON c.id = u.company_id
         WHERE u.email = :email AND u.company_id = :company_id
         LIMIT 1'
    );
    $stmt->execute([
        ':email' => $email,
        ':company_id' => $companyId,
    ]);

    return $stmt->fetch() ?: null;
}

function find_user_by_id(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT u.*, c.code AS company_code, c.name AS company_name
         FROM users u
         INNER JOIN companies c ON c.id = u.company_id
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $userId]);

    return $stmt->fetch() ?: null;
}

function create_session_record(PDO $pdo, int $userId): string
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

    $stmt = $pdo->prepare(
        'INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, is_active, expires_at)
         VALUES (:user_id, :session_token, :ip_address, :user_agent, 1, :expires_at)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':session_token' => $token,
        ':ip_address' => get_client_ip(),
        ':user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 65535),
        ':expires_at' => $expiresAt,
    ]);

    return $token;
}

function deactivate_session_record(PDO $pdo, string $sessionToken): void
{
    if ($sessionToken === '') {
        return;
    }

    $stmt = $pdo->prepare('UPDATE user_sessions SET is_active = 0 WHERE session_token = :session_token');
    $stmt->execute([':session_token' => $sessionToken]);
}

function reset_login_security(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = :id');
    $stmt->execute([':id' => $userId]);
}

function apply_login_failure(PDO $pdo, int $userId): void
{
    $maxAttempts = (int) get_setting_value($pdo, 'max_login_attempts', 5);
    $lockMinutes = (int) get_setting_value($pdo, 'account_lockout_duration', 30);

    $stmt = $pdo->prepare('SELECT login_attempts, username FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();
    $currentAttempts = (int) ($row['login_attempts'] ?? 0);
    $username = (string) ($row['username'] ?? 'Unknown');
    $nextAttempts = $currentAttempts + 1;

    $lockedUntil = $nextAttempts >= $maxAttempts ? date('Y-m-d H:i:s', time() + ($lockMinutes * 60)) : null;

    $update = $pdo->prepare('UPDATE users SET login_attempts = :login_attempts, locked_until = :locked_until WHERE id = :id');
    $update->execute([
        ':login_attempts' => $nextAttempts,
        ':locked_until' => $lockedUntil,
        ':id' => $userId,
    ]);

    if ($nextAttempts >= 3) {
        $ip = get_client_ip();
        $admins = $pdo->prepare("SELECT id FROM users WHERE role IN ('super_admin','admin') AND status = 'active'");
        $admins->execute();
        foreach ($admins->fetchAll(PDO::FETCH_COLUMN) as $adminId) {
            create_notification(
                $pdo, (int) $adminId,
                'การแจ้งเตือนความปลอดภัย: ล็อกอินล้มเหลว (' . $nextAttempts . ' ครั้ง)',
                "ผู้ใช้งาน '{$username}' ล็อกอินล้มเหลว {$nextAttempts} ครั้ง จาก IP: {$ip}",
                'warning', 'users', $userId, 'high'
            );
        }
    }
}

function user_redirect_url(array $user): string
{
    $role = (string) ($user['role'] ?? 'user');

    if (in_array($role, ['super_admin', 'admin'], true)) {
        return project_url('admin/dashboard.php');
    }

    return user_page_by_company((string) ($user['company_code'] ?? 'KOCH'));
}

function attempt_login(PDO $pdo, string $identifier, string $password, string $companySlug): array
{
    $company = get_company_by_slug($pdo, $companySlug);

    if ($company === null) {
        return ['success' => false, 'message' => 'Company not found.'];
    }

    $user = find_user_by_identifier($pdo, $identifier, (int) $company['id']);

    if ($user === null) {
        $user = find_admin_user_by_identifier($pdo, $identifier);
    }

    if ($user === null) {
        return ['success' => false, 'message' => 'Invalid username/email or password.'];
    }

    if (!empty($user['locked_until']) && strtotime((string) $user['locked_until']) > time()) {
        return ['success' => false, 'message' => 'Your account is temporarily locked. Please try again later.'];
    }

    if (!in_array((string) $user['status'], ['active', 'pending_verification'], true)) {
        return ['success' => false, 'message' => 'Your account is not active. Please contact support.'];
    }

    if (!password_verify($password, (string) $user['password_hash'])) {
        apply_login_failure($pdo, (int) $user['id']);
        log_activity($pdo, (int) $user['id'], 'LOGIN_FAILED', 'users', (int) $user['id']);
        return ['success' => false, 'message' => 'Invalid username/email or password.'];
    }

    reset_login_security($pdo, (int) $user['id']);
    $freshUser = find_user_by_id($pdo, (int) $user['id']);

    if ($freshUser === null) {
        return ['success' => false, 'message' => 'Unable to load user profile after login.'];
    }

    $sessionToken = create_session_record($pdo, (int) $freshUser['id']);
    set_authenticated_user($freshUser, $sessionToken);
    log_activity($pdo, (int) $freshUser['id'], 'LOGIN_SUCCESS', 'users', (int) $freshUser['id']);

    return [
        'success' => true,
        'message' => 'Login successful.',
        'user' => $freshUser,
        'redirect' => user_redirect_url($freshUser),
    ];
}

function register_user_account(PDO $pdo, array $payload, string $companySlug): array
{
    $company = get_company_by_slug($pdo, $companySlug);

    if ($company === null) {
        return ['success' => false, 'message' => 'Company not found.'];
    }

    $username = sanitize_text((string) ($payload['username'] ?? ''));
    $email = strtolower(sanitize_text((string) ($payload['email'] ?? '')));
    $password = (string) ($payload['password'] ?? '');
    $confirmPassword = (string) ($payload['confirm_password'] ?? '');
    $firstName = sanitize_text((string) ($payload['first_name'] ?? ''));
    $lastName = sanitize_text((string) ($payload['last_name'] ?? ''));
    $phone = sanitize_text((string) ($payload['phone'] ?? ''));
    $nickName = sanitize_text((string) ($payload['nick_name'] ?? ''));

    $errors = merge_validation_errors(
        validate_required_fields([
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ], [
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
        ]),
        ['email' => validate_email_address($email)],
        ['password' => validate_password_rules($password)],
        $phone !== '' ? ['phone' => validate_phone_number($phone)] : [],
        $password !== $confirmPassword ? ['confirm_password' => 'Password confirmation does not match.'] : []
    );

    if ($errors !== []) {
        return ['success' => false, 'message' => reset($errors) ?: 'Registration validation failed.', 'errors' => $errors];
    }

    $duplicateStmt = $pdo->prepare(
        'SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1'
    );
    $duplicateStmt->execute([
        ':username' => $username,
        ':email' => $email,
    ]);

    if ($duplicateStmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists.'];
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (
            username, email, password_hash, first_name, last_name, nick_name, phone,
            role, company_id, status, email_verified, created_at, updated_at
         ) VALUES (
            :username, :email, :password_hash, :first_name, :last_name, :nick_name, :phone,
            :role, :company_id, :status, :email_verified, NOW(), NOW()
         )'
    );

    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':nick_name' => $nickName !== '' ? $nickName : null,
        ':phone' => $phone,
        ':role' => 'user',
        ':company_id' => (int) $company['id'],
        ':status' => 'active',
        ':email_verified' => 0,
    ]);

    $userId = (int) $pdo->lastInsertId();
    log_activity($pdo, $userId, 'REGISTER_SUCCESS', 'users', $userId);

    $newUser = find_user_by_id($pdo, $userId);

    return [
        'success' => true,
        'message' => 'Registration successful. You can now sign in.',
        'user' => $newUser,
    ];
}

function resolve_or_create_customer_user(PDO $pdo, array $contact, string $companySlug): array
{
    $authUser = authenticated_user();
    $company = get_company_by_slug($pdo, $companySlug);

    if ($company === null) {
        throw new RuntimeException('Company not found.');
    }

    if ($authUser !== null && (int) $authUser['company_id'] === (int) $company['id']) {
        $user = find_user_by_id($pdo, (int) $authUser['id']);
        if ($user !== null) {
            return $user;
        }
    }

    $email = strtolower(sanitize_text((string) ($contact['email'] ?? '')));
    $phone = sanitize_text((string) ($contact['phone'] ?? ''));
    $firstName = sanitize_text((string) ($contact['first_name'] ?? 'Customer'));
    $lastName = sanitize_text((string) ($contact['last_name'] ?? 'User'));
    $nickName = sanitize_text((string) ($contact['nick_name'] ?? ''));

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $existing = $stmt->fetch();

    if ($existing) {
        $update = $pdo->prepare(
            'UPDATE users
             SET first_name = :first_name, last_name = :last_name, nick_name = :nick_name, phone = :phone, updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':nick_name' => $nickName !== '' ? $nickName : null,
            ':phone' => $phone,
            ':id' => (int) $existing['id'],
        ]);

        $fresh = find_user_by_id($pdo, (int) $existing['id']);
        if ($fresh !== null) {
            return $fresh;
        }
    }

    $usernameBase = preg_replace('/[^a-z0-9]/i', '', strstr($email, '@', true) ?: $firstName . $lastName) ?: 'customer';
    $username = strtolower(substr($usernameBase, 0, 20));
    $suffix = 1;

    while (true) {
        $candidate = $suffix === 1 ? $username : substr($username, 0, 16) . $suffix;
        $check = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $check->execute([':username' => $candidate]);
        if (!$check->fetch()) {
            $username = $candidate;
            break;
        }
        $suffix++;
    }

    $temporaryPassword = bin2hex(random_bytes(8));
    $insert = $pdo->prepare(
        'INSERT INTO users (
            username, email, password_hash, first_name, last_name, nick_name, phone,
            role, company_id, status, email_verified, created_at, updated_at
         ) VALUES (
            :username, :email, :password_hash, :first_name, :last_name, :nick_name, :phone,
            :role, :company_id, :status, :email_verified, NOW(), NOW()
         )'
    );

    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => password_hash($temporaryPassword, PASSWORD_BCRYPT),
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':nick_name' => $nickName !== '' ? $nickName : null,
        ':phone' => $phone,
        ':role' => 'user',
        ':company_id' => (int) $company['id'],
        ':status' => 'active',
        ':email_verified' => 0,
    ]);

    $userId = (int) $pdo->lastInsertId();
    log_activity($pdo, $userId, 'AUTO_REGISTER_FROM_QUOTATION', 'users', $userId);

    $user = find_user_by_id($pdo, $userId);
    if ($user === null) {
        throw new RuntimeException('Unable to create customer account.');
    }

    return $user;
}
