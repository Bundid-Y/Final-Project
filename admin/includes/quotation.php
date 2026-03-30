<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/activity.php';
require_once __DIR__ . '/upload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function generate_document_number(PDO $pdo, string $table, string $column, string $prefix): string
{
    $year = date('Y');

    do {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE YEAR(created_at) = :year");
        $stmt->execute([':year' => $year]);
        $next = ((int) $stmt->fetchColumn()) + 1;
        $number = sprintf('%s-%s-%04d', $prefix, $year, $next);

        $check = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :number");
        $check->execute([':number' => $number]);
        $exists = (int) $check->fetchColumn() > 0;

        if ($exists) {
            usleep(50000);
        }
    } while ($exists);

    return $number;
}

function get_company_staff_users(PDO $pdo, int $companyId): array
{
    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE (company_id = :company_id OR role = 'super_admin') AND role IN ('super_admin', 'admin', 'manager') AND status = 'active'"
    );
    $stmt->execute([':company_id' => $companyId]);

    return $stmt->fetchAll();
}

function normalize_note_payload(array $data): ?string
{
    $filtered = array_filter($data, static fn ($value) => $value !== null && $value !== '');
    return $filtered !== [] ? json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
}

function create_koch_quotation(PDO $pdo, array $payload, array $files = []): array
{
    $requiredErrors = validate_required_fields($payload, [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'nick_name' => 'Nick name',
        'phone' => 'Phone number',
        'email' => 'Email',
        'product_type' => 'Product type',
        'weight' => 'Weight',
        'brand' => 'Brand',
        'packaging_type' => 'Packaging type',
        'box_width' => 'Box width',
        'box_length' => 'Box length',
        'box_height' => 'Box height',
        'quantity' => 'Quantity',
        'comments' => 'Comments',
    ]);

    $errors = merge_validation_errors(
        $requiredErrors,
        ['email' => validate_email_address((string) ($payload['email'] ?? ''))],
        ['phone' => validate_phone_number((string) ($payload['phone'] ?? ''))]
    );

    if ($errors !== []) {
        return ['success' => false, 'message' => reset($errors) ?: 'Please complete the form.', 'errors' => $errors];
    }

    $pdo->beginTransaction();

    try {
        $user = resolve_or_create_customer_user($pdo, $payload, 'koch');
        $quotationNumber = generate_document_number($pdo, 'koch_quotations', 'quotation_number', 'KOCH');

        $_FILES = $files;
        $uploadedFile = handle_uploaded_file('reference_file', 'uploads/quotations/koch');

        $stmt = $pdo->prepare(
            'INSERT INTO koch_quotations (
                quotation_number, user_id, first_name, last_name, nick_name, phone, email,
                product_type, weight, brand, packaging_type, box_length, box_width, box_height,
                quantity, special_requirements, status, attachment_url, priority, notes, created_at, updated_at
             ) VALUES (
                :quotation_number, :user_id, :first_name, :last_name, :nick_name, :phone, :email,
                :product_type, :weight, :brand, :packaging_type, :box_length, :box_width, :box_height,
                :quantity, :special_requirements, :status, :attachment_url, :priority, :notes, NOW(), NOW()
             )'
        );

        $stmt->execute([
            ':quotation_number' => $quotationNumber,
            ':user_id' => (int) $user['id'],
            ':first_name' => sanitize_text((string) $payload['first_name']),
            ':last_name' => sanitize_text((string) $payload['last_name']),
            ':nick_name' => sanitize_text((string) $payload['nick_name']),
            ':phone' => sanitize_text((string) $payload['phone']),
            ':email' => strtolower(sanitize_text((string) $payload['email'])),
            ':product_type' => sanitize_text((string) $payload['product_type']),
            ':weight' => (float) $payload['weight'],
            ':brand' => sanitize_text((string) $payload['brand']),
            ':packaging_type' => sanitize_text((string) $payload['packaging_type']),
            ':box_length' => (float) $payload['box_length'],
            ':box_width' => (float) $payload['box_width'],
            ':box_height' => (float) $payload['box_height'],
            ':quantity' => max(1, (int) $payload['quantity']),
            ':special_requirements' => sanitize_text((string) $payload['comments']),
            ':status' => 'pending',
            ':attachment_url' => $uploadedFile['public_path'] ?? null,
            ':priority' => 'normal',
            ':notes' => normalize_note_payload([
                'box_unit' => sanitize_text((string) ($payload['box_unit'] ?? 'cm')),
            ]),
        ]);

        $quotationId = (int) $pdo->lastInsertId();

        if ($uploadedFile !== null) {
            save_attachment_record($pdo, 'koch_quotations', $quotationId, $uploadedFile, (int) $user['id']);
        }

        log_activity($pdo, (int) $user['id'], 'KOCH_QUOTATION_CREATED', 'koch_quotations', $quotationId, [], [
            'quotation_number' => $quotationNumber,
            'product_type' => sanitize_text((string) $payload['product_type']),
        ]);

        foreach (get_company_staff_users($pdo, (int) $user['company_id']) as $staff) {
            create_notification(
                $pdo,
                (int) $staff['id'],
                'New KOCH quotation request',
                'Quotation ' . $quotationNumber . ' has been submitted by ' . sanitize_text((string) $payload['first_name']) . ' ' . sanitize_text((string) $payload['last_name']),
                'info',
                'koch_quotations',
                $quotationId,
                'normal'
            );
        }

        // --- Send Email to Configured Admin Emails ---
        try {
            $stmtMail = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('admin_notify_email_koch', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass')");
            $stmtMail->execute();
            $settingsList = $stmtMail->fetchAll();
            $adminEmailSetting = '';
            $smtpConfig = ['host'=>'', 'port'=>'', 'user'=>'', 'pass'=>''];
            foreach($settingsList as $row) {
                if ($row['setting_key'] === 'admin_notify_email_koch') $adminEmailSetting = $row['setting_value'];
                else $smtpConfig[str_replace('smtp_','',$row['setting_key'])] = $row['setting_value'];
            }
            
            if (!empty($adminEmailSetting) && !empty($smtpConfig['host'])) {
                $toEmails = array_filter(array_map('trim', explode(',', (string) $adminEmailSetting)));
                if ($toEmails !== []) {
                    $mail = new PHPMailer(true);
                    
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = $smtpConfig['host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtpConfig['user'];
                    $mail->Password   = $smtpConfig['pass'];
                    $mail->SMTPSecure = (int)$smtpConfig['port'] === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = (int) $smtpConfig['port'];
                    $mail->CharSet    = 'UTF-8';

                    // Recipients
                    $mail->setFrom($smtpConfig['user'] ?: 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), 'KOCH Dashboard');
                    foreach ($toEmails as $to) {
                        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                            $mail->addAddress($to);
                        }
                    }

                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = "New KOCH Quotation Request: " . $quotationNumber;
                    $mail->Body    = "A new KOCH quotation request has been submitted.\n\n" .
                                     "Quotation Number: " . $quotationNumber . "\n" .
                                     "Customer: " . sanitize_text((string) $payload['first_name']) . " " . sanitize_text((string) $payload['last_name']) . " (" . sanitize_text((string) $payload['email']) . ")\n" .
                                     "Product: " . sanitize_text((string) $payload['product_type']) . "\n" .
                                     "Quantity: " . max(1, (int) $payload['quantity']) . "\n\n" .
                                     "Please log in to the admin dashboard to review.";

                    $mail->send();
                }
            }
        } catch (Throwable $e) {
            error_log('KOCH SMTP Error: ' . $e->getMessage());
        }

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Quotation submitted successfully. Your quotation number is ' . $quotationNumber,
            'quotation_number' => $quotationNumber,
            'quotation_id' => $quotationId,
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['success' => false, 'message' => APP_DEBUG ? $exception->getMessage() : 'Unable to submit quotation at the moment.'];
    }
}

