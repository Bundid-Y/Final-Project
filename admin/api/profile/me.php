<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/profile.php';

$user = authenticated_user();
if ($user === null) {
    json_response(false, 'Unauthenticated.', [], 401);
}

$pdo = Database::connection();
$profile = get_profile_summary($pdo, (int) $user['id']);
$json = [
    'profile' => $profile,
    'activities' => get_recent_activity_logs($pdo, (int) $user['id']),
    'koch_quotations' => get_koch_user_quotations($pdo, (int) $user['id']),
    'tnb_quotations' => get_tnb_user_quotations($pdo, (int) $user['id']),
];

json_response(true, 'Profile loaded.', $json);
