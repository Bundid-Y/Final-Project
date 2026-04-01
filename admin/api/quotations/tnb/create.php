<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../includes/quotation.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect_to(project_url('tnb/main/quotation.php'));
}

$redirectBack = project_url('tnb/main/quotation.php');

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    form_response(false, 'Security token mismatch. Please try again.', $redirectBack, $_POST, [], 422);
}

$pdo = Database::connection();
$result = create_tnb_quotation($pdo, $_POST, $_FILES);

if (!$result['success']) {
    form_response(false, $result['message'], $redirectBack, $_POST, $result, 422);
}

// Create notification for quotation submission
if (isset($result['user_id']) && isset($result['company_id'])) {
    require_once __DIR__ . '/../../../includes/activity.php';
    create_notification(
        $pdo,
        (int) $result['user_id'],
        'ส่งคำขอบริการสำเร็จ',
        'คำขอบริการขนส่งของคุณได้รับการบันทึกเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับโดยเร็วที่สุด',
        'success',
        'tnb_quotations',
        (int) ($result['quotation_id'] ?? 0),
        'normal',
        (int) $result['company_id']
    );
}

form_response(true, $result['message'], $redirectBack, [], $result);
