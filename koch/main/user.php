<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/profile.php';

$currentUser = authenticated_user();
if ($currentUser === null) {
    redirect_to(project_url('koch/main/login.php'));
}

// Strict company validation - KOCH users ONLY (except admins)
$userCompany = strtoupper((string) ($currentUser['company_code'] ?? ''));
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin'], true);

if ($userCompany !== 'KOCH' && !$isAdmin) {
    // Not a KOCH user and not admin - redirect to correct company page
    redirect_to(user_page_by_company($userCompany));
}

$successMessage = flash('success_message');
$errorMessage = flash('error_message');
try {
    $pdo = Database::connection();
    $profile = get_profile_summary($pdo, (int) $currentUser['id']);
    $activities = get_recent_activity_logs($pdo, (int) $currentUser['id'], 15);
    $quotations = get_koch_user_quotations($pdo, (int) $currentUser['id'], 20);
    $qStats = get_koch_quotation_stats($pdo, (int) $currentUser['id']);
    $notifications = get_user_notifications($pdo, (int) $currentUser['id']);
    $unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
    $sessions = get_user_login_sessions($pdo, (int) $currentUser['id']);
} catch (Throwable $e) {
    $profile = [];
    $activities = [];
    $quotations = [];
    $qStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    $notifications = [];
    $unreadCount = 0;
    $sessions = [];
}

$section = $_GET['section'] ?? 'dashboard';
$validSections = ['dashboard', 'profile', 'quotations', 'tracking', 'notifications', 'sessions', 'settings'];
if (!in_array($section, $validSections, true)) {
    $section = 'dashboard';
}

// Auto-mark notifications as read when viewing notifications section
if ($section === 'notifications' && isset($pdo)) {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0');
    $stmt->execute([':uid' => (int) $currentUser['id']]);
    $unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
}

