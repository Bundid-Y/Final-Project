<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/crud.php';
require_once __DIR__ . '/includes/content.php';

$pdo = Database::connection();
$user = require_admin_user();
$isSuperAdmin = in_array((string) $user['role'], ['super_admin'], true);
$companyId = $isSuperAdmin ? null : (int) $user['company_id'];
$section = $_GET['section'] ?? 'overview';
$validSections = ['overview','users','koch_quotations','tnb_quotations','notifications','activity','settings','sliders','partners','products','featured_products','truck_types','truck_types_index','truck_cards','email_templates','contact_messages','export_data'];
if (!in_array($section, $validSections, true)) $section = 'overview';

// Company mode toggle
$companyMode = $_GET['company_mode'] ?? ($_SESSION['admin_company_mode'] ?? 'all');
if (in_array($companyMode, ['all', 'koch', 'tnb'], true)) {
    $_SESSION['admin_company_mode'] = $companyMode;
}
$filterCompanyId = match($companyMode) {
    'koch' => get_company_id_by_code($pdo, 'KOCH'),
    'tnb' => get_company_id_by_code($pdo, 'TNB'),
    default => null,
};
// Store company context in session so notification/activity functions know which company is active
$_SESSION['admin_company_id_context'] = $filterCompanyId;

// Auto-mark notifications as read when viewing notifications section (BEFORE loading stats)
if ($section === 'notifications') {
    $markSql = 'UPDATE notifications SET is_read = 1 WHERE is_read = 0';
    if ($filterCompanyId !== null) {
        $markSql .= ' AND company_id = :cid';
        $markStmt = $pdo->prepare($markSql);
        $markStmt->execute([':cid' => $filterCompanyId]);
    } else {
        $pdo->exec($markSql);
    }
}

$stats = admin_dashboard_stats($pdo, $filterCompanyId);
$ext = admin_extended_stats($pdo, $filterCompanyId);
$activities = latest_admin_activities($pdo, $filterCompanyId, 8);
$recentKoch = admin_recent_koch_quotations($pdo, $filterCompanyId, 5);
$recentTnb = admin_recent_tnb_quotations($pdo, $filterCompanyId, 5);
$recentUsers = admin_recent_users($pdo, $filterCompanyId, 5);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
$totalPending = $ext['koch_pending'] + $ext['tnb_pending'];
$csrfToken = csrf_token();
$kochId = get_company_id_by_code($pdo, 'KOCH');
$tnbId = get_company_id_by_code($pdo, 'TNB');
$modeColors = match($companyMode) {
    'koch' => ['--primary:#ED2A2A','--primary-dark:#c41f1f','--primary-light:#fef2f2','--secondary:#325662'],
    'tnb' => ['--primary:#0d2d6b','--primary-dark:#091f4a','--primary-light:#eff6ff','--secondary:#325662'],
    default => ['--primary:#4f46e5','--primary-dark:#4338ca','--primary-light:#eef2ff','--secondary:#0f172a'],
};

function admin_status_badge(string $status): string {
    $m = ['pending'=>['#fff3e0','#e65100','Pending'],'processing'=>['#e3f2fd','#1565c0','Processing'],'quoted'=>['#f3e5f5','#7b1fa2','Quoted'],'approved'=>['#e8f5e9','#2e7d32','Approved'],'in_transit'=>['#e0f7fa','#00838f','In Transit'],'delivered'=>['#e8f5e9','#1b5e20','Delivered'],'completed'=>['#e0f2f1','#00695c','Completed'],'rejected'=>['#fbe9e7','#bf360c','Rejected'],'cancelled'=>['#efebe9','#4e342e','Cancelled'],'active'=>['#dcfce7','#166534','Active'],'inactive'=>['#f1f5f9','#64748b','Inactive'],'suspended'=>['#fee2e2','#991b1b','Suspended'],'deleted'=>['#fee2e2','#991b1b','Deleted']];
    $s = $m[$status] ?? ['#f5f5f5','#616161',$status];
    return '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:'.$s[0].';color:'.$s[1].'">'.h($s[2]).'</span>';
}
function admin_action_label(string $a): string {
    $m = [
        'LOGIN_SUCCESS'=>'Login',
        'LOGIN_FAILED'=>'Login Failed',
        'REGISTER_SUCCESS'=>'Register',
        'AUTO_REGISTER_FROM_QUOTATION'=>'Auto Register',
        'PROFILE_UPDATED'=>'Profile Update',
        'PASSWORD_CHANGED'=>'Password Change',
        'LOGOUT'=>'Logout',
        'KOCH_QUOTATION_CREATED'=>'KOCH Quotation',
        'TNB_QUOTATION_CREATED'=>'TNB Request',
        'CONTACT_MESSAGE_SENT'=>'Contact Message',
        'CONTACT_STATUS_CHANGED'=>'Contact Status Changed',
        'CONTACT_DELETED'=>'Contact Deleted',
        'SLIDER_CREATED'=>'Slider Created',
        'SLIDER_UPDATED'=>'Slider Updated',
        'SLIDER_DELETED'=>'Slider Deleted',
        'PARTNER_CREATED'=>'Partner Created',
        'PARTNER_UPDATED'=>'Partner Updated',
        'PARTNER_DELETED'=>'Partner Deleted',
        'PRODUCT_CREATED'=>'Product Created',
        'PRODUCT_UPDATED'=>'Product Updated',
        'PRODUCT_DELETED'=>'Product Deleted',
        'TRUCK_TYPE_CREATED'=>'Truck Type Created',
        'TRUCK_TYPE_UPDATED'=>'Truck Type Updated',
        'TRUCK_TYPE_DELETED'=>'Truck Type Deleted',
        'BRANCH_CREATED'=>'Branch Created',
        'BRANCH_UPDATED'=>'Branch Updated',
        'BRANCH_DELETED'=>'Branch Deleted',
        'USER_ROLE_CHANGED'=>'User Role Changed',
        'USER_STATUS_CHANGED'=>'User Status Changed',
        'FEATURED_PRODUCT_CREATED'=>'Featured Product Created',
        'FEATURED_PRODUCT_UPDATED'=>'Featured Product Updated',
        'FEATURED_PRODUCT_DELETED'=>'Featured Product Deleted',
        'TRUCK_CARD_CREATED'=>'Truck Card Created',
        'TRUCK_CARD_UPDATED'=>'Truck Card Updated',
        'TRUCK_CARD_DELETED'=>'Truck Card Deleted',
        'DATA_EXPORTED'=>'Data Exported',
        'DATA_EXPORT_FAILED'=>'Export Failed',
    ];
    return $m[$a] ?? $a;
}

