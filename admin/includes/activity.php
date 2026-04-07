<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function log_activity(PDO $pdo, ?int $userId, string $action, ?string $tableName = null, ?int $recordId = null, array $oldValues = [], array $newValues = [], ?int $companyId = null): void
{
    // Priority: explicit param > session context (dashboard company mode) > user's own company
    if ($companyId === null) {
        $companyId = $_SESSION['admin_company_id_context'] ?? null;
    }
    
    if ($companyId === null && $userId !== null) {
        $userStmt = $pdo->prepare('SELECT company_id FROM users WHERE id = :id LIMIT 1');
        $userStmt->execute([':id' => $userId]);
        $userCompanyId = $userStmt->fetchColumn();
        $companyId = $userCompanyId !== false ? (int) $userCompanyId : null;
    }
    
    // Get company name
    $companyName = null;
    if ($companyId !== null) {
        $companyStmt = $pdo->prepare('SELECT name FROM companies WHERE id = :id LIMIT 1');
        $companyStmt->execute([':id' => $companyId]);
        $companyName = $companyStmt->fetchColumn();
    }
    
    // We no longer prepend the company name so that translation mappings match the exact action string.
    $actionWithCompany = $action;
    
    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs (user_id, company_id, company_name, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
         VALUES (:user_id, :company_id, :company_name, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, :user_agent)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':company_id' => $companyId,
        ':company_name' => $companyName,
        ':action' => $actionWithCompany,
        ':table_name' => $tableName,
        ':record_id' => $recordId,
        ':old_values' => $oldValues !== [] ? json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':new_values' => $newValues !== [] ? json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':ip_address' => get_client_ip(),
        ':user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 65535),
    ]);
}

function create_notification(PDO $pdo, int $userId, string $title, string $message, string $type = 'info', ?string $relatedTable = null, ?int $relatedId = null, string $priority = 'normal', ?int $companyId = null): void
{
    // Priority: explicit param > session context (dashboard company mode) > user's own company
    if ($companyId === null) {
        $companyId = $_SESSION['admin_company_id_context'] ?? null;
    }
    
    if ($companyId === null) {
        $userStmt = $pdo->prepare('SELECT company_id FROM users WHERE id = :id LIMIT 1');
        $userStmt->execute([':id' => $userId]);
        $userCompanyId = $userStmt->fetchColumn();
        $companyId = $userCompanyId !== false ? (int) $userCompanyId : null;
    }
    
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, company_id, title, message, type, related_table, related_id, priority)
         VALUES (:user_id, :company_id, :title, :message, :type, :related_table, :related_id, :priority)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':company_id' => $companyId,
        ':title' => $title,
        ':message' => $message,
        ':type' => $type,
        ':related_table' => $relatedTable,
        ':related_id' => $relatedId,
        ':priority' => $priority,
    ]);
}
