<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/profile.php';

$currentUser = authenticated_user();
if ($currentUser === null) {
    redirect_to(project_url('tnb/main/Login.php'));
}

// Strict company validation - TNB users ONLY (except admins)
$userCompany = strtoupper((string) ($currentUser['company_code'] ?? ''));
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin'], true);

if ($userCompany !== 'TNB' && !$isAdmin) {
    // Not a TNB user and not admin - redirect to correct company page
    redirect_to(user_page_by_company($userCompany));
}

$successMessage = flash('success_message');
$errorMessage = flash('error_message');
try {
    $pdo = Database::connection();
    $profile = get_profile_summary($pdo, (int) $currentUser['id']);
    $activities = get_recent_activity_logs($pdo, (int) $currentUser['id'], 15);
    $serviceHistory = get_tnb_service_history($pdo, (int) $currentUser['id'], 20);
    $quotations = get_tnb_user_quotations($pdo, (int) $currentUser['id'], 20);
    $qStats = get_tnb_quotation_stats($pdo, (int) $currentUser['id']);
    $notifications = get_user_notifications($pdo, (int) $currentUser['id']);
    $unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
    $sessions = get_user_login_sessions($pdo, (int) $currentUser['id']);
} catch (Throwable $e) {
    $profile = [];
    $activities = [];
    $serviceHistory = [];
    $quotations = [];
    $qStats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    $notifications = [];
    $unreadCount = 0;
    $sessions = [];
}
$section = $_GET['section'] ?? 'dashboard';
if (!in_array($section, ['dashboard','profile','quotations','tracking','transport','notifications','sessions','settings'], true)) $section = 'dashboard';

// Auto-mark notifications as read when viewing notifications section
if ($section === 'notifications' && isset($pdo)) {
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0');
    $stmt->execute([':uid' => (int) $currentUser['id']]);
    $unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
}