$sectionTitles = ['overview'=>'Dashboard Overview','users'=>'User Management','koch_quotations'=>'KOCH Quotations','tnb_quotations'=>'TNB Requests','notifications'=>'Notifications','activity'=>'Activity Logs','settings'=>'System Settings','sliders'=>'Slider Management','partners'=>'Partner Management','products'=>'Product Management','featured_products'=>'Featured Products','truck_types'=>'Truck Type Management','truck_types_index'=>'Truck Types Index','truck_cards'=>'Truck Cards','branches'=>'Branch Management','email_templates'=>'Notification Emails','contact_messages'=>'Contact Messages'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard | KOCH & TNB</title>
<link rel="icon" type="image/png" href="<?php echo h(project_url('koch/img/company_logo/logo 2.png')); ?>"/>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<style>
:root{<?php echo implode(';', $modeColors); ?>;--bg:#f1f5f9;--card:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--success:#16a34a;--warning:#ea580c;--danger:#dc2626;--info:#2563eb;--radius:16px;--shadow:0 1px 3px rgba(0,0,0,.05),0 4px 12px rgba(0,0,0,.04);--sw:260px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
::selection{background:var(--primary);color:#fff}

/* Sidebar */
.sidebar{width:var(--sw);background:var(--secondary);color:#fff;position:fixed;top:0;left:0;bottom:0;z-index:1000;display:flex;flex-direction:column;transition:transform .3s}
.sb-brand{padding:20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-brand .logo{width:36px;height:36px;border-radius:10px;background:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
.sb-brand h2{font-size:15px;font-weight:700;letter-spacing:.3px}.sb-brand h2 small{display:block;font-size:11px;font-weight:400;color:rgba(255,255,255,.5);margin-top:2px}
.sb-user{padding:20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-user .avatar{width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;font-size:16px}
.sb-user .info h4{font-size:13px;font-weight:600}.sb-user .info span{font-size:11px;color:rgba(255,255,255,.5)}
.sb-nav{flex:1;overflow-y:auto;padding:12px 0}
.sb-nav .label{padding:10px 20px 4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.3)}
.sb-nav a{display:flex;align-items:center;gap:10px;padding:10px 20px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;font-weight:500;transition:all .15s;border-left:3px solid transparent}
.sb-nav a:hover{background:rgba(255,255,255,.06);color:#fff}
.sb-nav a.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--primary)}
.sb-nav a i{width:18px;text-align:center;font-size:14px}
.sb-nav .badge{margin-left:auto;background:var(--primary);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:8px;min-width:18px;text-align:center}
.sb-nav .badge.warn{background:var(--warning)}
.sb-nav .divider{height:1px;background:rgba(255,255,255,.06);margin:8px 20px}
.sb-foot{padding:16px 20px;border-top:1px solid rgba(255,255,255,.08)}
.sb-foot a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.5);text-decoration:none;font-size:12px;padding:6px 0;transition:color .15s}
.sb-foot a:hover{color:#fff}

/* Main */
.main{margin-left:var(--sw);min-height:100vh}
.topbar{background:var(--card);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 2px rgba(0,0,0,.04);position:sticky;top:0;z-index:100;gap:16px}
.topbar-left{display:flex;align-items:center;gap:12px}
.topbar-left h1{font-size:18px;font-weight:700}
.topbar-left .bc{font-size:12px;color:var(--muted)}
.mob-toggle{display:none;background:none;border:none;font-size:20px;cursor:pointer;color:var(--text);padding:6px}
.topbar-right{display:flex;align-items:center;gap:10px}
.tb-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;font-size:12px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:all .2s;font-family:inherit}
.tb-btn.primary{background:var(--primary);color:#fff}.tb-btn.primary:hover{background:var(--primary-dark)}
.tb-btn.ghost{background:transparent;color:var(--muted);border:1px solid var(--border)}.tb-btn.ghost:hover{border-color:var(--primary);color:var(--primary)}

.content{padding:24px 28px 48px}

/* Alert */
.alert{padding:12px 18px;border-radius:12px;margin-bottom:18px;font-weight:500;font-size:13px;display:flex;align-items:center;gap:8px}
.alert.ok{background:#dcfce7;color:#166534}.alert.er{background:#fee2e2;color:#991b1b}
.alert i{font-size:16px}

/* Stat Cards */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.stat-card{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 20px rgba(0,0,0,.08)}
.stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:3px}
.stat-card.purple::after{background:var(--primary)}.stat-card.blue::after{background:var(--info)}.stat-card.green::after{background:var(--success)}.stat-card.orange::after{background:var(--warning)}.stat-card.red::after{background:var(--danger)}.stat-card.teal::after{background:#0d9488}
.stat-card .sc-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.stat-card .sc-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px}
.stat-card.purple .sc-icon{background:#eef2ff;color:var(--primary)}.stat-card.blue .sc-icon{background:#dbeafe;color:var(--info)}.stat-card.green .sc-icon{background:#dcfce7;color:var(--success)}.stat-card.orange .sc-icon{background:#fff7ed;color:var(--warning)}.stat-card.red .sc-icon{background:#fee2e2;color:var(--danger)}.stat-card.teal .sc-icon{background:#ccfbf1;color:#0d9488}
.stat-card .sc-change{font-size:11px;font-weight:600;padding:2px 8px;border-radius:8px}
.stat-card .sc-change.up{background:#dcfce7;color:#166534}.stat-card .sc-change.down{background:#fee2e2;color:#991b1b}
.stat-card .sc-num{font-size:26px;font-weight:800;line-height:1;margin-bottom:4px}
.stat-card .sc-label{font-size:12px;color:var(--muted);font-weight:500}

/* Card */
.card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;overflow:hidden}
.card-h{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-h h2{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}.card-h h2 i{font-size:15px;color:var(--primary)}
.card-h .link{font-size:12px;color:var(--primary);text-decoration:none;font-weight:600}
.card-h .link:hover{text-decoration:underline}
.card-b{padding:20px}

/* Table */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{text-align:left;padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);background:var(--bg);border-bottom:1px solid var(--border)}
.tbl td{padding:12px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}.tbl tr:hover td{background:#f8fafc}
.tbl .empty td{text-align:center;color:var(--muted);padding:32px 14px}

/* Grid layouts */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.grid-3{display:grid;grid-template-columns:2fr 1fr;gap:20px}

/* Quick Action */
.qa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
.qa-item{background:var(--card);border-radius:12px;padding:16px;text-align:center;text-decoration:none;color:var(--text);box-shadow:var(--shadow);transition:all .2s;border:1.5px solid transparent}
.qa-item:hover{border-color:var(--primary);transform:translateY(-2px)}
.qa-item i{font-size:22px;margin-bottom:8px;display:block}
.qa-item span{font-size:12px;font-weight:600}
.qa-item.koch i{color:#ED2A2A}.qa-item.tnb i{color:#0d2d6b}.qa-item.all i{color:var(--primary)}.qa-item.notif i{color:var(--warning)}

/* User row */
.user-row{display:flex;align-items:center;gap:10px}
.user-row .u-avatar{width:32px;height:32px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}
.user-row .u-info h4{font-size:13px;font-weight:600;margin-bottom:1px}.user-row .u-info span{font-size:11px;color:var(--muted)}

/* Responsive */
.sb-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:999}
@media(max-width:1200px){.stats-row{grid-template-columns:repeat(2,1fr)}.qa-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:1024px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.sb-overlay.show{display:block}.main{margin-left:0}.mob-toggle{display:block}.content{padding:20px 16px 40px}.topbar{padding:12px 16px}.grid-2,.grid-3{grid-template-columns:1fr}}
@media(max-width:600px){.stats-row{grid-template-columns:1fr}.qa-grid{grid-template-columns:1fr}}

/* Modal */
.modal-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center;padding:20px}
.modal-overlay.show{display:flex}
.modal{background:#fff;border-radius:16px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:modalIn .25s ease}
@keyframes modalIn{from{opacity:0;transform:scale(.95) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-head{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-head h3{font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px}
.modal-head h3 i{color:var(--primary)}
.modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);padding:4px 8px;border-radius:6px;transition:all .15s}
.modal-close:hover{background:var(--bg);color:var(--text)}
.modal-body{padding:24px}
.fm-group{margin-bottom:16px}
.fm-group label{display:block;font-size:12px;font-weight:600;color:var(--text);margin-bottom:6px}
.fm-group label span{color:var(--danger);margin-left:2px}
.fm-input{width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;transition:border-color .2s;background:#fff;color:var(--text)}
.fm-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.1)}
select.fm-input{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px}
textarea.fm-input{resize:vertical;min-height:80px}
.fm-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.fm-check{display:flex;align-items:center;gap:8px;margin-top:8px}
.fm-check input[type=checkbox]{width:18px;height:18px;accent-color:var(--primary);cursor:pointer}
.fm-check label{margin-bottom:0;cursor:pointer}
.modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:8px}
.btn{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:all .2s;display:inline-flex;align-items:center;gap:6px}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark)}
.btn-danger{background:var(--danger);color:#fff}.btn-danger:hover{background:#b91c1c}
.btn-ghost{background:transparent;color:var(--muted);border:1px solid var(--border)}.btn-ghost:hover{border-color:var(--text);color:var(--text)}
.btn-sm{padding:5px 12px;font-size:11px;border-radius:8px}
.btn-xs{padding:3px 8px;font-size:10px;border-radius:6px}
.act-btns{display:flex;gap:4px}

/* Role badge colors */
.role-super_admin{background:#fef3c7;color:#92400e}.role-admin{background:#dbeafe;color:#1e40af}.role-manager{background:#e0e7ff;color:#3730a3}.role-user{background:#f1f5f9;color:#475569}

/* Permission table */
.perm-table{width:100%;border-collapse:collapse;margin-top:12px;font-size:12px}
.perm-table th{background:var(--bg);padding:8px 12px;text-align:left;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)}
.perm-table td{padding:8px 12px;border-bottom:1px solid var(--border)}
.perm-table .perm-yes{color:var(--success);font-weight:700}.perm-table .perm-no{color:var(--muted)}
</style>
</head>
<body>
<div class="sb-overlay" id="sbOverlay" onclick="toggleSB()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <div class="logo">K&T</div>
        <h2>Admin Panel<small>KOCH & TNB System</small></h2>
    </div>
    <div class="sb-user">
        <div class="avatar"><i class="fas fa-user-shield"></i></div>
        <div class="info">
            <h4><?php echo h($user['username']); ?></h4>
            <span><?php echo h(ucfirst((string) $user['role'])); ?> &middot; <?php echo h((string) $user['company_name']); ?></span>
        </div>
    </div>
    <div style="padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.08)">
        <div style="display:flex;gap:4px;background:rgba(255,255,255,.08);border-radius:8px;padding:3px">
            <a href="?section=<?php echo h($section);?>&company_mode=all" style="flex:1;text-align:center;padding:5px 8px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;transition:all .2s;<?php echo $companyMode==='all'?'background:var(--primary);color:#fff':'color:rgba(255,255,255,.5)';?>">All</a>
            <a href="?section=<?php echo h($section);?>&company_mode=koch" style="flex:1;text-align:center;padding:5px 8px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;transition:all .2s;<?php echo $companyMode==='koch'?'background:#ED2A2A;color:#fff':'color:rgba(255,255,255,.5)';?>">KOCH</a>
            <a href="?section=<?php echo h($section);?>&company_mode=tnb" style="flex:1;text-align:center;padding:5px 8px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;transition:all .2s;<?php echo $companyMode==='tnb'?'background:#0d2d6b;color:#fff':'color:rgba(255,255,255,.5)';?>">TNB</a>
        </div>
    </div>
    <nav class="sb-nav">
        <div class="label">Main</div>
        <a href="?section=notifications" class="<?php echo $section==='notifications'?'active':'';?>"><i class="fas fa-bell"></i> Notifications<?php if((int)$stats['unread_notifications']>0):?><span class="badge"><?php echo (int)$stats['unread_notifications'];?></span><?php endif;?></a>
        <a href="?section=overview" class="<?php echo $section==='overview'?'active':'';?>"><i class="fas fa-chart-pie"></i> Dashboard<?php if($totalPending>0):?><span class="badge warn"><?php echo $totalPending;?></span><?php endif;?></a>
        <div class="divider"></div>
        <div class="label">Business</div>
        <a href="?section=users" class="<?php echo $section==='users'?'active':'';?>"><i class="fas fa-users"></i> Users</a>
        <?php if ($companyMode !== 'tnb'): ?>
        <a href="?section=koch_quotations" class="<?php echo $section==='koch_quotations'?'active':'';?>"><i class="fas fa-box"></i> KOCH Quotations<?php if($ext['koch_pending']>0):?><span class="badge warn"><?php echo $ext['koch_pending'];?></span><?php endif;?></a>
        <?php endif; ?>
        <?php if ($companyMode !== 'koch'): ?>
        <a href="?section=tnb_quotations" class="<?php echo $section==='tnb_quotations'?'active':'';?>"><i class="fas fa-truck"></i> TNB Requests<?php if($ext['tnb_pending']>0):?><span class="badge warn"><?php echo $ext['tnb_pending'];?></span><?php endif;?></a>
        <?php endif; ?>
        <a href="?section=contact_messages" class="<?php echo $section==='contact_messages'?'active':'';?>"><i class="fas fa-envelope-open-text"></i> Contact Messages</a>
        <div class="divider"></div>
        <?php if ($companyMode !== 'all'): ?>
        <div class="label">Content</div>
        <a href="?section=sliders" class="<?php echo $section==='sliders'?'active':'';?>"><i class="fas fa-images"></i> Sliders</a>
        <a href="?section=partners" class="<?php echo $section==='partners'?'active':'';?>"><i class="fas fa-handshake"></i> Partners</a>
        <?php if ($companyMode === 'koch'): ?>
        <a href="?section=products" class="<?php echo $section==='products'?'active':'';?>"><i class="fas fa-boxes-stacked"></i> Products</a>
        <a href="?section=featured_products" class="<?php echo $section==='featured_products'?'active':'';?>"><i class="fas fa-star"></i> Featured Products</a>
        <?php elseif ($companyMode === 'tnb'): ?>
        <a href="?section=truck_cards" class="<?php echo $section==='truck_cards'?'active':'';?>"><i class="fas fa-id-card"></i> Truck Cards</a>
        <?php endif; ?>
        <?php endif; ?>
        <div class="divider"></div>
        <div class="label">Communications</div>
        <a href="?section=email_templates" class="<?php echo $section==='email_templates'?'active':'';?>"><i class="fas fa-envelope"></i> Notification Emails</a>
        <a href="?section=activity" class="<?php echo $section==='activity'?'active':'';?>"><i class="fas fa-history"></i> Activity Logs</a>
        <div class="divider"></div>
        <div class="label">Export</div>
        <a href="?section=export_data" class="<?php echo $section==='export_data'?'active':'';?>"><i class="fas fa-file-export"></i> Export Data</a>
        <div class="divider"></div>
        <div class="label">System</div>
        <a href="?section=settings" class="<?php echo $section==='settings'?'active':'';?>"><i class="fas fa-cog"></i> Settings</a>
    </nav>
    <div class="sb-foot">
        <?php
        // Determine user page company: login_company (new sessions) → cookie detection (old sessions) → company_code
        // CRITICAL: Must use login origin, NOT user's actual company, to avoid cross-company redirect bug
        $userPageCompany = $user['login_company'] ?? null;
        if ($userPageCompany === null) {
            $hasTnb  = isset($_COOKIE['tnb_session']);
            $hasKoch = isset($_COOKIE['koch_session']);
            if ($hasTnb && !$hasKoch) { $userPageCompany = 'tnb'; }
            elseif ($hasKoch && !$hasTnb) { $userPageCompany = 'koch'; }
            else { $userPageCompany = strtolower((string)($user['company_code'] ?? 'koch')); }
        }
        $userPageUrl = user_page_by_company(company_code_from_slug($userPageCompany));
        ?>
        <a href="<?php echo h($userPageUrl);?>"><i class="fas fa-external-link-alt"></i> Front User Page</a>
        <a href="<?php echo h(project_url('koch/main/index.php'));?>"><i class="fas fa-globe"></i> KOCH Website</a>
        <a href="<?php echo h(project_url('tnb/main/index.php'));?>"><i class="fas fa-globe"></i> TNB Website</a>
        <a href="<?php echo h(project_url('admin/api/auth/logout.php'));?>?company=<?php echo h($userPageCompany);?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="mob-toggle" onclick="toggleSB()"><i class="fas fa-bars"></i></button>
            <div>
                <h1><?php echo $sectionTitles[$section] ?? 'Dashboard';?></h1>
                <div class="bc">Admin Panel &rsaquo; <?php echo $sectionTitles[$section] ?? 'Dashboard';?></div>
            </div>
        </div>
        <div class="topbar-right">
            <?php if ($companyMode !== 'tnb'): ?>
            <a href="<?php echo h(project_url('koch/main/quotation.php'));?>" class="tb-btn ghost"><i class="fas fa-plus"></i> KOCH Quote</a>
            <?php endif; ?>
            <?php if ($companyMode !== 'koch'): ?>
            <a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" class="tb-btn ghost"><i class="fas fa-plus"></i> TNB Request</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <?php if($successMessage):?><div class="alert ok"><i class="fas fa-check-circle"></i> <?php echo h((string)$successMessage);?></div><?php endif;?>
        <?php if($errorMessage):?><div class="alert er"><i class="fas fa-exclamation-circle"></i> <?php echo h((string)$errorMessage);?></div><?php endif;?>

<?php if($section==='overview'): ?>
<!-- =================== OVERVIEW =================== -->

<?php if($companyMode === 'all'): ?>
<!-- ALL MODE: Summary only -->
<div class="stats-row">
    <div class="stat-card purple">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div><?php if($ext['new_users_month']>0):?><span class="sc-change up">+<?php echo $ext['new_users_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['users']);?></div>
        <div class="sc-label">Total Users (All)</div>
    </div>
    <div class="stat-card blue">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-box"></i></div><?php if($ext['koch_month']>0):?><span class="sc-change up">+<?php echo $ext['koch_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['koch_quotations']);?></div>
        <div class="sc-label">KOCH Quotations (Total)</div>
    </div>
    <div class="stat-card teal">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-truck"></i></div><?php if($ext['tnb_month']>0):?><span class="sc-change up">+<?php echo $ext['tnb_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['tnb_quotations']);?></div>
        <div class="sc-label">TNB Requests (Total)</div>
    </div>
    <div class="stat-card orange">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div>
        <div class="sc-num"><?php echo $totalPending;?></div>
        <div class="sc-label">Pending Approval (All)</div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-b" style="padding:20px;text-align:center">
        <i class="fas fa-info-circle" style="font-size:24px;color:var(--primary);margin-bottom:8px;display:block"></i>
        <p style="font-size:14px;font-weight:600;margin:0 0 6px">ภาพรวมรวม KOCH & TNB</p>
        <p style="font-size:12px;color:var(--muted);margin:0">เลือก <strong>KOCH</strong> หรือ <strong>TNB</strong> ที่แถบด้านซ้ายเพื่อจัดการข้อมูลของแต่ละบริษัท</p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-h"><h2><i class="fas fa-box" style="color:#ED2A2A"></i> KOCH Summary</h2></div>
        <div class="card-b">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div style="padding:12px;background:var(--bg);border-radius:10px;text-align:center"><div style="font-size:22px;font-weight:800"><?php echo number_format((int)$stats['koch_quotations']);?></div><div style="font-size:11px;color:var(--muted)">Quotations</div></div>
                <div style="padding:12px;background:var(--bg);border-radius:10px;text-align:center"><div style="font-size:22px;font-weight:800"><?php echo $ext['koch_pending'];?></div><div style="font-size:11px;color:var(--muted)">Pending</div></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-h"><h2><i class="fas fa-truck" style="color:#0d2d6b"></i> TNB Summary</h2></div>
        <div class="card-b">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div style="padding:12px;background:var(--bg);border-radius:10px;text-align:center"><div style="font-size:22px;font-weight:800"><?php echo number_format((int)$stats['tnb_quotations']);?></div><div style="font-size:11px;color:var(--muted)">Requests</div></div>
                <div style="padding:12px;background:var(--bg);border-radius:10px;text-align:center"><div style="font-size:22px;font-weight:800"><?php echo $ext['tnb_pending'];?></div><div style="font-size:11px;color:var(--muted)">Pending</div></div>
                <div style="padding:12px;background:var(--bg);border-radius:10px;text-align:center"><div style="font-size:22px;font-weight:800"><?php echo $ext['tnb_in_transit'];?></div><div style="font-size:11px;color:var(--muted)">In Transit</div></div>
            </div>
        </div>
    </div>
</div>

<?php elseif($companyMode === 'koch'): ?>
<!-- KOCH MODE -->
<div class="stats-row">
    <div class="stat-card purple">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div><?php if($ext['new_users_month']>0):?><span class="sc-change up">+<?php echo $ext['new_users_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['users']);?></div>
        <div class="sc-label">KOCH Users</div>
    </div>
    <div class="stat-card blue">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-box"></i></div><?php if($ext['koch_month']>0):?><span class="sc-change up">+<?php echo $ext['koch_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['koch_quotations']);?></div>
        <div class="sc-label">KOCH Quotations</div>
    </div>
    <div class="stat-card orange">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div>
        <div class="sc-num"><?php echo $ext['koch_pending'];?></div>
        <div class="sc-label">Pending</div>
    </div>
    <div class="stat-card green">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-bell"></i></div></div>
        <div class="sc-num"><?php echo number_format((int)$stats['unread_notifications']);?></div>
        <div class="sc-label">Notifications</div>
    </div>
</div>

<div class="qa-grid">
    <a href="<?php echo h(project_url('koch/main/quotation.php'));?>" class="qa-item koch"><i class="fas fa-box-open"></i><span>New Quotation</span></a>
    <a href="?section=koch_quotations" class="qa-item all"><i class="fas fa-clipboard-list"></i><span>New (<?php echo $ext['koch_pending'];?>)</span></a>
    <a href="?section=products" class="qa-item koch"><i class="fas fa-boxes-stacked"></i><span>Products</span></a>
    <a href="?section=notifications" class="qa-item notif"><i class="fas fa-bell"></i><span>Notifications (<?php echo (int)$stats['unread_notifications'];?>)</span></a>
</div>

<div class="grid-3">
    <div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-box"></i> Recent KOCH Quotations</h2><a href="?section=koch_quotations" class="link">View All &rarr;</a></div>
            <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Product</th><th>Status</th></tr></thead><tbody>
            <?php if($recentKoch===[]):?><tr class="empty"><td colspan="4">No quotations yet</td></tr>
            <?php else: foreach($recentKoch as $q):?><tr>
                <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['quotation_number']);?></td>
                <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
                <td style="font-size:12px"><?php echo h((string)$q['product_type']);?></td>
                <td><?php echo admin_status_badge((string)$q['status']);?></td>
            </tr><?php endforeach; endif;?>
            </tbody></table></div></div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-user-plus"></i> Recent Users</h2><a href="?section=users" class="link">View All &rarr;</a></div>
            <div class="card-b">
            <?php if($recentUsers===[]):?><p style="text-align:center;color:var(--muted);padding:20px 0;font-size:13px">No users yet</p>
            <?php else: foreach($recentUsers as $u):?>
                <div class="user-row" style="padding:8px 0;border-bottom:1px solid var(--border);<?php echo end($recentUsers)===$u?'border:none':'';?>">
                    <div class="u-avatar"><?php echo strtoupper(substr((string)($u['first_name']??$u['username']),0,1));?></div>
                    <div class="u-info">
                        <h4><?php echo h(trim(($u['first_name']??'').' '.($u['last_name']??''))?:$u['username']);?></h4>
                        <span><?php echo h((string)($u['company_name']??'-'));?> &middot; <?php echo h(ucfirst((string)$u['role']));?></span>
                    </div>
                    <div style="margin-left:auto"><?php echo admin_status_badge((string)$u['status']);?></div>
                </div>
            <?php endforeach; endif;?>
            </div>
        </div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-history"></i> Recent Activity</h2><a href="?section=activity" class="link">View All &rarr;</a></div>
            <div class="card-b" style="padding:12px 20px">
            <?php if($activities===[]):?><p style="text-align:center;color:var(--muted);padding:20px 0;font-size:13px">No activity yet</p>
            <?php else: foreach(array_slice($activities,0,6) as $a):?>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--primary);flex-shrink:0"></div>
                    <div style="flex:1"><strong><?php echo h((string)($a['username']??'System'));?></strong> — <?php echo h(admin_action_label((string)$a['action']));?></div>
                    <div style="color:var(--muted);white-space:nowrap"><?php echo h(date('d/m H:i',strtotime((string)$a['created_at'])));?></div>
                </div>
            <?php endforeach; endif;?>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- TNB MODE -->
<div class="stats-row">
    <div class="stat-card purple">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div><?php if($ext['new_users_month']>0):?><span class="sc-change up">+<?php echo $ext['new_users_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['users']);?></div>
        <div class="sc-label">TNB Users</div>
    </div>
    <div class="stat-card teal">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-truck"></i></div><?php if($ext['tnb_month']>0):?><span class="sc-change up">+<?php echo $ext['tnb_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['tnb_quotations']);?></div>
        <div class="sc-label">TNB Requests</div>
    </div>
    <div class="stat-card orange">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div>
        <div class="sc-num"><?php echo $ext['tnb_pending'];?></div>
        <div class="sc-label">Pending</div>
    </div>
    <div class="stat-card blue">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-shipping-fast"></i></div></div>
        <div class="sc-num"><?php echo $ext['tnb_in_transit'];?></div>
        <div class="sc-label">In Transit</div>
    </div>
</div>

<div class="qa-grid">
    <a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" class="qa-item tnb"><i class="fas fa-shipping-fast"></i><span>New TNB Request</span></a>
    <a href="?section=tnb_quotations" class="qa-item all"><i class="fas fa-clipboard-list"></i><span>New (<?php echo $ext['tnb_pending'];?>)</span></a>
    <a href="?section=truck_cards" class="qa-item tnb"><i class="fas fa-id-card"></i><span>Truck Cards</span></a>
    <a href="?section=notifications" class="qa-item notif"><i class="fas fa-bell"></i><span>Notifications (<?php echo (int)$stats['unread_notifications'];?>)</span></a>
</div>

<div class="grid-3">
    <div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-truck"></i> Recent TNB Requests</h2><a href="?section=tnb_quotations" class="link">View All &rarr;</a></div>
            <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Service</th><th>Route</th><th>Status</th></tr></thead><tbody>
            <?php if($recentTnb===[]):?><tr class="empty"><td colspan="5">No requests yet</td></tr>
            <?php else: foreach($recentTnb as $q):?><tr>
                <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['request_number']);?></td>
                <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
                <td style="font-size:12px"><?php echo h((string)$q['service_type']);?></td>
                <td style="font-size:12px"><?php echo h((string)($q['route']??'-'));?></td>
                <td><?php echo admin_status_badge((string)$q['status']);?></td>
            </tr><?php endforeach; endif;?>
            </tbody></table></div></div>
        </div>
    </div>
    <div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-user-plus"></i> Recent Users</h2><a href="?section=users" class="link">View All &rarr;</a></div>
            <div class="card-b">
            <?php if($recentUsers===[]):?><p style="text-align:center;color:var(--muted);padding:20px 0;font-size:13px">No users yet</p>
            <?php else: foreach($recentUsers as $u):?>
                <div class="user-row" style="padding:8px 0;border-bottom:1px solid var(--border);<?php echo end($recentUsers)===$u?'border:none':'';?>">
                    <div class="u-avatar"><?php echo strtoupper(substr((string)($u['first_name']??$u['username']),0,1));?></div>
                    <div class="u-info">
                        <h4><?php echo h(trim(($u['first_name']??'').' '.($u['last_name']??''))?:$u['username']);?></h4>
                        <span><?php echo h((string)($u['company_name']??'-'));?> &middot; <?php echo h(ucfirst((string)$u['role']));?></span>
                    </div>
                    <div style="margin-left:auto"><?php echo admin_status_badge((string)$u['status']);?></div>
                </div>
            <?php endforeach; endif;?>
            </div>
        </div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-history"></i> Recent Activity</h2><a href="?section=activity" class="link">View All &rarr;</a></div>
            <div class="card-b" style="padding:12px 20px">
            <?php if($activities===[]):?><p style="text-align:center;color:var(--muted);padding:20px 0;font-size:13px">No activity yet</p>
            <?php else: foreach(array_slice($activities,0,6) as $a):?>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px">
                    <div style="width:6px;height:6px;border-radius:50%;background:var(--primary);flex-shrink:0"></div>
                    <div style="flex:1"><strong><?php echo h((string)($a['username']??'System'));?></strong> — <?php echo h(admin_action_label((string)$a['action']));?></div>
                    <div style="color:var(--muted);white-space:nowrap"><?php echo h(date('d/m H:i',strtotime((string)$a['created_at'])));?></div>
                </div>
            <?php endforeach; endif;?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php elseif($section==='users'): ?>
<!-- =================== USERS =================== -->
<div class="stats-row">
    <div class="stat-card purple"><div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['users']);?></div><div class="sc-label">Total Users</div></div>
    <div class="stat-card green"><div class="sc-top"><div class="sc-icon"><i class="fas fa-user-check"></i></div></div><div class="sc-num"><?php echo number_format($ext['active_users']);?></div><div class="sc-label">Active Users</div></div>
    <div class="stat-card blue"><div class="sc-top"><div class="sc-icon"><i class="fas fa-user-plus"></i></div></div><div class="sc-num"><?php echo number_format($ext['new_users_month']);?></div><div class="sc-label">New This Month</div></div>
    <div class="stat-card orange"><div class="sc-top"><div class="sc-icon"><i class="fas fa-bell"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['unread_notifications']);?></div><div class="sc-label">Unread Notifications</div></div>
</div>

<!-- RBAC Permission Reference -->
<div class="card" style="margin-bottom:20px">
    <div class="card-h"><h2><i class="fas fa-shield-alt"></i> Role Permissions Reference</h2></div>
    <div class="card-b" style="padding:12px 20px">
        <table class="perm-table">
            <thead><tr><th>Permission</th><th>Super Admin</th><th>Admin</th><th>Manager</th><th>User</th></tr></thead>
            <tbody>
                <tr><td>View all companies data</td><td class="perm-yes"><i class="fas fa-check"></i> Yes</td><td class="perm-no"><i class="fas fa-times"></i> Own company</td><td class="perm-no"><i class="fas fa-times"></i> Own team</td><td class="perm-no"><i class="fas fa-times"></i> Own data</td></tr>
                <tr><td>Manage users</td><td class="perm-yes"><i class="fas fa-check"></i> All</td><td class="perm-yes"><i class="fas fa-check"></i> Own company</td><td class="perm-no"><i class="fas fa-times"></i> Own team</td><td class="perm-no"><i class="fas fa-times"></i> No</td></tr>
                <tr><td>Change user roles</td><td class="perm-yes"><i class="fas fa-check"></i> All roles</td><td class="perm-yes"><i class="fas fa-check"></i> Manager/User</td><td class="perm-no"><i class="fas fa-times"></i> No</td><td class="perm-no"><i class="fas fa-times"></i> No</td></tr>
                <tr><td>Content Management (CRUD)</td><td class="perm-yes"><i class="fas fa-check"></i> All</td><td class="perm-yes"><i class="fas fa-check"></i> Own company</td><td class="perm-yes"><i class="fas fa-check"></i> View only</td><td class="perm-no"><i class="fas fa-times"></i> No</td></tr>
                <tr><td>View quotations</td><td class="perm-yes"><i class="fas fa-check"></i> All</td><td class="perm-yes"><i class="fas fa-check"></i> Own company</td><td class="perm-yes"><i class="fas fa-check"></i> Own team</td><td class="perm-yes"><i class="fas fa-check"></i> Own only</td></tr>
                <tr><td>Activity logs</td><td class="perm-yes"><i class="fas fa-check"></i> All</td><td class="perm-yes"><i class="fas fa-check"></i> Own company</td><td class="perm-no"><i class="fas fa-times"></i> No</td><td class="perm-no"><i class="fas fa-times"></i> No</td></tr>
                <tr><td>System settings</td><td class="perm-yes"><i class="fas fa-check"></i> Full</td><td class="perm-no"><i class="fas fa-times"></i> View only</td><td class="perm-no"><i class="fas fa-times"></i> No</td><td class="perm-no"><i class="fas fa-times"></i> No</td></tr>
                <tr><td>Email config</td><td class="perm-yes"><i class="fas fa-check"></i> Full</td><td class="perm-yes"><i class="fas fa-check"></i> Own company</td><td class="perm-no"><i class="fas fa-times"></i> No</td><td class="perm-no"><i class="fas fa-times"></i> No</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-h"><h2><i class="fas fa-users"></i> All Users</h2></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>User</th><th>Email</th><th>Company</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
    <?php
    $allUsers = $pdo->prepare('SELECT u.*, c.name AS company_name FROM users u LEFT JOIN companies c ON c.id = u.company_id' . ($filterCompanyId !== null ? ' WHERE u.company_id = :cid AND u.status != \'inactive\'' : ' WHERE u.status != \'inactive\'') . ' ORDER BY u.created_at DESC LIMIT 50');
    $allUsers->execute($filterCompanyId !== null ? [':cid' => $filterCompanyId] : []);
    $allUsersList = $allUsers->fetchAll();
    if($allUsersList===[]):?><tr class="empty"><td colspan="7">No users found</td></tr>
    <?php else: foreach($allUsersList as $u):?><tr>
        <td><div class="user-row"><div class="u-avatar"><?php echo strtoupper(substr((string)($u['first_name']??$u['username']),0,1));?></div><div class="u-info"><h4><?php echo h(trim(($u['first_name']??'').' '.($u['last_name']??''))?:$u['username']);?></h4><span>@<?php echo h((string)$u['username']);?></span></div></div></td>
        <td style="font-size:12px"><?php echo h((string)$u['email']);?></td>
        <td style="font-size:12px"><?php echo h((string)($u['company_name']??'-'));?></td>
        <td><span class="btn-xs role-<?php echo h((string)$u['role']);?>" style="display:inline-block;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600"><?php echo h(ucfirst(str_replace('_',' ',(string)$u['role'])));?></span></td>
        <td><?php echo admin_status_badge((string)$u['status']);?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y',strtotime((string)$u['created_at'])));?></td>
        <td>
            <div class="act-btns">
                <button class="btn btn-sm btn-ghost" onclick="openUserModal(<?php echo (int)$u['id'];?>,'<?php echo h((string)$u['username']);?>','<?php echo h((string)$u['role']);?>','<?php echo h((string)$u['status']);?>')"><i class="fas fa-edit"></i></button>
                <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete user <?php echo h((string)$u['username']);?>?')">
                    <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
                    <input type="hidden" name="entity" value="user">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$u['id'];?>">
                    <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=users'));?>">
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='koch_quotations'): ?>
<!-- =================== KOCH QUOTATIONS =================== -->
<?php
// Auto-mark as read
if ($ext['koch_pending'] > 0) {
    if ($filterCompanyId !== null) {
        $pdo->prepare("UPDATE koch_quotations SET is_read = 1 WHERE is_read = 0 AND user_id IN (SELECT id FROM users WHERE company_id = :cid)")->execute([':cid' => $filterCompanyId]);
    } else {
        $pdo->exec("UPDATE koch_quotations SET is_read = 1 WHERE is_read = 0");
    }
    $ext['koch_pending'] = 0; // Clear the badge immediately for the UI
}
?>
<div class="stats-row">
    <div class="stat-card blue"><div class="sc-top"><div class="sc-icon"><i class="fas fa-file-alt"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['koch_quotations']);?></div><div class="sc-label">Total KOCH Quotations</div></div>
    <div class="stat-card orange"><div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div><div class="sc-num"><?php echo $ext['koch_pending'];?></div><div class="sc-label">New / Unread</div></div>
    <div class="stat-card purple"><div class="sc-top"><div class="sc-icon"><i class="fas fa-calendar"></i></div></div><div class="sc-num"><?php echo $ext['koch_month'];?></div><div class="sc-label">This Month</div></div>
    <div class="stat-card green"><div class="sc-top"><div class="sc-icon"><i class="fas fa-check-circle"></i></div></div><div class="sc-num">-</div><div class="sc-label">Completed</div></div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-box"></i> All KOCH Quotations</h2><a href="<?php echo h(project_url('koch/main/quotation.php'));?>" class="tb-btn primary" style="font-size:11px;padding:6px 12px"><i class="fas fa-plus"></i> New</a></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Product</th><th>Quantity</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php
    $kAll = $pdo->prepare('SELECT * FROM koch_quotations ORDER BY created_at DESC LIMIT 50');
    $kAll->execute();
    $kList = $kAll->fetchAll();
    if($kList===[]):?><tr class="empty"><td colspan="6">No KOCH quotations found</td></tr>
    <?php else: foreach($kList as $q):?><tr>
        <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['quotation_number']);?></td>
        <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
        <td style="font-size:12px"><?php echo h((string)$q['product_type']);?></td>
        <td style="font-size:12px"><?php echo h((string)($q['quantity']??'-'));?></td>
        <td><?php echo admin_status_badge((string)$q['status']);?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$q['created_at'])));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='tnb_quotations'): ?>
