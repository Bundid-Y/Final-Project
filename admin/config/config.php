<?php
declare(strict_types=1);

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Asia/Bangkok');

defined('APP_NAME') || define('APP_NAME', 'KOCH & TNB');
defined('APP_DEBUG') || define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
defined('DB_HOST') || define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
defined('DB_PORT') || define('DB_PORT', getenv('DB_PORT') ?: '3306');
defined('DB_NAME') || define('DB_NAME', getenv('DB_NAME') ?: 'koch_tnb_system');
defined('DB_USERNAME') || define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
defined('DB_PASSWORD') || define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
defined('DB_CHARSET') || define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
defined('SESSION_NAME') || define('SESSION_NAME', getenv('SESSION_NAME') ?: 'koch_tnb_session');
defined('SESSION_LIFETIME') || define('SESSION_LIFETIME', (int) (getenv('SESSION_LIFETIME') ?: 86400));
defined('PASSWORD_MIN_LENGTH') || define('PASSWORD_MIN_LENGTH', (int) (getenv('PASSWORD_MIN_LENGTH') ?: 8));
defined('UPLOAD_ROOT') || define('UPLOAD_ROOT', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'uploads');
defined('UPLOAD_MAX_SIZE') || define('UPLOAD_MAX_SIZE', (int) (getenv('UPLOAD_MAX_SIZE') ?: 10485760));
