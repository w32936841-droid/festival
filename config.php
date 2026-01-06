<?php
// Festival System Global Configuration v1.0.2
declare(strict_types=1);

// Include localization system
require_once __DIR__ . '/languages/i18n.php';

// Versioning
define('FEST_VERSION', 'v0.2');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'onvipir1_yalda_festival');
define('DB_USER', 'onvipir1_yalda_user');
define('DB_PASSWORD', 'k5n4jBjM@W=(8Ya-');

// Production Database (uncomment and configure for production)
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'onvipir1_yalda_festival');
// define('DB_USER', 'onvipir1_yalda_user');
// define('DB_PASSWORD', 'k5n4jBjM@W=(8Ya-');

// Telegram Bot API Configuration
// For testing, use a test token or enter the actual bot token
define('BOT_API_TOKEN', '199115607d51221b49ec07ea23ac8dbb'); // Change to actual token
define('BOT_API_URL', 'https://bot.onvipone.ir/api');

// System Settings
ini_set('max_execution_time', 30);
date_default_timezone_set('UTC');

// Database Connection Function
function getDatabaseConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    return $pdo;
}

// Utility Functions
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function logEvent(string $action, array $data = []): void {
    $pdo = getDatabaseConnection();
    $userId = $data['user_id'] ?? null;
    $details = json_encode($data);

    $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, timestamp) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $action]);
}
?>