<!-- =================== TNB QUOTATIONS =================== -->
<?php
// Auto-mark as read
if ($ext['tnb_pending'] > 0) {
    if ($filterCompanyId !== null) {
        $pdo->prepare("UPDATE tnb_quotations SET is_read = 1 WHERE is_read = 0 AND user_id IN (SELECT id FROM users WHERE company_id = :cid)")->execute([':cid' => $filterCompanyId]);
    } else {
        $pdo->exec("UPDATE tnb_quotations SET is_read = 1 WHERE is_read = 0");
    }
    $ext['tnb_pending'] = 0; // Clear the badge immediately for the UI
}
?>
<div class="stats-row">
    <div class="stat-card teal"><div class="sc-top"><div class="sc-icon"><i class="fas fa-file-alt"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['tnb_quotations']);?></div><div class="sc-label">Total TNB Requests</div></div>
    <div class="stat-card orange"><div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div><div class="sc-num"><?php echo $ext['tnb_pending'];?></div><div class="sc-label">New / Unread</div></div>
    <div class="stat-card blue"><div class="sc-top"><div class="sc-icon"><i class="fas fa-shipping-fast"></i></div></div><div class="sc-num"><?php echo $ext['tnb_in_transit'];?></div><div class="sc-label">In Transit</div></div>
    <div class="stat-card purple"><div class="sc-top"><div class="sc-icon"><i class="fas fa-calendar"></i></div></div><div class="sc-num"><?php echo $ext['tnb_month'];?></div><div class="sc-label">This Month</div></div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-truck"></i> All TNB Requests</h2><a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" class="tb-btn primary" style="font-size:11px;padding:6px 12px"><i class="fas fa-plus"></i> New</a></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Service</th><th>Route</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php
    $tAll = $pdo->prepare('SELECT * FROM tnb_quotations ORDER BY created_at DESC LIMIT 50');
    $tAll->execute();
    $tList = $tAll->fetchAll();
    if($tList===[]):?><tr class="empty"><td colspan="6">No TNB requests found</td></tr>
    <?php else: foreach($tList as $q):?><tr>
        <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['request_number']);?></td>
        <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
        <td style="font-size:12px"><?php echo h((string)$q['service_type']);?></td>
        <td style="font-size:12px"><?php echo h((string)($q['route']??'-'));?></td>
        <td><?php echo admin_status_badge((string)$q['status']);?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$q['created_at'])));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='activity'): ?>
