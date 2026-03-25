<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/activity.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect_to(project_url('koch/main/contact.php'));
}

$company = strtolower(post_string('company', 'koch'));
$companyCode = company_code_from_slug($company);
$redirectBack = project_url($company . '/main/contact.php');

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    set_flash('error_message', 'Security token mismatch. Please try again.');
    redirect_to($redirectBack);
}

$name = sanitize_text(post_string('name'));
$email = sanitize_text(post_string('email'));
$phone = sanitize_text(post_string('phone'));
$subject = sanitize_text(post_string('subject'));
$message = sanitize_text(post_string('message'));

if ($name === '' || $email === '' || $message === '') {
    set_flash('error_message', 'กรุณากรอกชื่อ อีเมล และข้อความ');
    redirect_to($redirectBack);
}

$pdo = Database::connection();

$companyStmt = $pdo->prepare('SELECT id FROM companies WHERE code = :code LIMIT 1');
$companyStmt->execute([':code' => $companyCode]);
$companyId = $companyStmt->fetchColumn();

$stmt = $pdo->prepare(
    'INSERT INTO contact_messages (company_id, name, email, phone, subject, message, ip_address, user_agent, status)
     VALUES (:company_id, :name, :email, :phone, :subject, :message, :ip, :ua, :status)'
);
$stmt->execute([
    ':company_id' => $companyId ?: null,
    ':name'       => $name,
    ':email'      => $email,
    ':phone'      => $phone,
    ':subject'    => $subject,
    ':message'    => $message,
    ':ip'         => get_client_ip(),
    ':ua'         => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
    ':status'     => 'new',
]);

$contactId = (int) $pdo->lastInsertId();

$authUser = authenticated_user();
if ($authUser) {
    log_activity($pdo, (int) $authUser['id'], 'CONTACT_MESSAGE_SENT', 'contact_messages', $contactId);
}

$admins = $pdo->prepare("SELECT id FROM users WHERE role IN ('super_admin','admin') AND status = 'active'");
$admins->execute();
foreach ($admins->fetchAll(PDO::FETCH_COLUMN) as $adminId) {
    create_notification(
        $pdo, (int) $adminId,
        'New Contact Message',
        'New contact from ' . $name . ' (' . $email . '): ' . mb_substr($subject ?: $message, 0, 80),
        'info', 'contact_messages', $contactId, 'normal'
    );
}

set_flash('success_message', 'ส่งข้อความเรียบร้อยแล้ว เราจะติดต่อกลับโดยเร็วที่สุด');
redirect_to($redirectBack);
