<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/helpers.php';

function require_authenticated_user(): array
{
    $user = authenticated_user();

    // Fallback: if no auth in current session (admin dashboard), try company sessions
    if ($user === null) {
        $user = find_auth_from_company_sessions();
    }

    if ($user === null) {
        redirect_to(project_url('koch/main/login.php'));
    }

    return $user;
}

function find_auth_from_company_sessions(): ?array
{
    $currentName = session_name();
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_write_close();

    foreach (['koch_session', 'tnb_session'] as $companySession) {
        session_name($companySession);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        if (session_start()) {
            $user = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : null;
            session_write_close();

            if ($user !== null && in_array((string) ($user['role'] ?? ''), ['super_admin', 'admin', 'manager'], true)) {
                // Found admin auth, copy to admin session
                session_name($currentName);
                session_set_cookie_params([
                    'lifetime' => SESSION_LIFETIME,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                session_start();
                $_SESSION['auth_user'] = $user;
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = (string) $user['username'];
                $_SESSION['user_role'] = (string) $user['role'];
                $_SESSION['company_id'] = (int) $user['company_id'];
                $_SESSION['company_code'] = (string) $user['company_code'];
                return $user;
            }
        }
    }

    // No auth found, restore original session
    session_name($currentName);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    return null;
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

    $usersStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE status != \'inactive\'' . ($companyId !== null ? ' AND company_id = :company_id' : ''));
    $usersStmt->execute($params);

    $kochStmt = $pdo->prepare('SELECT COUNT(*) FROM koch_quotations' . ($companyId !== null ? ' WHERE user_id IN (SELECT id FROM users WHERE company_id = :company_id)' : ''));
    $kochStmt->execute($params);

    $tnbStmt = $pdo->prepare('SELECT COUNT(*) FROM tnb_quotations' . ($companyId !== null ? ' WHERE user_id IN (SELECT id FROM users WHERE company_id = :company_id)' : ''));
    $tnbStmt->execute($params);

    $notificationsStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE is_read = 0' . ($companyId !== null ? ' AND company_id = :company_id' : ''));
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
        $sql .= ' WHERE al.company_id = :company_id';
        $params[':company_id'] = $companyId;
    }

    $sql .= ' ORDER BY al.created_at DESC LIMIT ' . (int) $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function admin_extended_stats(PDO $pdo, ?int $companyId = null): array
{
    $params = [];
    $cf = '';
    if ($companyId !== null) {
        $cf = ' WHERE company_id = :cid';
        $params[':cid'] = $companyId;
    }

    $activeUsers = $pdo->prepare("SELECT COUNT(*) FROM users" . ($cf !== '' ? $cf . " AND status = 'active'" : " WHERE status = 'active'"));
    $activeUsers->execute($params);

    $newUsersMonth = $pdo->prepare("SELECT COUNT(*) FROM users WHERE status != 'inactive' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')" . ($companyId !== null ? " AND company_id = :cid" : ''));
    $newUsersMonth->execute($params);

    $kochPending = $pdo->prepare("SELECT COUNT(*) FROM koch_quotations WHERE is_read = 0" . ($companyId !== null ? " AND user_id IN (SELECT id FROM users WHERE company_id = :cid)" : ''));
    $kochPending->execute($params);

    $tnbPending = $pdo->prepare("SELECT COUNT(*) FROM tnb_quotations WHERE is_read = 0" . ($companyId !== null ? " AND user_id IN (SELECT id FROM users WHERE company_id = :cid)" : ''));
    $tnbPending->execute($params);

    $tnbInTransit = $pdo->prepare("SELECT COUNT(*) FROM tnb_quotations WHERE status = 'in_transit'" . ($companyId !== null ? " AND user_id IN (SELECT id FROM users WHERE company_id = :cid)" : ''));
    $tnbInTransit->execute($params);

    $kochMonth = $pdo->prepare("SELECT COUNT(*) FROM koch_quotations WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')" . ($companyId !== null ? " AND user_id IN (SELECT id FROM users WHERE company_id = :cid)" : ''));
    $kochMonth->execute($params);

    $tnbMonth = $pdo->prepare("SELECT COUNT(*) FROM tnb_quotations WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')" . ($companyId !== null ? " AND user_id IN (SELECT id FROM users WHERE company_id = :cid)" : ''));
    $tnbMonth->execute($params);

    return [
        'active_users' => (int) $activeUsers->fetchColumn(),
        'new_users_month' => (int) $newUsersMonth->fetchColumn(),
        'koch_pending' => (int) $kochPending->fetchColumn(),
        'tnb_pending' => (int) $tnbPending->fetchColumn(),
        'tnb_in_transit' => (int) $tnbInTransit->fetchColumn(),
        'koch_month' => (int) $kochMonth->fetchColumn(),
        'tnb_month' => (int) $tnbMonth->fetchColumn(),
    ];
}

function admin_recent_koch_quotations(PDO $pdo, ?int $companyId = null, int $limit = 5): array
{
    $sql = 'SELECT kq.quotation_number, kq.first_name, kq.last_name, kq.product_type, kq.status, kq.created_at FROM koch_quotations kq';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE kq.user_id IN (SELECT id FROM users WHERE company_id = :cid)';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY kq.created_at DESC LIMIT ' . (int) $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function admin_recent_tnb_quotations(PDO $pdo, ?int $companyId = null, int $limit = 5): array
{
    $sql = 'SELECT tq.request_number, tq.first_name, tq.last_name, tq.service_type, tq.route, tq.status, tq.created_at FROM tnb_quotations tq';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE tq.user_id IN (SELECT id FROM users WHERE company_id = :cid)';
        $params[':cid'] = $companyId;
    }
    $sql .= ' ORDER BY tq.created_at DESC LIMIT ' . (int) $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function admin_recent_users(PDO $pdo, ?int $companyId = null, int $limit = 5): array
{
    $sql = 'SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.role, u.status, u.created_at, c.name AS company_name FROM users u LEFT JOIN companies c ON c.id = u.company_id';
    $params = [];
    if ($companyId !== null) {
        $sql .= ' WHERE u.company_id = :cid AND u.status != \'inactive\'';
        $params[':cid'] = $companyId;
    } else {
        $sql .= ' WHERE u.status != \'inactive\'';
    }
    $sql .= ' ORDER BY u.created_at DESC LIMIT ' . (int) $limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