<!-- =================== ACTIVITY LOGS WITH FILTERS =================== -->
<?php
$actFilters = [
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'user' => $_GET['filter_user'] ?? '',
    'company_id' => $filterCompanyId ?? '',
    'action' => $_GET['filter_action'] ?? '',
];
$actResult = get_activity_logs_filtered($pdo, $actFilters, 50, 0);
$distinctActions = get_distinct_actions($pdo);
?>
<div class="card" style="margin-bottom:16px">
    <div class="card-h"><h2><i class="fas fa-filter"></i> Filters</h2></div>
    <div class="card-b">
        <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
            <input type="hidden" name="section" value="activity">
            <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date From</label><input type="date" name="date_from" value="<?php echo h($actFilters['date_from']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
            <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date To</label><input type="date" name="date_to" value="<?php echo h($actFilters['date_to']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
            <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">User</label><input type="text" name="filter_user" value="<?php echo h($actFilters['user']);?>" placeholder="Username" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
            <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Action</label><select name="filter_action" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Actions</option><option value="LOGIN_SUCCESS" <?php echo $actFilters['action']==='LOGIN_SUCCESS'?'selected':'';?>>Login</option><option value="LOGIN_FAILED" <?php echo $actFilters['action']==='LOGIN_FAILED'?'selected':'';?>>Login Failed</option><option value="REGISTER_SUCCESS" <?php echo $actFilters['action']==='REGISTER_SUCCESS'?'selected':'';?>>Register</option><option value="PROFILE_UPDATED" <?php echo $actFilters['action']==='PROFILE_UPDATED'?'selected':'';?>>Profile Update</option><option value="PASSWORD_CHANGED" <?php echo $actFilters['action']==='PASSWORD_CHANGED'?'selected':'';?>>Password Change</option><option value="KOCH_QUOTATION_CREATED" <?php echo $actFilters['action']==='KOCH_QUOTATION_CREATED'?'selected':'';?>>KOCH Quotation</option><option value="TNB_QUOTATION_CREATED" <?php echo $actFilters['action']==='TNB_QUOTATION_CREATED'?'selected':'';?>>TNB Request</option><option value="CONTACT_MESSAGE_SENT" <?php echo $actFilters['action']==='CONTACT_MESSAGE_SENT'?'selected':'';?>>Contact Message</option><option value="SLIDER_CREATED" <?php echo $actFilters['action']==='SLIDER_CREATED'?'selected':'';?>>Slider Created</option><option value="SLIDER_UPDATED" <?php echo $actFilters['action']==='SLIDER_UPDATED'?'selected':'';?>>Slider Updated</option><option value="SLIDER_DELETED" <?php echo $actFilters['action']==='SLIDER_DELETED'?'selected':'';?>>Slider Deleted</option><option value="PRODUCT_CREATED" <?php echo $actFilters['action']==='PRODUCT_CREATED'?'selected':'';?>>Product Created</option><option value="PRODUCT_UPDATED" <?php echo $actFilters['action']==='PRODUCT_UPDATED'?'selected':'';?>>Product Updated</option><option value="PRODUCT_DELETED" <?php echo $actFilters['action']==='PRODUCT_DELETED'?'selected':'';?>>Product Deleted</option><option value="TRUCK_TYPE_CREATED" <?php echo $actFilters['action']==='TRUCK_TYPE_CREATED'?'selected':'';?>>Truck Type Created</option><option value="TRUCK_TYPE_UPDATED" <?php echo $actFilters['action']==='TRUCK_TYPE_UPDATED'?'selected':'';?>>Truck Type Updated</option><option value="TRUCK_TYPE_DELETED" <?php echo $actFilters['action']==='TRUCK_TYPE_DELETED'?'selected':'';?>>Truck Type Deleted</option><option value="DATA_EXPORTED" <?php echo $actFilters['action']==='DATA_EXPORTED'?'selected':'';?>>Data Exported</option><option value="DATA_EXPORT_FAILED" <?php echo $actFilters['action']==='DATA_EXPORT_FAILED'?'selected':'';?>>Export Failed</option></select></div>
            <div style="display:flex;gap:6px"><button type="submit" class="tb-btn primary" style="flex:1"><i class="fas fa-search"></i> Filter</button><a href="?section=activity" class="tb-btn ghost" style="flex:1;text-align:center"><i class="fas fa-times"></i> Clear</a></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-history"></i> Activity Logs</h2><span style="font-size:12px;color:var(--muted)"><?php echo number_format($actResult['total']);?> records</span></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Date</th><th>User</th><th>Company</th><th>Details</th></tr></thead><tbody>
    <?php if($actResult['data']===[]):?><tr class="empty"><td colspan="4">No activity logs found</td></tr>
    <?php else: foreach($actResult['data'] as $a):?><tr>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$a['created_at'])));?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)($a['username']??'System'));?></td>
        <td style="font-size:12px"><?php echo h((string)($a['company_code']??'-'));?></td>
        <td style="font-size:12px;color:var(--primary)"><?php echo h(admin_action_label((string)$a['action']));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='notifications'): ?>
<!-- =================== NOTIFICATIONS =================== -->
<div class="card">
    <div class="card-h">
        <h2><i class="fas fa-bell"></i> System Notifications</h2>
        <div style="display:flex;gap:8px;align-items:center">
            <?php if((int)$stats['unread_notifications']>0):?><span style="background:var(--primary);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px"><?php echo (int)$stats['unread_notifications'];?> unread</span>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline">
                <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
                <input type="hidden" name="entity" value="notification">
                <input type="hidden" name="action" value="mark_all_read">
                <input type="hidden" name="id" value="0">
                <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=notifications'));?>">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check-double"></i> Mark All Read</button>
            </form>
            <?php endif;?>
        </div>
    </div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Title</th><th>Message</th><th>User</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php
    $nSql = 'SELECT n.*, u.username FROM notifications n LEFT JOIN users u ON u.id = n.user_id' . ($filterCompanyId !== null ? ' WHERE n.company_id = :cid' : '') . ' ORDER BY n.created_at DESC LIMIT 50';
    $nStmt = $pdo->prepare($nSql);
    $nStmt->execute($filterCompanyId !== null ? [':cid' => $filterCompanyId] : []);
    $nList = $nStmt->fetchAll();
    if($nList===[]):?><tr class="empty"><td colspan="6">No notifications found</td></tr>
    <?php else: foreach($nList as $n):?><tr style="<?php echo !$n['is_read']?'background:#f0f9ff':'';?>">
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$n['title']);?></td>
        <td style="font-size:12px;color:var(--muted);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo h((string)$n['message']);?></td>
        <td style="font-size:12px"><?php echo h((string)($n['username']??'-'));?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$n['created_at'])));?></td>
        <td><?php echo !$n['is_read']?'<span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;background:#dbeafe;color:var(--info)">Unread</span>':'<span style="font-size:11px;color:var(--muted)">Read</span>';?></td>
        <td>
            <div class="act-btns">
                <?php if(!$n['is_read']):?>
                <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline">
                    <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
                    <input type="hidden" name="entity" value="notification">
                    <input type="hidden" name="action" value="mark_read">
                    <input type="hidden" name="id" value="<?php echo (int)$n['id'];?>">
                    <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=notifications'));?>">
                    <button type="submit" class="btn btn-xs btn-ghost" title="Mark Read"><i class="fas fa-check"></i></button>
                </form>
                <?php endif;?>
                <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete this notification?')">
                    <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
                    <input type="hidden" name="entity" value="notification">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$n['id'];?>">
                    <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=notifications'));?>">
                    <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='sliders'): ?>
