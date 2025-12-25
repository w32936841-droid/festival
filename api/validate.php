<?php
/**
 * Yalda Festival - User ID Validation Endpoint
 * Validates if a Telegram User ID exists in the bot database
 */

define('YALDA_API', true);
require_once __DIR__ . '/config.php';

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
        'message' => ERROR_INVALID_REQUEST
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$telegramId = trim($data['telegram_id']);

// Validate format
if (!ctype_digit($telegramId) || strlen($telegramId) < 5) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'آیدی کاربری نامعتبر است | Invalid User ID format'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

yaldaLog("Validation request for User ID: $telegramId");

// Call bot API to check if user exists - FIXED: use chat_id parameter
$response = callBotAPI('users', 'GET', [
    'actions' => 'user',
    'chat_id' => $telegramId  // FIXED: was 'chatid', now 'chat_id'
]);

// Check if API call was successful
if (!$response['success']) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => ERROR_API_CONNECTION,
        'debug' => $response['error'] ?? 'Unknown error'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if user exists in bot
$apiData = $response['data'];

if (!$apiData || !isset($apiData['status']) || $apiData['status'] !== true) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => ERROR_USER_NOT_FOUND,
        'api_message' => $apiData['msg'] ?? 'Unknown error'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if user data exists
if (empty($apiData['obj']['users'])) {
    // User doesn't exist - tell them to start the bot
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => ERROR_USER_NOT_STARTED_BOT
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userData = $apiData['obj']['users'][0];

yaldaLog("User found", $userData);

// User exists and is valid
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'آیدی کاربری معتبر است | User ID is valid',
    'user' => [
        'user_id' => $userData['user_id'],
        'username' => $userData['username'] ?? 'none',
        'status' => $userData['User_Status'] ?? 'Active'
    ]
], JSON_UNESCAPED_UNICODE);
?>
