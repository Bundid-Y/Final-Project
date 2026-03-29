<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/admin.php';
require_once __DIR__ . '/../../includes/crud.php';
require_once __DIR__ . '/../../includes/activity.php';

$pdo  = Database::connection();
$user = require_admin_user();
$adminId = (int) $user['id'];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirect_to(project_url('admin/dashboard.php'));
}

if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
    set_flash('error_message', 'Security token mismatch.');
    redirect_to($_SERVER['HTTP_REFERER'] ?? project_url('admin/dashboard.php'));
}

$entity = sanitize_text((string) ($_POST['entity'] ?? ''));
$action = sanitize_text((string) ($_POST['action'] ?? ''));
$id     = (int) ($_POST['id'] ?? 0);
$back   = (string) ($_POST['redirect_back'] ?? '');

if ($back === '') {
    $back = $_SERVER['HTTP_REFERER'] ?? project_url('admin/dashboard.php');
}

try {
    $result = match ($entity) {
        'slider'          => handle_slider($pdo, $action, $id, $_POST, $adminId),
        'partner'         => handle_partner($pdo, $action, $id, $_POST, $adminId),
        'product'         => handle_product($pdo, $action, $id, $_POST, $adminId),
        'truck_type'      => handle_truck_type($pdo, $action, $id, $_POST, $adminId),
        'branch'          => handle_branch($pdo, $action, $id, $_POST, $adminId),
        'email_template'  => handle_email_template($pdo, $action, $id, $_POST, $adminId),
        'email_recipient' => handle_email_recipient($pdo, $action, $id, $_POST, $adminId),
        'user'            => handle_user($pdo, $action, $id, $_POST, $adminId),
        'contact_message' => handle_contact_message($pdo, $action, $id, $_POST, $adminId),
        'featured_product' => handle_featured_product($pdo, $action, $id, $_POST, $adminId),
        'notification'    => handle_notification($pdo, $action, $id, $_POST, $adminId),
        default           => ['success' => false, 'message' => 'Unknown entity.'],
    };

    if ($result['success']) {
        // Don't create a notification for notification-related actions (prevents paradox of creating unread while marking all read)
        if ($entity !== 'notification') {
            create_notification($pdo, $adminId, 'CRUD: ' . ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $entity)), $result['message'], 'success');
        }
    }

    set_flash($result['success'] ? 'success_message' : 'error_message', $result['message']);
} catch (Throwable $e) {
    set_flash('error_message', 'Error: ' . $e->getMessage());
}

redirect_to($back);

// ── Handlers ────────────────────────────────────────────────

function handle_slider(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_slider($pdo, $post, $adminId, $_FILES),
        'update' => update_slider($pdo, $id, $post, $adminId, $_FILES),
        'delete' => delete_slider($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_partner(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_partner($pdo, $post, $adminId, $_FILES),
        'update' => update_partner($pdo, $id, $post, $adminId, $_FILES),
        'delete' => delete_partner($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_product(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_product($pdo, $post, $adminId, $_FILES),
        'update' => update_product($pdo, $id, $post, $adminId, $_FILES),
        'delete' => delete_product($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_truck_type(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_truck_type($pdo, $post, $adminId, $_FILES),
        'update' => update_truck_type($pdo, $id, $post, $adminId, $_FILES),
        'delete' => delete_truck_type($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_branch(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_branch($pdo, $post, $adminId),
        'update' => update_branch($pdo, $id, $post, $adminId),
        'delete' => delete_branch($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_email_template(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_email_template($pdo, $post, $adminId),
        'update' => update_email_template($pdo, $id, $post, $adminId),
        'delete' => delete_email_template($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_email_recipient(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'create' => create_email_recipient($pdo, $post, $adminId),
        'update' => update_email_recipient($pdo, $id, $post, $adminId),
        'delete' => delete_email_recipient($pdo, $id, $adminId),
        default  => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_user(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        'update_role'   => update_user_role($pdo, $id, sanitize_text((string) ($post['role'] ?? '')), $adminId),
        'update_status' => update_user_status($pdo, $id, sanitize_text((string) ($post['status'] ?? '')), $adminId),
        'delete'        => delete_user_admin($pdo, $id, $adminId),
        default         => ['success' => false, 'message' => 'Invalid action.'],
    };
}

function handle_contact_message(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    if ($action === 'update_status') {
        $status = sanitize_text((string) ($post['status'] ?? ''));
        $valid  = ['new', 'read', 'replied', 'archived'];
        if (!in_array($status, $valid, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }
        $stmt = $pdo->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $id]);
        log_activity($pdo, $adminId, 'CONTACT_STATUS_CHANGED', 'contact_messages', $id);
        return ['success' => true, 'message' => 'Contact message status updated.'];
    }
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->execute([':id' => $id]);
        log_activity($pdo, $adminId, 'CONTACT_DELETED', 'contact_messages', $id);
        return ['success' => true, 'message' => 'Contact message deleted.'];
    }
    return ['success' => false, 'message' => 'Invalid action.'];
}

function handle_notification(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    if ($action === 'mark_read') {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return ['success' => true, 'message' => 'Notification marked as read.'];
    }
    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
        $stmt->execute();
        return ['success' => true, 'message' => 'All notifications marked as read.'];
    }
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM notifications WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return ['success' => true, 'message' => 'Notification deleted.'];
    }
    return ['success' => false, 'message' => 'Invalid action.'];
}

function handle_featured_product(PDO $pdo, string $action, int $id, array $post, int $adminId): array
{
    return match ($action) {
        "create" => create_featured_product($pdo, $post, $adminId, $_FILES),
        "update" => update_featured_product($pdo, $id, $post, $adminId, $_FILES),
        "delete" => delete_featured_product($pdo, $id, $adminId),
        default  => ["success" => false, "message" => "Invalid action."],
    };
}