<!-- =================== SLIDERS =================== -->
<?php $allSliders = get_all_sliders($pdo, $filterCompanyId); ?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-images"></i> Slider Contents</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allSliders);?> items</span><button class="btn btn-sm btn-primary" onclick="openModal('sliderModal')"><i class="fas fa-plus"></i> Add Slider</button></div></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>ID</th><th>Image</th><th>Title</th><th>Subtitle</th><th>Company</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if($allSliders===[]):?><tr class="empty"><td colspan="8">No sliders found</td></tr>
    <?php else: foreach($allSliders as $s):?><tr>
        <td style="font-size:12px;font-weight:600">#<?php echo (int)$s['id'];?></td>
        <td><?php if($s['image_url']):?><img src="<?php echo h(resolve_image_url((string)$s['image_url']));?>" style="width:60px;height:35px;object-fit:cover;border-radius:6px" alt=""><?php else:?><span style="color:var(--muted);font-size:11px">No image</span><?php endif;?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$s['title']);?></td>
        <td style="font-size:12px;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo h((string)($s['subtitle']??''));?></td>
        <td style="font-size:12px"><?php echo h((string)($s['company_name']??'-'));?></td>
        <td style="font-size:12px;text-align:center"><?php echo (int)$s['slide_order'];?></td>
        <td><?php echo admin_status_badge($s['is_active']?'active':'inactive');?></td>
        <td><div class="act-btns">
            <button class="btn btn-xs btn-ghost" onclick="openEditSlider(<?php echo (int)$s['id'];?>,'<?php echo h(addslashes((string)$s['title']));?>','<?php echo h(addslashes((string)($s['subtitle']??'')));?>',<?php echo (int)($s['company_id']??0);?>,<?php echo $s['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete this slider?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="slider"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$s['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=sliders'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='partners'): ?>
<!-- =================== PARTNERS =================== -->
<?php $allPartners = get_all_partners($pdo, $filterCompanyId); ?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-handshake"></i> Partners</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allPartners);?> partners</span><button class="btn btn-sm btn-primary" onclick="openModal('partnerModal')"><i class="fas fa-plus"></i> Add Partner</button></div></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Logo</th><th>Name</th><th>Company</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if($allPartners===[]):?><tr class="empty"><td colspan="5">No partners found</td></tr>
    <?php else: foreach($allPartners as $p):?><tr>
        <td><?php if($p['logo_url']):?><img src="<?php echo h(resolve_image_url((string)$p['logo_url']));?>" style="width:40px;height:40px;object-fit:contain;border-radius:6px;background:#f8fafc;padding:4px" alt=""><?php else:?><span style="color:var(--muted);font-size:11px">-</span><?php endif;?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$p['name']);?></td>
        <td style="font-size:12px"><?php echo h((string)($p['company_name']??'-'));?></td>
        <td><?php echo admin_status_badge($p['is_active']?'active':'inactive');?></td>
        <td><div class="act-btns">
            <button class="btn btn-xs btn-ghost" onclick="openEditPartner(<?php echo (int)$p['id'];?>,'<?php echo h(addslashes((string)$p['name']));?>',<?php echo (int)($p['company_id']??0);?>,<?php echo $p['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="partner"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$p['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=partners'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='products'): ?>
<!-- =================== PRODUCTS =================== -->
<?php $allProducts = get_all_products_admin($pdo, $filterCompanyId); ?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-boxes-stacked"></i> Products (KOCH)</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allProducts);?> products</span><button class="btn btn-sm btn-primary" onclick="openModal('productModal')"><i class="fas fa-plus"></i> Add Product</button></div></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if($allProducts===[]):?><tr class="empty"><td colspan="5">No products found</td></tr>
    <?php else: foreach($allProducts as $p):?><tr>
        <td><?php if($p['image_url']):?><img src="<?php echo h(resolve_image_url((string)$p['image_url']));?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px" alt=""><?php else:?><span style="color:var(--muted);font-size:11px">-</span><?php endif;?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$p['name']);?></td>
        <td style="font-size:12px"><?php echo h(ucfirst((string)($p['category']??'')));?></td>
        <td><?php echo admin_status_badge($p['is_active']?'active':'inactive');?></td>
        <td><div class="act-btns">
            <button class="btn btn-xs btn-ghost" onclick="openEditProduct(<?php echo (int)$p['id'];?>,'<?php echo h(addslashes((string)$p['name']));?>','<?php echo h(addslashes((string)($p['description']??'')));?>','<?php echo h(addslashes((string)($p['category']??'')));?>',<?php echo $p['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="product"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$p['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=products'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='featured_products'): ?>
<!-- =================== FEATURED PRODUCTS =================== -->
<?php $allFeaturedProducts = get_all_featured_products_admin($pdo, $filterCompanyId); ?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-star"></i> Featured Products</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allFeaturedProducts);?> items</span><button class="btn btn-sm btn-primary" onclick="openModal('featuredProductModal')"><i class="fas fa-plus"></i> Add Featured Product</button></div></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Image</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if($allFeaturedProducts===[]):?><tr class="empty"><td colspan="4">No featured products found</td></tr>
    <?php else: foreach($allFeaturedProducts as $fp):?><tr>
        <td><?php if($fp['image_url']):?><img src="<?php echo h(resolve_image_url((string)$fp['image_url']));?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px" alt=""><?php else:?><span style="color:var(--muted);font-size:11px">-</span><?php endif;?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$fp['name']);?></td>
        <td><?php echo admin_status_badge($fp['is_active']?'active':'inactive');?></td>
        <td><div class="act-btns">
            <button class="btn btn-xs btn-ghost" onclick="openEditFeaturedProduct(<?php echo (int)$fp['id'];?>,'<?php echo h(addslashes((string)$fp['name']));?>','<?php echo h(addslashes((string)($fp['description']??'')));?>',<?php echo $fp['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="featured_product"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$fp['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=featured_products'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='truck_types_index'): ?>
<!-- =================== TRUCK TYPES INDEX =================== -->
<?php 
// Block KOCH users from accessing truck types index
if ($user['company_id'] == get_company_id_by_code($pdo, 'KOCH') && $user['role'] !== 'super_admin') {
    header('Location: ' . project_url('admin/dashboard.php?section=overview'));
    exit;
}
$allTrucks = get_all_truck_types_admin($pdo, $filterCompanyId); 
?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-truck-pickup"></i> Truck Types Index (TNB)</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allTrucks);?> types</span><button class="btn btn-sm btn-primary" onclick="openModal('truckModal')"><i class="fas fa-plus"></i> Add Truck Type</button></div></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Capacity</th><th>Order</th><th>Status</th><th>Index Display</th><th>Actions</th></tr></thead><tbody>
    <?php if($allTrucks===[]):?><tr class="empty"><td colspan="8">No truck types found</td></tr>
    <?php else: foreach($allTrucks as $t):?><tr>
        <td style="font-size:12px;font-weight:600">#<?php echo (int)$t['id'];?></td>
        <td><?php if($t['image_url']):?><img src="<?php echo h(resolve_image_url((string)$t['image_url']));?>" style="width:50px;height:35px;object-fit:cover;border-radius:6px" alt=""><?php else:?><span style="color:var(--muted);font-size:11px">-</span><?php endif;?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$t['name']);?></td>
        <td style="font-size:12px"><?php echo h((string)($t['capacity']??'-'));?></td>
        <td style="font-size:12px;text-align:center"><?php echo (int)$t['display_order'];?></td>
        <td><?php echo admin_status_badge($t['is_active']?'active':'inactive');?></td>
        <td><span style="font-size:11px;color:var(--muted)">Will show on TNB index page</span></td>
        <td><div class="act-btns">
            <button class="btn btn-xs btn-ghost" onclick="openEditTruck(<?php echo (int)$t['id'];?>,'<?php echo h(addslashes((string)$t['name']));?>','<?php echo h(addslashes((string)($t['description']??'')));?>','<?php echo h(addslashes((string)($t['capacity']??'')));?>',<?php echo $t['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="truck_type"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$t['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=truck_types_index'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='truck_types'): ?>
<!-- =================== TRUCK TYPES =================== -->
<?php 
// Block KOCH users from accessing truck types
if ($user['company_id'] == get_company_id_by_code($pdo, 'KOCH') && $user['role'] !== 'super_admin') {
    header('Location: ' . project_url('admin/dashboard.php?section=overview'));
    exit;
}
$allTrucks = get_all_truck_types_admin($pdo, $filterCompanyId); 
?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-truck-moving"></i> Truck Types (TNB)</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allTrucks);?> types</span><button class="btn btn-sm btn-primary" onclick="openModal('truckModal')"><i class="fas fa-plus"></i> Add Truck Type</button></div></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Capacity</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if($allTrucks===[]):?><tr class="empty"><td colspan="7">No truck types found</td></tr>
    <?php else: foreach($allTrucks as $t):?><tr>
        <td style="font-size:12px;font-weight:600">#<?php echo (int)$t['id'];?></td>
        <td><?php if($t['image_url']):?><img src="<?php echo h(resolve_image_url((string)$t['image_url']));?>" style="width:50px;height:35px;object-fit:cover;border-radius:6px" alt=""><?php else:?><span style="color:var(--muted);font-size:11px">-</span><?php endif;?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$t['name']);?></td>
        <td style="font-size:12px"><?php echo h((string)($t['capacity']??'-'));?></td>
        <td style="font-size:12px;text-align:center"><?php echo (int)$t['display_order'];?></td>
        <td><?php echo admin_status_badge($t['is_active']?'active':'inactive');?></td>
        <td><div class="act-btns">
            <button class="btn btn-xs btn-ghost" onclick="openEditTruck(<?php echo (int)$t['id'];?>,'<?php echo h(addslashes((string)$t['name']));?>','<?php echo h(addslashes((string)($t['description']??'')));?>','<?php echo h(addslashes((string)($t['capacity']??'')));?>',<?php echo $t['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="truck_type"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$t['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=truck_types'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='truck_cards'): ?>
