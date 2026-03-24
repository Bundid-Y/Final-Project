<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';

function validate_required_fields(array $source, array $fields): array
{
    $errors = [];

    foreach ($fields as $field => $label) {
        $value = isset($source[$field]) ? sanitize_text((string) $source[$field]) : '';
        if ($value === '') {
            $errors[$field] = $label . ' is required.';
        }
    }

    return $errors;
}

function validate_email_address(string $email): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }

    return null;
}

function validate_password_rules(string $password): ?string
{
    if (mb_strlen($password) < PASSWORD_MIN_LENGTH) {
        return 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }

    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Password must contain uppercase, lowercase, and number characters.';
    }

    return null;
}

function validate_phone_number(string $phone): ?string
{
    $normalized = preg_replace('/[^0-9+]/', '', $phone) ?? '';

    if ($normalized === '') {
        return 'Please enter a phone number.';
    }

    if (mb_strlen($normalized) < 8 || mb_strlen($normalized) > 15) {
        return 'Phone number format is invalid.';
    }

    return null;
}

function validate_company_slug(string $company): ?string
{
    $company = strtolower(trim($company));

    if (!in_array($company, ['koch', 'tnb'], true)) {
        return 'Invalid company value.';
    }

    return null;
}

function merge_validation_errors(array ...$bags): array
{
    $errors = [];

    foreach ($bags as $bag) {
        foreach ($bag as $field => $message) {
            if ($message !== null && $message !== '') {
                $errors[$field] = $message;
            }
        }
    }

    return $errors;
}
