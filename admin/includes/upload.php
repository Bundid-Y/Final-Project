<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';

function ensure_directory(string $path): void
{
    if (!is_dir($path)) {
        if (!mkdir($path, 0777, true)) {
            throw new RuntimeException('Unable to create upload directory: ' . $path);
        }
        chmod($path, 0777);
    }
    if (!is_dir($path)) {
        throw new RuntimeException('Directory still not accessible: ' . $path);
    }
}

function handle_uploaded_file(string $fieldName, string $context): ?array
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed.');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > UPLOAD_MAX_SIZE) {
        throw new RuntimeException('Uploaded file exceeds the allowed size.');
    }

    $originalName = (string) ($file['name'] ?? 'upload');
    $tmpName = (string) ($file['tmp_name'] ?? '');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName) ?: 'application/octet-stream';

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Unsupported file type. Allowed: JPG, PNG, WEBP, PDF.');
    }

    $extension = $allowedMimeTypes[$mimeType];
    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetDirectory = UPLOAD_ROOT . DIRECTORY_SEPARATOR . trim($context, DIRECTORY_SEPARATOR);
    
    // Debug: log the path for troubleshooting
    error_log("Upload directory: " . $targetDirectory);
    error_log("UPLOAD_ROOT constant: " . UPLOAD_ROOT);
    
    ensure_directory($targetDirectory);

    $targetAbsolutePath = $targetDirectory . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($tmpName, $targetAbsolutePath)) {
        throw new RuntimeException('Unable to move uploaded file.');
    }

    return [
        'original_name' => $originalName,
        'file_name' => $safeName,
        'file_path' => $targetAbsolutePath,
        'public_path' => upload_public_path($targetAbsolutePath),
        'file_size' => $size,
        'mime_type' => $mimeType,
        'file_type' => $extension,
    ];
}

function save_attachment_record(PDO $pdo, string $tableName, int $recordId, array $fileMeta, int $uploadedBy): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO file_attachments (
            table_name, record_id, file_name, original_name, file_path, file_size, file_type, mime_type, uploaded_by, is_active
         ) VALUES (
            :table_name, :record_id, :file_name, :original_name, :file_path, :file_size, :file_type, :mime_type, :uploaded_by, 1
         )'
    );

    $stmt->execute([
        ':table_name' => $tableName,
        ':record_id' => $recordId,
        ':file_name' => $fileMeta['file_name'],
        ':original_name' => $fileMeta['original_name'],
        ':file_path' => $fileMeta['public_path'],
        ':file_size' => $fileMeta['file_size'],
        ':file_type' => $fileMeta['file_type'],
        ':mime_type' => $fileMeta['mime_type'],
        ':uploaded_by' => $uploadedBy,
    ]);

    return (int) $pdo->lastInsertId();
}