<!-- =================== TRUCK CARDS (TNB Index Display) =================== -->
<?php 
// Block KOCH users from accessing truck cards
if ($user['company_id'] == get_company_id_by_code($pdo, 'KOCH') && $user['role'] !== 'super_admin') {
    header('Location: ' . project_url('admin/dashboard.php?section=overview'));
    exit;
}
$allTruckCards = get_all_truck_types_admin($pdo, $filterCompanyId); 
?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-id-card"></i> Truck Cards (TNB)</h2><div style="display:flex;gap:8px;align-items:center"><span style="font-size:12px;color:var(--muted)"><?php echo count($allTruckCards);?> cards</span><button class="btn btn-sm btn-primary" onclick="openModal('truckModal')"><i class="fas fa-plus"></i> Add Truck Card</button></div></div>
    <div class="card-b" style="padding:12px 20px;background:#eff6ff;border-bottom:1px solid var(--border)">
        <p style="font-size:12px;color:#1e40af;margin:0"><i class="fas fa-info-circle"></i> <strong>Truck Cards</strong> จะแสดงที่หน้าเว็บ TNB ในส่วน "ประเภทรถ" — ข้อมูลที่เพิ่ม/แก้ไขที่นี่จะปรากฏบน TNB website โดยตรง</p>
    </div>
    <div class="card-b" style="padding:20px">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
        <?php if($allTruckCards===[]): ?>
            <p style="text-align:center;color:var(--muted);padding:40px 0;font-size:13px;grid-column:1/-1">No truck cards found. Click "Add Truck Card" to create one.</p>
        <?php else: foreach($allTruckCards as $tc): ?>
            <div style="background:var(--bg);border-radius:12px;overflow:hidden;transition:transform .2s,box-shadow .2s;border:1.5px solid var(--border)" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <?php if($tc['image_url']):?><img src="<?php echo h(resolve_image_url((string)$tc['image_url']));?>" style="width:100%;height:130px;object-fit:cover" alt=""><?php else:?><div style="width:100%;height:130px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:12px">No Image</div><?php endif;?>
                <div style="padding:12px">
                    <h4 style="font-size:13px;font-weight:700;margin:0 0 4px"><?php echo h((string)$tc['name']);?></h4>
                    <p style="font-size:11px;color:var(--muted);margin:0 0 8px"><?php echo h((string)($tc['capacity']??'-'));?></p>
                    <div style="display:flex;gap:4px">
                        <?php echo admin_status_badge($tc['is_active']?'active':'inactive');?>
                    </div>
                    <div style="display:flex;gap:4px;margin-top:8px">
                        <button class="btn btn-xs btn-ghost" onclick="openEditTruck(<?php echo (int)$tc['id'];?>,'<?php echo h(addslashes((string)$tc['name']));?>','<?php echo h(addslashes((string)($tc['description']??'')));?>','<?php echo h(addslashes((string)($tc['image_url']??'')));?>','<?php echo h(addslashes((string)($tc['capacity']??'')));?>',<?php echo (int)$tc['display_order'];?>,<?php echo $tc['is_active']?1:0;?>)"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="truck_type"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$tc['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=truck_cards'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<?php elseif($section==='email_templates'): ?>
<!-- =================== NOTIFICATION EMAILS =================== -->
<?php 
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('admin_notify_email_koch', 'admin_notify_email_tnb', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass')");
$stmt->execute();
$emails = [];
$smtp = ['host'=>'smtp.gmail.com', 'port'=>'587', 'user'=>'', 'pass'=>''];
foreach($stmt->fetchAll() as $row) {
    if ($row['setting_key'] === 'admin_notify_email_koch') $emails['koch'] = $row['setting_value'];
    if ($row['setting_key'] === 'admin_notify_email_tnb') $emails['tnb'] = $row['setting_value'];
    if ($row['setting_key'] === 'smtp_host') $smtp['host'] = $row['setting_value'];
    if ($row['setting_key'] === 'smtp_port') $smtp['port'] = $row['setting_value'];
    if ($row['setting_key'] === 'smtp_user') $smtp['user'] = $row['setting_value'];
    if ($row['setting_key'] === 'smtp_pass') $smtp['pass'] = $row['setting_value'];
}
?>

<div style="display:flex; flex-direction:column; gap:20px; align-items:center;">

<div class="card" style="width: 100%; max-width: 600px; margin: 0;">
    <div class="card-h"><h2><i class="fas fa-envelope"></i> Notification Emails</h2></div>
    <div class="card-b" style="padding: 20px;">
        <p style="font-size: 13px; color: var(--muted); margin-bottom: 20px;">
            กรอกอีเมลที่แอดมินต้องการให้ระบบแจ้งเตือนเมื่อมีลูกค้าขอใบเสนอราคา (สามารถใส่ได้หลายอีเมลโดยคั่นด้วย ,)
        </p>
        <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>">
            <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>">
            <input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
            <input type="hidden" name="entity" value="system_settings">
            <input type="hidden" name="action" value="update_admin_emails">
            <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=email_templates'));?>">
            
            <?php if ($companyMode === 'all' || $companyMode === 'koch'): ?>
            <div class="fm-group">
                <label style="color: #c41f1f;">KOCH Notification Email</label>
                <input type="text" name="admin_notify_email_koch" class="fm-input" value="<?php echo h($emails['koch'] ?? '');?>" placeholder="e.g. admin@koch.com, sales@koch.com">
            </div>
            <?php endif; ?>

            <?php if ($companyMode === 'all' || $companyMode === 'tnb'): ?>
            <div class="fm-group" style="margin-top:20px;">
                <label style="color: #0d2d6b;">TNB Notification Email</label>
                <input type="text" name="admin_notify_email_tnb" class="fm-input" value="<?php echo h($emails['tnb'] ?? '');?>" placeholder="e.g. admin@tnb.com, info@tnb.com">
            </div>
            <?php endif; ?>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-save"></i> Save Emails</button>
            </div>
        </form>
    </div>
</div>

<?php if ($companyMode === 'all'): ?>
<div class="card" style="width: 100%; max-width: 600px; margin: 0;">
    <div class="card-h"><h2><i class="fas fa-server"></i> SMTP Configuration</h2></div>
    <div class="card-b" style="padding: 20px; background:#f8fafc;">
        <p style="font-size: 13px; color: var(--muted); margin-bottom: 20px;">
            ตั้งค่าระบบส่งอีเมลกลาง (เช่น ผู้ส่งอีเมล noreply) แนะนำให้ใช้รหัสผ่านแบบ <strong>App Password</strong> ของ Gmail เพื่อความปลอดภัย
        </p>
        <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>">
            <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>">
            <input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
            <input type="hidden" name="entity" value="system_settings">
            <input type="hidden" name="action" value="update_smtp_config">
            <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=email_templates'));?>">
            
            <div class="fm-row">
                <div class="fm-group">
                    <label>SMTP Host</label>
                    <input type="text" name="smtp_host" class="fm-input" value="<?php echo h($smtp['host']);?>" placeholder="smtp.gmail.com" required>
                </div>
                <div class="fm-group" style="flex: 0 0 100px;">
                    <label>Port</label>
                    <input type="number" name="smtp_port" class="fm-input" value="<?php echo h($smtp['port']);?>" placeholder="587" required>
                </div>
            </div>
            
            <div class="fm-group">
                <label>SMTP Username (Email)</label>
                <input type="email" name="smtp_user" class="fm-input" value="<?php echo h($smtp['user']);?>" placeholder="your-email@gmail.com">
            </div>
            
            <div class="fm-group">
                <label>SMTP Password (App Password)</label>
                <input type="password" name="smtp_pass" class="fm-input" value="<?php echo h($smtp['pass']);?>" placeholder="••••••••••••••••">
                <small style="font-size: 11px; color: #64748b; margin-top:4px; display:block;">รหัสผ่านของ Email หลัก หรือ Gmail App Password 16 หลัก</small>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-secondary" style="width: 100%; background:var(--secondary); color:#fff;"><i class="fas fa-save"></i> Save SMTP Config</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

</div>

<?php elseif($section==='contact_messages'): ?>
<!-- =================== CONTACT MESSAGES =================== -->
<?php $allContacts = get_all_contact_messages($pdo, $filterCompanyId); ?>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-envelope-open-text"></i> Contact Messages</h2><span style="font-size:12px;color:var(--muted)"><?php echo count($allContacts);?> messages</span></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Company</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>
    <?php if($allContacts===[]):?><tr class="empty"><td colspan="8">No contact messages found</td></tr>
    <?php else: foreach($allContacts as $cm):?><tr>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$cm['name']);?></td>
        <td style="font-size:12px"><?php echo h((string)$cm['email']);?></td>
        <td style="font-size:12px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo h((string)($cm['subject']??'-'));?></td>
        <td style="font-size:11px;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo h((string)($cm['message']??'-'));?></td>
        <td style="font-size:12px"><?php echo h((string)($cm['company_name']??'-'));?></td>
        <td><?php echo admin_status_badge((string)($cm['status']??'new'));?></td>
        <td style="font-size:11px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$cm['created_at'])));?></td>
        <td><div class="act-btns">
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="contact_message"><input type="hidden" name="action" value="update_status"><input type="hidden" name="id" value="<?php echo (int)$cm['id'];?>"><input type="hidden" name="status" value="read"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=contact_messages'));?>"><button type="submit" class="btn btn-xs btn-ghost" title="Mark Read"><i class="fas fa-eye"></i></button></form>
            <form method="POST" action="<?php echo h(project_url('admin/api/crud/handler.php'));?>" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>"><input type="hidden" name="entity" value="contact_message"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$cm['id'];?>"><input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=contact_messages'));?>"><button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></form>
        </div></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='export_data'): ?>
<!-- =================== EXPORT DATA =================== -->
<?php
// Get current export type
$exportType = $_GET['export_type'] ?? '';
$filterMode = $_GET['filter_mode'] ?? 'all'; // 'all' or 'filter'

// Export filters for each type
$expFilters = [
    'users' => [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'company_id' => $filterCompanyId ?? '',
        'role' => $_GET['filter_role'] ?? '',
        'status' => $_GET['filter_status'] ?? '',
    ],
    'quotations' => [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'company_id' => $filterCompanyId ?? '',
        'user' => $_GET['filter_user'] ?? '',
        'status' => $_GET['filter_status'] ?? '',
        'product_type' => $_GET['filter_product_type'] ?? '',
    ],
    'transport' => [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'company_id' => $filterCompanyId ?? '',
        'user' => $_GET['filter_user'] ?? '',
        'status' => $_GET['filter_status'] ?? '',
        'service_type' => $_GET['filter_service_type'] ?? '',
    ],
    'activity' => [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'company_id' => $filterCompanyId ?? '',
        'user' => $_GET['filter_user'] ?? '',
        'action' => $_GET['filter_action'] ?? '',
    ],
    'contacts' => [
        'date_from' => $_GET['date_from'] ?? '',
        'date_to' => $_GET['date_to'] ?? '',
        'company_id' => $filterCompanyId ?? '',
        'status' => $_GET['filter_status'] ?? '',
    ],
];
?>

<!-- Export Type Selection -->
<div class="card" style="margin-bottom:16px">
    <div class="card-h"><h2><i class="fas fa-file-export"></i> Select Export Type</h2></div>
    <div class="card-b">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px">
            <button class="btn btn-primary" onclick="selectExportType('users')" id="btn-users" style="width:100%"><i class="fas fa-users"></i> Users</button>
            <button class="btn btn-primary" onclick="selectExportType('quotations')" id="btn-quotations" style="width:100%"><i class="fas fa-box"></i> Quotations</button>
            <button class="btn btn-primary" onclick="selectExportType('transport')" id="btn-transport" style="width:100%"><i class="fas fa-truck"></i> Transport</button>
            <button class="btn btn-primary" onclick="selectExportType('activity')" id="btn-activity" style="width:100%"><i class="fas fa-history"></i> Activity Logs</button>
            <button class="btn btn-primary" onclick="selectExportType('contacts')" id="btn-contacts" style="width:100%"><i class="fas fa-envelope"></i> Contact Messages</button>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div id="filterSection" style="display:none">
    <div class="card" style="margin-bottom:16px">
        <div class="card-h"><h2><i class="fas fa-filter"></i> Export Filters</h2></div>
        <div class="card-b">
            <div style="display:flex;gap:10px;margin-bottom:15px">
                <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
                    <input type="radio" name="export_mode" value="all" <?php echo $filterMode==='all'?'checked':'';?> onchange="toggleFilterMode('all')">
                    <span style="font-size:13px">Export All Data</span>
                </label>
                <label style="display:flex;align-items:center;gap:5px;cursor:pointer">
                    <input type="radio" name="export_mode" value="filter" <?php echo $filterMode==='filter'?'checked':'';?> onchange="toggleFilterMode('filter')">
                    <span style="font-size:13px">Apply Filters</span>
                </label>
            </div>
            
            <div id="filterFields" style="display:<?php echo $filterMode==='filter'?'block':'none';?>">
                <!-- Filter fields will be dynamically loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div id="exportOptions" style="display:none">
    <div class="card">
        <div class="card-h"><h2><i class="fas fa-download"></i> Export Format</h2></div>
        <div class="card-b">
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <button class="btn btn-success" onclick="exportWithFilters('csv')"><i class="fas fa-file-csv"></i> CSV</button>
                <button class="btn btn-success" onclick="exportWithFilters('excel')"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-success" onclick="exportWithFilters('pdf')"><i class="fas fa-file-pdf"></i> PDF</button>
                <button class="btn btn-success" onclick="exportWithFilters('word')"><i class="fas fa-file-word"></i> Word</button>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-file-export"></i> Export Data</h2><p style="font-size:12px;color:var(--muted)">Export filtered data in various formats</p></div>
    <div class="card-b">
        <div class="grid-3">
            <div class="card">
                <div class="card-h"><h3><i class="fas fa-users"></i> Users</h3></div>
                <div class="card-b">
                    <p style="font-size:13px;color:var(--muted)">Export all users data</p>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-primary" onclick="exportData('users','csv')">📊 CSV</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('users','excel')">📈 Excel</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('users','pdf')">📄 PDF</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-h"><h3><i class="fas fa-box"></i> Quotations</h3></div>
                <div class="card-b">
                    <p style="font-size:13px;color:var(--muted)">Export KOCH quotations</p>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-primary" onclick="exportData('quotations','csv')">📊 CSV</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('quotations','excel')">📈 Excel</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('quotations','pdf')">📄 PDF</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-h"><h3><i class="fas fa-truck"></i> Transport Requests</h3></div>
                <div class="card-b">
                    <p style="font-size:13px;color:var(--muted)">Export TNB transport requests</p>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-primary" onclick="exportData('transport','csv')">📊 CSV</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('transport','excel')">📈 Excel</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('transport','pdf')">📄 PDF</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-h"><h3><i class="fas fa-history"></i> Activity Logs</h3></div>
                <div class="card-b">
                    <p style="font-size:13px;color:var(--muted)">Export system activity logs</p>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-primary" onclick="exportData('activity','csv')">📊 CSV</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('activity','excel')">📈 Excel</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('activity','pdf')">📄 PDF</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-h"><h3><i class="fas fa-envelope"></i> Contact Messages</h3></div>
                <div class="card-b">
                    <p style="font-size:13px;color:var(--muted)">Export contact form messages</p>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-primary" onclick="exportData('contacts','csv')">📊 CSV</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('contacts','excel')">📈 Excel</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('contacts','pdf')">📄 PDF</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-h"><h3><i class="fas fa-chart-bar"></i> Reports</h3></div>
                <div class="card-b">
                    <p style="font-size:13px;color:var(--muted)">Export system reports</p>
                    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-sm btn-primary" onclick="exportData('reports','csv')">📊 CSV</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('reports','excel')">📈 Excel</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('reports','pdf')">📄 PDF</button>
                        <button class="btn btn-sm btn-primary" onclick="exportData('reports','word')">📋 Word</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif($section==='settings'): ?>
