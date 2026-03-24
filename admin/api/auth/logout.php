<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = Database::connection();
$currentUser = authenticated_user();
$company = strtolower(post_string('company', $_GET['company'] ?? 'koch'));

if ($currentUser !== null) {
    deactivate_session_record($pdo, (string) ($currentUser['session_token'] ?? ''));
    log_activity($pdo, (int) $currentUser['id'], 'LOGOUT', 'users', (int) $currentUser['id']);
    $company = company_slug_from_code((string) ($currentUser['company_code'] ?? 'KOCH'));
}

destroy_authenticated_session();
set_flash('success_message', 'Logged out successfully.');

$redirectUrl = login_page_by_company(company_code_from_slug($company));

if (request_expects_json()) {
    json_response(true, 'Logged out successfully.', ['redirect' => $redirectUrl]);
}

redirect_to($redirectUrl);
