<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = Database::connection();
$currentUser = authenticated_user();

// Determine company: GET/POST param is most reliable (hardcoded in logout links)
$company = strtolower((string) ($_GET['company'] ?? $_POST['company'] ?? ''));
if ($company !== 'tnb' && $company !== 'koch') {
    // Fallback to login_company (tracks which login page the user came from)
    if ($currentUser !== null && !empty($currentUser['login_company'])) {
        $company = (string) $currentUser['login_company'];
    } elseif ($currentUser !== null) {
        $company = company_slug_from_code((string) ($currentUser['company_code'] ?? 'KOCH'));
    } else {
        $company = 'koch';
    }
}

// Log activity before destroying session data
if ($currentUser !== null) {
    deactivate_session_record($pdo, (string) ($currentUser['session_token'] ?? ''));
    log_activity($pdo, (int) $currentUser['id'], 'LOGOUT', 'users', (int) $currentUser['id']);
}

// Destroy current company session AND admin session (but NOT the other company's session)
// Company sessions (koch/tnb) are independent from each other, but admin session
// must be cleared too so the user is fully logged out from the management area.
$destroyedSessionName = session_name();
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// 1. Destroy current session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
}
session_destroy();

// 2. Also destroy admin session if current was a company session (and vice versa)
$alsoDestroy = ($destroyedSessionName !== SESSION_NAME) ? SESSION_NAME : null;
if ($alsoDestroy === null && $destroyedSessionName === SESSION_NAME) {
    // Logged out from admin session → also destroy the login company session
    $alsoDestroy = ($company === 'tnb') ? 'tnb_session' : 'koch_session';
}
if ($alsoDestroy !== null) {
    session_name($alsoDestroy);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    if (session_start()) {
        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        session_destroy();
    }
}

// Start a fresh login session to carry the success flash message
$loginSession = ($company === 'tnb') ? 'tnb_session' : 'koch_session';
session_name($loginSession);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
$_SESSION['_flash']['success_message'] = 'ออกจากระบบเรียบร้อยแล้ว';
session_write_close();

$redirectUrl = login_page_by_company(company_code_from_slug($company));

if (request_expects_json()) {
    json_response(true, 'Logged out successfully.', ['redirect' => $redirectUrl]);
}

redirect_to($redirectUrl);
