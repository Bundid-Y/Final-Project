<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin.php';

$pdo = Database::connection();
$user = require_admin_user();
$isSuperAdmin = in_array((string) $user['role'], ['super_admin'], true);
$companyId = $isSuperAdmin ? null : (int) $user['company_id'];
$stats = admin_dashboard_stats($pdo, $companyId);
$ext = admin_extended_stats($pdo, $companyId);
$activities = latest_admin_activities($pdo, $companyId, 8);
$recentKoch = admin_recent_koch_quotations($pdo, $companyId, 5);
$recentTnb = admin_recent_tnb_quotations($pdo, $companyId, 5);
$recentUsers = admin_recent_users($pdo, $companyId, 5);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
$totalPending = $ext['koch_pending'] + $ext['tnb_pending'];
$section = $_GET['section'] ?? 'overview';
$validSections = ['overview','users','koch_quotations','tnb_quotations','notifications','activity','settings'];
if (!in_array($section, $validSections, true)) $section = 'overview';

function admin_status_badge(string $status): string {
    $m = ['pending'=>['#fff3e0','#e65100','Pending'],'processing'=>['#e3f2fd','#1565c0','Processing'],'quoted'=>['#f3e5f5','#7b1fa2','Quoted'],'approved'=>['#e8f5e9','#2e7d32','Approved'],'in_transit'=>['#e0f7fa','#00838f','In Transit'],'delivered'=>['#e8f5e9','#1b5e20','Delivered'],'completed'=>['#e0f2f1','#00695c','Completed'],'rejected'=>['#fbe9e7','#bf360c','Rejected'],'cancelled'=>['#efebe9','#4e342e','Cancelled'],'active'=>['#dcfce7','#166534','Active'],'inactive'=>['#f1f5f9','#64748b','Inactive'],'suspended'=>['#fee2e2','#991b1b','Suspended']];
    $s = $m[$status] ?? ['#f5f5f5','#616161',$status];
    return '<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:'.$s[0].';color:'.$s[1].'">'.h($s[2]).'</span>';
}
function admin_action_label(string $a): string {
    $m = ['LOGIN_SUCCESS'=>'Login','LOGIN_FAILED'=>'Login Failed','REGISTER_SUCCESS'=>'Register','PROFILE_UPDATED'=>'Profile Update','PASSWORD_CHANGED'=>'Password Change','KOCH_QUOTATION_CREATED'=>'KOCH Quotation','TNB_QUOTATION_CREATED'=>'TNB Request','AUTO_REGISTER_FROM_QUOTATION'=>'Auto Register'];
    return $m[$a] ?? $a;
}