<!-- =================== SETTINGS =================== -->
<div class="grid-2">
    <div class="card">
        <div class="card-h"><h2><i class="fas fa-database"></i> System Information</h2></div>
        <div class="card-b">
            <div style="display:grid;gap:12px">
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px"><span style="font-size:13px;color:var(--muted)">Database</span><strong style="font-size:13px"><?php echo h(DB_NAME);?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px"><span style="font-size:13px;color:var(--muted)">PHP Version</span><strong style="font-size:13px"><?php echo h(PHP_VERSION);?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px"><span style="font-size:13px;color:var(--muted)">Server</span><strong style="font-size:13px"><?php echo h((string)($_SERVER['SERVER_SOFTWARE']??'Unknown'));?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px"><span style="font-size:13px;color:var(--muted)">Session Lifetime</span><strong style="font-size:13px"><?php echo number_format(SESSION_LIFETIME/3600,1);?>h</strong></div>
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px"><span style="font-size:13px;color:var(--muted)">Max Upload</span><strong style="font-size:13px"><?php echo number_format(UPLOAD_MAX_SIZE/1048576,0);?>MB</strong></div>
                <div style="display:flex;justify-content:space-between;padding:12px;background:var(--bg);border-radius:10px"><span style="font-size:13px;color:var(--muted)">Debug Mode</span><strong style="font-size:13px"><?php echo APP_DEBUG?'ON':'OFF';?></strong></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-h"><h2><i class="fas fa-link"></i> Quick Links</h2></div>
        <div class="card-b">
            <div style="display:grid;gap:10px">
                <a href="<?php echo h(project_url('koch/main/index.php'));?>" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'"><i class="fas fa-globe" style="color:#ED2A2A"></i> KOCH Website</a>
                <a href="<?php echo h(project_url('tnb/main/index.php'));?>" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'"><i class="fas fa-globe" style="color:#0d2d6b"></i> TNB Website</a>
                <a href="<?php echo h(project_url('koch/main/user.php'));?>" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'"><i class="fas fa-user" style="color:#ED2A2A"></i> KOCH User Page</a>
                <a href="<?php echo h(project_url('tnb/main/user.php'));?>" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'"><i class="fas fa-user" style="color:#0d2d6b"></i> TNB User Page</a>
                <a href="<?php echo h(project_url('koch/main/quotation.php'));?>" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'"><i class="fas fa-box" style="color:#ED2A2A"></i> KOCH Quotation Form</a>
                <a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;text-decoration:none;color:var(--text);font-size:13px;font-weight:600;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='var(--bg)'"><i class="fas fa-truck" style="color:#0d2d6b"></i> TNB Quotation Form</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    </div>
</div>
<!-- =================== MODAL FORMS =================== -->
<?php $crudUrl = project_url('admin/api/crud/handler.php'); ?>

<!-- User Edit Modal -->
<div class="modal-overlay" id="userModal" onclick="if(event.target===this)closeModal('userModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-user-edit"></i> Edit User</h3><button class="modal-close" onclick="closeModal('userModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="user">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=users'));?>">
        <input type="hidden" name="id" id="um_id">
        <div class="modal-body">
            <div class="fm-group"><label>Username</label><input type="text" class="fm-input" id="um_name" disabled></div>
            <div class="fm-row">
                <div class="fm-group"><label>Role<span>*</span></label><select name="role" id="um_role" class="fm-input" required><option value="user">User</option><option value="manager">Manager</option><option value="admin">Admin</option><option value="super_admin">Super Admin</option></select></div>
                <div class="fm-group"><label>Status<span>*</span></label><select name="status" id="um_status" class="fm-input" required><option value="active">Active</option><option value="inactive">Inactive</option><option value="suspended">Suspended</option></select></div>
            </div>
            <div style="background:var(--bg);border-radius:10px;padding:14px;margin-top:8px">
                <p style="font-size:11px;font-weight:600;color:var(--muted);margin:0 0 8px;text-transform:uppercase;letter-spacing:.5px">Role Description</p>
                <div style="font-size:12px;line-height:1.7;color:var(--text)">
                    <div><strong style="color:#92400e">Super Admin</strong> — Full access to all companies, users, settings, and system configuration</div>
                    <div><strong style="color:#1e40af">Admin</strong> — Manage own company's users, content, quotations, and emails</div>
                    <div><strong style="color:#3730a3">Manager</strong> — View own team's data, limited content access</div>
                    <div><strong style="color:#475569">User</strong> — View own data only, submit quotations</div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" onclick="closeModal('userModal')">Cancel</button>
            <button type="submit" name="action" value="update_role" class="btn btn-primary"><i class="fas fa-save"></i> Update Role</button>
            <button type="submit" name="action" value="update_status" class="btn btn-primary" style="background:var(--success)"><i class="fas fa-toggle-on"></i> Update Status</button>
        </div>
    </form>
</div></div>

