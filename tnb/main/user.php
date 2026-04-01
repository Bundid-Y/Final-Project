<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/profile.php';

$currentUser = authenticated_user();
if ($currentUser === null) {
    redirect_to(project_url('tnb/main/Login.php'));
}

// Strict company validation - TNB users ONLY (except admins)
$userCompany = strtoupper((string) ($currentUser['company_code'] ?? ''));
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true);

if ($userCompany !== 'TNB' && !$isAdmin) {
    // Not a TNB user and not admin - redirect to correct company page
    redirect_to(user_page_by_company($userCompany));
}

$pdo = Database::connection();
$profile = get_profile_summary($pdo, (int) $currentUser['id']);
$activities = get_recent_activity_logs($pdo, (int) $currentUser['id'], 15);
$serviceHistory = get_tnb_service_history($pdo, (int) $currentUser['id'], 20);
$quotations = get_tnb_user_quotations($pdo, (int) $currentUser['id'], 20);
$qStats = get_tnb_quotation_stats($pdo, (int) $currentUser['id']);
$notifications = get_user_notifications($pdo, (int) $currentUser['id']);
$unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
$sessions = get_user_login_sessions($pdo, (int) $currentUser['id']);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
$section = $_GET['section'] ?? 'dashboard';
if (!in_array($section, ['dashboard','profile','quotations','tracking','transport','notifications','sessions','settings'], true)) $section = 'dashboard';

// Auto-mark notifications as read when viewing notifications or quotations sections
if ($section === 'notifications' || $section === 'quotations') {
    // Force mark ALL unread notifications as read for this user (regardless of count)
    $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0');
    $stmt->execute([':uid' => (int) $currentUser['id']]);
    
    // Force refresh unread count from database to ensure it's 0
    $unreadCount = get_unread_notification_count($pdo, (int) $currentUser['id']);
    
    // Mark that user has viewed quotations section to hide pending badge
    if ($section === 'quotations') {
        $_SESSION['tnb_viewed_quotations'] = true;
    }
}

// Clear viewed flag when there are no more pending quotations (real data update)
if ((int)$qStats['pending'] === 0) {
    unset($_SESSION['tnb_viewed_quotations']);
}
$fullName = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
$avatarUrl = !empty($profile['avatar_url']) ? h((string) $profile['avatar_url']) : '../img/company_logo/tnb_logo.webp';
$isAdmin = in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true);

