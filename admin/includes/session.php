<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Check if headers already sent
    if (headers_sent()) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    
    // Determine session name from URL path and request parameters
    // Priority: URL path > POST/GET company param > default
    // NO shared cookie - prevents cross-session contamination between tabs
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $sessionName = SESSION_NAME; // default for admin dashboard
    
    // 1. URL path detection (highest priority - always correct for page requests)
    if (strpos($requestUri, '/tnb/') !== false) {
        $sessionName = 'tnb_session';
    } elseif (strpos($requestUri, '/koch/') !== false) {
        $sessionName = 'koch_session';
    } elseif (strpos($requestUri, '/admin/') !== false) {
        // 2. For admin API calls, check POST/GET company parameter
        $company = strtolower((string) ($_POST['company'] ?? $_GET['company'] ?? ''));
        if ($company === 'tnb') {
            $sessionName = 'tnb_session';
        } elseif ($company === 'koch') {
            $sessionName = 'koch_session';
        }
        // No company param → use default SESSION_NAME (admin dashboard pages)
    }

    session_name($sessionName);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string
{
    start_app_session();
    return (string) $_SESSION['_csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    start_app_session();

    if (!is_string($token) || $token === '') {
        return false;
    }

    return isset($_SESSION['_csrf_token']) && hash_equals((string) $_SESSION['_csrf_token'], $token);
}

function set_flash(string $key, mixed $value): void
{
    start_app_session();
    $_SESSION['_flash'][$key] = $value;
}

function flash(string $key, mixed $default = null): mixed
{
    start_app_session();

    if (!isset($_SESSION['_flash'][$key])) {
        return $default;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function set_old_input(array $input): void
{
    start_app_session();
    $_SESSION['_old'] = $input;
}

function old_input(string $key, string $default = ''): string
{
    start_app_session();
    return isset($_SESSION['_old'][$key]) ? (string) $_SESSION['_old'][$key] : $default;
}

function clear_old_input(): void
{
    start_app_session();
    unset($_SESSION['_old']);
}

function set_authenticated_user(array $user, string $sessionToken): void
{
    start_app_session();
    session_regenerate_id(true);

    // Determine which login page the user came from (based on current session name)
    $currentSessName = session_name();
    $loginCompany = ($currentSessName === 'tnb_session') ? 'tnb' : 'koch';

    $authData = [
        'id' => (int) $user['id'],
        'username' => (string) $user['username'],
        'email' => (string) $user['email'],
        'first_name' => (string) $user['first_name'],
        'last_name' => (string) $user['last_name'],
        'nick_name' => (string) ($user['nick_name'] ?? ''),
        'phone' => (string) ($user['phone'] ?? ''),
        'role' => (string) $user['role'],
        'company_id' => (int) $user['company_id'],
        'company_code' => (string) $user['company_code'],
        'company_name' => (string) $user['company_name'],
        'status' => (string) $user['status'],
        'session_token' => $sessionToken,
        'login_company' => $loginCompany,
    ];

    $_SESSION['auth_user'] = $authData;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['user_role'] = (string) $user['role'];
    $_SESSION['company_id'] = (int) $user['company_id'];
    $_SESSION['company_code'] = (string) $user['company_code'];

    // If admin/manager user, also store auth in admin dashboard session
    // This allows admin dashboard to work independently from company pages
    if (in_array((string) $user['role'], ['super_admin', 'admin', 'manager'], true)) {
        $currentSessionName = session_name();
        if ($currentSessionName !== SESSION_NAME) {
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            session_write_close();

            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
            session_regenerate_id(true);
            $_SESSION['auth_user'] = $authData;
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = (string) $user['username'];
            $_SESSION['user_role'] = (string) $user['role'];
            $_SESSION['company_id'] = (int) $user['company_id'];
            $_SESSION['company_code'] = (string) $user['company_code'];
            if (!isset($_SESSION['_csrf_token'])) {
                $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
            }
            session_write_close();

            // Switch back to original company session
            session_name($currentSessionName);
            session_start();
        }
    }
}

function authenticated_user(): ?array
{
    start_app_session();
    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : null;
}

function is_authenticated(): bool
{
    return authenticated_user() !== null;
}

function destroy_authenticated_session(): ?array
{
    start_app_session();
    $user = authenticated_user();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        // DO NOT delete other cookies - each session is independent
    }

    session_destroy();

    return $user;
}
