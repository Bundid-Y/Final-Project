<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin.php';

$pdo = Database::connection();
$user = require_admin_user();
$companyId = in_array((string) $user['role'], ['super_admin'], true) ? null : (int) $user['company_id'];
$stats = admin_dashboard_stats($pdo, $companyId);
$activities = latest_admin_activities($pdo, $companyId);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | KOCH & TNB</title>
    <style>
        body{margin:0;font-family:Inter,Arial,sans-serif;background:#f4f7fb;color:#132238}
        .topbar{display:flex;justify-content:space-between;align-items:center;padding:20px 28px;background:#132238;color:#fff}
        .topbar a{color:#fff;text-decoration:none}
        .wrap{padding:24px;max-width:1200px;margin:0 auto}
        .alert{padding:14px 16px;border-radius:12px;margin-bottom:16px}
        .alert.success{background:#e8f7ee;color:#0f7a3a}
        .alert.error{background:#fdecec;color:#b42318}
        .hero{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;margin-bottom:24px}
        .hero-card{background:#fff;border-radius:18px;padding:24px;box-shadow:0 10px 30px rgba(19,34,56,.08);flex:1 1 320px}
        .badge{display:inline-block;padding:6px 10px;border-radius:999px;background:#e8efff;color:#2649a0;font-size:12px;font-weight:700}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px}
        .stat{background:#fff;padding:20px;border-radius:18px;box-shadow:0 10px 30px rgba(19,34,56,.08)}
        .stat h3{margin:0 0 6px;font-size:14px;color:#526072}
        .stat strong{font-size:32px}
        .panel{background:#fff;border-radius:18px;padding:20px;box-shadow:0 10px 30px rgba(19,34,56,.08)}
        table{width:100%;border-collapse:collapse}
        th,td{text-align:left;padding:12px;border-bottom:1px solid #edf1f6;font-size:14px}
        .actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:16px}
        .button{display:inline-flex;align-items:center;justify-content:center;padding:12px 16px;border-radius:12px;text-decoration:none;background:#2649a0;color:#fff;font-weight:600}
        .button.secondary{background:#eef3ff;color:#2649a0}
    </style>
</head>
<body>
    <div class="topbar">
        <div>
            <strong>KOCH & TNB Admin</strong>
            <div style="font-size:13px;opacity:.8;margin-top:4px;">Signed in as <?php echo h($user['username']); ?> (<?php echo h($user['role']); ?>)</div>
        </div>
        <div style="display:flex;gap:16px;align-items:center;">
            <a href="<?php echo h(user_page_by_company((string) $user['company_code'])); ?>">Front User Page</a>
            <a href="<?php echo h(project_url('admin/api/auth/logout.php')); ?>">Logout</a>
        </div>
    </div>
    <div class="wrap">
        <?php if ($successMessage): ?>
            <div class="alert success"><?php echo h((string) $successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert error"><?php echo h((string) $errorMessage); ?></div>
        <?php endif; ?>
        <div class="hero">
            <div class="hero-card">
                <span class="badge"><?php echo h((string) $user['company_name']); ?></span>
                <h1 style="margin:16px 0 8px;">Dashboard พร้อมใช้งานกับฐานข้อมูลจริง</h1>
                <p style="margin:0;color:#526072;line-height:1.6;">หน้านี้เชื่อมกับฐานข้อมูล <strong>koch_tnb_system</strong> และสรุปผู้ใช้, ใบเสนอราคา และกิจกรรมล่าสุดจากระบบหลังบ้านที่เก็บไว้ใต้โฟลเดอร์ <strong>admin</strong> ทั้งหมด</p>
                <div class="actions">
                    <a class="button" href="<?php echo h(project_url('koch/main/quotation.php')); ?>">KOCH Quotation</a>
                    <a class="button secondary" href="<?php echo h(project_url('tnb/main/quotation.php')); ?>">TNB Quotation</a>
                </div>
            </div>
        </div>
        <div class="grid">
            <div class="stat"><h3>Total Users</h3><strong><?php echo number_format((int) $stats['users']); ?></strong></div>
            <div class="stat"><h3>KOCH Quotations</h3><strong><?php echo number_format((int) $stats['koch_quotations']); ?></strong></div>
            <div class="stat"><h3>TNB Requests</h3><strong><?php echo number_format((int) $stats['tnb_quotations']); ?></strong></div>
            <div class="stat"><h3>Unread Notifications</h3><strong><?php echo number_format((int) $stats['unread_notifications']); ?></strong></div>
        </div>
        <div class="panel">
            <h2 style="margin-top:0;">Recent Activity</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($activities === []): ?>
                        <tr>
                            <td colspan="5">No activity found yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo h((string) $activity['created_at']); ?></td>
                                <td><?php echo h((string) ($activity['username'] ?? '-')); ?></td>
                                <td><?php echo h((string) $activity['action']); ?></td>
                                <td><?php echo h((string) ($activity['table_name'] ?? '-')); ?></td>
                                <td><?php echo h((string) ($activity['record_id'] ?? '-')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