// Quotation badge logic: only show badge when there are NEW pending quotations since last visit
// Uses cookie (not session) — persists reliably across admin/company session switches
$currPending = (int) $qStats['pending'];
$cookieParams = ['expires' => time() + 86400 * 90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax'];
$prevSeen = isset($_COOKIE['koch_q_seen']) ? (int) $_COOKIE['koch_q_seen'] : -1;

if ($prevSeen === -1) {
    // First visit ever — initialize cookie, no badge (user already sees the data)
    setcookie('koch_q_seen', (string) $currPending, $cookieParams);
    $_COOKIE['koch_q_seen'] = (string) $currPending;
    $newQuotations = 0;
} elseif ($section === 'quotations') {
    // Viewing quotations — update cookie, clear badge
    setcookie('koch_q_seen', (string) $currPending, $cookieParams);
    $_COOKIE['koch_q_seen'] = (string) $currPending;
    $newQuotations = 0;
} else {
    // Badge only if pending count increased (new quotations added)
    $newQuotations = max(0, $currPending - $prevSeen);
}

$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$avatarUrl = !empty($profile['avatar_url']) ? h((string) $profile['avatar_url']) : '../img/company_logo/logo 2.png';
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin'], true);

function koch_status_badge(string $status): string {
    $map = [
        'pending' => ['bg' => '#fff3e0', 'color' => '#e65100', 'label' => 'รอดำเนินการ'],
        'processing' => ['bg' => '#e3f2fd', 'color' => '#1565c0', 'label' => 'กำลังดำเนินการ'],
        'quoted' => ['bg' => '#f3e5f5', 'color' => '#7b1fa2', 'label' => 'เสนอราคาแล้ว'],
        'approved' => ['bg' => '#e8f5e9', 'color' => '#2e7d32', 'label' => 'อนุมัติแล้ว'],
        'completed' => ['bg' => '#e0f2f1', 'color' => '#00695c', 'label' => 'เสร็จสิ้น'],
        'rejected' => ['bg' => '#fbe9e7', 'color' => '#bf360c', 'label' => 'ปฏิเสธ'],
        'cancelled' => ['bg' => '#efebe9', 'color' => '#4e342e', 'label' => 'ยกเลิก'],
    ];
    $s = $map[$status] ?? ['bg' => '#f5f5f5', 'color' => '#616161', 'label' => $status];
    return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:' . $s['bg'] . ';color:' . $s['color'] . '">' . htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8') . '</span>';
}

function koch_action_label(string $action): string {
    $action = preg_replace('/^\[.*?\]\s*/', '', $action);
    $map = [
        'LOGIN_SUCCESS' => 'เข้าสู่ระบบสำเร็จ',
        'LOGIN_FAILED' => 'เข้าสู่ระบบไม่สำเร็จ',
        'REGISTER_SUCCESS' => 'ลงทะเบียนสำเร็จ',
        'PROFILE_UPDATED' => 'แก้ไขโปรไฟล์',
        'PASSWORD_CHANGED' => 'เปลี่ยนรหัสผ่าน',
        'KOCH_QUOTATION_CREATED' => 'ส่งใบเสนอราคา KOCH',
        'TNB_QUOTATION_CREATED' => 'ส่งใบเสนอราคา TNB',
        'AUTO_REGISTER_FROM_QUOTATION' => 'ลงทะเบียนอัตโนมัติ',
    ];
    return $map[$action] ?? $action;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | KOCH Packaging</title>
    <link rel="icon" type="image/png" href="../img/company_logo/logo 2.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --koch-primary: #ED2A2A;
            --koch-primary-dark: #c41e1e;
            --koch-primary-light: #fef2f2;
            --koch-secondary: #325662;
            --koch-secondary-dark: #243f49;
            --koch-bg: #f0f3f8;
            --koch-card: #ffffff;
            --koch-text: #1e293b;
            --koch-text-muted: #64748b;
            --koch-border: #e8ecf1;
            --koch-success: #059669;
            --koch-warning: #d97706;
            --koch-info: #2563eb;
            --koch-danger: #dc2626;
            --koch-radius: 16px;
            --koch-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 14px rgba(0,0,0,.06);
            --sidebar-width: 272px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: auto; }
        body { font-family: 'Inter', 'Sarabun', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--koch-bg); color: var(--koch-text); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        ::selection { background: var(--koch-primary); color: #fff; }

        /* Layout */
        .user-layout { display: flex; min-height: 100vh; padding-top: 0; }

        /* ===== SIDEBAR (Light InApp Style) ===== */
        .user-sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            color: var(--koch-text);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            border-right: 1px solid var(--koch-border);
            box-shadow: 2px 0 8px rgba(0,0,0,.03);
        }
        .sidebar-header {
            padding: 22px 20px 18px;
            border-bottom: 1px solid var(--koch-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header img { width: 40px; height: 40px; border-radius: 12px; background: var(--koch-primary-light); padding: 4px; object-fit: contain; }
        .sidebar-header .brand { font-size: 17px; font-weight: 800; color: var(--koch-primary); letter-spacing: -.2px; }
        .sidebar-header .brand small { display: block; font-size: 11px; font-weight: 500; color: var(--koch-text-muted); opacity: 1; }

        .sidebar-profile {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid var(--koch-border);
            background: linear-gradient(135deg, var(--koch-primary-light) 0%, #fff5f5 100%);
        }
        .sidebar-avatar {
            width: 72px; height: 72px; border-radius: 50%; object-fit: cover;
            border: 3px solid var(--koch-primary);
            margin: 0 auto 10px;
            display: block;
            background: #f8fafc;
            box-shadow: 0 4px 12px rgba(237,42,42,.15);
        }
        .sidebar-profile h3 { font-size: 15px; font-weight: 700; margin-bottom: 4px; color: var(--koch-text); }
        .sidebar-profile .role-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: var(--koch-primary);
            color: #fff;
            margin-top: 4px;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 10px; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 14px;
            color: var(--koch-text-muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all .2s ease;
            border-radius: 10px;
            margin-bottom: 2px;
            border-left: none;
        }
        .sidebar-nav a:hover { background: #f8fafc; color: var(--koch-text); }
        .sidebar-nav a.active {
            background: var(--koch-primary-light);
            color: var(--koch-primary);
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(237,42,42,.12);
        }
        .sidebar-nav a.active i { color: var(--koch-primary); }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 15px; color: #94a3b8; transition: color .2s; }
        .sidebar-nav a:hover i { color: var(--koch-text); }
        .sidebar-nav .nav-badge {
            margin-left: auto;
            background: var(--koch-primary);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 8px;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }
        .sidebar-nav .nav-divider {
            height: 1px;
            background: var(--koch-border);
            margin: 10px 8px;
        }
        .sidebar-nav .nav-label {
            padding: 10px 14px 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1.2px;
        }

        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--koch-border);
            background: #fafbfc;
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 9px;
            color: var(--koch-text-muted); text-decoration: none; font-size: 12.5px; padding: 7px 8px;
            border-radius: 8px; transition: all .2s;
        }
        .sidebar-footer a:hover { color: var(--koch-primary); background: var(--koch-primary-light); }
        .sidebar-footer a i { font-size: 13px; width: 16px; text-align: center; }

        /* ===== MAIN CONTENT ===== */
        .user-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 0;
            min-height: 100vh;
        }
        .main-topbar {
            background: var(--koch-card);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--koch-border);
        }
        .topbar-left h1 { font-size: 18px; font-weight: 700; letter-spacing: -.2px; }
        .topbar-left .breadcrumb { font-size: 12px; color: var(--koch-text-muted); margin-top: 1px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            text-decoration: none; border: none; cursor: pointer;
            transition: all .2s; font-family: inherit;
        }
        .topbar-btn.primary { background: var(--koch-primary); color: #fff; box-shadow: 0 2px 8px rgba(237,42,42,.25); }
        .topbar-btn.primary:hover { background: var(--koch-primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(237,42,42,.3); }
        .topbar-btn.ghost { background: transparent; color: var(--koch-text-muted); }
        .topbar-btn.ghost:hover { background: var(--koch-bg); }

        .mobile-toggle {
            display: none;
            background: none; border: none; font-size: 20px; cursor: pointer; color: var(--koch-text);
            padding: 8px; border-radius: 8px; transition: background .2s;
        }
        .mobile-toggle:hover { background: #f1f5f9; }

        .main-content { padding: 24px 28px 48px; }

        /* ===== ALERT ===== */
        .k-alert {
            position: fixed; top: 22px; right: 22px; z-index: 9999;
            padding: 14px 20px; border-radius: 12px;
            font-weight: 600; font-size: 13px;
            display: flex; align-items: center; gap: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.12); max-width: 400px;
            animation: kAlertIn .4s cubic-bezier(.4,0,.2,1);
            backdrop-filter: blur(10px);
        }
        .k-alert.success { background: rgba(220,252,231,.95); color: #166534; border: 1px solid #bbf7d0; }
        .k-alert.error { background: rgba(254,226,226,.95); color: #991b1b; border: 1px solid #fecaca; }
        .k-alert i { font-size: 17px; }
        @keyframes kAlertIn { from { opacity:0; transform: translateY(-12px) scale(.96); } to { opacity:1; transform: translateY(0) scale(1); } }

        /* ===== STAT CARDS (InApp Pastel Style) ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            border-radius: var(--koch-radius);
            padding: 22px 20px;
            position: relative;
            overflow: hidden;
            transition: transform .25s ease, box-shadow .25s ease;
            border: 1px solid transparent;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        .stat-card::before { display: none; }

        /* Pastel backgrounds like InApp */
        .stat-card.red { background: linear-gradient(135deg, #fff1f1 0%, #fee2e2 100%); border-color: #fecaca; }
        .stat-card.blue { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-color: #bfdbfe; }
        .stat-card.green { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #bbf7d0; }
        .stat-card.orange { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-color: #fde68a; }

        .stat-card .stat-icon {
            width: 46px; height: 46px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px; margin-bottom: 16px;
        }
        .stat-card.red .stat-icon { background: rgba(237,42,42,.12); color: var(--koch-primary); }
        .stat-card.blue .stat-icon { background: rgba(37,99,235,.12); color: var(--koch-info); }
        .stat-card.green .stat-icon { background: rgba(5,150,105,.12); color: var(--koch-success); }
        .stat-card.orange .stat-icon { background: rgba(217,119,6,.12); color: var(--koch-warning); }
        .stat-card .stat-number { font-size: 30px; font-weight: 800; line-height: 1; margin-bottom: 4px; color: var(--koch-text); }
        .stat-card .stat-label { font-size: 13px; color: var(--koch-text-muted); font-weight: 500; }

        /* ===== CARD ===== */
        .k-card {
            background: var(--koch-card);
            border-radius: var(--koch-radius);
            box-shadow: var(--koch-shadow);
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid var(--koch-border);
            transition: box-shadow .2s;
        }
        .k-card:hover { box-shadow: 0 2px 6px rgba(0,0,0,.04), 0 8px 20px rgba(0,0,0,.08); }
        .k-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--koch-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbfd;
        }
        .k-card-header h2 { font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 9px; letter-spacing: -.1px; }
        .k-card-header h2 i { color: var(--koch-primary); font-size: 16px; opacity: .85; }
        .k-card-body { padding: 22px; }

        /* ===== TABLE ===== */
        .k-table-wrap { overflow-x: auto; }
        .k-table { width: 100%; border-collapse: collapse; }
        .k-table th {
            text-align: left;
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #94a3b8;
            background: #f8fafc;
            border-bottom: 1px solid var(--koch-border);
        }
        .k-table td {
            padding: 13px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: var(--koch-text);
        }
        .k-table tr:last-child td { border-bottom: none; }
        .k-table tr { transition: background .15s; }
        .k-table tr:hover td { background: #f8fafc; }
        .k-table .empty-row td { text-align: center; color: var(--koch-text-muted); padding: 48px 16px; font-size: 14px; }

        /* ===== FORM ===== */
        .k-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .k-field { display: flex; flex-direction: column; gap: 6px; }
        .k-field.full { grid-column: 1 / -1; }
        .k-field label { font-size: 12px; font-weight: 600; color: var(--koch-text); text-transform: uppercase; letter-spacing: .3px; }
        .k-field input, .k-field select, .k-field textarea {
            padding: 11px 14px;
            border: 1.5px solid var(--koch-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: all .2s ease;
            background: #fff;
            color: var(--koch-text);
        }
        .k-field input:focus, .k-field select:focus, .k-field textarea:focus {
            outline: none;
            border-color: var(--koch-primary);
            box-shadow: 0 0 0 4px rgba(237,42,42,.08);
        }
        .k-field input[readonly] { background: #f8fafc; color: var(--koch-text-muted); cursor: not-allowed; border-style: dashed; }

        /* ===== BUTTONS ===== */
        .k-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 22px; border-radius: 10px;
            font-size: 13.5px; font-weight: 600; font-family: inherit;
            border: none; cursor: pointer;
            text-decoration: none; transition: all .2s ease;
        }
        .k-btn.primary { background: var(--koch-primary); color: #fff; box-shadow: 0 2px 8px rgba(237,42,42,.2); }
        .k-btn.primary:hover { background: var(--koch-primary-dark); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(237,42,42,.3); }
        .k-btn.secondary { background: var(--koch-secondary); color: #fff; }
        .k-btn.secondary:hover { background: var(--koch-secondary-dark); transform: translateY(-1px); }
        .k-btn.outline { background: transparent; border: 1.5px solid var(--koch-border); color: var(--koch-text); }
        .k-btn.outline:hover { border-color: var(--koch-primary); color: var(--koch-primary); background: var(--koch-primary-light); }

        .k-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }

        /* ===== NOTIFICATION ITEM ===== */
        .notif-item {
            display: flex; gap: 14px; padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .notif-icon.info { background: #eff6ff; color: #2563eb; }
        .notif-icon.success { background: #f0fdf4; color: #059669; }
        .notif-icon.warning { background: #fffbeb; color: #d97706; }
        .notif-icon.error { background: #fef2f2; color: #dc2626; }
        .notif-content { flex: 1; }
        .notif-content h4 { font-size: 13.5px; font-weight: 600; margin-bottom: 2px; }
        .notif-content p { font-size: 12.5px; color: var(--koch-text-muted); margin: 0; line-height: 1.5; }
        .notif-time { font-size: 11px; color: #94a3b8; white-space: nowrap; font-weight: 500; }
        .notif-unread { background: var(--koch-primary-light); border-radius: 12px; margin: 0 -12px; padding: 14px 12px !important; border-left: 3px solid var(--koch-primary); }

        /* ===== SESSION ITEM ===== */
        .session-item {
            display: flex; align-items: center; gap: 14px; padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .session-item:last-child { border-bottom: none; }
        .session-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .session-icon.active { background: #f0fdf4; color: #059669; }
        .session-icon.inactive { background: #f8fafc; color: #94a3b8; }
        .session-info { flex: 1; }
        .session-info h4 { font-size: 13.5px; font-weight: 600; margin-bottom: 2px; }
        .session-info p { font-size: 12px; color: var(--koch-text-muted); margin: 0; }
        .session-status { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
        .session-status.active { background: #dcfce7; color: #166534; }
        .session-status.expired { background: #f1f5f9; color: #64748b; }

        /* ===== TWO COLUMN GRID ===== */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* ===== RESPONSIVE ===== */
        .sidebar-overlay {
            display: none;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15,23,42,.4); z-index: 999;
            backdrop-filter: blur(2px);
        }
        @media (max-width: 1024px) {
            .user-sidebar { transform: translateX(-100%); }
            .user-sidebar.open { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .user-main { margin-left: 0; }
            .mobile-toggle { display: block; }
            .main-content { padding: 20px 16px 40px; }
            .main-topbar { padding: 12px 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .k-form-grid { grid-template-columns: 1fr; }
            .two-col { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside class="user-sidebar" id="userSidebar">
        <div class="sidebar-header">
            <img src="../img/company_logo/logo 2.png" alt="KOCH">
            <div class="brand">KOCH<small>Packaging Services</small></div>
        </div>
        <div class="sidebar-profile">
            <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="sidebar-avatar">
            <h3><?php echo h($fullName ?: $profile['username'] ?? 'User'); ?></h3>
            <span class="role-badge"><?php echo h(ucfirst((string) ($profile['role'] ?? 'user'))); ?></span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">เมนูหลัก</div>
            <a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> แดชบอร์ด
            </a>
            <a href="?section=profile" class="<?php echo $section === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> โปรไฟล์ของฉัน
            </a>
            <a href="?section=quotations" class="<?php echo $section === 'quotations' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i> ใบเสนอราคา
                <?php if ($newQuotations > 0): ?>
                    <span class="nav-badge"><?php echo $newQuotations; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=tracking" class="<?php echo $section === 'tracking' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> ติดตามสถานะงาน
            </a>
            <div class="nav-divider"></div>
            <div class="nav-label">การแจ้งเตือน</div>
            <a href="?section=notifications" class="<?php echo $section === 'notifications' ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i> การแจ้งเตือน
                <?php if ($unreadCount > 0): ?>
                    <span class="nav-badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="?section=sessions" class="<?php echo $section === 'sessions' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i> ประวัติเข้าใช้ระบบ
            </a>
            <div class="nav-divider"></div>
            <div class="nav-label">ตั้งค่า</div>
            <a href="?section=settings" class="<?php echo $section === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> ตั้งค่าบัญชี
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../main/index.php"><i class="fas fa-home"></i> กลับหน้าเว็บไซต์</a>
            <?php if ($isAdmin): ?>
                <a href="<?php echo h(project_url('admin/dashboard.php')); ?>"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
            <?php endif; ?>
            <a href="<?php echo h(project_url('admin/api/auth/logout.php')); ?>?company=koch"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="user-main">
        <div class="main-topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1><?php
                    $titles = [
                        'dashboard' => 'แดชบอร์ด',
                        'profile' => 'โปรไฟล์ของฉัน',
                        'quotations' => 'ใบเสนอราคา',
                        'tracking' => 'ติดตามสถานะงาน',
                        'notifications' => 'การแจ้งเตือน',
                        'sessions' => 'ประวัติเข้าใช้ระบบ',
                        'settings' => 'ตั้งค่าบัญชี',
                    ];
                    echo $titles[$section] ?? 'แดชบอร์ด';
                ?></h1>
                <div class="breadcrumb">KOCH Packaging &rsaquo; <?php echo $titles[$section] ?? 'แดชบอร์ด'; ?></div>
            </div>
            <div class="topbar-right">
                <?php if ($section === 'quotations'): ?>
                    <a href="<?php echo h(project_url('koch/main/quotation.php')); ?>" class="topbar-btn primary"><i class="fas fa-plus"></i> ขอใบเสนอราคา</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="main-content">
            <?php if ($successMessage): ?>
                <div class="k-alert success" id="flashAlert"><i class="fas fa-check-circle"></i> <?php echo h((string) $successMessage); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="k-alert error" id="flashAlert"><i class="fas fa-exclamation-circle"></i> <?php echo h((string) $errorMessage); ?></div>
            <?php endif; ?>

            <?php if ($section === 'dashboard'): ?>
            <!-- =================== DASHBOARD =================== -->
            <div class="stats-grid">
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['total']; ?></div>
                    <div class="stat-label">ใบเสนอราคาทั้งหมด</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['pending']; ?></div>
                    <div class="stat-label">รอดำเนินการ</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['processing']; ?></div>
                    <div class="stat-label">กำลังดำเนินการ</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['completed']; ?></div>
                    <div class="stat-label">เสร็จสิ้นแล้ว</div>
                </div>
            </div>

            <div class="two-col">
                <div class="k-card">
                    <div class="k-card-header">
                        <h2><i class="fas fa-history"></i> กิจกรรมล่าสุด</h2>
                    </div>
                    <div class="k-card-body" style="padding:0;">
                        <div class="k-table-wrap">
                            <table class="k-table">
                                <thead><tr><th>วันที่</th><th>กิจกรรม</th></tr></thead>
                                <tbody>
                                <?php if ($activities === []): ?>
                                    <tr class="empty-row"><td colspan="2">ยังไม่มีกิจกรรม</td></tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($activities, 0, 8) as $a): ?>
                                    <tr>
                                        <td style="white-space:nowrap;font-size:12px;color:var(--koch-text-muted)"><?php echo h((string) $a['created_at']); ?></td>
                                        <td><?php echo h(koch_action_label((string) $a['action'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="k-card">
                    <div class="k-card-header">
                        <h2><i class="fas fa-bell"></i> การแจ้งเตือนล่าสุด</h2>
                        <?php if ($unreadCount > 0): ?>
                            <span style="background:var(--koch-primary);color:#fff;font-size:12px;font-weight:700;padding:3px 10px;border-radius:12px;"><?php echo $unreadCount; ?> ใหม่</span>
                        <?php endif; ?>
                    </div>
                    <div class="k-card-body">
                        <?php if ($notifications === []): ?>
                            <p style="text-align:center;color:var(--koch-text-muted);padding:20px 0;">ยังไม่มีการแจ้งเตือน</p>
                        <?php else: ?>
                            <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                            <div class="notif-item <?php echo !$n['is_read'] ? 'notif-unread' : ''; ?>">
                                <div class="notif-icon <?php echo h((string) ($n['type'] ?? 'info')); ?>">
                                    <i class="fas fa-<?php echo $n['type'] === 'success' ? 'check' : ($n['type'] === 'warning' ? 'exclamation-triangle' : ($n['type'] === 'error' ? 'times' : 'info')); ?>"></i>
                                </div>
                                <div class="notif-content">
                                    <h4><?php echo h((string) $n['title']); ?></h4>
                                    <p><?php echo h((string) $n['message']); ?></p>
                                </div>
                                <div class="notif-time"><?php echo h(date('d/m H:i', strtotime((string) $n['created_at']))); ?></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="k-card">
                <div class="k-card-header">
                    <h2><i class="fas fa-file-invoice"></i> ใบเสนอราคาล่าสุด</h2>
                    <a href="?section=quotations" class="k-btn outline" style="padding:6px 14px;font-size:13px;">ดูทั้งหมด</a>
                </div>
                <div class="k-card-body" style="padding:0;">
                    <div class="k-table-wrap">
                        <table class="k-table">
                            <thead>
                                <tr><th>เลขที่</th><th>วันที่</th><th>ประเภทสินค้า</th><th>สถานะ</th></tr>
                            </thead>
                            <tbody>
                            <?php if ($quotations === []): ?>
                                <tr class="empty-row"><td colspan="4">ยังไม่มีใบเสนอราคา</td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($quotations, 0, 5) as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td style="white-space:nowrap;"><?php echo h(date('d/m/Y', strtotime((string) $q['created_at']))); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td><?php echo koch_status_badge((string) $q['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'profile'): ?>
            <!-- =================== PROFILE =================== -->
            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-user-edit"></i> แก้ไขข้อมูลส่วนตัว</h2></div>
                <div class="k-card-body">
                    <form action="../../admin/api/profile/update.php" method="POST">
                        <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>">
                        <input type="hidden" name="company" value="koch">
                        <div class="k-form-grid">
                            <div class="k-field">
                                <label>Username</label>
                                <input type="text" value="<?php echo h((string) ($profile['username'] ?? '')); ?>" readonly>
                            </div>
                            <div class="k-field">
                                <label>บริษัท</label>
                                <input type="text" value="<?php echo h((string) ($profile['company_name'] ?? 'KOCH')); ?>" readonly>
                            </div>
                            <div class="k-field">
                                <label for="first_name">ชื่อ</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo h(old_input('first_name', (string) ($profile['first_name'] ?? ''))); ?>" required>
                            </div>
                            <div class="k-field">
                                <label for="last_name">นามสกุล</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo h(old_input('last_name', (string) ($profile['last_name'] ?? ''))); ?>" required>
                            </div>
                            <div class="k-field">
                                <label for="nick_name">ชื่อเล่น</label>
                                <input type="text" id="nick_name" name="nick_name" value="<?php echo h(old_input('nick_name', (string) ($profile['nick_name'] ?? ''))); ?>">
                            </div>
                            <div class="k-field">
                                <label for="phone">เบอร์โทรศัพท์</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo h(old_input('phone', (string) ($profile['phone'] ?? ''))); ?>" required>
                            </div>
                            <div class="k-field full">
                                <label for="email">อีเมล</label>
                                <input type="email" id="email" name="email" value="<?php echo h(old_input('email', (string) ($profile['email'] ?? ''))); ?>" required>
                            </div>
                            <div class="k-field">
                                <label for="department">แผนก</label>
                                <input type="text" id="department" name="department" value="<?php echo h(old_input('department', (string) ($profile['department'] ?? ''))); ?>">
                            </div>
                            <div class="k-field">
                                <label for="position">ตำแหน่ง</label>
                                <input type="text" id="position" name="position" value="<?php echo h(old_input('position', (string) ($profile['position'] ?? ''))); ?>">
                            </div>
                        </div>
                        <div class="k-actions">
                            <button type="submit" class="k-btn primary"><i class="fas fa-save"></i> บันทึกข้อมูล</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-id-card"></i> ข้อมูลบัญชี</h2></div>
                <div class="k-card-body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
                        <div style="padding:16px;background:var(--koch-bg);border-radius:12px;">
                            <div style="font-size:12px;color:var(--koch-text-muted);margin-bottom:4px;">สถานะบัญชี</div>
                            <div style="font-weight:700;color:var(--koch-success);">Active</div>
                        </div>
                        <div style="padding:16px;background:var(--koch-bg);border-radius:12px;">
                            <div style="font-size:12px;color:var(--koch-text-muted);margin-bottom:4px;">สมาชิกตั้งแต่</div>
                            <div style="font-weight:600;"><?php echo h(date('d/m/Y', strtotime((string) ($profile['created_at'] ?? 'now')))); ?></div>
                        </div>
                        <div style="padding:16px;background:var(--koch-bg);border-radius:12px;">
                            <div style="font-size:12px;color:var(--koch-text-muted);margin-bottom:4px;">เข้าใช้งานล่าสุด</div>
                            <div style="font-weight:600;"><?php echo $profile['last_login'] ? h(date('d/m/Y H:i', strtotime((string) $profile['last_login']))) : 'ไม่มีข้อมูล'; ?></div>
                        </div>
                        <div style="padding:16px;background:var(--koch-bg);border-radius:12px;">
                            <div style="font-size:12px;color:var(--koch-text-muted);margin-bottom:4px;">บทบาท</div>
                            <div style="font-weight:600;"><?php echo h(ucfirst((string) ($profile['role'] ?? 'user'))); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'quotations'): ?>
            <!-- =================== QUOTATIONS =================== -->
            <div class="stats-grid">
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['total']; ?></div>
                    <div class="stat-label">ทั้งหมด</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['pending']; ?></div>
                    <div class="stat-label">รอดำเนินการ</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['approved']; ?></div>
                    <div class="stat-label">อนุมัติแล้ว</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fas fa-flag-checkered"></i></div>
                    <div class="stat-number"><?php echo (int)$qStats['completed']; ?></div>
                    <div class="stat-label">เสร็จสิ้น</div>
                </div>
            </div>

            <div class="k-card">
                <div class="k-card-header">
                    <h2><i class="fas fa-file-invoice"></i> รายการใบเสนอราคาทั้งหมด</h2>
                </div>
                <div class="k-card-body" style="padding:0;">
                    <div class="k-table-wrap">
                        <table class="k-table">
                            <thead>
                                <tr>
                                    <th>เลขที่</th>
                                    <th>วันที่</th>
                                    <th>ประเภทสินค้า</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($quotations === []): ?>
                                <tr class="empty-row"><td colspan="4">ยังไม่มีใบเสนอราคา</td></tr>
                            <?php else: ?>
                                <?php foreach ($quotations as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td style="white-space:nowrap;"><?php echo h(date('d/m/Y H:i', strtotime((string) $q['created_at']))); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td><?php echo koch_status_badge((string) $q['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'tracking'): ?>
            <!-- =================== TRACKING =================== -->
            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-tasks"></i> ติดตามสถานะงาน</h2></div>
                <div class="k-card-body" style="padding:0;">
                    <div class="k-table-wrap">
                        <table class="k-table">
                            <thead>
                                <tr>
                                    <th>เลขที่</th>
                                    <th>ประเภทสินค้า</th>
                                    <th>วันที่ส่ง</th>
                                    <th>สถานะ</th>
                                    <th>ขั้นตอน</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $activeQuotations = array_filter($quotations, fn($q) => in_array((string)$q['status'], ['pending', 'processing', 'quoted', 'approved']));
                            if ($activeQuotations === []): ?>
                                <tr class="empty-row"><td colspan="5">ไม่มีงานที่กำลังดำเนินการ</td></tr>
                            <?php else: ?>
                                <?php foreach ($activeQuotations as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td style="white-space:nowrap;"><?php echo h(date('d/m/Y', strtotime((string) $q['created_at']))); ?></td>
                                    <td><?php echo koch_status_badge((string) $q['status']); ?></td>
                                    <td>
                                        <?php
                                        $steps = ['pending' => 1, 'processing' => 2, 'quoted' => 3, 'approved' => 4, 'completed' => 5];
                                        $current = $steps[$q['status']] ?? 1;
                                        $total = 5;
                                        $pct = ($current / $total) * 100;
                                        ?>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="flex:1;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                                <div style="height:100%;width:<?php echo $pct; ?>%;background:var(--koch-primary);border-radius:3px;transition:width .3s;"></div>
                                            </div>
                                            <span style="font-size:12px;font-weight:600;color:var(--koch-text-muted);"><?php echo $current; ?>/<?php echo $total; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-check-circle"></i> งานที่เสร็จสิ้นแล้ว</h2></div>
                <div class="k-card-body" style="padding:0;">
                    <div class="k-table-wrap">
                        <table class="k-table">
                            <thead><tr><th>เลขที่</th><th>ประเภท</th><th>วันที่เสร็จ</th><th>สถานะ</th></tr></thead>
                            <tbody>
                            <?php
                            $doneQuotations = array_filter($quotations, fn($q) => in_array((string)$q['status'], ['completed', 'rejected', 'cancelled']));
                            if ($doneQuotations === []): ?>
                                <tr class="empty-row"><td colspan="4">ยังไม่มีงานที่เสร็จสิ้น</td></tr>
                            <?php else: ?>
                                <?php foreach ($doneQuotations as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td style="white-space:nowrap;"><?php echo h(date('d/m/Y', strtotime((string) $q['created_at']))); ?></td>
                                    <td><?php echo koch_status_badge((string) $q['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'notifications'): ?>
            <!-- =================== NOTIFICATIONS =================== -->
            <div class="k-card">
                <div class="k-card-header">
                    <h2><i class="fas fa-bell"></i> การแจ้งเตือนทั้งหมด</h2>
                    <?php if ($unreadCount > 0): ?>
                        <span style="background:var(--koch-primary);color:#fff;font-size:12px;font-weight:700;padding:4px 12px;border-radius:12px;"><?php echo $unreadCount; ?> ยังไม่อ่าน</span>
                    <?php endif; ?>
                </div>
                <div class="k-card-body">
                    <?php if ($notifications === []): ?>
                        <div style="text-align:center;padding:48px 20px;color:var(--koch-text-muted);">
                            <i class="fas fa-bell-slash" style="font-size:48px;margin-bottom:16px;opacity:.3;display:block;"></i>
                            <p style="font-size:16px;font-weight:600;">ยังไม่มีการแจ้งเตือน</p>
                            <p style="font-size:13px;">เมื่อมีอัปเดตจากใบเสนอราคาของคุณ จะแสดงที่นี่</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                        <div class="notif-item <?php echo !$n['is_read'] ? 'notif-unread' : ''; ?>">
                            <div class="notif-icon <?php echo h((string) ($n['type'] ?? 'info')); ?>">
                                <i class="fas fa-<?php echo $n['type'] === 'success' ? 'check' : ($n['type'] === 'warning' ? 'exclamation-triangle' : ($n['type'] === 'error' ? 'times' : 'info')); ?>"></i>
                            </div>
                            <div class="notif-content">
                                <h4><?php echo h((string) $n['title']); ?></h4>
                                <p><?php echo h((string) $n['message']); ?></p>
                            </div>
                            <div class="notif-time"><?php echo h(date('d/m/Y H:i', strtotime((string) $n['created_at']))); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($section === 'sessions'): ?>
            <!-- =================== LOGIN SESSIONS =================== -->
            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-shield-alt"></i> ประวัติการเข้าสู่ระบบ</h2></div>
                <div class="k-card-body">
                    <?php if ($sessions === []): ?>
                        <p style="text-align:center;color:var(--koch-text-muted);padding:32px 0;">ไม่มีข้อมูลเซสชั่น</p>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                        <?php
                            $isActive = (int)$s['is_active'] === 1 && strtotime((string)$s['expires_at']) > time();
                            $isCurrent = isset($currentUser['session_token']) && $s['session_token'] === $currentUser['session_token'];
                        ?>
                        <div class="session-item">
                            <div class="session-info">
                                <h4><?php echo h(date('d/m/Y H:i', strtotime((string) $s['created_at']))); ?> <?php echo $isCurrent ? '<span style="color:var(--koch-primary);font-size:11px;">(เซสชั่นปัจจุบัน)</span>' : ''; ?></h4>
                            </div>
                            <span class="session-status <?php echo $isActive ? 'active' : 'expired'; ?>">
                                <?php echo $isActive ? 'Active' : 'Expired'; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-history"></i> ประวัติกิจกรรมทั้งหมด</h2></div>
                <div class="k-card-body" style="padding:0;">
                    <div class="k-table-wrap">
                        <table class="k-table">
                            <thead><tr><th>วันที่</th><th>กิจกรรม</th><th>ตาราง</th></tr></thead>
                            <tbody>
                            <?php if ($activities === []): ?>
                                <tr class="empty-row"><td colspan="3">ยังไม่มีกิจกรรม</td></tr>
                            <?php else: ?>
                                <?php foreach ($activities as $a): ?>
                                <tr>
                                    <td style="white-space:nowrap;font-size:13px;"><?php echo h(date('d/m/Y H:i', strtotime((string) $a['created_at']))); ?></td>
                                    <td><?php echo h(koch_action_label((string) $a['action'])); ?></td>
                                    <td style="font-size:13px;color:var(--koch-text-muted);"><?php echo h((string) ($a['table_name'] ?? '-')); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php elseif ($section === 'settings'): ?>
            <!-- =================== SETTINGS =================== -->
            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-lock"></i> เปลี่ยนรหัสผ่าน</h2></div>
                <div class="k-card-body">
                    <form action="../../admin/api/profile/change-password.php" method="POST">
                        <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>">
                        <input type="hidden" name="company" value="koch">
                        <div class="k-form-grid">
                            <div class="k-field full">
                                <label for="current_password">รหัสผ่านปัจจุบัน</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="k-field">
                                <label for="new_password">รหัสผ่านใหม่</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="k-field">
                                <label for="confirm_new_password">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                            </div>
                        </div>
                        <p style="font-size:12px;color:var(--koch-text-muted);margin-top:12px;">รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลข</p>
                        <div class="k-actions">
                            <button type="submit" class="k-btn primary"><i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php endif; ?>

        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('userSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        document.addEventListener('DOMContentLoaded', function() {
            var a = document.getElementById('flashAlert');
            if (a) {
                setTimeout(function() {
                    a.style.transition = 'opacity .5s ease';
                    a.style.opacity = '0';
                    setTimeout(function() { a.remove(); }, 500);
                }, 5000);
            }
        });
    
    // Save scroll position before navigating
        document.querySelectorAll('.sidebar-nav a[href^="?section"]').forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('sidebarScroll', document.querySelector('.sidebar-nav').scrollTop);
                sessionStorage.setItem('mainScroll', window.scrollY);
            });
        });
        // Restore scroll position after page load
        var savedSidebar = sessionStorage.getItem('sidebarScroll');
        var savedMain = sessionStorage.getItem('mainScroll');
        if (savedSidebar) { document.querySelector('.sidebar-nav').scrollTop = parseInt(savedSidebar); }
        if (savedMain) { window.scrollTo(0, parseInt(savedMain)); }
    </script>
    <?php clear_old_input(); ?>
</body>
</html>
