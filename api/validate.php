<?php
/**
 * Festival System - User ID Validation Endpoint
 * Validates if a Telegram User ID exists in the bot database
 * Version: 1.0.1
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/bot-api.php';

// CORS headers - MUST be before any output
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get and decode JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['telegram_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$telegramId = trim($data['telegram_id']);

// Validate format (Telegram user IDs are numeric and typically 9-10 digits)
if (!ctype_digit($telegramId) || strlen($telegramId) < 5 || strlen($telegramId) > 15) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid User ID format'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Log the validation request
logEvent('user_validation_attempt', ['user_id' => $telegramId]);

// Check if bot token is configured
if (BOT_API_TOKEN === 'REPLACE_WITH_TOKEN' || empty(BOT_API_TOKEN)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Bot API token not configured',
        'details' => 'Please configure BOT_API_TOKEN in config.php'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if user exists using bot API
$userCheck = checkTelegramUser($telegramId);

// Log for debugging
logEvent('bot_api_check', [
    'user_id' => $telegramId,
    'result' => $userCheck
]);

if (!$userCheck['valid']) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => $userCheck['error'] ?: 'User not found in bot system',
        'details' => 'Please start the bot first and try again',
        'debug' => [
            'user_id' => $telegramId,
            'token_set' => !empty(BOT_API_TOKEN),
            'api_url' => BOT_API_URL,
            'error' => $userCheck['error'] ?? 'Unknown error'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// User is valid
logEvent('user_validation_success', ['user_id' => $telegramId]);

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'User ID is valid',
    'user' => $userCheck['user_data'] ?? [
        'user_id' => $telegramId,
        'status' => 'active'
    ]
], JSON_UNESCAPED_UNICODE);
?>