$sectionTitles = ['overview'=>'Dashboard Overview','users'=>'User Management','koch_quotations'=>'KOCH Quotations','tnb_quotations'=>'TNB Requests','notifications'=>'Notifications','activity'=>'Activity Logs','settings'=>'System Settings'];
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
:root{--primary:#4f46e5;--primary-dark:#4338ca;--primary-light:#eef2ff;--secondary:#0f172a;--bg:#f1f5f9;--card:#fff;--text:#0f172a;--muted:#64748b;--border:#e2e8f0;--success:#16a34a;--warning:#ea580c;--danger:#dc2626;--info:#2563eb;--radius:16px;--shadow:0 1px 3px rgba(0,0,0,.05),0 4px 12px rgba(0,0,0,.04);--sw:260px}
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
    <nav class="sb-nav">
        <div class="label">Main</div>
        <a href="?section=overview" class="<?php echo $section==='overview'?'active':'';?>"><i class="fas fa-chart-pie"></i> Dashboard<?php if($totalPending>0):?><span class="badge warn"><?php echo $totalPending;?></span><?php endif;?></a>
        <div class="divider"></div>
        <div class="label">Management</div>
        <a href="?section=users" class="<?php echo $section==='users'?'active':'';?>"><i class="fas fa-users"></i> Users<span class="badge"><?php echo number_format((int)$stats['users']);?></span></a>
        <a href="?section=koch_quotations" class="<?php echo $section==='koch_quotations'?'active':'';?>"><i class="fas fa-box"></i> KOCH Quotations<?php if($ext['koch_pending']>0):?><span class="badge warn"><?php echo $ext['koch_pending'];?></span><?php endif;?></a>
        <a href="?section=tnb_quotations" class="<?php echo $section==='tnb_quotations'?'active':'';?>"><i class="fas fa-truck"></i> TNB Requests<?php if($ext['tnb_pending']>0):?><span class="badge warn"><?php echo $ext['tnb_pending'];?></span><?php endif;?></a>
        <div class="divider"></div>
        <div class="label">System</div>
        <a href="?section=notifications" class="<?php echo $section==='notifications'?'active':'';?>"><i class="fas fa-bell"></i> Notifications<?php if((int)$stats['unread_notifications']>0):?><span class="badge"><?php echo (int)$stats['unread_notifications'];?></span><?php endif;?></a>
        <a href="?section=activity" class="<?php echo $section==='activity'?'active':'';?>"><i class="fas fa-history"></i> Activity Logs</a>
        <a href="?section=settings" class="<?php echo $section==='settings'?'active':'';?>"><i class="fas fa-cog"></i> Settings</a>
    </nav>
    <div class="sb-foot">
        <a href="<?php echo h(user_page_by_company((string)$user['company_code']));?>"><i class="fas fa-external-link-alt"></i> Front User Page</a>
        <a href="<?php echo h(project_url('koch/main/index.php'));?>"><i class="fas fa-globe"></i> KOCH Website</a>
        <a href="<?php echo h(project_url('tnb/main/index.php'));?>"><i class="fas fa-globe"></i> TNB Website</a>
        <a href="<?php echo h(project_url('admin/api/auth/logout.php'));?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
            <a href="<?php echo h(project_url('koch/main/quotation.php'));?>" class="tb-btn ghost"><i class="fas fa-plus"></i> KOCH Quote</a>
            <a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" class="tb-btn ghost"><i class="fas fa-plus"></i> TNB Request</a>
        </div>
    </div>

    <div class="content">
        <?php if($successMessage):?><div class="alert ok"><i class="fas fa-check-circle"></i> <?php echo h((string)$successMessage);?></div><?php endif;?>
        <?php if($errorMessage):?><div class="alert er"><i class="fas fa-exclamation-circle"></i> <?php echo h((string)$errorMessage);?></div><?php endif;?>

<?php if($section==='overview'): ?>
<!-- =================== OVERVIEW =================== -->
<div class="stats-row">
    <div class="stat-card purple">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div><?php if($ext['new_users_month']>0):?><span class="sc-change up">+<?php echo $ext['new_users_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['users']);?></div>
        <div class="sc-label">Total Users</div>
    </div>
    <div class="stat-card blue">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-box"></i></div><?php if($ext['koch_month']>0):?><span class="sc-change up">+<?php echo $ext['koch_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['koch_quotations']);?></div>
        <div class="sc-label">KOCH Quotations</div>
    </div>
    <div class="stat-card teal">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-truck"></i></div><?php if($ext['tnb_month']>0):?><span class="sc-change up">+<?php echo $ext['tnb_month'];?> this month</span><?php endif;?></div>
        <div class="sc-num"><?php echo number_format((int)$stats['tnb_quotations']);?></div>
        <div class="sc-label">TNB Requests</div>
    </div>
    <div class="stat-card orange">
        <div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div>
        <div class="sc-num"><?php echo $totalPending;?></div>
        <div class="sc-label">Pending Approval</div>
    </div>
</div>

<div class="qa-grid">
    <a href="<?php echo h(project_url('koch/main/quotation.php'));?>" class="qa-item koch"><i class="fas fa-box-open"></i><span>New KOCH Quotation</span></a>
    <a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" class="qa-item tnb"><i class="fas fa-shipping-fast"></i><span>New TNB Request</span></a>
    <a href="?section=koch_quotations" class="qa-item all"><i class="fas fa-clipboard-list"></i><span>Pending Items (<?php echo $totalPending;?>)</span></a>
    <a href="?section=notifications" class="qa-item notif"><i class="fas fa-bell"></i><span>Notifications (<?php echo (int)$stats['unread_notifications'];?>)</span></a>
</div>

<div class="grid-3">
    <div>
        <div class="card">
            <div class="card-h"><h2><i class="fas fa-box"></i> Recent KOCH Quotations</h2><a href="?section=koch_quotations" class="link">View All &rarr;</a></div>
            <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Product</th><th>Price</th><th>Status</th></tr></thead><tbody>
            <?php if($recentKoch===[]):?><tr class="empty"><td colspan="5">No quotations yet</td></tr>
            <?php else: foreach($recentKoch as $q):?><tr>
                <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['quotation_number']);?></td>
                <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
                <td style="font-size:12px"><?php echo h((string)$q['product_type']);?></td>
                <td style="font-size:12px"><?php echo $q['quoted_price']!==null?h(number_format((float)$q['quoted_price'],2)):'<span style="color:var(--muted)">-</span>';?></td>
                <td><?php echo admin_status_badge((string)$q['status']);?></td>
            </tr><?php endforeach; endif;?>
            </tbody></table></div></div>
        </div>
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

<?php elseif($section==='users'): ?>
<!-- =================== USERS =================== -->
<div class="stats-row">
    <div class="stat-card purple"><div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['users']);?></div><div class="sc-label">Total Users</div></div>
    <div class="stat-card green"><div class="sc-top"><div class="sc-icon"><i class="fas fa-user-check"></i></div></div><div class="sc-num"><?php echo number_format($ext['active_users']);?></div><div class="sc-label">Active Users</div></div>
    <div class="stat-card blue"><div class="sc-top"><div class="sc-icon"><i class="fas fa-user-plus"></i></div></div><div class="sc-num"><?php echo number_format($ext['new_users_month']);?></div><div class="sc-label">New This Month</div></div>
    <div class="stat-card orange"><div class="sc-top"><div class="sc-icon"><i class="fas fa-bell"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['unread_notifications']);?></div><div class="sc-label">Unread Notifications</div></div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-users"></i> All Users</h2></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>User</th><th>Email</th><th>Company</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody>
    <?php
    $allUsers = $pdo->prepare('SELECT u.*, c.name AS company_name FROM users u LEFT JOIN companies c ON c.id = u.company_id' . ($companyId !== null ? ' WHERE u.company_id = :cid' : '') . ' ORDER BY u.created_at DESC LIMIT 50');
    $allUsers->execute($companyId !== null ? [':cid' => $companyId] : []);
    $allUsersList = $allUsers->fetchAll();
    if($allUsersList===[]):?><tr class="empty"><td colspan="6">No users found</td></tr>
    <?php else: foreach($allUsersList as $u):?><tr>
        <td><div class="user-row"><div class="u-avatar"><?php echo strtoupper(substr((string)($u['first_name']??$u['username']),0,1));?></div><div class="u-info"><h4><?php echo h(trim(($u['first_name']??'').' '.($u['last_name']??''))?:$u['username']);?></h4><span>@<?php echo h((string)$u['username']);?></span></div></div></td>
        <td style="font-size:12px"><?php echo h((string)$u['email']);?></td>
        <td style="font-size:12px"><?php echo h((string)($u['company_name']??'-'));?></td>
        <td><span style="font-size:11px;font-weight:600;padding:3px 8px;border-radius:6px;background:var(--primary-light);color:var(--primary)"><?php echo h(ucfirst((string)$u['role']));?></span></td>
        <td><?php echo admin_status_badge((string)$u['status']);?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y',strtotime((string)$u['created_at'])));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='koch_quotations'): ?>
