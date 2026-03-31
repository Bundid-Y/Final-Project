<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/profile.php';

$currentUser = authenticated_user();
if ($currentUser === null) {
    redirect_to(project_url('koch/main/login.php'));
}

// Strict company validation - KOCH users ONLY (except admins)
$userCompany = strtoupper((string) ($currentUser['company_code'] ?? ''));
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true);

if ($userCompany !== 'KOCH' && !$isAdmin) {
    // Not a KOCH user and not admin - redirect to correct company page
    redirect_to(user_page_by_company($userCompany));
}

$pdo = Database::connection();
$profile = get_profile_summary($pdo, (int) $currentUser['id']);
$activities = get_recent_activity_logs($pdo, (int) $currentUser['id'], 15);
$quotations = get_koch_user_quotations($pdo, (int) $currentUser['id'], 20);
$qStats = get_koch_quotation_stats($pdo, (int) $currentUser['id']);
$notifications = get_user_notifications($pdo, (int) $currentUser['id']);
$unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
$sessions = get_user_login_sessions($pdo, (int) $currentUser['id']);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');

$section = $_GET['section'] ?? 'dashboard';
$validSections = ['dashboard', 'profile', 'quotations', 'tracking', 'notifications', 'sessions', 'settings'];
if (!in_array($section, $validSections, true)) {
    $section = 'dashboard';
}

