<?php
require_once __DIR__ . '/../../admin/includes/bootstrap.php';
require_once __DIR__ . '/../../admin/includes/profile.php';

$currentUser = authenticated_user();
if ($currentUser === null) {
    redirect_to(project_url('koch/main/login.php'));
}

if ((string) ($currentUser['company_code'] ?? 'KOCH') !== 'KOCH' && !in_array((string) ($currentUser['role'] ?? 'user'), ['super_admin', 'admin', 'manager'], true)) {
    redirect_to(user_page_by_company((string) $currentUser['company_code']));
}

$pdo = Database::connection();
$profile = get_profile_summary($pdo, (int) $currentUser['id']);
$activities = get_recent_activity_logs($pdo, (int) $currentUser['id']);
$quotations = get_koch_user_quotations($pdo, (int) $currentUser['id']);
$successMessage = flash('success_message');
$errorMessage = flash('error_message');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOCH User Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
    <style>
        body{background:#f5f7fb;color:#132238}
        .profile-shell{max-width:1200px;margin:0 auto;padding:40px 20px 60px}
        .profile-grid{display:grid;grid-template-columns:320px minmax(0,1fr);gap:20px;align-items:start}
        .profile-card,.panel-card{background:#fff;border-radius:20px;padding:24px;box-shadow:0 12px 30px rgba(19,34,56,.08)}
        .profile-avatar{width:120px;height:120px;border-radius:50%;object-fit:cover;background:#eef3ff;display:block;margin:0 auto 16px}
        .profile-name{text-align:center;font-size:26px;font-weight:700;margin:0 0 6px}
        .profile-meta{text-align:center;color:#607086;margin:0 0 18px}
        .stats-list{display:grid;gap:12px}
        .stat-item{padding:14px 16px;border-radius:14px;background:#f6f8fc}
        .alert{padding:14px 16px;border-radius:14px;margin-bottom:16px}
        .alert.success{background:#e8f7ee;color:#0f7a3a}
        .alert.error{background:#fdecec;color:#b42318}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .field{display:grid;gap:8px}
        .field.full{grid-column:1/-1}
        .field label{font-weight:600;color:#253446}
        .field input{padding:12px 14px;border:1px solid #d8e0eb;border-radius:12px;font-size:14px}
        .actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:12px;text-decoration:none;border:none;font-weight:700;cursor:pointer}
        .btn.primary{background:#0d4ed8;color:#fff}
        .btn.secondary{background:#eef3ff;color:#2245a3}
        .panel-card{margin-top:20px}
        .table-wrap{overflow:auto}
        table{width:100%;border-collapse:collapse}
        th,td{text-align:left;padding:12px;border-bottom:1px solid #edf1f6;font-size:14px;white-space:nowrap}
        @media (max-width: 900px){.profile-grid{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <?php include '../component/menubar.php'; ?>
    <main class="profile-shell">
        <?php if ($successMessage): ?>
            <div class="alert success"><?php echo h((string) $successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert error"><?php echo h((string) $errorMessage); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <aside class="profile-card">
                <img src="<?php echo h((string) ($profile['avatar_url'] ?: '../img/company_logo/logo 2.png')); ?>" alt="User Avatar" class="profile-avatar">
                <h1 class="profile-name"><?php echo h(trim((string) ($profile['first_name'] ?? '') . ' ' . (string) ($profile['last_name'] ?? ''))); ?></h1>
                <p class="profile-meta"><?php echo h((string) ($profile['role'] ?? 'user')); ?> · <?php echo h((string) ($profile['company_name'] ?? 'KOCH')); ?></p>
                <div class="stats-list">
                    <div class="stat-item"><strong>Username:</strong> <?php echo h((string) ($profile['username'] ?? '')); ?></div>
                    <div class="stat-item"><strong>Email:</strong> <?php echo h((string) ($profile['email'] ?? '')); ?></div>
                    <div class="stat-item"><strong>Phone:</strong> <?php echo h((string) ($profile['phone'] ?? '-')); ?></div>
                    <div class="stat-item"><strong>Last Login:</strong> <?php echo h((string) ($profile['last_login'] ?? '-')); ?></div>
                </div>
            </aside>

            <section>
                <div class="profile-card">
                    <h2 style="margin-top:0;">Profile Information</h2>
                    <form action="../../admin/api/profile/update.php" method="POST">
                        <input type="hidden" name="_csrf" value="<?php echo h(csrf_token()); ?>">
                        <input type="hidden" name="company" value="koch">
                        <div class="form-grid">
                            <div class="field">
                                <label for="username">Username</label>
                                <input type="text" id="username" value="<?php echo h((string) ($profile['username'] ?? '')); ?>" readonly>
                            </div>
                            <div class="field">
                                <label for="company_name">Company</label>
                                <input type="text" id="company_name" value="<?php echo h((string) ($profile['company_name'] ?? 'KOCH')); ?>" readonly>
                            </div>
                            <div class="field">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo h(old_input('first_name', (string) ($profile['first_name'] ?? ''))); ?>">
                            </div>
                            <div class="field">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo h(old_input('last_name', (string) ($profile['last_name'] ?? ''))); ?>">
                            </div>
                            <div class="field">
                                <label for="nick_name">Nick Name</label>
                                <input type="text" id="nick_name" name="nick_name" value="<?php echo h(old_input('nick_name', (string) ($profile['nick_name'] ?? ''))); ?>">
                            </div>
                            <div class="field">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo h(old_input('phone', (string) ($profile['phone'] ?? ''))); ?>">
                            </div>
                            <div class="field full">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo h(old_input('email', (string) ($profile['email'] ?? ''))); ?>">
                            </div>
                            <div class="field">
                                <label for="department">Department</label>
                                <input type="text" id="department" name="department" value="<?php echo h(old_input('department', (string) ($profile['department'] ?? ''))); ?>">
                            </div>
                            <div class="field">
                                <label for="position">Position</label>
                                <input type="text" id="position" name="position" value="<?php echo h(old_input('position', (string) ($profile['position'] ?? ''))); ?>">
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn primary">Update Profile</button>
                            <a href="../../admin/api/auth/logout.php?company=koch" class="btn secondary">Logout</a>
                        </div>
                    </form>
                </div>

                <div class="panel-card">
                    <h2 style="margin-top:0;">Recent Activity</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Table</th>
                                    <th>Record</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($activities === []): ?>
                                    <tr><td colspan="4">No activity found yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo h((string) $activity['created_at']); ?></td>
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

                <div class="panel-card">
                    <h2 style="margin-top:0;">Quotation History</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Quotation Number</th>
                                    <th>Date</th>
                                    <th>Product Type</th>
                                    <th>Quoted Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($quotations === []): ?>
                                    <tr><td colspan="5">No quotations found yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($quotations as $quotation): ?>
                                        <tr>
                                            <td><?php echo h((string) $quotation['quotation_number']); ?></td>
                                            <td><?php echo h((string) $quotation['created_at']); ?></td>
                                            <td><?php echo h((string) $quotation['product_type']); ?></td>
                                            <td><?php echo $quotation['quoted_price'] !== null ? h(number_format((float) $quotation['quoted_price'], 2)) : '-'; ?></td>
                                            <td><?php echo h((string) $quotation['status']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <?php include '../component/footer.php'; ?>
    <?php clear_old_input(); ?>
</body>
</html>
