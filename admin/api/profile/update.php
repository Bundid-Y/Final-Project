<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/profile.php';

$company = strtolower(post_string('company', 'koch'));
$loginRedirect = login_page_by_company(company_code_from_slug($company));

$user = authenticated_user();
if ($user === null) {
    form_response(false, 'Please login first.', $loginRedirect, [], [], 401);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect_to(user_page_by_company((string) $user['company_code']));
}

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    form_response(false, 'Security token mismatch. Please try again.', user_page_by_company((string) $user['company_code']), $_POST, [], 422);
}

$result = update_profile_details(Database::connection(), (int) $user['id'], $_POST);

if (!$result['success']) {
    form_response(false, $result['message'], user_page_by_company((string) $user['company_code']), $_POST, $result, 422);
}

$refreshedProfile = $result['profile'] ?? null;
if (is_array($refreshedProfile) && isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
    $_SESSION['auth_user']['first_name'] = $refreshedProfile['first_name'];
    $_SESSION['auth_user']['last_name'] = $refreshedProfile['last_name'];
    $_SESSION['auth_user']['nick_name'] = $refreshedProfile['nick_name'] ?? '';
    $_SESSION['auth_user']['phone'] = $refreshedProfile['phone'] ?? '';
    $_SESSION['auth_user']['email'] = $refreshedProfile['email'];
    $_SESSION['username'] = $_SESSION['auth_user']['username'];
}

form_response(true, $result['message'], user_page_by_company((string) $user['company_code']), [], $result);
