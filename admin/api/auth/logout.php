<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = Database::connection();
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

$sessParams = [
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
];

// --- 1. Find the authenticated user from ANY active session ---
// start_app_session() (via bootstrap) already opened a session based on GET/POST company param.
// But that session may be EMPTY if the param was wrong (e.g. admin dashboard sent ?company=koch
// but the user actually logged in via TNB). So we search all sessions to find the real auth.

$currentUser  = authenticated_user();
$foundInSession = session_name(); // which session had auth

if ($currentUser === null) {
    // Current session has no auth — search other sessions.
    // Check company sessions FIRST (tnb_session, koch_session) because their name
    // directly tells us the login origin. Admin session (SESSION_NAME) is last resort.
    $allSessions = ['tnb_session', 'koch_session', SESSION_NAME];
    $checkedSession = session_name();
    session_write_close();

    foreach ($allSessions as $sessName) {
        if ($sessName === $checkedSession) {
            continue; // already checked
        }
        session_name($sessName);
        session_set_cookie_params($sessParams);
        if (session_start()) {
            $user = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])
                ? $_SESSION['auth_user'] : null;
            session_write_close();
            if ($user !== null) {
                $currentUser    = $user;
                $foundInSession = $sessName;
                break;
            }
        }
    }
}

// --- 2. Determine the correct company to redirect to ---
// Priority: login_company (most accurate) → session name where auth was found → GET param → company_code
$company = '';

if ($currentUser !== null && !empty($currentUser['login_company'])) {
    $company = (string) $currentUser['login_company'];
}

if ($company === '') {
    // Derive from the session name where auth was actually stored
    if ($foundInSession === 'tnb_session') {
        $company = 'tnb';
    } elseif ($foundInSession === 'koch_session') {
        $company = 'koch';
    }
}

if ($company === '') {
    // Fall back to GET/POST param
    $company = strtolower((string) ($_GET['company'] ?? $_POST['company'] ?? ''));
}

if ($company !== 'tnb' && $company !== 'koch') {
    // Last resort: user's company_code or default
    $company = ($currentUser !== null)
        ? company_slug_from_code((string) ($currentUser['company_code'] ?? 'KOCH'))
        : 'koch';
}

// --- 3. Log activity before destroying sessions ---
if ($currentUser !== null) {
    deactivate_session_record($pdo, (string) ($currentUser['session_token'] ?? ''));
    log_activity($pdo, (int) $currentUser['id'], 'LOGOUT', 'users', (int) $currentUser['id']);
}

// --- 4. Destroy ALL sessions belonging to this user ---
// Company session (tnb/koch from login origin) + admin session.
// Do NOT touch the OTHER company's session (KOCH↔TNB independence).
$companySession = ($company === 'tnb') ? 'tnb_session' : 'koch_session';
$sessionsToDestroy = array_unique([$companySession, SESSION_NAME]);

foreach ($sessionsToDestroy as $sessName) {
    // Close any currently active session first
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_name($sessName);
    session_set_cookie_params($sessParams);
    if (session_start()) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie($sessName, '', time() - 3600, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
    }
}

// --- 5. Start a fresh login session for flash message ---
$loginSession = ($company === 'tnb') ? 'tnb_session' : 'koch_session';
session_name($loginSession);
session_set_cookie_params($sessParams);
session_start();
$_SESSION['_flash']['success_message'] = 'ออกจากระบบเรียบร้อยแล้ว';
session_write_close();

// --- 6. Redirect to the correct login page ---
$redirectUrl = login_page_by_company(company_code_from_slug($company));

if (request_expects_json()) {
    json_response(true, 'Logged out successfully.', ['redirect' => $redirectUrl]);
}

redirect_to($redirectUrl);
