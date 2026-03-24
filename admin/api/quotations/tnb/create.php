<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../includes/quotation.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect_to(project_url('tnb/main/quotation.php'));
}

$redirectBack = project_url('tnb/main/quotation.php');

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    form_response(false, 'Security token mismatch. Please try again.', $redirectBack, $_POST, [], 422);
}

$result = create_tnb_quotation(Database::connection(), $_POST, $_FILES);

if (!$result['success']) {
    form_response(false, $result['message'], $redirectBack, $_POST, $result, 422);
}

form_response(true, $result['message'], $redirectBack, [], $result);