function create_tnb_quotation(PDO $pdo, array $payload, array $files = []): array
{
    $requiredErrors = validate_required_fields($payload, [
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'nick_name' => 'Nick name',
        'phone' => 'Phone number',
        'email' => 'Email',
        'service_type' => 'Service type',
        'weight' => 'Cargo weight',
        'route' => 'Route',
        'vehicle_type' => 'Vehicle type',
        'cargo_width' => 'Cargo width',
        'cargo_length' => 'Cargo length',
        'cargo_height' => 'Cargo height',
        'quantity' => 'Quantity',
        'comments' => 'Comments',
    ]);

    $errors = merge_validation_errors(
        $requiredErrors,
        ['email' => validate_email_address((string) ($payload['email'] ?? ''))],
        ['phone' => validate_phone_number((string) ($payload['phone'] ?? ''))]
    );

    if ($errors !== []) {
        return ['success' => false, 'message' => reset($errors) ?: 'Please complete the form.', 'errors' => $errors];
    }

    $pdo->beginTransaction();

    try {
        $user = resolve_or_create_customer_user($pdo, $payload, 'tnb');
        $requestNumber = generate_document_number($pdo, 'tnb_quotations', 'request_number', 'TNB');

        $_FILES = $files;
        $uploadedFile = handle_uploaded_file('reference_file', 'uploads/quotations/tnb');

        $stmt = $pdo->prepare(
            'INSERT INTO tnb_quotations (
                request_number, user_id, first_name, last_name, nick_name, phone, email,
                service_type, cargo_weight, route, vehicle_type, cargo_width, cargo_length, cargo_height,
                special_requirements, status, attachment_url, priority, notes, created_at, updated_at
             ) VALUES (
                :request_number, :user_id, :first_name, :last_name, :nick_name, :phone, :email,
                :service_type, :cargo_weight, :route, :vehicle_type, :cargo_width, :cargo_length, :cargo_height,
                :special_requirements, :status, :attachment_url, :priority, :notes, NOW(), NOW()
             )'
        );

        $stmt->execute([
            ':request_number' => $requestNumber,
            ':user_id' => (int) $user['id'],
            ':first_name' => sanitize_text((string) $payload['first_name']),
            ':last_name' => sanitize_text((string) $payload['last_name']),
            ':nick_name' => sanitize_text((string) $payload['nick_name']),
            ':phone' => sanitize_text((string) $payload['phone']),
            ':email' => strtolower(sanitize_text((string) $payload['email'])),
            ':service_type' => sanitize_text((string) $payload['service_type']),
            ':cargo_weight' => (float) $payload['weight'],
            ':route' => sanitize_text((string) $payload['route']),
            ':vehicle_type' => sanitize_text((string) $payload['vehicle_type']),
            ':cargo_width' => (float) $payload['cargo_width'],
            ':cargo_length' => (float) $payload['cargo_length'],
            ':cargo_height' => (float) $payload['cargo_height'],
            ':special_requirements' => sanitize_text((string) $payload['comments']),
            ':status' => 'pending',
            ':attachment_url' => $uploadedFile['public_path'] ?? null,
            ':priority' => 'normal',
            ':notes' => normalize_note_payload([
                'cargo_unit' => sanitize_text((string) ($payload['cargo_unit'] ?? 'cm')),
                'quantity' => max(1, (int) ($payload['quantity'] ?? 1)),
            ]),
        ]);

        $quotationId = (int) $pdo->lastInsertId();

        if ($uploadedFile !== null) {
            save_attachment_record($pdo, 'tnb_quotations', $quotationId, $uploadedFile, (int) $user['id']);
        }

        log_activity($pdo, (int) $user['id'], 'TNB_QUOTATION_CREATED', 'tnb_quotations', $quotationId, [], [
            'request_number' => $requestNumber,
            'service_type' => sanitize_text((string) $payload['service_type']),
        ]);

        foreach (get_company_staff_users($pdo, (int) $user['company_id']) as $staff) {
            create_notification(
                $pdo,
                (int) $staff['id'],
                'New TNB quotation request',
                'Request ' . $requestNumber . ' has been submitted by ' . sanitize_text((string) $payload['first_name']) . ' ' . sanitize_text((string) $payload['last_name']),
                'info',
                'tnb_quotations',
                $quotationId,
                'normal'
            );
        }

        // --- Send Email to Configured Admin Emails ---
        try {
            $stmtMail = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('admin_notify_email_tnb', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass')");
            $stmtMail->execute();
            $settingsList = $stmtMail->fetchAll();
            $adminEmailSetting = '';
            $smtpConfig = ['host'=>'', 'port'=>'', 'user'=>'', 'pass'=>''];
            foreach($settingsList as $row) {
                if ($row['setting_key'] === 'admin_notify_email_tnb') $adminEmailSetting = $row['setting_value'];
                else $smtpConfig[str_replace('smtp_','',$row['setting_key'])] = $row['setting_value'];
            }
            
            if (!empty($adminEmailSetting) && !empty($smtpConfig['host'])) {
                $toEmails = array_filter(array_map('trim', explode(',', (string) $adminEmailSetting)));
                if ($toEmails !== []) {
                    $mail = new PHPMailer(true);
                    
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = $smtpConfig['host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtpConfig['user'];
                    $mail->Password   = $smtpConfig['pass'];
                    $mail->SMTPSecure = (int)$smtpConfig['port'] === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = (int) $smtpConfig['port'];
                    $mail->CharSet    = 'UTF-8';

                    // Recipients
                    $mail->setFrom($smtpConfig['user'] ?: 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), 'TNB Dashboard');
                    foreach ($toEmails as $to) {
                        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                            $mail->addAddress($to);
                        }
                    }

                    // Content
                    $mail->isHTML(false);
                    $mail->Subject = "New TNB Quotation Request: " . $requestNumber;
                    $mail->Body    = "A new TNB quotation request has been submitted.\n\n" .
                                     "Request Number: " . $requestNumber . "\n" .
                                     "Customer: " . sanitize_text((string) $payload['first_name']) . " " . sanitize_text((string) $payload['last_name']) . " (" . sanitize_text((string) $payload['email']) . ")\n" .
                                     "Service Type: " . sanitize_text((string) $payload['service_type']) . "\n" .
                                     "Route: " . sanitize_text((string) $payload['route']) . "\n\n" .
                                     "Please log in to the admin dashboard to review.";

                    $mail->send();
                }
            }
        } catch (Throwable $e) {
            error_log('TNB SMTP Error: ' . $e->getMessage());
        }

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Request submitted successfully. Your request number is ' . $requestNumber,
            'request_number' => $requestNumber,
            'quotation_id' => $quotationId,
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('TNB Quotaion Submition Error: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
        error_log('Stack trace: ' . $exception->getTraceAsString());

        return ['success' => false, 'message' => APP_DEBUG ? $exception->getMessage() : 'Unable to submit request at the moment.'];
    }
}
