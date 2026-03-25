<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect_to(project_url('koch/main/login.php'));
}

$company = strtolower(post_string('company', 'koch'));
$redirectBack = login_page_by_company(company_code_from_slug($company));
$oldInput = [
    'register_username' => post_string('username'),
    'register_email' => post_string('email'),
];

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    form_response(false, 'Security token mismatch. Please try again.', $redirectBack, $oldInput, [], 422);
}

$result = register_user_account(Database::connection(), $_POST, $company);

if (!$result['success']) {
    form_response(false, $result['message'], $redirectBack, $oldInput, $result, 422);
}

set_flash('success_message', $result['message']);
clear_old_input();

if (request_expects_json()) {
    json_response(true, $result['message'], [
        'redirect' => $redirectBack,
        'user' => $result['user'],
    ]);
}

redirect_to($redirectBack);