$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$avatarUrl = !empty($profile['avatar_url']) ? h((string) $profile['avatar_url']) : '../img/company_logo/logo 2.png';
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true);

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
            --koch-secondary: #325662;
            --koch-secondary-dark: #243f49;
            --koch-bg: #f4f6fa;
            --koch-card: #ffffff;
            --koch-text: #1a2332;
            --koch-text-muted: #64748b;
            --koch-border: #e2e8f0;
            --koch-success: #16a34a;
            --koch-warning: #ea580c;
            --koch-info: #2563eb;
            --koch-radius: 16px;
            --koch-shadow: 0 1px 3px rgba(0,0,0,.06), 0 6px 16px rgba(0,0,0,.04);
            --sidebar-width: 280px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Sarabun', 'Inter', sans-serif; background: var(--koch-bg); color: var(--koch-text); line-height: 1.6; }

        /* Layout */
        .user-layout { display: flex; min-height: 100vh; padding-top: 0; }

        /* Sidebar */
        .user-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--koch-secondary) 0%, var(--koch-secondary-dark) 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
        }
        .sidebar-header {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .sidebar-header img { width: 44px; height: 44px; border-radius: 10px; background: #fff; padding: 4px; }
        .sidebar-header .brand { font-size: 18px; font-weight: 700; letter-spacing: .5px; }
        .sidebar-header .brand small { display: block; font-size: 11px; font-weight: 400; opacity: .7; }

        .sidebar-profile {
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-avatar {
            width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
            border: 3px solid rgba(255,255,255,.3);
            margin: 0 auto 10px;
            display: block;
            background: rgba(255,255,255,.15);
        }
        .sidebar-profile h3 { font-size: 16px; font-weight: 600; margin-bottom: 2px; }
        .sidebar-profile .role-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: var(--koch-primary);
            color: #fff;
            margin-top: 6px;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 16px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar-nav a.active {
            background: rgba(255,255,255,.12);
            color: #fff;
            border-left-color: var(--koch-primary);
            font-weight: 600;
        }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 15px; }
        .sidebar-nav .nav-badge {
            margin-left: auto;
            background: var(--koch-primary);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }
        .sidebar-nav .nav-divider {
            height: 1px;
            background: rgba(255,255,255,.1);
            margin: 12px 24px;
        }
        .sidebar-nav .nav-label {
            padding: 8px 24px 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255,255,255,.4);
            letter-spacing: 1px;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 10px;
            color: rgba(255,255,255,.6); text-decoration: none; font-size: 13px; padding: 8px 0;
            transition: color .2s;
        }
        .sidebar-footer a:hover { color: #fff; }

        /* Main Content */
        .user-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 0;
            min-height: 100vh;
        }
        .main-topbar {
            background: var(--koch-card);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-left h1 { font-size: 20px; font-weight: 700; }
        .topbar-left .breadcrumb { font-size: 13px; color: var(--koch-text-muted); }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .topbar-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            text-decoration: none; border: none; cursor: pointer;
            transition: all .2s;
        }
        .topbar-btn.primary { background: var(--koch-primary); color: #fff; }
        .topbar-btn.primary:hover { background: var(--koch-primary-dark); }
        .topbar-btn.ghost { background: transparent; color: var(--koch-text-muted); }
        .topbar-btn.ghost:hover { background: var(--koch-bg); }

        .mobile-toggle {
            display: none;
            background: none; border: none; font-size: 22px; cursor: pointer; color: var(--koch-text);
            padding: 8px;
        }

        .main-content { padding: 28px 32px 48px; }

        /* Alert */
        .k-alert {
            padding: 14px 20px; border-radius: var(--koch-radius);
            margin-bottom: 20px; font-weight: 500; font-size: 14px;
            display: flex; align-items: center; gap: 10px;
        }
        .k-alert.success { background: #dcfce7; color: #166534; }
        .k-alert.error { background: #fee2e2; color: #991b1b; }
        .k-alert i { font-size: 18px; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--koch-card);
            border-radius: var(--koch-radius);
            padding: 22px;
            box-shadow: var(--koch-shadow);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
        }
        .stat-card.red::before { background: var(--koch-primary); }
        .stat-card.blue::before { background: var(--koch-info); }
        .stat-card.green::before { background: var(--koch-success); }
        .stat-card.orange::before { background: var(--koch-warning); }
        .stat-card .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin-bottom: 14px;
        }
        .stat-card.red .stat-icon { background: #fee2e2; color: var(--koch-primary); }
        .stat-card.blue .stat-icon { background: #dbeafe; color: var(--koch-info); }
        .stat-card.green .stat-icon { background: #dcfce7; color: var(--koch-success); }
        .stat-card.orange .stat-icon { background: #fff7ed; color: var(--koch-warning); }
        .stat-card .stat-number { font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
        .stat-card .stat-label { font-size: 13px; color: var(--koch-text-muted); font-weight: 500; }

        /* Card */
        .k-card {
            background: var(--koch-card);
            border-radius: var(--koch-radius);
            box-shadow: var(--koch-shadow);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .k-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--koch-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .k-card-header h2 { font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .k-card-header h2 i { color: var(--koch-primary); font-size: 18px; }
        .k-card-body { padding: 24px; }

        /* Table */
        .k-table-wrap { overflow-x: auto; }
        .k-table { width: 100%; border-collapse: collapse; }
        .k-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--koch-text-muted);
            background: var(--koch-bg);
            border-bottom: 1px solid var(--koch-border);
        }
        .k-table td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--koch-border);
            vertical-align: middle;
        }
        .k-table tr:last-child td { border-bottom: none; }
        .k-table tr:hover td { background: #f8fafc; }
        .k-table .empty-row td { text-align: center; color: var(--koch-text-muted); padding: 40px 16px; }

        /* Form */
        .k-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
        .k-field { display: flex; flex-direction: column; gap: 6px; }
        .k-field.full { grid-column: 1 / -1; }
        .k-field label { font-size: 13px; font-weight: 600; color: var(--koch-text); }
        .k-field input, .k-field select, .k-field textarea {
            padding: 11px 14px;
            border: 1.5px solid var(--koch-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color .2s;
            background: #fff;
        }
        .k-field input:focus, .k-field select:focus, .k-field textarea:focus {
            outline: none;
            border-color: var(--koch-primary);
            box-shadow: 0 0 0 3px rgba(237,42,42,.1);
        }
        .k-field input[readonly] { background: var(--koch-bg); color: var(--koch-text-muted); cursor: not-allowed; }

        /* Buttons */
        .k-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 22px; border-radius: 10px;
            font-size: 14px; font-weight: 600; font-family: inherit;
            border: none; cursor: pointer;
            text-decoration: none; transition: all .2s;
        }
        .k-btn.primary { background: var(--koch-primary); color: #fff; }
        .k-btn.primary:hover { background: var(--koch-primary-dark); transform: translateY(-1px); }
        .k-btn.secondary { background: var(--koch-secondary); color: #fff; }
        .k-btn.secondary:hover { background: var(--koch-secondary-dark); }
        .k-btn.outline { background: transparent; border: 1.5px solid var(--koch-border); color: var(--koch-text); }
        .k-btn.outline:hover { border-color: var(--koch-primary); color: var(--koch-primary); }

        .k-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }

        /* Notification Item */
        .notif-item {
            display: flex; gap: 14px; padding: 16px 0;
            border-bottom: 1px solid var(--koch-border);
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .notif-icon.info { background: #dbeafe; color: #2563eb; }
        .notif-icon.success { background: #dcfce7; color: #16a34a; }
        .notif-icon.warning { background: #fff7ed; color: #ea580c; }
        .notif-icon.error { background: #fee2e2; color: #dc2626; }
        .notif-content { flex: 1; }
        .notif-content h4 { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .notif-content p { font-size: 13px; color: var(--koch-text-muted); margin: 0; }
        .notif-time { font-size: 12px; color: var(--koch-text-muted); white-space: nowrap; }
        .notif-unread { background: #fef2f2; border-radius: 12px; margin: 0 -12px; padding: 16px 12px !important; }

        /* Session Item */
        .session-item {
            display: flex; align-items: center; gap: 14px; padding: 16px 0;
            border-bottom: 1px solid var(--koch-border);
        }
        .session-item:last-child { border-bottom: none; }
        .session-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .session-icon.active { background: #dcfce7; color: #16a34a; }
        .session-icon.inactive { background: #f1f5f9; color: #94a3b8; }
        .session-info { flex: 1; }
        .session-info h4 { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .session-info p { font-size: 12px; color: var(--koch-text-muted); margin: 0; }
        .session-status { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
        .session-status.active { background: #dcfce7; color: #166534; }
        .session-status.expired { background: #f1f5f9; color: #64748b; }

        /* Two Column Grid */
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

        /* Responsive */
        .sidebar-overlay {
            display: none;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,.5); z-index: 999;
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
                <?php if ((int)$qStats['pending'] > 0): ?>
                    <span class="nav-badge"><?php echo (int)$qStats['pending']; ?></span>
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
                <a href="../main/quotation.php" class="topbar-btn primary"><i class="fas fa-plus"></i> ขอใบเสนอราคา</a>
            </div>
        </div>

        <div class="main-content">
            <?php if ($successMessage): ?>
                <div class="k-alert success"><i class="fas fa-check-circle"></i> <?php echo h((string) $successMessage); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="k-alert error"><i class="fas fa-exclamation-circle"></i> <?php echo h((string) $errorMessage); ?></div>
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
                                <tr><th>เลขที่</th><th>วันที่</th><th>ประเภทสินค้า</th><th>ราคาเสนอ</th><th>สถานะ</th></tr>
                            </thead>
                            <tbody>
                            <?php if ($quotations === []): ?>
                                <tr class="empty-row"><td colspan="5">ยังไม่มีใบเสนอราคา — <a href="../main/quotation.php" style="color:var(--koch-primary);font-weight:600;">ขอใบเสนอราคาแรก</a></td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($quotations, 0, 5) as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td style="white-space:nowrap;"><?php echo h(date('d/m/Y', strtotime((string) $q['created_at']))); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td><?php echo $q['quoted_price'] !== null ? h(number_format((float) $q['quoted_price'], 2)) . ' ฿' : '<span style="color:var(--koch-text-muted)">รอเสนอราคา</span>'; ?></td>
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
                    <a href="../main/quotation.php" class="k-btn primary" style="padding:8px 16px;font-size:13px;"><i class="fas fa-plus"></i> ขอใบเสนอราคาใหม่</a>
                </div>
                <div class="k-card-body" style="padding:0;">
                    <div class="k-table-wrap">
                        <table class="k-table">
                            <thead>
                                <tr>
                                    <th>เลขที่</th>
                                    <th>วันที่</th>
                                    <th>ประเภทสินค้า</th>
                                    <th>ราคาเสนอ</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($quotations === []): ?>
                                <tr class="empty-row"><td colspan="5">ยังไม่มีใบเสนอราคา</td></tr>
                            <?php else: ?>
                                <?php foreach ($quotations as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td style="white-space:nowrap;"><?php echo h(date('d/m/Y H:i', strtotime((string) $q['created_at']))); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td><?php echo $q['quoted_price'] !== null ? h(number_format((float) $q['quoted_price'], 2)) . ' ฿' : '<span style="color:var(--koch-text-muted)">รอเสนอราคา</span>'; ?></td>
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
                            $activeQuotations = array_filter($quotations, fn($q) => in_array((string)$q['status'], ['processing', 'quoted', 'approved']));
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
                            <thead><tr><th>เลขที่</th><th>ประเภท</th><th>ราคา</th><th>วันที่เสร็จ</th><th>สถานะ</th></tr></thead>
                            <tbody>
                            <?php
                            $doneQuotations = array_filter($quotations, fn($q) => in_array((string)$q['status'], ['completed', 'rejected', 'cancelled']));
                            if ($doneQuotations === []): ?>
                                <tr class="empty-row"><td colspan="5">ยังไม่มีงานที่เสร็จสิ้น</td></tr>
                            <?php else: ?>
                                <?php foreach ($doneQuotations as $q): ?>
                                <tr>
                                    <td style="font-weight:600;"><?php echo h((string) $q['quotation_number']); ?></td>
                                    <td><?php echo h((string) $q['product_type']); ?></td>
                                    <td><?php echo $q['quoted_price'] !== null ? h(number_format((float) $q['quoted_price'], 2)) . ' ฿' : '-'; ?></td>
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
                            $ua = (string) $s['user_agent'];
                            $browser = 'Unknown Browser';
                            if (str_contains($ua, 'Chrome')) $browser = 'Chrome';
                            elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
                            elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
                            elseif (str_contains($ua, 'Edge')) $browser = 'Edge';
                            $os = 'Unknown OS';
                            if (str_contains($ua, 'Windows')) $os = 'Windows';
                            elseif (str_contains($ua, 'Mac')) $os = 'macOS';
                            elseif (str_contains($ua, 'Linux')) $os = 'Linux';
                            elseif (str_contains($ua, 'Android')) $os = 'Android';
                            elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
                        ?>
                        <div class="session-item">
                            <div class="session-icon <?php echo $isActive ? 'active' : 'inactive'; ?>">
                                <i class="fas fa-<?php echo $isActive ? 'laptop' : 'laptop'; ?>"></i>
                            </div>
                            <div class="session-info">
                                <h4><?php echo h($browser . ' on ' . $os); ?> <?php echo $isCurrent ? '<span style="color:var(--koch-primary);font-size:11px;">(เซสชั่นปัจจุบัน)</span>' : ''; ?></h4>
                                <p>IP: <?php echo h((string) $s['ip_address']); ?> &middot; <?php echo h(date('d/m/Y H:i', strtotime((string) $s['created_at']))); ?></p>
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
                            <thead><tr><th>วันที่</th><th>กิจกรรม</th><th>ตาราง</th><th>รหัส</th></tr></thead>
                            <tbody>
                            <?php if ($activities === []): ?>
                                <tr class="empty-row"><td colspan="4">ยังไม่มีกิจกรรม</td></tr>
                            <?php else: ?>
                                <?php foreach ($activities as $a): ?>
                                <tr>
                                    <td style="white-space:nowrap;font-size:13px;"><?php echo h(date('d/m/Y H:i', strtotime((string) $a['created_at']))); ?></td>
                                    <td><?php echo h(koch_action_label((string) $a['action'])); ?></td>
                                    <td style="font-size:13px;color:var(--koch-text-muted);"><?php echo h((string) ($a['table_name'] ?? '-')); ?></td>
                                    <td style="font-size:13px;"><?php echo h((string) ($a['record_id'] ?? '-')); ?></td>
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

            <div class="k-card">
                <div class="k-card-header"><h2><i class="fas fa-palette"></i> การตั้งค่าบัญชี</h2></div>
                <div class="k-card-body">
                    <div style="display:grid;gap:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:var(--koch-bg);border-radius:12px;">
                            <div>
                                <div style="font-weight:600;margin-bottom:2px;">การแจ้งเตือนทางอีเมล</div>
                                <div style="font-size:13px;color:var(--koch-text-muted);">รับอีเมลเมื่อมีอัปเดตสถานะใบเสนอราคา</div>
                            </div>
                            <label style="position:relative;width:48px;height:26px;display:inline-block;">
                                <input type="checkbox" checked style="opacity:0;width:0;height:0;">
                                <span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#16a34a;border-radius:26px;transition:.3s;"></span>
                            </label>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:var(--koch-bg);border-radius:12px;">
                            <div>
                                <div style="font-weight:600;margin-bottom:2px;">ภาษาที่แสดง</div>
                                <div style="font-size:13px;color:var(--koch-text-muted);">เลือกภาษาสำหรับการแสดงผล</div>
                            </div>
                            <select style="padding:8px 12px;border:1.5px solid var(--koch-border);border-radius:8px;font-size:14px;font-family:inherit;">
                                <option value="th">ไทย</option>
                                <option value="en">English</option>
                                <option value="zh">中文</option>
                                <option value="jp">日本語</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="k-card" style="border:1.5px solid #fee2e2;">
                <div class="k-card-header"><h2 style="color:#dc2626;"><i class="fas fa-exclamation-triangle"></i> โซนอันตราย</h2></div>
                <div class="k-card-body">
                    <p style="font-size:14px;color:var(--koch-text-muted);margin-bottom:16px;">การลบบัญชีจะไม่สามารถกู้คืนได้ ข้อมูลทั้งหมดจะถูกลบอย่างถาวร</p>
                    <button class="k-btn" style="background:#fee2e2;color:#dc2626;" disabled><i class="fas fa-trash"></i> ขอลบบัญชี (กรุณาติดต่อแอดมิน)</button>
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
    </script>
    <?php clear_old_input(); ?>
</body>
</html>