<!-- Slider Modal -->
<div class="modal-overlay" id="sliderModal" onclick="if(event.target===this)closeModal('sliderModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-images"></i> <span id="sm_title">Add Slider</span></h3><button class="modal-close" onclick="closeModal('sliderModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="slider">
        <input type="hidden" name="action" id="sm_action" value="create">
        <input type="hidden" name="id" id="sm_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=sliders'));?>">
        <div class="modal-body">
            <div class="fm-group"><label>Title<span>*</span></label><input type="text" name="title" id="sm_name" class="fm-input" required maxlength="255"></div>
            <div class="fm-group"><label>Subtitle</label><input type="text" name="subtitle" id="sm_subtitle" class="fm-input" maxlength="500"></div>
            <div class="fm-group"><label>OR Upload Image</label><input type="file" name="image_file" id="sm_file" class="fm-input" accept="image/jpeg,image/png,image/webp"><small style="color:var(--muted);font-size:11px">JPG, PNG, WEBP up to 10MB.</small></div>
            <div class="fm-group">
                <div class="fm-group"><label>Company<span>*</span></label><select name="company_id" id="sm_company" class="fm-input" required><option value="<?php echo $kochId;?>">KOCH</option><option value="<?php echo $tnbId;?>">TNB</option></select></div>
            </div>
            <div class="fm-check"><input type="checkbox" name="is_active" id="sm_active" value="1" checked><label for="sm_active">Active</label></div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('sliderModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<!-- Partner Modal -->
<div class="modal-overlay" id="partnerModal" onclick="if(event.target===this)closeModal('partnerModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-handshake"></i> <span id="pm_title">Add Partner</span></h3><button class="modal-close" onclick="closeModal('partnerModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="partner">
        <input type="hidden" name="action" id="pm_action" value="create">
        <input type="hidden" name="id" id="pm_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=partners'));?>">
        <div class="modal-body">
            <div class="fm-group"><label>Partner Name<span>*</span></label><input type="text" name="name" id="pm_name" class="fm-input" required maxlength="255"></div>
            <div class="fm-group"><label>OR Upload Logo</label><input type="file" name="logo_file" id="pm_file" class="fm-input" accept="image/jpeg,image/png,image/webp"><small style="color:var(--muted);font-size:11px">JPG, PNG, WEBP up to 10MB.</small></div>
            <div class="fm-group">
                <div class="fm-group"><label>Company<span>*</span></label><select name="company_id" id="pm_company" class="fm-input" required><option value="<?php echo $kochId;?>">KOCH</option><option value="<?php echo $tnbId;?>">TNB</option></select></div>
            </div>
            <div class="fm-check"><input type="checkbox" name="is_active" id="pm_active" value="1" checked><label for="pm_active">Active</label></div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('partnerModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<!-- Product Modal -->
<div class="modal-overlay" id="productModal" onclick="if(event.target===this)closeModal('productModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-boxes-stacked"></i> <span id="prd_title">Add Product</span></h3><button class="modal-close" onclick="closeModal('productModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="product">
        <input type="hidden" name="action" id="prd_action" value="create">
        <input type="hidden" name="id" id="prd_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=products'));?>">
        <div class="modal-body">
            <div class="fm-group"><label>Product Name<span>*</span></label><input type="text" name="name" id="prd_name" class="fm-input" required maxlength="255"></div>
            <div class="fm-group"><label>Description</label><textarea name="description" id="prd_desc" class="fm-input"></textarea></div>
            <div class="fm-group"><label>OR Upload Image</label><input type="file" name="image_file" id="prd_file" class="fm-input" accept="image/jpeg,image/png,image/webp"><small style="color:var(--muted);font-size:11px">JPG, PNG, WEBP up to 10MB.</small></div>
            <div class="fm-group"><label>Category<span>*</span></label><select name="category" id="prd_cat" class="fm-input" required><option value="">Select Category</option><option value="mail">กล่องกระดาษ</option><option value="corrugated">บรรจุภัณฑ์ไม้</option><option value="diecut">บรรจุภัณฑ์พลาสติก</option><option value="accessory">บรรจุภัณฑ์เหล็ก</option></select></div>
            <div class="fm-check"><input type="checkbox" name="is_active" id="prd_active" value="1" checked><label for="prd_active">Active</label></div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('productModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<!-- Truck Type Modal -->
<div class="modal-overlay" id="truckModal" onclick="if(event.target===this)closeModal('truckModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-truck-moving"></i> <span id="tt_title">Add Truck Type</span></h3><button class="modal-close" onclick="closeModal('truckModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="truck_type">
        <input type="hidden" name="action" id="tt_action" value="create">
        <input type="hidden" name="id" id="tt_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=truck_types'));?>">
        <div class="modal-body">
            <div class="fm-group"><label>Truck Name<span>*</span></label><input type="text" name="name" id="tt_name" class="fm-input" required maxlength="255"></div>
            <div class="fm-group"><label>Description</label><textarea name="description" id="tt_desc" class="fm-input"></textarea></div>
            <div class="fm-group"><label>OR Upload Image</label><input type="file" name="image_file" id="tt_file" class="fm-input" accept="image/jpeg,image/png,image/webp"><small style="color:var(--muted);font-size:11px">JPG, PNG, WEBP up to 10MB.</small></div>
            <div class="fm-group">
                <div class="fm-group"><label>Capacity</label><input type="text" name="capacity" id="tt_cap" class="fm-input" maxlength="100" placeholder="e.g. 10 tons"></div>
            </div>
            <div class="fm-check"><input type="checkbox" name="is_active" id="tt_active" value="1" checked><label for="tt_active">Active</label></div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('truckModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<!-- Featured Product Modal -->
<div class="modal-overlay" id="featuredProductModal" onclick="if(event.target===this)closeModal('featuredProductModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-star"></i> <span id="fp_title">Add Featured Product</span></h3><button class="modal-close" onclick="closeModal('featuredProductModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="featured_product">
        <input type="hidden" name="action" id="fp_action" value="create">
        <input type="hidden" name="id" id="fp_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=featured_products'));?>">
        <div class="modal-body">
            <div class="fm-group"><label>Product Name<span>*</span></label><input type="text" name="name" id="fp_name" class="fm-input" required maxlength="255"></div>
            <div class="fm-group"><label>Description</label><textarea name="description" id="fp_desc" class="fm-input"></textarea></div>
            <div class="fm-group"><label>OR Upload Image</label><input type="file" name="image_file" id="fp_file" class="fm-input" accept="image/jpeg,image/png,image/webp"><small style="color:var(--muted);font-size:11px">JPG, PNG, WEBP up to 10MB.</small></div>
            <div class="fm-check"><input type="checkbox" name="is_active" id="fp_active" value="1" checked><label for="fp_active">Active</label></div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('featuredProductModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<!-- Email Template Modal -->
<div class="modal-overlay" id="emailTplModal" onclick="if(event.target===this)closeModal('emailTplModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-file-alt"></i> <span id="etm_title">Add Email Template</span></h3><button class="modal-close" onclick="closeModal('emailTplModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="email_template">
        <input type="hidden" name="action" id="etm_action" value="create">
        <input type="hidden" name="id" id="etm_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=email_templates'));?>">
        <div class="modal-body">
            <div class="fm-group"><label>Template Name<span>*</span></label><input type="text" name="name" id="etm_name" class="fm-input" required maxlength="100" placeholder="e.g. quotation_confirmation"></div>
            <div class="fm-group"><label>Subject<span>*</span></label><input type="text" name="subject" id="etm_subject" class="fm-input" required maxlength="255"></div>
            <div class="fm-group"><label>HTML Content</label><textarea name="html_content" id="etm_html" class="fm-input" style="min-height:120px" placeholder="Email body HTML..."></textarea></div>
            <div class="fm-group"><label>Variables</label><input type="text" name="variables" id="etm_vars" class="fm-input" placeholder="e.g. {{name}}, {{email}}, {{quotation_number}}"></div>
            <div class="fm-row">
                <div class="fm-group"><label>Company</label><select name="company_id" id="etm_company" class="fm-input"><option value="">Global (All)</option><option value="<?php echo $kochId;?>">KOCH</option><option value="<?php echo $tnbId;?>">TNB</option></select></div>
                <div class="fm-check" style="margin-top:24px"><input type="checkbox" name="is_active" id="etm_active" value="1" checked><label for="etm_active">Active</label></div>
            </div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('emailTplModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<!-- Email Recipient Modal -->
<div class="modal-overlay" id="emailRecModal" onclick="if(event.target===this)closeModal('emailRecModal')">
<div class="modal">
    <div class="modal-head"><h3><i class="fas fa-at"></i> <span id="erm_title">Add Email Recipient</span></h3><button class="modal-close" onclick="closeModal('emailRecModal')">&times;</button></div>
    <form method="POST" action="<?php echo h($crudUrl);?>" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken);?>"><input type="hidden" name="company_mode" value="<?php echo h($companyMode);?>">
        <input type="hidden" name="entity" value="email_recipient">
        <input type="hidden" name="action" id="erm_action" value="create">
        <input type="hidden" name="id" id="erm_id" value="0">
        <input type="hidden" name="redirect_back" value="<?php echo h(project_url('admin/dashboard.php?section=email_recipients'));?>">
        <div class="modal-body">
            <div style="background:#eff6ff;border-radius:10px;padding:14px;margin-bottom:16px">
                <p style="font-size:12px;color:#1e40af;margin:0"><i class="fas fa-info-circle"></i> Add recipient emails that should receive notifications for specific events. For example, add your sales team email to receive new quotation alerts.</p>
            </div>
            <div class="fm-group"><label>Event Type<span>*</span></label><select name="event_type" id="erm_event" class="fm-input" required>
                <option value="new_quotation">New Quotation</option>
                <option value="quotation_approved">Quotation Approved</option>
                <option value="new_contact">New Contact Message</option>
                <option value="login_failed">Login Failed Alert</option>
                <option value="new_registration">New User Registration</option>
                <option value="system_alert">System Alert</option>
            </select></div>
            <div class="fm-row">
                <div class="fm-group"><label>Recipient Name<span>*</span></label><input type="text" name="recipient_name" id="erm_name" class="fm-input" required maxlength="255" placeholder="e.g. Sales Team"></div>
                <div class="fm-group"><label>Email<span>*</span></label><input type="email" name="recipient_email" id="erm_email" class="fm-input" required maxlength="255" placeholder="e.g. sales@koch.co.th"></div>
            </div>
            <div class="fm-row">
                <div class="fm-group"><label>Company</label><select name="company_id" id="erm_company" class="fm-input"><option value="">Global (All)</option><option value="<?php echo $kochId;?>">KOCH</option><option value="<?php echo $tnbId;?>">TNB</option></select></div>
                <div class="fm-check" style="margin-top:24px"><input type="checkbox" name="is_active" id="erm_active" value="1" checked><label for="erm_active">Active</label></div>
            </div>
        </div>
        <div class="modal-foot"><button type="button" class="btn btn-ghost" onclick="closeModal('emailRecModal')">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button></div>
    </form>
</div></div>

<script>
function toggleSB(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('sbOverlay').classList.toggle('show')}
function openModal(id){document.getElementById(id).classList.add('show')}
function closeModal(id){document.getElementById(id).classList.remove('show')}

function openUserModal(id,name,role,status){
    document.getElementById('um_id').value=id;
    document.getElementById('um_name').value=name;
    document.getElementById('um_role').value=role;
    document.getElementById('um_status').value=status;
    openModal('userModal');
}

function openEditSlider(id,title,subtitle,companyId,active){
    document.getElementById('sm_title').textContent='Edit Slider #'+id;
    document.getElementById('sm_action').value='update';
    document.getElementById('sm_id').value=id;
    document.getElementById('sm_name').value=title;
    document.getElementById('sm_subtitle').value=subtitle;
    document.getElementById('sm_company').value=companyId;
    document.getElementById('sm_active').checked=!!active;
    openModal('sliderModal');
}

function openEditPartner(id,name,companyId,active){
    document.getElementById('pm_title').textContent='Edit Partner #'+id;
    document.getElementById('pm_action').value='update';
    document.getElementById('pm_id').value=id;
    document.getElementById('pm_name').value=name;
    document.getElementById('pm_company').value=companyId;
    document.getElementById('pm_active').checked=!!active;
    openModal('partnerModal');
}

function openEditProduct(id,name,desc,category,active){
    document.getElementById('prd_title').textContent='Edit Product #'+id;
    document.getElementById('prd_action').value='update';
    document.getElementById('prd_id').value=id;
    document.getElementById('prd_name').value=name;
    document.getElementById('prd_desc').value=desc;
    document.getElementById('prd_cat').value=category;
    document.getElementById('prd_active').checked=!!active;
    openModal('productModal');
}

function openEditFeaturedProduct(id,name,desc,active){
    document.getElementById('fp_title').textContent='Edit Featured Product #'+id;
    document.getElementById('fp_action').value='update';
    document.getElementById('fp_id').value=id;
    document.getElementById('fp_name').value=name;
    document.getElementById('fp_desc').value=desc;
    document.getElementById('fp_active').checked=!!active;
    openModal('featuredProductModal');
}

let currentExportType = '';
let currentFilterMode = 'all';

// Export type selection
function selectExportType(type) {
    currentExportType = type;
    
    // Update button states
    document.querySelectorAll('[id^="btn-"]').forEach(btn => {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    });
    document.getElementById('btn-' + type).classList.remove('btn-primary');
    document.getElementById('btn-' + type).classList.add('btn-success');
    
    // Show filter section and export options
    document.getElementById('filterSection').style.display = 'block';
    document.getElementById('exportOptions').style.display = 'block';
    
    // Load specific filter fields
    loadFilterFields(type);
}

// Toggle filter mode
function toggleFilterMode(mode) {
    currentFilterMode = mode;
    const filterFields = document.getElementById('filterFields');
    filterFields.style.display = mode === 'filter' ? 'block' : 'none';
}

// Load filter fields based on export type
function loadFilterFields(type) {
    const filterFields = document.getElementById('filterFields');
    let html = '';
    
    switch(type) {
        case 'users':
            html = `
                <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
                    <input type="hidden" name="section" value="export_data">
                    <input type="hidden" name="export_type" value="users">
                    <input type="hidden" name="filter_mode" value="filter">
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date From</label><input type="date" name="date_from" value="<?php echo h($expFilters['users']['date_from']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date To</label><input type="date" name="date_to" value="<?php echo h($expFilters['users']['date_to']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Role</label><select name="filter_role" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Roles</option><option value="super_admin">Super Admin</option><option value="admin">Admin</option><option value="manager">Manager</option><option value="user">User</option></select></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Status</label><select name="filter_status" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="suspended">Suspended</option></select></div>
                </form>
            `;
            break;
        case 'quotations':
            html = `
                <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
                    <input type="hidden" name="section" value="export_data">
                    <input type="hidden" name="export_type" value="quotations">
                    <input type="hidden" name="filter_mode" value="filter">
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date From</label><input type="date" name="date_from" value="<?php echo h($expFilters['quotations']['date_from']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date To</label><input type="date" name="date_to" value="<?php echo h($expFilters['quotations']['date_to']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Customer</label><input type="text" name="filter_user" value="<?php echo h($expFilters['quotations']['user']);?>" placeholder="Username" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Status</label><select name="filter_status" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Status</option><option value="pending">Pending</option><option value="processing">Processing</option><option value="quoted">Quoted</option><option value="approved">Approved</option><option value="rejected">Rejected</option><option value="completed">Completed</option></select></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Product Type</label><select name="filter_product_type" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Types</option><option value="Engine Parts">Engine Parts</option><option value="Body Parts">Body Parts</option><option value="Electrical Parts">Electrical Parts</option><option value="Other">Other</option></select></div>
                </form>
            `;
            break;
        case 'transport':
            html = `
                <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
                    <input type="hidden" name="section" value="export_data">
                    <input type="hidden" name="export_type" value="transport">
                    <input type="hidden" name="filter_mode" value="filter">
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date From</label><input type="date" name="date_from" value="<?php echo h($expFilters['transport']['date_from']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date To</label><input type="date" name="date_to" value="<?php echo h($expFilters['transport']['date_to']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Customer</label><input type="text" name="filter_user" value="<?php echo h($expFilters['transport']['user']);?>" placeholder="Username" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Status</label><select name="filter_status" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Status</option><option value="pending">Pending</option><option value="processing">Processing</option><option value="approved">Approved</option><option value="in_transit">In Transit</option><option value="delivered">Delivered</option><option value="rejected">Rejected</option><option value="completed">Completed</option></select></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Service Type</label><select name="filter_service_type" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Services</option><option value="domestic">Domestic Transport</option><option value="international">International Transport</option><option value="warehousing">Warehousing</option><option value="fleet_management">Fleet Management</option></select></div>
                </form>
            `;
            break;
        case 'activity':
            html = `
                <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
                    <input type="hidden" name="section" value="export_data">
                    <input type="hidden" name="export_type" value="activity">
                    <input type="hidden" name="filter_mode" value="filter">
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date From</label><input type="date" name="date_from" value="<?php echo h($expFilters['activity']['date_from']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date To</label><input type="date" name="date_to" value="<?php echo h($expFilters['activity']['date_to']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">User</label><input type="text" name="filter_user" value="<?php echo h($expFilters['activity']['user']);?>" placeholder="Username" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Action</label><select name="filter_action" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Actions</option><option value="LOGIN_SUCCESS">Login</option><option value="LOGIN_FAILED">Login Failed</option><option value="REGISTER_SUCCESS">Register</option><option value="PROFILE_UPDATED">Profile Update</option><option value="PASSWORD_CHANGED">Password Change</option><option value="KOCH_QUOTATION_CREATED">KOCH Quotation</option><option value="TNB_QUOTATION_CREATED">TNB Request</option><option value="CONTACT_MESSAGE_SENT">Contact Message</option><option value="SLIDER_CREATED">Slider Created</option><option value="SLIDER_UPDATED">Slider Updated</option><option value="SLIDER_DELETED">Slider Deleted</option><option value="PRODUCT_CREATED">Product Created</option><option value="PRODUCT_UPDATED">Product Updated</option><option value="PRODUCT_DELETED">Product Deleted</option><option value="TRUCK_TYPE_CREATED">Truck Type Created</option><option value="TRUCK_TYPE_UPDATED">Truck Type Updated</option><option value="TRUCK_TYPE_DELETED">Truck Type Deleted</option><option value="DATA_EXPORTED">Data Exported</option><option value="DATA_EXPORT_FAILED">Export Failed</option></select></div>
                </form>
            `;
            break;
        case 'contacts':
            html = `
                <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;align-items:end">
                    <input type="hidden" name="section" value="export_data">
                    <input type="hidden" name="export_type" value="contacts">
                    <input type="hidden" name="filter_mode" value="filter">
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date From</label><input type="date" name="date_from" value="<?php echo h($expFilters['contacts']['date_from']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Date To</label><input type="date" name="date_to" value="<?php echo h($expFilters['contacts']['date_to']);?>" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"></div>
                    <div><label style="font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:4px">Status</label><select name="filter_status" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit"><option value="">All Status</option><option value="new">New</option><option value="read">Read</option><option value="replied">Replied</option><option value="closed">Closed</option></select></div>
                </form>
            `;
            break;
    }
    
    filterFields.innerHTML = html;
}

// Export with filters
function exportWithFilters(format) {
    if (!currentExportType) {
        alert('Please select an export type first.');
        return;
    }
    
    const url = '<?php echo h(project_url('admin/api/export/handler.php'));?>';
    const params = new URLSearchParams({
        type: currentExportType,
        format: format,
        _csrf: '<?php echo h($csrfToken);?>'
    });
    
    // Add filters if in filter mode
    if (currentFilterMode === 'filter') {
        const form = document.querySelector('#filterFields form');
        if (form) {
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                if (key !== 'section' && key !== 'export_type' && key !== 'filter_mode') {
                    params.append(key, value);
                }
            }
        }
    }
    
    window.open(url + '?' + params.toString(), '_blank');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load current export type if exists
    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('export_type');
    const mode = urlParams.get('filter_mode') || 'all';
    
    if (type) {
        selectExportType(type);
        toggleFilterMode(mode);
    }
});

function openEditTruck(id,name,desc,capacity,active){
    document.getElementById('tt_title').textContent='Edit Truck Type #'+id;
    document.getElementById('tt_action').value='update';
    document.getElementById('tt_id').value=id;
    document.getElementById('tt_name').value=name;
    document.getElementById('tt_desc').value=desc;
    document.getElementById('tt_cap').value=capacity;
    document.getElementById('tt_active').checked=!!active;
    openModal('truckModal');
}

function openEditEmailTpl(id,name,subject,vars,companyId,active){
    document.getElementById('etm_title').textContent='Edit Template #'+id;
    document.getElementById('etm_action').value='update';
    document.getElementById('etm_id').value=id;
    document.getElementById('etm_name').value=name;
    document.getElementById('etm_subject').value=subject;
    document.getElementById('etm_vars').value=vars;
    document.getElementById('etm_company').value=companyId||'';
    document.getElementById('etm_active').checked=!!active;
    openModal('emailTplModal');
}

function openEditEmailRec(id,eventType,name,email,companyId,active){
    document.getElementById('erm_title').textContent='Edit Recipient #'+id;
    document.getElementById('erm_action').value='update';
    document.getElementById('erm_id').value=id;
    document.getElementById('erm_event').value=eventType;
    document.getElementById('erm_name').value=name;
    document.getElementById('erm_email').value=email;
    document.getElementById('erm_company').value=companyId||'';
    document.getElementById('erm_active').checked=!!active;
    openModal('emailRecModal');
}

document.addEventListener('keydown',function(e){if(e.key==='Escape'){document.querySelectorAll('.modal-overlay.show').forEach(m=>m.classList.remove('show'))}});

document.addEventListener('DOMContentLoaded', function() {
    const sbNav = document.querySelector('.sb-nav');
    if (sbNav) {
        const scrollPos = sessionStorage.getItem('sbNavScroll');
        if (scrollPos) {
            sbNav.scrollTop = parseInt(scrollPos, 10);
        }
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('sbNavScroll', sbNav.scrollTop);
        });
    }
});
</script>
</body>
</html>
