<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function log_activity(PDO $pdo, ?int $userId, string $action, ?string $tableName = null, ?int $recordId = null, array $oldValues = [], array $newValues = []): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
         VALUES (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address, :user_agent)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':action' => $action,
        ':table_name' => $tableName,
        ':record_id' => $recordId,
        ':old_values' => $oldValues !== [] ? json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':new_values' => $newValues !== [] ? json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':ip_address' => get_client_ip(),
        ':user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 65535),
    ]);
}

function create_notification(PDO $pdo, int $userId, string $title, string $message, string $type = 'info', ?string $relatedTable = null, ?int $relatedId = null, string $priority = 'normal'): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, type, related_table, related_id, priority)
         VALUES (:user_id, :title, :message, :type, :related_table, :related_id, :priority)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':message' => $message,
        ':type' => $type,
        ':related_table' => $relatedTable,
        ':related_id' => $relatedId,
        ':priority' => $priority,
    ]);
}
