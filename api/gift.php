<?php
/**
 * Yalda Festival - Gift Generation Endpoint
 * Generates gift code and sends it to bot via API
 */

define('YALDA_API', true);
require_once __DIR__ . '/config.php';

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['telegram_id']) || !isset($data['fruit_type'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => ERROR_INVALID_REQUEST
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$telegramId = trim($data['telegram_id']);
$fruitType = strtolower(trim($data['fruit_type']));

// Validate fruit type
if (!in_array($fruitType, ['watermelon', 'pomegranate'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'نوع میوه نامعتبر | Invalid fruit type'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

yaldaLog("Gift request", ['user_id' => $telegramId, 'fruit' => $fruitType]);

// STEP 1: Check if user exists in bot - FIXED: use chat_id parameter
$userCheckResponse = callBotAPI('users', 'GET', [
    'actions' => 'user',
    'chat_id' => $telegramId  // FIXED: was 'chatid', now 'chat_id'
]);

if (!$userCheckResponse['success'] || 
    !isset($userCheckResponse['data']['status']) || 
    $userCheckResponse['data']['status'] !== true ||
    empty($userCheckResponse['data']['obj']['users'])) {
    
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => ERROR_USER_NOT_STARTED_BOT
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

yaldaLog("User exists in bot", $userCheckResponse['data']['obj']['users'][0]);

// STEP 2: Check if user already participated (using local log file)
$participationFile = __DIR__ . '/yalda_participants.json';
$participants = [];

if (file_exists($participationFile)) {
    $participants = json_decode(file_get_contents($participationFile), true) ?: [];
}

if (isset($participants[$telegramId])) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => ERROR_ALREADY_PARTICIPATED,
        'previous_gift' => $participants[$telegramId]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// STEP 3: Select gift based on fruit type and weighted random
$giftOptions = ($fruitType === 'watermelon') ? WATERMELON_GIFTS : POMEGRANATE_GIFTS;

// Weighted random selection
$totalWeight = array_sum(array_column($giftOptions, 'weight'));
$random = mt_rand(1, $totalWeight);
$currentWeight = 0;
$selectedGift = $giftOptions[0]; // Fallback

foreach ($giftOptions as $gift) {
    $currentWeight += $gift['weight'];
    if ($random <= $currentWeight) {
        $selectedGift = $gift;
        break;
    }
}

// Generate unique gift code
$uniqueCode = strtoupper(YALDA_GIFT_PREFIX . bin2hex(random_bytes(4)));

yaldaLog("Gift selected", ['code' => $uniqueCode, 'gift' => $selectedGift]);

// STEP 4: Create gift in bot based on type
if ($selectedGift['type'] === 'discount_percentage') {
    // Create discount code in bot via API - FIXED: action name and parameters
    $discountResult = callBotAPI('discount', 'POST', [
        'actions' => 'discount_sell_add',  // FIXED: was 'discountselladd'
        'code' => $uniqueCode,
        'percent' => $selectedGift['value'],  // FIXED: was 'value', now 'percent'
        'limit_use' => 1,  // One-time use
        'agent' => 'allusers',
        'usefirst' => 0,
        'useuser' => $telegramId,  // Only this user can use it
        'code_product' => 'all',
        'code_panel' => 'all',
        'time' => strtotime('+30 days'),  // Expires in 30 days
        'type' => 'all'
    ]);
    
    yaldaLog("Discount API response", $discountResult);
    
    if (!$discountResult['success']) {
        yaldaLog("Failed to create discount code - API connection error", $discountResult);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'خطا در ایجاد کد تخفیف | Error creating discount code',
            'debug' => $discountResult['error'] ?? 'API connection failed'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($discountResult['data']['status']) || $discountResult['data']['status'] !== true) {
        yaldaLog("Failed to create discount code - Bot API error", $discountResult['data']);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'خطا در ایجاد کد تخفیف | Error creating discount code',
            'api_error' => $discountResult['data']['msg'] ?? 'Unknown API error'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    yaldaLog("Discount code created successfully", $discountResult['data']);
    
} elseif ($selectedGift['type'] === 'balance_credit') {
    // Add balance to user via API - FIXED: action name and parameter
    $balanceResult = callBotAPI('users', 'POST', [
        'actions' => 'add_balance',  // FIXED: was 'addbalance'
        'chat_id' => $telegramId,    // FIXED: was 'chatid'
        'amount' => $selectedGift['value']
    ]);
    
    yaldaLog("Balance API response", $balanceResult);
    
    if (!$balanceResult['success']) {
        yaldaLog("Failed to add balance - API connection error", $balanceResult);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'خطا در افزودن اعتبار | Error adding balance',
            'debug' => $balanceResult['error'] ?? 'API connection failed'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($balanceResult['data']['status']) || $balanceResult['data']['status'] !== true) {
        yaldaLog("Failed to add balance - Bot API error", $balanceResult['data']);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'خطا در افزودن اعتبار | Error adding balance',
            'api_error' => $balanceResult['data']['msg'] ?? 'Unknown API error'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    yaldaLog("Balance added successfully", $balanceResult['data']);
}

// STEP 5: Send Telegram notification
$notificationSent = sendTelegramGiftNotification($telegramId, $uniqueCode, $selectedGift['description']);
yaldaLog("Telegram notification sent", ['success' => $notificationSent]);

// STEP 6: Record participation
$participants[$telegramId] = [
    'code' => $uniqueCode,
    'fruit' => $fruitType,
    'gift_type' => $selectedGift['type'],
    'value' => $selectedGift['value'],
    'description' => $selectedGift['description'],
    'timestamp' => time(),
    'date' => date('Y-m-d H:i:s')
];

file_put_contents($participationFile, json_encode($participants, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

yaldaLog("Participation recorded", $participants[$telegramId]);

// Return success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'هدیه با موفقیت ارسال شد | Gift sent successfully',
    'gift' => [
        'code' => $uniqueCode,
        'description' => $selectedGift['description'],
        'type' => $selectedGift['type'],
        'value' => $selectedGift['value'],
        'expires' => $selectedGift['type'] === 'discount_percentage' ? date('Y-m-d', strtotime('+30 days')) : null
    ]
], JSON_UNESCAPED_UNICODE);
?>
