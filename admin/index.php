<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$user = authenticated_user();

if ($user === null) {
    redirect_to(project_url('koch/main/login.php'));
}

if (in_array((string) ($user['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true)) {
    redirect_to(project_url('admin/dashboard.php'));
}

redirect_to(user_page_by_company((string) ($user['company_code'] ?? 'KOCH')));
