<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/helpers.php';

function require_authenticated_user(): array
{
    $user = authenticated_user();

    if ($user === null) {
        redirect_to(project_url('koch/main/login.php'));
    }

    return $user;
}

function require_admin_user(): array
{
    $user = require_authenticated_user();

    if (!in_array((string) ($user['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true)) {
        redirect_to(user_page_by_company((string) ($user['company_code'] ?? 'KOCH')));
    }

    return $user;
}

function admin_dashboard_stats(PDO $pdo, ?int $companyId = null): array
{
    $params = [];
    $companyFilter = '';

    if ($companyId !== null) {
        $companyFilter = ' WHERE company_id = :company_id';
        $params[':company_id'] = $companyId;
    }

    $usersStmt = $pdo->prepare('SELECT COUNT(*) FROM users' . $companyFilter);
    $usersStmt->execute($params);

    $kochStmt = $pdo->prepare('SELECT COUNT(*) FROM koch_quotations' . ($companyId !== null ? ' WHERE user_id IN (SELECT id FROM users WHERE company_id = :company_id)' : ''));
    $kochStmt->execute($params);

    $tnbStmt = $pdo->prepare('SELECT COUNT(*) FROM tnb_quotations' . ($companyId !== null ? ' WHERE user_id IN (SELECT id FROM users WHERE company_id = :company_id)' : ''));
    $tnbStmt->execute($params);

    $notificationsStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE is_read = 0' . ($companyId !== null ? ' AND user_id IN (SELECT id FROM users WHERE company_id = :company_id)' : ''));
    $notificationsStmt->execute($params);

    return [
        'users' => (int) $usersStmt->fetchColumn(),
        'koch_quotations' => (int) $kochStmt->fetchColumn(),
        'tnb_quotations' => (int) $tnbStmt->fetchColumn(),
        'unread_notifications' => (int) $notificationsStmt->fetchColumn(),
    ];
}

function latest_admin_activities(PDO $pdo, ?int $companyId = null, int $limit = 10): array
{
    $sql = 'SELECT al.action, al.table_name, al.record_id, al.created_at, u.username
            FROM activity_logs al
            LEFT JOIN users u ON u.id = al.user_id';

    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE (u.company_id = :company_id OR u.company_id IS NULL)';
        $params[':company_id'] = $companyId;
    }

    $sql .= ' ORDER BY al.created_at DESC LIMIT ' . (int) $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