<!-- =================== KOCH QUOTATIONS =================== -->
<div class="stats-row">
    <div class="stat-card blue"><div class="sc-top"><div class="sc-icon"><i class="fas fa-file-alt"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['koch_quotations']);?></div><div class="sc-label">Total KOCH Quotations</div></div>
    <div class="stat-card orange"><div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div><div class="sc-num"><?php echo $ext['koch_pending'];?></div><div class="sc-label">Pending</div></div>
    <div class="stat-card purple"><div class="sc-top"><div class="sc-icon"><i class="fas fa-calendar"></i></div></div><div class="sc-num"><?php echo $ext['koch_month'];?></div><div class="sc-label">This Month</div></div>
    <div class="stat-card green"><div class="sc-top"><div class="sc-icon"><i class="fas fa-check-circle"></i></div></div><div class="sc-num">-</div><div class="sc-label">Completed</div></div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-box"></i> All KOCH Quotations</h2><a href="<?php echo h(project_url('koch/main/quotation.php'));?>" class="tb-btn primary" style="font-size:11px;padding:6px 12px"><i class="fas fa-plus"></i> New</a></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Product</th><th>Quantity</th><th>Price</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php
    $kAll = $pdo->prepare('SELECT * FROM koch_quotations' . ($companyId !== null ? ' WHERE user_id IN (SELECT id FROM users WHERE company_id = :cid)' : '') . ' ORDER BY created_at DESC LIMIT 50');
    $kAll->execute($companyId !== null ? [':cid' => $companyId] : []);
    $kList = $kAll->fetchAll();
    if($kList===[]):?><tr class="empty"><td colspan="7">No KOCH quotations found</td></tr>
    <?php else: foreach($kList as $q):?><tr>
        <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['quotation_number']);?></td>
        <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
        <td style="font-size:12px"><?php echo h((string)$q['product_type']);?></td>
        <td style="font-size:12px"><?php echo h((string)($q['quantity']??'-'));?></td>
        <td style="font-size:12px"><?php echo $q['quoted_price']!==null?h(number_format((float)$q['quoted_price'],2)).' ฿':'<span style="color:var(--muted)">-</span>';?></td>
        <td><?php echo admin_status_badge((string)$q['status']);?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$q['created_at'])));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='tnb_quotations'): ?>
