<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/session.php';

function json_response(bool $success, string $message, array $data = [], int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}

function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function form_response(bool $success, string $message, string $redirectUrl, array $oldInput = [], array $jsonData = [], int $jsonStatus = 200): never
{
    if (request_expects_json()) {
        json_response($success, $message, $jsonData, $jsonStatus);
    }

    set_flash($success ? 'success_message' : 'error_message', $message);

    if (!$success) {
        set_old_input($oldInput);
    } else {
        clear_old_input();
    }

    redirect_to($redirectUrl);
}
