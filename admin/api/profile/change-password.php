<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/profile.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(project_url('koch/main/login.php'));
}

$csrfToken = post_string('_csrf');
if (!verify_csrf_token($csrfToken)) {
    $company = post_string('company', 'koch');
    form_response(false, 'Invalid security token. Please try again.', project_url($company . '/main/user.php?section=settings'));
}

$currentUser = authenticated_user();
if ($currentUser === null) {
    redirect_to(project_url('koch/main/login.php'));
}

$company = post_string('company', 'koch');
$redirectUrl = project_url($company . '/main/user.php?section=settings');

$pdo = Database::connection();

$result = change_user_password(
    $pdo,
    (int) $currentUser['id'],
    post_string('current_password'),
    post_string('new_password'),
    post_string('confirm_new_password')
);

form_response(
    $result['success'],
    $result['message'],
    $redirectUrl,
    [],
    $result,
    $result['success'] ? 200 : 422
);