<!-- =================== TNB QUOTATIONS =================== -->
<div class="stats-row">
    <div class="stat-card teal"><div class="sc-top"><div class="sc-icon"><i class="fas fa-file-alt"></i></div></div><div class="sc-num"><?php echo number_format((int)$stats['tnb_quotations']);?></div><div class="sc-label">Total TNB Requests</div></div>
    <div class="stat-card orange"><div class="sc-top"><div class="sc-icon"><i class="fas fa-clock"></i></div></div><div class="sc-num"><?php echo $ext['tnb_pending'];?></div><div class="sc-label">Pending</div></div>
    <div class="stat-card blue"><div class="sc-top"><div class="sc-icon"><i class="fas fa-shipping-fast"></i></div></div><div class="sc-num"><?php echo $ext['tnb_in_transit'];?></div><div class="sc-label">In Transit</div></div>
    <div class="stat-card purple"><div class="sc-top"><div class="sc-icon"><i class="fas fa-calendar"></i></div></div><div class="sc-num"><?php echo $ext['tnb_month'];?></div><div class="sc-label">This Month</div></div>
</div>
<div class="card">
    <div class="card-h"><h2><i class="fas fa-truck"></i> All TNB Requests</h2><a href="<?php echo h(project_url('tnb/main/quotation.php'));?>" class="tb-btn primary" style="font-size:11px;padding:6px 12px"><i class="fas fa-plus"></i> New</a></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Number</th><th>Customer</th><th>Service</th><th>Route</th><th>Price</th><th>Tracking</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php
    $tAll = $pdo->prepare('SELECT * FROM tnb_quotations' . ($companyId !== null ? ' WHERE user_id IN (SELECT id FROM users WHERE company_id = :cid)' : '') . ' ORDER BY created_at DESC LIMIT 50');
    $tAll->execute($companyId !== null ? [':cid' => $companyId] : []);
    $tList = $tAll->fetchAll();
    if($tList===[]):?><tr class="empty"><td colspan="8">No TNB requests found</td></tr>
    <?php else: foreach($tList as $q):?><tr>
        <td style="font-weight:600;font-size:12px"><?php echo h((string)$q['request_number']);?></td>
        <td style="font-size:12px"><?php echo h($q['first_name'].' '.$q['last_name']);?></td>
        <td style="font-size:12px"><?php echo h((string)$q['service_type']);?></td>
        <td style="font-size:12px"><?php echo h((string)($q['route']??'-'));?></td>
        <td style="font-size:12px"><?php echo $q['quoted_price']!==null?h(number_format((float)$q['quoted_price'],2)).' ฿':'<span style="color:var(--muted)">-</span>';?></td>
        <td style="font-size:12px"><?php echo h((string)($q['tracking_number']??'-'));?></td>
        <td><?php echo admin_status_badge((string)$q['status']);?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$q['created_at'])));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='activity'): ?>