function tnb_status_badge(string $status): string {
    $m = ['pending'=>['#fff3e0','#e65100','รอดำเนินการ'],'processing'=>['#e3f2fd','#1565c0','กำลังดำเนินการ'],'quoted'=>['#f3e5f5','#7b1fa2','เสนอราคาแล้ว'],'approved'=>['#e8f5e9','#2e7d32','อนุมัติแล้ว'],'in_transit'=>['#e0f7fa','#00838f','กำลังขนส่ง'],'delivered'=>['#e8f5e9','#1b5e20','ส่งถึงแล้ว'],'completed'=>['#e0f2f1','#00695c','เสร็จสิ้น'],'rejected'=>['#fbe9e7','#bf360c','ปฏิเสธ'],'cancelled'=>['#efebe9','#4e342e','ยกเลิก']];
    $s = $m[$status] ?? ['#f5f5f5','#616161',$status];
    return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:'.$s[0].';color:'.$s[1].'">'.htmlspecialchars($s[2],ENT_QUOTES,'UTF-8').'</span>';
}
function tnb_action_label(string $a): string {
    $m = ['LOGIN_SUCCESS'=>'เข้าสู่ระบบสำเร็จ','LOGIN_FAILED'=>'เข้าสู่ระบบไม่สำเร็จ','REGISTER_SUCCESS'=>'ลงทะเบียนสำเร็จ','PROFILE_UPDATED'=>'แก้ไขโปรไฟล์','PASSWORD_CHANGED'=>'เปลี่ยนรหัสผ่าน','TNB_QUOTATION_CREATED'=>'ส่งใบขอบริการขนส่ง','KOCH_QUOTATION_CREATED'=>'ส่งใบเสนอราคา KOCH','AUTO_REGISTER_FROM_QUOTATION'=>'ลงทะเบียนอัตโนมัติ'];
    return $m[$a] ?? $a;
}
$titles = ['dashboard'=>'แดชบอร์ด','profile'=>'โปรไฟล์ของฉัน','quotations'=>'ใบขอบริการขนส่ง','tracking'=>'ติดตามการขนส่ง','transport'=>'ข้อมูลรถและเส้นทาง','notifications'=>'การแจ้งเตือน','sessions'=>'ประวัติเข้าใช้ระบบ','settings'=>'ตั้งค่าบัญชี'];
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
:root{--tp:#0d2d6b;--tp-d:#091f4d;--ta:#1a73e8;--ts:#325662;--ts-d:#243f49;--bg:#f0f4f8;--card:#fff;--tx:#1a2332;--txm:#64748b;--bd:#e2e8f0;--ok:#16a34a;--wn:#ea580c;--r:16px;--sh:0 1px 3px rgba(0,0,0,.06),0 6px 16px rgba(0,0,0,.04);--sw:280px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sarabun','Inter',sans-serif;background:var(--bg);color:var(--tx);line-height:1.6}
.user-sidebar{width:var(--sw);background:linear-gradient(180deg,var(--tp),var(--tp-d));color:#fff;position:fixed;top:0;left:0;bottom:0;z-index:1000;display:flex;flex-direction:column;transition:transform .3s}
.sb-hd{padding:28px 24px 20px;border-bottom:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:14px}
.sb-hd img{width:44px;height:44px;border-radius:10px;background:#fff;padding:4px;object-fit:contain}
.sb-hd .brand{font-size:18px;font-weight:700}.sb-hd .brand small{display:block;font-size:11px;font-weight:400;opacity:.7}
.sb-profile{padding:24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.1)}
.sb-avatar{width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);margin:0 auto 10px;display:block;background:rgba(255,255,255,.15)}
.sb-profile h3{font-size:16px;font-weight:600;margin-bottom:2px}
.sb-profile .role-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:600;background:var(--ta);color:#fff;margin-top:6px}
.sb-nav{flex:1;overflow-y:auto;padding:16px 0}
.sb-nav a{display:flex;align-items:center;gap:12px;padding:12px 24px;color:rgba(255,255,255,.75);text-decoration:none;font-size:14px;font-weight:500;transition:all .2s;border-left:3px solid transparent}
.sb-nav a:hover{background:rgba(255,255,255,.08);color:#fff}
.sb-nav a.active{background:rgba(255,255,255,.12);color:#fff;border-left-color:var(--ta);font-weight:600}
.sb-nav a i{width:20px;text-align:center;font-size:15px}
.sb-nav .nb{margin-left:auto;background:var(--ta);color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;min-width:20px;text-align:center}
.sb-nav .dv{height:1px;background:rgba(255,255,255,.1);margin:12px 24px}
.sb-nav .lb{padding:8px 24px 4px;font-size:11px;font-weight:600;text-transform:uppercase;color:rgba(255,255,255,.4);letter-spacing:1px}
.sb-ft{padding:16px 24px;border-top:1px solid rgba(255,255,255,.1)}
.sb-ft a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;padding:8px 0;transition:color .2s}
.sb-ft a:hover{color:#fff}
.user-main{margin-left:var(--sw);min-height:100vh}
.topbar{background:var(--card);padding:16px 32px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,.04);position:sticky;top:0;z-index:100}
.topbar h1{font-size:20px;font-weight:700}.topbar .bc{font-size:13px;color:var(--txm)}
.topbar-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:all .2s;background:var(--ta);color:#fff}
.topbar-btn:hover{background:var(--tp)}
.mob-tog{display:none;background:none;border:none;font-size:22px;cursor:pointer;color:var(--tx);padding:8px}
.mc{padding:28px 32px 48px}
.ta{padding:14px 20px;border-radius:var(--r);margin-bottom:20px;font-weight:500;font-size:14px;display:flex;align-items:center;gap:10px}
.ta.ok{background:#dcfce7;color:#166534}.ta.er{background:#fee2e2;color:#991b1b}
.sg{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px}
.sc{background:var(--card);border-radius:var(--r);padding:22px;box-shadow:var(--sh);position:relative;overflow:hidden}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:4px}
.sc.nv::before{background:var(--tp)}.sc.bl::before{background:var(--ta)}.sc.gn::before{background:var(--ok)}.sc.og::before{background:var(--wn)}.sc.tl::before{background:var(--ts)}
.sc .si{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:14px}
.sc.nv .si{background:#e0e7ff;color:var(--tp)}.sc.bl .si{background:#dbeafe;color:var(--ta)}.sc.gn .si{background:#dcfce7;color:var(--ok)}.sc.og .si{background:#fff7ed;color:var(--wn)}.sc.tl .si{background:#e0f2f1;color:var(--ts)}
.sc .sn{font-size:28px;font-weight:800;line-height:1;margin-bottom:4px}.sc .sl{font-size:13px;color:var(--txm);font-weight:500}
.tc{background:var(--card);border-radius:var(--r);box-shadow:var(--sh);margin-bottom:24px;overflow:hidden}
.tc-h{padding:20px 24px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between}
.tc-h h2{font-size:17px;font-weight:700;display:flex;align-items:center;gap:10px}.tc-h h2 i{color:var(--ta);font-size:18px}
.tc-b{padding:24px}
.tw{overflow-x:auto}
.tt{width:100%;border-collapse:collapse}
.tt th{text-align:left;padding:12px 16px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--txm);background:var(--bg);border-bottom:1px solid var(--bd)}
.tt td{padding:14px 16px;font-size:14px;border-bottom:1px solid var(--bd);vertical-align:middle}
.tt tr:last-child td{border-bottom:none}.tt tr:hover td{background:#f8fafc}
.tt .empty td{text-align:center;color:var(--txm);padding:40px 16px}
.fg{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.ff{display:flex;flex-direction:column;gap:6px}.ff.full{grid-column:1/-1}
.ff label{font-size:13px;font-weight:600;color:var(--tx)}
.ff input,.ff select,.ff textarea{padding:11px 14px;border:1.5px solid var(--bd);border-radius:10px;font-size:14px;font-family:inherit;transition:border-color .2s;background:#fff}
.ff input:focus,.ff select:focus{outline:none;border-color:var(--ta);box-shadow:0 0 0 3px rgba(26,115,232,.1)}
.ff input[readonly]{background:var(--bg);color:var(--txm);cursor:not-allowed}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:10px;font-size:14px;font-weight:600;font-family:inherit;border:none;cursor:pointer;text-decoration:none;transition:all .2s}
.btn.p{background:var(--ta);color:#fff}.btn.p:hover{background:var(--tp);transform:translateY(-1px)}
.btn.s{background:var(--ts);color:#fff}.btn.s:hover{background:var(--ts-d)}
.btn.o{background:transparent;border:1.5px solid var(--bd);color:var(--tx)}.btn.o:hover{border-color:var(--ta);color:var(--ta)}
.ba{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
.ni{display:flex;gap:14px;padding:16px 0;border-bottom:1px solid var(--bd)}.ni:last-child{border-bottom:none}
.nic{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.nic.info{background:#dbeafe;color:#2563eb}.nic.success{background:#dcfce7;color:var(--ok)}.nic.warning{background:#fff7ed;color:var(--wn)}.nic.error{background:#fee2e2;color:#dc2626}
.nix{flex:1}.nix h4{font-size:14px;font-weight:600;margin-bottom:2px}.nix p{font-size:13px;color:var(--txm);margin:0}
.nt{font-size:12px;color:var(--txm);white-space:nowrap}
.nu{background:#eff6ff;border-radius:12px;margin:0 -12px;padding:16px 12px!important}
.sei{display:flex;align-items:center;gap:14px;padding:16px 0;border-bottom:1px solid var(--bd)}.sei:last-child{border-bottom:none}
.sec{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.sec.a{background:#dcfce7;color:var(--ok)}.sec.i{background:#f1f5f9;color:#94a3b8}
.seinf{flex:1}.seinf h4{font-size:14px;font-weight:600;margin-bottom:2px}.seinf p{font-size:12px;color:var(--txm);margin:0}
.sest{font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px}
.sest.a{background:#dcfce7;color:#166534}.sest.e{background:#f1f5f9;color:#64748b}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:24px}
.sidebar-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:999}
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
        <a href="?section=quotations" class="<?php echo $section==='quotations'?'active':''; ?>"><i class="fas fa-file-invoice"></i> ใบขอบริการ<?php if((int)$qStats['pending']>0 && !isset($_SESSION['tnb_viewed_quotations'])):?><span class="nb"><?php echo (int)$qStats['pending'];?></span><?php endif;?></a>
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
        <div><a href="../main/quotation.php" class="topbar-btn"><i class="fas fa-plus"></i> ขอบริการขนส่ง</a></div>
    </div>
    <div class="mc">
        <?php if($successMessage):?><div class="ta ok"><i class="fas fa-check-circle"></i> <?php echo h((string)$successMessage);?></div><?php endif;?>
        <?php if($errorMessage):?><div class="ta er"><i class="fas fa-exclamation-circle"></i> <?php echo h((string)$errorMessage);?></div><?php endif;?>

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
<div class="tc"><div class="tc-h"><h2><i class="fas fa-file-invoice"></i> คำขอบริการล่าสุด</h2><a href="?section=quotations" class="btn o" style="padding:6px 14px;font-size:13px">ดูทั้งหมด</a></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>วันที่</th><th>ประเภท</th><th>เส้นทาง</th><th>ราคา</th><th>สถานะ</th></tr></thead><tbody>
<?php if($quotations===[]):?><tr class="empty"><td colspan="6">ยังไม่มีคำขอบริการ — <a href="../main/quotation.php" style="color:var(--ta);font-weight:600">ขอบริการแรก</a></td></tr>
<?php else: foreach(array_slice($quotations,0,5) as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td style="white-space:nowrap"><?php echo h(date('d/m/Y',strtotime((string)$q['created_at'])));?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td><?php echo $q['quoted_price']!==null?h(number_format((float)$q['quoted_price'],2)).' ฿':'<span style="color:var(--txm)">รอเสนอราคา</span>';?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td></tr><?php endforeach; endif;?>
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
<div class="tc"><div class="tc-h"><h2><i class="fas fa-file-invoice"></i> คำขอบริการทั้งหมด</h2><a href="../main/quotation.php" class="btn p" style="padding:8px 16px;font-size:13px"><i class="fas fa-plus"></i> ขอบริการใหม่</a></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>วันที่</th><th>ประเภท</th><th>เส้นทาง</th><th>ราคา</th><th>Tracking</th><th>สถานะ</th></tr></thead><tbody>
<?php if($quotations===[]):?><tr class="empty"><td colspan="7">ยังไม่มีคำขอบริการ</td></tr>
<?php else: foreach($quotations as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td style="white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$q['created_at'])));?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td><?php echo $q['quoted_price']!==null?h(number_format((float)$q['quoted_price'],2)).' ฿':'<span style="color:var(--txm)">รอเสนอราคา</span>';?></td><td style="font-size:13px"><?php echo h((string)($q['tracking_number']??'-'));?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='tracking'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-shipping-fast"></i> ติดตามสถานะการขนส่ง</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>ประเภท</th><th>เส้นทาง</th><th>Tracking</th><th>สถานะ</th><th>ขั้นตอน</th></tr></thead><tbody>
<?php $active=array_filter($quotations,fn($q)=>in_array((string)$q['status'],['processing','quoted','approved','in_transit']));
if($active===[]):?><tr class="empty"><td colspan="6">ไม่มีงานขนส่งที่กำลังดำเนินการ</td></tr>
<?php else: foreach($active as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td style="font-size:13px"><?php echo h((string)($q['tracking_number']??'-'));?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td><td>
<?php $steps=['pending'=>1,'processing'=>2,'quoted'=>3,'approved'=>4,'in_transit'=>5,'delivered'=>6,'completed'=>7];$c=$steps[$q['status']]??1;$t=7;$p=($c/$t)*100;?>
<div style="display:flex;align-items:center;gap:8px"><div style="flex:1;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden"><div style="height:100%;width:<?php echo $p;?>%;background:var(--ta);border-radius:3px"></div></div><span style="font-size:12px;font-weight:600;color:var(--txm)"><?php echo $c;?>/<?php echo $t;?></span></div>
</td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-check-circle"></i> งานขนส่งที่เสร็จสิ้น</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>เลขที่</th><th>ประเภท</th><th>เส้นทาง</th><th>ราคา</th><th>สถานะ</th></tr></thead><tbody>
<?php $done=array_filter($quotations,fn($q)=>in_array((string)$q['status'],['delivered','completed','rejected','cancelled']));
if($done===[]):?><tr class="empty"><td colspan="5">ยังไม่มีงานที่เสร็จสิ้น</td></tr>
<?php else: foreach($done as $q):?><tr><td style="font-weight:600"><?php echo h((string)$q['request_number']);?></td><td><?php echo h((string)$q['service_type']);?></td><td><?php echo h((string)($q['route']??'-'));?></td><td><?php echo $q['quoted_price']!==null?h(number_format((float)$q['quoted_price'],2)).' ฿':'-';?></td><td><?php echo tnb_status_badge((string)$q['status']);?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='transport'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-truck"></i> ข้อมูลรถบรรทุก</h2></div><div class="tc-b">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px">
<?php
$truckTypes = $pdo->query("SELECT * FROM truck_types WHERE is_active = 1 ORDER BY display_order")->fetchAll();
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
<div class="tc"><div class="tc-h"><h2><i class="fas fa-shield-alt"></i> ประวัติการเข้าสู่ระบบ</h2></div><div class="tc-b">
<?php if($sessions===[]):?><p style="text-align:center;color:var(--txm);padding:32px 0">ไม่มีข้อมูลเซสชั่น</p>
<?php else: foreach($sessions as $s):
$isA=(int)$s['is_active']===1&&strtotime((string)$s['expires_at'])>time();
$isC=isset($currentUser['session_token'])&&$s['session_token']===$currentUser['session_token'];
$ua=(string)$s['user_agent'];
$br='Unknown';if(str_contains($ua,'Chrome'))$br='Chrome';elseif(str_contains($ua,'Firefox'))$br='Firefox';elseif(str_contains($ua,'Safari'))$br='Safari';elseif(str_contains($ua,'Edge'))$br='Edge';
$os='Unknown';if(str_contains($ua,'Windows'))$os='Windows';elseif(str_contains($ua,'Mac'))$os='macOS';elseif(str_contains($ua,'Linux'))$os='Linux';elseif(str_contains($ua,'Android'))$os='Android';elseif(str_contains($ua,'iPhone')||str_contains($ua,'iPad'))$os='iOS';
?>
<div class="sei"><div class="sec <?php echo $isA?'a':'i';?>"><i class="fas fa-laptop"></i></div><div class="seinf"><h4><?php echo h($br.' on '.$os);?> <?php echo $isC?'<span style="color:var(--ta);font-size:11px">(เซสชั่นปัจจุบัน)</span>':'';?></h4><p>IP: <?php echo h((string)$s['ip_address']);?> &middot; <?php echo h(date('d/m/Y H:i',strtotime((string)$s['created_at'])));?></p></div><span class="sest <?php echo $isA?'a':'e';?>"><?php echo $isA?'Active':'Expired';?></span></div>
<?php endforeach; endif;?>
</div></div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-history"></i> ประวัติกิจกรรมทั้งหมด</h2></div><div class="tc-b" style="padding:0"><div class="tw"><table class="tt"><thead><tr><th>วันที่</th><th>กิจกรรม</th><th>ตาราง</th><th>รหัส</th></tr></thead><tbody>
<?php if($activities===[]):?><tr class="empty"><td colspan="4">ยังไม่มีกิจกรรม</td></tr>
<?php else: foreach($activities as $a):?><tr><td style="white-space:nowrap;font-size:13px"><?php echo h(date('d/m/Y H:i',strtotime((string)$a['created_at'])));?></td><td><?php echo h(tnb_action_label((string)$a['action']));?></td><td style="font-size:13px;color:var(--txm)"><?php echo h((string)($a['table_name']??'-'));?></td><td style="font-size:13px"><?php echo h((string)($a['record_id']??'-'));?></td></tr><?php endforeach; endif;?>
</tbody></table></div></div></div>

<?php elseif($section==='settings'): ?>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-lock"></i> เปลี่ยนรหัสผ่าน</h2></div><div class="tc-b">
<form action="../../admin/api/profile/change-password.php" method="POST"><input type="hidden" name="_csrf" value="<?php echo h(csrf_token());?>"><input type="hidden" name="company" value="tnb">
<div class="fg">
    <div class="ff full"><label for="current_password">รหัสผ่านปัจจุบัน</label><input type="password" id="current_password" name="current_password" required></div>
    <div class="ff"><label for="new_password">รหัสผ่านใหม่</label><input type="password" id="new_password" name="new_password" required></div>
    <div class="ff"><label for="confirm_new_password">ยืนยันรหัสผ่านใหม่</label><input type="password" id="confirm_new_password" name="confirm_new_password" required></div>
</div>
<p style="font-size:12px;color:var(--txm);margin-top:12px">รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลข</p>
<div class="ba"><button type="submit" class="btn p"><i class="fas fa-key"></i> เปลี่ยนรหัสผ่าน</button></div>
</form></div></div>
<div class="tc"><div class="tc-h"><h2><i class="fas fa-palette"></i> การตั้งค่าบัญชี</h2></div><div class="tc-b"><div style="display:grid;gap:20px">
<div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:var(--bg);border-radius:12px"><div><div style="font-weight:600;margin-bottom:2px">การแจ้งเตือนทางอีเมล</div><div style="font-size:13px;color:var(--txm)">รับอีเมลเมื่อมีอัปเดตสถานะการขนส่ง</div></div><label style="position:relative;width:48px;height:26px;display:inline-block"><input type="checkbox" checked style="opacity:0;width:0;height:0"><span style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:var(--ok);border-radius:26px;transition:.3s"></span></label></div>
<div style="display:flex;align-items:center;justify-content:space-between;padding:16px;background:var(--bg);border-radius:12px"><div><div style="font-weight:600;margin-bottom:2px">ภาษาที่แสดง</div><div style="font-size:13px;color:var(--txm)">เลือกภาษาสำหรับการแสดงผล</div></div><select style="padding:8px 12px;border:1.5px solid var(--bd);border-radius:8px;font-size:14px;font-family:inherit"><option value="th">ไทย</option><option value="en">English</option><option value="zh">中文</option><option value="jp">日本語</option></select></div>
</div></div></div>
<div class="tc" style="border:1.5px solid #fee2e2"><div class="tc-h"><h2 style="color:#dc2626"><i class="fas fa-exclamation-triangle"></i> โซนอันตราย</h2></div><div class="tc-b"><p style="font-size:14px;color:var(--txm);margin-bottom:16px">การลบบัญชีจะไม่สามารถกู้คืนได้ ข้อมูลทั้งหมดจะถูกลบอย่างถาวร</p><button class="btn" style="background:#fee2e2;color:#dc2626" disabled><i class="fas fa-trash"></i> ขอลบบัญชี (กรุณาติดต่อแอดมิน)</button></div></div>
<?php endif; ?>

    </div>
</div>
<script>function toggleSidebar(){document.getElementById('userSidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('show')}</script>
<?php clear_old_input(); ?>
</body>
</html>