// Quotation badge: only show when there are NEW pending quotations since last visit
$currPending = (int) $qStats['pending'];
$cookieParams = ['expires' => time() + 86400 * 90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax'];
$prevSeen = isset($_COOKIE['tnb_q_seen']) ? (int) $_COOKIE['tnb_q_seen'] : -1;
if ($prevSeen === -1) {
    setcookie('tnb_q_seen', (string) $currPending, $cookieParams);
    $_COOKIE['tnb_q_seen'] = (string) $currPending;
    $newQuotations = 0;
} elseif ($section === 'quotations') {
    setcookie('tnb_q_seen', (string) $currPending, $cookieParams);
    $_COOKIE['tnb_q_seen'] = (string) $currPending;
    $newQuotations = 0;
} else {
    $newQuotations = max(0, $currPending - $prevSeen);
}
$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$avatarUrl = !empty($profile['avatar_url']) ? h((string) $profile['avatar_url']) : '../img/company_logo/tnb_logo.webp';
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin'], true);

function tnb_status_badge(string $status): string {
    $m = ['pending'=>['#fff3e0','#e65100','รอดำเนินการ'],'processing'=>['#e3f2fd','#1565c0','กำลังดำเนินการ'],'quoted'=>['#f3e5f5','#7b1fa2','เสนอราคาแล้ว'],'approved'=>['#e8f5e9','#2e7d32','อนุมัติแล้ว'],'in_transit'=>['#e0f7fa','#00838f','กำลังขนส่ง'],'delivered'=>['#e8f5e9','#1b5e20','ส่งถึงแล้ว'],'completed'=>['#e0f2f1','#00695c','เสร็จสิ้น'],'rejected'=>['#fbe9e7','#bf360c','ปฏิเสธ'],'cancelled'=>['#efebe9','#4e342e','ยกเลิก']];
    $s = $m[$status] ?? ['#f5f5f5','#616161',$status];
    return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:'.$s[0].';color:'.$s[1].'">'.htmlspecialchars($s[2],ENT_QUOTES,'UTF-8').'</span>';
}
function tnb_action_label(string $a): string {
    $a = preg_replace('/^\[.*?\]\s*/', '', $a);
    $m = ['LOGIN_SUCCESS'=>'เข้าสู่ระบบสำเร็จ','LOGIN_FAILED'=>'เข้าสู่ระบบไม่สำเร็จ','REGISTER_SUCCESS'=>'ลงทะเบียนสำเร็จ','PROFILE_UPDATED'=>'แก้ไขโปรไฟล์','PASSWORD_CHANGED'=>'เปลี่ยนรหัสผ่าน','TNB_QUOTATION_CREATED'=>'ส่งใบเสนอราคาขนส่ง','KOCH_QUOTATION_CREATED'=>'ส่งใบเสนอราคา KOCH','AUTO_REGISTER_FROM_QUOTATION'=>'ลงทะเบียนอัตโนมัติ'];
    return $m[$a] ?? $a;
}
$titles = ['dashboard'=>'แดชบอร์ด','profile'=>'โปรไฟล์ของฉัน','quotations'=>'ใบเสนอราคา','tracking'=>'ติดตามการขนส่ง','transport'=>'ข้อมูลรถและเส้นทาง','notifications'=>'การแจ้งเตือน','sessions'=>'ประวัติเข้าใช้ระบบ','settings'=>'ตั้งค่าบัญชี'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Account | TNB Logistics</title>
<link rel="icon" type="image/png" href="../img/company_logo/tnb_logo.webp"/>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<style>
:root{--tp:#0d2d6b;--tp-d:#091f4d;--tp-l:#eff6ff;--ta:#1a73e8;--ta-l:#e8f0fe;--ts:#325662;--ts-d:#243f49;--bg:#f0f3f8;--card:#fff;--tx:#1e293b;--txm:#64748b;--bd:#e8ecf1;--ok:#059669;--wn:#d97706;--r:16px;--sh:0 1px 3px rgba(0,0,0,.04),0 4px 14px rgba(0,0,0,.06);--sw:272px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:auto}
body{font-family:'Inter','Sarabun',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--bg);color:var(--tx);line-height:1.6;-webkit-font-smoothing:antialiased}
::selection{background:var(--ta);color:#fff}

/* ===== SIDEBAR (Light InApp Style) ===== */
.user-sidebar{width:var(--sw);background:#fff;color:var(--tx);position:fixed;top:0;left:0;bottom:0;z-index:1000;display:flex;flex-direction:column;transition:transform .3s cubic-bezier(.4,0,.2,1);border-right:1px solid var(--bd);box-shadow:2px 0 8px rgba(0,0,0,.03)}
.sb-hd{padding:22px 20px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:12px}
.sb-hd img{width:40px;height:40px;border-radius:12px;background:var(--tp-l);padding:4px;object-fit:contain}
.sb-hd .brand{font-size:17px;font-weight:800;color:var(--tp);letter-spacing:-.2px}.sb-hd .brand small{display:block;font-size:11px;font-weight:500;color:var(--txm);opacity:1}

.sb-profile{padding:20px;text-align:center;border-bottom:1px solid var(--bd);background:linear-gradient(135deg,var(--tp-l) 0%,#f0f4ff 100%)}
.sb-avatar{width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--ta);margin:0 auto 10px;display:block;background:#f8fafc;box-shadow:0 4px 12px rgba(26,115,232,.15)}
.sb-profile h3{font-size:15px;font-weight:700;margin-bottom:4px;color:var(--tx)}
.sb-profile .role-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:600;background:var(--ta);color:#fff;margin-top:4px}

.sb-nav{flex:1;overflow-y:auto;padding:12px 10px}
.sb-nav a{display:flex;align-items:center;gap:11px;padding:10px 14px;color:var(--txm);text-decoration:none;font-size:13.5px;font-weight:500;transition:all .2s ease;border-radius:10px;margin-bottom:2px;border-left:none}
.sb-nav a:hover{background:#f8fafc;color:var(--tx)}
.sb-nav a.active{background:var(--ta-l);color:var(--ta);font-weight:600;box-shadow:inset 0 0 0 1px rgba(26,115,232,.12)}
.sb-nav a.active i{color:var(--ta)}
.sb-nav a i{width:20px;text-align:center;font-size:15px;color:#94a3b8;transition:color .2s}
.sb-nav a:hover i{color:var(--tx)}
.sb-nav .nb{margin-left:auto;background:var(--ta);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:8px;min-width:18px;text-align:center;line-height:1.4}
.sb-nav .dv{height:1px;background:var(--bd);margin:10px 8px}
.sb-nav .lb{padding:10px 14px 4px;font-size:10px;font-weight:700;text-transform:uppercase;color:#94a3b8;letter-spacing:1.2px}

.sb-ft{padding:14px 16px;border-top:1px solid var(--bd);background:#fafbfc}
.sb-ft a{display:flex;align-items:center;gap:9px;color:var(--txm);text-decoration:none;font-size:12.5px;padding:7px 8px;border-radius:8px;transition:all .2s}
.sb-ft a:hover{color:var(--ta);background:var(--ta-l)}
.sb-ft a i{font-size:13px;width:16px;text-align:center}

/* ===== MAIN CONTENT ===== */
.user-main{margin-left:var(--sw);min-height:100vh}
.topbar{background:var(--card);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,.05);position:sticky;top:0;z-index:100;border-bottom:1px solid var(--bd)}
.topbar h1{font-size:18px;font-weight:700;letter-spacing:-.2px}.topbar .bc{font-size:12px;color:var(--txm);margin-top:1px}
.topbar-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:all .2s;background:var(--ta);color:#fff;font-family:inherit;box-shadow:0 2px 8px rgba(26,115,232,.25)}
.topbar-btn:hover{background:var(--tp);transform:translateY(-1px);box-shadow:0 4px 12px rgba(13,45,107,.3)}

.mob-tog{display:none;background:none;border:none;font-size:20px;cursor:pointer;color:var(--tx);padding:8px;border-radius:8px;transition:background .2s}
.mob-tog:hover{background:#f1f5f9}
.mc{padding:24px 28px 48px}

/* ===== ALERT ===== */
.ta{position:fixed;top:22px;right:22px;z-index:9999;padding:14px 20px;border-radius:12px;font-weight:600;font-size:13px;display:flex;align-items:center;gap:10px;box-shadow:0 8px 30px rgba(0,0,0,.12);max-width:400px;animation:taSlide .4s cubic-bezier(.4,0,.2,1);backdrop-filter:blur(10px)}
.ta.ok{background:rgba(220,252,231,.95);color:#166534;border:1px solid #bbf7d0}.ta.er{background:rgba(254,226,226,.95);color:#991b1b;border:1px solid #fecaca}
.ta i{font-size:17px}
@keyframes taSlide{from{opacity:0;transform:translateY(-12px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}

/* ===== STAT CARDS (InApp Pastel Style) ===== */
.sg{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;margin-bottom:24px}
.sc{border-radius:var(--r);padding:22px 20px;position:relative;overflow:hidden;transition:transform .25s ease,box-shadow .25s ease;border:1px solid transparent}
.sc:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.08)}
.sc::before{display:none}

/* Pastel backgrounds */
.sc.nv{background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);border-color:#c7d2fe}
.sc.bl{background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);border-color:#bfdbfe}
.sc.gn{background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-color:#bbf7d0}
.sc.og{background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%);border-color:#fde68a}
.sc.tl{background:linear-gradient(135deg,#f0fdfa 0%,#ccfbf1 100%);border-color:#99f6e4}

.sc .si{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:19px;margin-bottom:16px}
.sc.nv .si{background:rgba(13,45,107,.1);color:var(--tp)}.sc.bl .si{background:rgba(26,115,232,.1);color:var(--ta)}.sc.gn .si{background:rgba(5,150,105,.1);color:var(--ok)}.sc.og .si{background:rgba(217,119,6,.1);color:var(--wn)}.sc.tl .si{background:rgba(13,148,136,.1);color:var(--ts)}
.sc .sn{font-size:30px;font-weight:800;line-height:1;margin-bottom:4px;color:var(--tx)}.sc .sl{font-size:13px;color:var(--txm);font-weight:500}

/* ===== CARD ===== */
.tc{background:var(--card);border-radius:var(--r);box-shadow:var(--sh);margin-bottom:20px;overflow:hidden;border:1px solid var(--bd);transition:box-shadow .2s}
.tc:hover{box-shadow:0 2px 6px rgba(0,0,0,.04),0 8px 20px rgba(0,0,0,.08)}
.tc-h{padding:18px 22px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;background:#fafbfd}
.tc-h h2{font-size:15px;font-weight:700;display:flex;align-items:center;gap:9px;letter-spacing:-.1px}.tc-h h2 i{color:var(--ta);font-size:16px;opacity:.85}
.tc-b{padding:22px}

/* ===== TABLE ===== */
.tw{overflow-x:auto}
.tt{width:100%;border-collapse:collapse}
.tt th{text-align:left;padding:11px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;background:#f8fafc;border-bottom:1px solid var(--bd)}
.tt td{padding:13px 16px;font-size:13.5px;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:var(--tx)}
.tt tr:last-child td{border-bottom:none}
.tt tr{transition:background .15s}
.tt tr:hover td{background:#f8fafc}
.tt .empty td{text-align:center;color:var(--txm);padding:48px 16px;font-size:14px}

/* ===== FORM ===== */
.fg{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.ff{display:flex;flex-direction:column;gap:6px}.ff.full{grid-column:1/-1}
.ff label{font-size:12px;font-weight:600;color:var(--tx);text-transform:uppercase;letter-spacing:.3px}
.ff input,.ff select,.ff textarea{padding:11px 14px;border:1.5px solid var(--bd);border-radius:10px;font-size:14px;font-family:inherit;transition:all .2s ease;background:#fff;color:var(--tx)}
.ff input:focus,.ff select:focus{outline:none;border-color:var(--ta);box-shadow:0 0 0 4px rgba(26,115,232,.08)}
.ff input[readonly]{background:#f8fafc;color:var(--txm);cursor:not-allowed;border-style:dashed}

/* ===== BUTTONS ===== */
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:10px;font-size:13.5px;font-weight:600;font-family:inherit;border:none;cursor:pointer;text-decoration:none;transition:all .2s ease}
.btn.p{background:var(--ta);color:#fff;box-shadow:0 2px 8px rgba(26,115,232,.2)}.btn.p:hover{background:var(--tp);transform:translateY(-1px);box-shadow:0 4px 14px rgba(13,45,107,.3)}
.btn.s{background:var(--ts);color:#fff}.btn.s:hover{background:var(--ts-d);transform:translateY(-1px)}
.btn.o{background:transparent;border:1.5px solid var(--bd);color:var(--tx)}.btn.o:hover{border-color:var(--ta);color:var(--ta);background:var(--ta-l)}
.ba{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}

/* ===== NOTIFICATION ITEM ===== */
.ni{display:flex;gap:14px;padding:14px 0;border-bottom:1px solid #f1f5f9;transition:background .15s}.ni:last-child{border-bottom:none}
.nic{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.nic.info{background:#eff6ff;color:#2563eb}.nic.success{background:#f0fdf4;color:#059669}.nic.warning{background:#fffbeb;color:#d97706}.nic.error{background:#fef2f2;color:#dc2626}
.nix{flex:1}.nix h4{font-size:13.5px;font-weight:600;margin-bottom:2px}.nix p{font-size:12.5px;color:var(--txm);margin:0;line-height:1.5}
.nt{font-size:11px;color:#94a3b8;white-space:nowrap;font-weight:500}
.nu{background:var(--tp-l);border-radius:12px;margin:0 -12px;padding:14px 12px!important;border-left:3px solid var(--ta)}

/* ===== SESSION ITEM ===== */
.sei{display:flex;align-items:center;gap:14px;padding:14px 0;border-bottom:1px solid #f1f5f9}.sei:last-child{border-bottom:none}
.sec{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.sec.a{background:#f0fdf4;color:#059669}.sec.i{background:#f8fafc;color:#94a3b8}
.seinf{flex:1}.seinf h4{font-size:13.5px;font-weight:600;margin-bottom:2px}.seinf p{font-size:12px;color:var(--txm);margin:0}
.sest{font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px}
.sest.a{background:#dcfce7;color:#166534}.sest.e{background:#f1f5f9;color:#64748b}

/* ===== TWO COLUMN GRID ===== */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px}

/* ===== RESPONSIVE ===== */
.sidebar-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,.4);z-index:999;backdrop-filter:blur(2px)}
@media(max-width:1024px){.user-sidebar{transform:translateX(-100%)}.user-sidebar.open{transform:translateX(0)}.sidebar-overlay.show{display:block}.user-main{margin-left:0}.mob-tog{display:block}.mc{padding:20px 16px 40px}.topbar{padding:12px 16px}.sg{grid-template-columns:repeat(2,1fr)}.fg{grid-template-columns:1fr}.two-col{grid-template-columns:1fr}}
@media(max-width:600px){.sg{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="user-sidebar" id="userSidebar">
    <div class="sb-hd"><img src="../img/company_logo/tnb_logo.webp" alt="TNB"><div class="brand">TNB<small>Logistics Co., Ltd.</small></div></div>
    <div class="sb-profile">
        <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="sb-avatar">
        <h3><?php echo h($fullName ?: $profile['username'] ?? 'User'); ?></h3>
        <span class="role-badge"><?php echo h(ucfirst((string) ($profile['role'] ?? 'user'))); ?></span>
    </div>
    <nav class="sb-nav">
        <div class="lb">เมนูหลัก</div>
        <a href="?section=dashboard" class="<?php echo $section==='dashboard'?'active':''; ?>"><i class="fas fa-chart-pie"></i> แดชบอร์ด</a>
        <a href="?section=profile" class="<?php echo $section==='profile'?'active':''; ?>"><i class="fas fa-user-circle"></i> โปรไฟล์ของฉัน</a>
        <a href="?section=quotations" class="<?php echo $section==='quotations'?'active':''; ?>"><i class="fas fa-file-invoice"></i> ใบเสนอราคา<?php if($newQuotations>0):?><span class="nb"><?php echo $newQuotations;?></span><?php endif;?></a>
        <a href="?section=tracking" class="<?php echo $section==='tracking'?'active':''; ?>"><i class="fas fa-shipping-fast"></i> ติดตามการขนส่ง</a>
        <a href="?section=transport" class="<?php echo $section==='transport'?'active':''; ?>"><i class="fas fa-truck"></i> ข้อมูลรถและเส้นทาง</a>
        <div class="dv"></div>
        <div class="lb">การแจ้งเตือน</div>
        <a href="?section=notifications" class="<?php echo $section==='notifications'?'active':''; ?>"><i class="fas fa-bell"></i> การแจ้งเตือน<?php if($unreadCount>0):?><span class="nb"><?php echo $unreadCount;?></span><?php endif;?></a>
        <a href="?section=sessions" class="<?php echo $section==='sessions'?'active':''; ?>"><i class="fas fa-shield-alt"></i> ประวัติเข้าใช้ระบบ</a>
        <div class="dv"></div>
        <div class="lb">ตั้งค่า</div>
        <a href="?section=settings" class="<?php echo $section==='settings'?'active':''; ?>"><i class="fas fa-cog"></i> ตั้งค่าบัญชี</a>
    </nav>
    <div class="sb-ft">
        <a href="../main/index.php"><i class="fas fa-home"></i> กลับหน้าเว็บไซต์</a>
        <?php if($isAdmin):?><a href="<?php echo h(project_url('admin/dashboard.php'));?>"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a><?php endif;?>
        <a href="<?php echo h(project_url('admin/api/auth/logout.php'));?>?company=tnb"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
    </div>
</aside>

<div class="user-main">
    <div class="topbar">
        <div><button class="mob-tog" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button><h1><?php echo $titles[$section]??'แดชบอร์ด';?></h1><div class="bc">TNB Logistics &rsaquo; <?php echo $titles[$section]??'แดชบอร์ด';?></div></div>
        <div><?php if ($section === 'quotations'): ?><a href="<?php echo h(project_url('tnb/main/quotation.php')); ?>" class="topbar-btn"><i class="fas fa-plus"></i> ขอใบเสนอราคา</a><?php endif; ?></div>
    </div>
    <div class="mc">
        <?php if($successMessage):?><div class="ta ok" id="flashAlert"><i class="fas fa-check-circle"></i> <?php echo h((string)$successMessage);?></div><?php endif;?>
        <?php if($errorMessage):?><div class="ta er" id="flashAlert"><i class="fas fa-exclamation-circle"></i> <?php echo h((string)$errorMessage);?></div><?php endif;?>

<?php if($section==='dashboard'): ?>
<div class="sg">
    <div class="sc nv"><div class="si"><i class="fas fa-file-invoice"></i></div><div class="sn"><?php echo (int)$qStats['total'];?></div><div class="sl">คำขอทั้งหมด</div></div>
    <div class="sc og"><div class="si"><i class="fas fa-clock"></i></div><div class="sn"><?php echo (int)$qStats['pending'];?></div><div class="sl">รอดำเนินการ</div></div>
    <div class="sc tl"><div class="si"><i class="fas fa-shipping-fast"></i></div><div class="sn"><?php echo (int)$qStats['in_transit'];?></div><div class="sl">กำลังขนส่ง</div></div>
    <div class="sc gn"><div class="si"><i class="fas fa-check-circle"></i></div><div class="sn"><?php echo (int)$qStats['completed'];?></div><div class="sl">เสร็จสิ้นแล้ว</div></div>
</div>
<div class="two-col">
    <div class="tc"><div class="tc-h"><h2><i class="fas fa-history"></i> กิจกรรมล่าสุด</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>วันที่</th><th>กิจกรรม</th></tr></thead><tbody>
    <?php if($activities===[]):?><tr class="empty"><td colspan="2">ยังไม่มีกิจกรรม</td></tr>
    <?php else: foreach(array_slice($activities,0,8) as $a):?><tr><td style="white-space:nowrap;font-size:12px;color:var(--txm)"><?php echo h((string)$a['created_at']);?></td><td><?php echo h(tnb_action_label((string)$a['action']));?></td></tr><?php endforeach; endif;?>
    </tbody></table></div></div></div>
    <div class="tc"><div class="tc-h"><h2><i class="fas fa-bell"></i> การแจ้งเตือนล่าสุด</h2><?php if($unreadCount>0):?><span style="background:var(--ta);color:#fff;font-size:12px;font-weight:700;padding:3px 10px;border-radius:12px"><?php echo $unreadCount;?> ใหม่</span><?php endif;?></div><div class="tc-b">
    <?php if($notifications===[]):?><p style="text-align:center;color:var(--txm);padding:20px 0">ยังไม่มีการแจ้งเตือน</p>
    <?php else: foreach(array_slice($notifications,0,5) as $n):?>
    <div class="ni <?php echo !$n['is_read']?'nu':'';?>"><div class="nic <?php echo h((string)($n['type']??'info'));?>"><i class="fas fa-<?php echo $n['type']==='success'?'check':($n['type']==='warning'?'exclamation-triangle':($n['type']==='error'?'times':'info'));?>"></i></div><div class="nix"><h4><?php echo h((string)$n['title']);?></h4><p><?php echo h((string)$n['message']);?></p></div><div class="nt"><?php echo h(date('d/m H:i',strtotime((string)$n['created_at'])));?></div></div>
    <?php endforeach; endif;?>
    </div></div>
</div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-file-invoice"></i> คำขอบริการล่าสุด</h2><a href="?section=quotations" class="btn o" style="padding:6px 14px;font-size:13px">ดูทั้งหมด</a></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>วันที่</th><th>ประเภท</th><th>เส้นทาง</th><th>สถานะ</th></tr></thead><tbody>
<?php if($quotations===[]):?><tr class="empty"><td colspan="5">ยังไม่มีคำขอบริการ</td></tr>
<?php else: foreach(array_slice($quotations,0,5) as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td style="white-space:nowrap"><?php echo h(date('d/m/Y',strtotime((string)$q['created_at'])));?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='profile'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-user-edit"></i> แก้ไขข้อมูลส่วนตัว</h2></div><div class="tc-b">
<form action="../../admin/api/profile/update.php" method="POST"><input type="hidden" name="_csrf" value="<?php echo h(csrf_token());?>"><input type="hidden" name="company" value="tnb">
<div class="fg">
    <div class="ff"><label>Username</label><input type="text" value="<?php echo h((string)($profile['username']??''));?>" readonly></div>
    <div class="ff"><label>บริษัท</label><input type="text" value="<?php echo h((string)($profile['company_name']??'TNB'));?>" readonly></div>
    <div class="ff"><label for="first_name">ชื่อ</label><input type="text" id="first_name" name="first_name" value="<?php echo h(old_input('first_name',(string)($profile['first_name']??'')));?>" required></div>
    <div class="ff"><label for="last_name">นามสกุล</label><input type="text" id="last_name" name="last_name" value="<?php echo h(old_input('last_name',(string)($profile['last_name']??'')));?>" required></div>
    <div class="ff"><label for="nick_name">ชื่อเล่น</label><input type="text" id="nick_name" name="nick_name" value="<?php echo h(old_input('nick_name',(string)($profile['nick_name']??'')));?>"></div>
    <div class="ff"><label for="phone">เบอร์โทรศัพท์</label><input type="tel" id="phone" name="phone" value="<?php echo h(old_input('phone',(string)($profile['phone']??'')));?>" required></div>
    <div class="ff full"><label for="email">อีเมล</label><input type="email" id="email" name="email" value="<?php echo h(old_input('email',(string)($profile['email']??'')));?>" required></div>
    <div class="ff"><label for="department">แผนก</label><input type="text" id="department" name="department" value="<?php echo h(old_input('department',(string)($profile['department']??'')));?>"></div>
    <div class="ff"><label for="position">ตำแหน่ง</label><input type="text" id="position" name="position" value="<?php echo h(old_input('position',(string)($profile['position']??'')));?>"></div>
</div>
<div class="ba"><button type="submit" class="btn p"><i class="fas fa-save"></i> บันทึกข้อมูล</button></div>
</form></div></div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-id-card"></i> ข้อมูลบัญชี</h2></div><div class="tc-b">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px">
    <div style="padding:16px;background:var(--bg);border-radius:12px"><div style="font-size:12px;color:var(--txm);margin-bottom:4px">สถานะบัญชี</div><div style="font-weight:700;color:var(--ok)">Active</div></div>
    <div style="padding:16px;background:var(--bg);border-radius:12px"><div style="font-size:12px;color:var(--txm);margin-bottom:4px">สมาชิกตั้งแต่</div><div style="font-weight:600"><?php echo h(date('d/m/Y',strtotime((string)($profile['created_at']??'now'))));?></div></div>
    <div style="padding:16px;background:var(--bg);border-radius:12px"><div style="font-size:12px;color:var(--txm);margin-bottom:4px">เข้าใช้งานล่าสุด</div><div style="font-weight:600"><?php echo $profile['last_login']?h(date('d/m/Y H:i',strtotime((string)$profile['last_login']))):'ไม่มีข้อมูล';?></div></div>
    <div style="padding:16px;background:var(--bg);border-radius:12px"><div style="font-size:12px;color:var(--txm);margin-bottom:4px">บทบาท</div><div style="font-weight:600"><?php echo h(ucfirst((string)($profile['role']??'user')));?></div></div>
</div></div></div>

<?php elseif($section==='quotations'): ?>
<div class="sg">
    <div class="sc nv"><div class="si"><i class="fas fa-file-alt"></i></div><div class="sn"><?php echo (int)$qStats['total'];?></div><div class="sl">ทั้งหมด</div></div>
    <div class="sc og"><div class="si"><i class="fas fa-hourglass-half"></i></div><div class="sn"><?php echo (int)$qStats['pending'];?></div><div class="sl">รอดำเนินการ</div></div>
    <div class="sc tl"><div class="si"><i class="fas fa-shipping-fast"></i></div><div class="sn"><?php echo (int)$qStats['in_transit'];?></div><div class="sl">กำลังขนส่ง</div></div>
    <div class="sc gn"><div class="si"><i class="fas fa-flag-checkered"></i></div><div class="sn"><?php echo (int)$qStats['completed'];?></div><div class="sl">เสร็จสิ้น</div></div>
</div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-file-invoice"></i> คำขอบริการทั้งหมด</h2><a href="../main/quotation.php" class="btn p" style="padding:8px 16px;font-size:13px"><i class="fas fa-plus"></i> ขอใบเสนอราคา</a></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>วันที่</th><th>ประเภท</th><th>เส้นทาง</th><th>Tracking</th><th>สถานะ</th></tr></thead><tbody>
<?php if($quotations===[]):?><tr class="empty"><td colspan="6">ยังไม่มีคำขอบริการ</td></tr>
<?php else: foreach($quotations as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td style="white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$q['created_at'])));?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td style="font-size:13px"><?php echo h((string)($q['tracking_number']??'-'));?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='tracking'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-shipping-fast"></i> ติดตามสถานะการขนส่ง</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>ประเภท</th><th>เส้นทาง</th><th>Tracking</th><th>สถานะ</th><th>ขั้นตอน</th></tr></thead><tbody>
<?php $active=array_filter($quotations,fn($q)=>in_array((string)$q['status'],['pending','processing','quoted','approved','in_transit']));
if($active===[]):?><tr class="empty"><td colspan="6">ไม่มีงานขนส่งที่กำลังดำเนินการ</td></tr>
<?php else: foreach($active as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td style="font-size:13px"><?php echo h((string)($q['tracking_number']??'-'));?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td><td>
<?php $steps=['pending'=>1,'processing'=>2,'quoted'=>3,'approved'=>4,'in_transit'=>5,'delivered'=>6,'completed'=>7];$c=$steps[$q['status']]??1;$t=7;$p=($c/$t)*100;?>
<div style="display:flex;align-items:center;gap:8px"><div style="flex:1;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden"><div style="height:100%;width:<?php echo $p;?>%;background:var(--ta);border-radius:3px"></div></div><span style="font-size:12px;font-weight:600;color:var(--txm)"><?php echo $c;?>/<?php echo $t;?></span></div>
</td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-check-circle"></i> งานขนส่งที่เสร็จสิ้น</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>ประเภท</th><th>เส้นทาง</th><th>สถานะ</th></tr></thead><tbody>
<?php $done=array_filter($quotations,fn($q)=>in_array((string)$q['status'],['delivered','completed','rejected','cancelled']));
if($done===[]):?><tr class="empty"><td colspan="4">ยังไม่มีงานที่เสร็จสิ้น</td></tr>
<?php else: foreach($done as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='transport'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-truck"></i> ข้อมูลรถบรรทุก</h2></div><div class="tc-b">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px">
<?php
$truckTypes = $pdo->query("SELECT * FROM truck_cards WHERE is_active = 1 ORDER BY display_order")->fetchAll();
if($truckTypes===[]): ?>
<p style="text-align:center;color:var(--txm);padding:32px 0">ไม่มีข้อมูลรถบรรทุกในขณะนี้</p>
<?php else: foreach($truckTypes as $truck): ?>
<div style="border:1.5px solid var(--bd);border-radius:14px;padding:20px;transition:border-color .2s">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
        <div style="width:48px;height:48px;border-radius:12px;background:#e0e7ff;color:var(--tp);display:flex;align-items:center;justify-content:center;font-size:20px"><i class="fas fa-truck-moving"></i></div>
        <div><h3 style="font-size:16px;font-weight:700;margin-bottom:2px"><?php echo h((string)$truck['name']);?></h3><p style="font-size:13px;color:var(--txm);margin:0"><?php echo h((string)($truck['description']??''));?></p></div>
    </div>
    <?php if(!empty($truck['capacity'])):?><div style="font-size:13px;padding:8px 0;border-top:1px solid var(--bd)"><strong>น้ำหนักบรรทุก:</strong> <?php echo h((string)$truck['capacity']);?></div><?php endif;?>
    <?php if(!empty($truck['dimensions'])):?><div style="font-size:13px;padding:8px 0;border-top:1px solid var(--bd)"><strong>ขนาด:</strong> <?php echo h((string)$truck['dimensions']);?></div><?php endif;?>
</div>
<?php endforeach; endif; ?>
</div></div></div>

<div class="tc"><div class="tc-h"><h2><i class="fas fa-route"></i> เส้นทางขนส่งที่ใช้บริการ</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>เส้นทาง</th><th>ประเภทรถ</th><th>วันที่</th><th>สถานะ</th></tr></thead><tbody>
<?php if($serviceHistory===[]):?><tr class="empty"><td colspan="5">ยังไม่มีประวัติเส้นทาง</td></tr>
<?php else: foreach(array_slice($serviceHistory,0,10) as $s):?><tr><td style="font-weight:600"><?php echo h((string)$s['request_number']);?></td><td><?php echo h((string)($s['route']??'-'));?></td><td><?php echo h((string)($s['service_type']??'-'));?></td><td style="white-space:nowrap"><?php echo h(date('d/m/Y',strtotime((string)$s['created_at'])));?></td><td><?php echo tnb_status_badge((string)$s['status']);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='notifications'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-bell"></i> การแจ้งเตือนทั้งหมด</h2><?php if($unreadCount>0):?><span style="background:var(--ta);color:#fff;font-size:12px;font-weight:700;padding:4px 12px;border-radius:12px"><?php echo $unreadCount;?> ยังไม่อ่าน</span><?php endif;?></div><div class="tc-b">
<?php if($notifications===[]):?>
<div style="text-align:center;padding:48px 20px;color:var(--txm)"><i class="fas fa-bell-slash" style="font-size:48px;margin-bottom:16px;opacity:.3;display:block"></i><p style="font-size:16px;font-weight:600">ยังไม่มีการแจ้งเตือน</p><p style="font-size:13px">เมื่อมีอัปเดตจากคำขอบริการของคุณ จะแสดงที่นี่</p></div>
<?php else: foreach($notifications as $n):?>
<div class="ni <?php echo !$n['is_read']?'nu':'';?>"><div class="nic <?php echo h((string)($n['type']??'info'));?>"><i class="fas fa-<?php echo $n['type']==='success'?'check':($n['type']==='warning'?'exclamation-triangle':($n['type']==='error'?'times':'info'));?>"></i></div><div class="nix"><h4><?php echo h((string)$n['title']);?></h4><p><?php echo h((string)$n['message']);?></p></div><div class="nt"><?php echo h(date('d/m/Y H:i',strtotime((string)$n['created_at'])));?></div></div>
<?php endforeach; endif;?>
</div></div>

<?php elseif($section==='sessions'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-shield-alt"></i> ประวัติการเข้าใช้ระบบ</h2></div><div class="tc-b">
<?php if($sessions===[]):?><p style="text-align:center;color:var(--txm);padding:32px 0">ไม่มีข้อมูลเซสชั่น</p>
<?php else: foreach($sessions as $s): ?><?php
$isActive=(int)$s['is_active']===1&&strtotime((string)$s['expires_at'])>time();
$isCurrent=isset($currentUser['session_token'])&&$s['session_token']===$currentUser['session_token'];
?><div class="sei"><div class="seinf"><h4><?php echo h(date('d/m/Y H:i',strtotime((string)$s['created_at']))); ?> <?php echo $isCurrent?'<span style="color:var(--ta);font-size:11px">(เซสชั่นปัจจุบัน)</span>':''; ?></h4></div><span class="sest <?php echo $isActive?'a':'e'; ?>"><?php echo $isActive?'Active':'Expired'; ?></span></div><?php endforeach; endif;?>
</div></div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-history"></i> ประวัติกิจกรรมทั้งหมด</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>วันที่</th><th>กิจกรรม</th><th>ตาราง</th></tr></thead><tbody>
<?php if($activities===[]):?><tr class="empty"><td colspan="3">ยังไม่มีกิจกรรม</td></tr>
<?php else: foreach($activities as $a):?><tr><td style="white-space:nowrap;font-size:13px"><?php echo h(date('d/m/Y H:i',strtotime((string)$a['created_at'])));?></td><td><?php echo h(tnb_action_label((string)$a['action']));?></td><td style="font-size:13px;color:var(--txm)"><?php echo h((string)($a['table_name']??'-'));?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='settings'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-lock"></i> เปลี่ยนรหัสผ่าน</h2></div><div class="tc-b">
<form action="../../admin/api/profile/change-password.php" method="POST"><input type="hidden" name="_csrf" value="<?php echo h(csrf_token());?>"><input type="hidden" name="company" value="tnb">
<div class="fg">
    <div class="ff full"><label for="current_password">รหัสผ่านปัจจุบัน</label><input type="password" id="current_password" name="current_password" required></div>
    <div class="ff"><label for="new_password">รหัสผ่านใหม่</label><input type="password" id="new_password" name="new_password" required></div>
    <div class="ff"><label for="confirm_new_password">ยืนยันรหัสผ่านใหม่</label><input type="password" id="confirm_new_password" name="confirm_new_password" required></div>
</div>
<p style="font-size:12px;color:var(--txm);margin-top:12px">โปรดใช้รหัสผ่านที่มีความปลอดภัยสูง</p>
<div class="ba"><button type="submit" class="btn p"><i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน</button></div>
</form></div></div>

<?php endif; ?>

    </div>
</div>
<script>
function toggleSidebar(){document.getElementById('userSidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show')}
document.addEventListener('DOMContentLoaded',function(){
    var a=document.getElementById('flashAlert');
    if(a){setTimeout(function(){a.style.transition='opacity .5s ease';a.style.opacity='0';setTimeout(function(){a.remove()},500)},5000);}
    // Save scroll position before navigating
    document.querySelectorAll('.sb-nav a[href^="?section"]').forEach(function(l){
        l.addEventListener('click',function(){
            sessionStorage.setItem('tnbSidebarScroll',document.querySelector('.sb-nav').scrollTop);
            sessionStorage.setItem('tnbMainScroll',window.scrollY);
        });
    });
    // Restore scroll position after page load
    var ss=sessionStorage.getItem('tnbSidebarScroll');
    var ms=sessionStorage.getItem('tnbMainScroll');
    if(ss){document.querySelector('.sb-nav').scrollTop=parseInt(ss);}
    if(ms){window.scrollTo(0,parseInt(ms));}
});
</script>
<?php clear_old_input(); ?>
</body>
</html>