<!-- =================== ACTIVITY LOGS =================== -->
<div class="card">
    <div class="card-h"><h2><i class="fas fa-history"></i> All Activity Logs</h2></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Date</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th></tr></thead><tbody>
    <?php
    $allAct = latest_admin_activities($pdo, $companyId, 50);
    if($allAct===[]):?><tr class="empty"><td colspan="5">No activity logs found</td></tr>
    <?php else: foreach($allAct as $a):?><tr>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$a['created_at'])));?></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)($a['username']??'-'));?></td>
        <td style="font-size:12px"><?php echo h(admin_action_label((string)$a['action']));?></td>
        <td style="font-size:12px;color:var(--muted)"><?php echo h((string)($a['table_name']??'-'));?></td>
        <td style="font-size:12px"><?php echo h((string)($a['record_id']??'-'));?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
</div>

<?php elseif($section==='notifications'): ?>
<!-- =================== NOTIFICATIONS =================== -->
<div class="card">
    <div class="card-h"><h2><i class="fas fa-bell"></i> System Notifications</h2><?php if((int)$stats['unread_notifications']>0):?><span style="background:var(--primary);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px"><?php echo (int)$stats['unread_notifications'];?> unread</span><?php endif;?></div>
    <div class="card-b" style="padding:0"><div class="tbl-wrap"><table class="tbl"><thead><tr><th>Type</th><th>Title</th><th>Message</th><th>User</th><th>Date</th><th>Status</th></tr></thead><tbody>
    <?php
    $nSql = 'SELECT n.*, u.username FROM notifications n LEFT JOIN users u ON u.id = n.user_id' . ($companyId !== null ? ' WHERE n.user_id IN (SELECT id FROM users WHERE company_id = :cid)' : '') . ' ORDER BY n.created_at DESC LIMIT 50';
    $nStmt = $pdo->prepare($nSql);
    $nStmt->execute($companyId !== null ? [':cid' => $companyId] : []);
    $nList = $nStmt->fetchAll();
    if($nList===[]):?><tr class="empty"><td colspan="6">No notifications found</td></tr>
    <?php else: foreach($nList as $n):?><tr style="<?php echo !$n['is_read']?'background:#f0f9ff':'';?>">
        <td><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?php echo match((string)($n['type']??'info')){'success'=>'var(--success)','warning'=>'var(--warning)','error'=>'var(--danger)',default=>'var(--info)'};?>"></span></td>
        <td style="font-size:12px;font-weight:600"><?php echo h((string)$n['title']);?></td>
        <td style="font-size:12px;color:var(--muted);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo h((string)$n['message']);?></td>
        <td style="font-size:12px"><?php echo h((string)($n['username']??'-'));?></td>
        <td style="font-size:12px;white-space:nowrap"><?php echo h(date('d/m/Y H:i',strtotime((string)$n['created_at'])));?></td>
        <td><?php echo !$n['is_read']?'<span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;background:#dbeafe;color:var(--info)">Unread</span>':'<span style="font-size:11px;color:var(--muted)">Read</span>';?></td>
    </tr><?php endforeach; endif;?>
    </tbody></table></div></div>
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
<script>
function toggleSB(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('sbOverlay').classList.toggle('show')}
</script>
</body>
</html>
