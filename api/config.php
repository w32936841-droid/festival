<?php
/**
 * Yalda Festival API Configuration
 * Connection settings for bot server communication
 */

// Prevent direct access
if (!defined('YALDA_API')) {
    define('YALDA_API', true);
}

// Bot API Configuration
define('BOT_API_BASE_URL', 'https://bot.onvipone.ir/api');
define('BOT_API_TOKEN', '199115607d51221b49ec07ea23ac8dbb');

// Yalda Gift Configuration
define('YALDA_EVENT_NAME', 'Yalda Night 2025');
define('YALDA_GIFT_PREFIX', 'YALDA2025-');

// Gift Type Configuration (what each fruit gives)
define('WATERMELON_GIFTS', [
    [
        'type' => 'discount_percentage',
        'value' => 10,
        'description' => 'تخفیف ۱۰٪ برای خرید بعدی | 10% discount for next purchase',
        'weight' => 40 // 40% chance
    ],
    [
        'type' => 'discount_percentage',
        'value' => 15,
        'description' => 'تخفیف ۱۵٪ برای خرید بعدی | 15% discount for next purchase',
        'weight' => 30 // 30% chance
    ],
    [
        'type' => 'discount_percentage',
        'value' => 20,
        'description' => 'تخفیف ۲۰٪ برای خرید بعدی | 20% discount for next purchase',
        'weight' => 20 // 20% chance
    ],
    [
        'type' => 'balance_credit',
        'value' => 5000,
        'description' => '۵,۰۰۰ تومان اعتبار هدیه | 5,000 Toman gift credit',
        'weight' => 10 // 10% chance
    ]
]);

define('POMEGRANATE_GIFTS', [
    [
        'type' => 'discount_percentage',
        'value' => 15,
        'description' => 'تخفیف ۱۵٪ برای خرید بعدی | 15% discount for next purchase',
        'weight' => 35 // 35% chance
    ],
    [
        'type' => 'discount_percentage',
        'value' => 20,
        'description' => 'تخفیف ۲۰٪ برای خرید بعدی | 20% discount for next purchase',
        'weight' => 30 // 30% chance
    ],
    [
        'type' => 'discount_percentage',
        'value' => 25,
        'description' => 'تخفیف ۲۵٪ برای خرید بعدی | 25% discount for next purchase',
        'weight' => 20 // 20% chance
    ],
    [
        'type' => 'balance_credit',
        'value' => 10000,
        'description' => '۱۰,۰۰۰ تومان اعتبار هدیه | 10,000 Toman gift credit',
        'weight' => 15 // 15% chance
    ]
]);

// Telegram Notification Settings
define('TELEGRAM_NOTIFICATION_ENABLED', true);
define('TELEGRAM_NOTIFICATION_MESSAGE_FA', "🎁 تبریک! هدیه شب یلدا شما:\n\n<code>{code}</code>\n\n{description}\n\nبرای استفاده از هدیه، کد را در بخش خرید وارد کنید.");
define('TELEGRAM_NOTIFICATION_MESSAGE_EN', "🎁 Congratulations! Your Yalda Night gift:\n\n<code>{code}</code>\n\n{description}\n\nTo use your gift, enter the code during purchase.");

// Error Messages
define('ERROR_USER_NOT_FOUND', 'آیدی کاربری یافت نشد | User ID not found');
define('ERROR_USER_NOT_STARTED_BOT', 'لطفا ابتدا ربات را استارت کنید | Please start the bot first');
define('ERROR_ALREADY_PARTICIPATED', 'شما قبلاً در این رویداد شرکت کرده‌اید | You have already participated in this event');
define('ERROR_API_CONNECTION', 'خطا در ارتباط با سرور | Server connection error');
define('ERROR_INVALID_REQUEST', 'درخواست نامعتبر | Invalid request');

// Database settings (if you want to log locally)
define('LOG_TO_FILE', true);
define('LOG_FILE_PATH', __DIR__ . '/yalda_log.txt');

// Timezone
date_default_timezone_set('Asia/Tehran');

// Helper function for logging
function yaldaLog($message, $data = null) {
    if (!LOG_TO_FILE) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= "\n" . print_r($data, true);
    }
    
    $logMessage .= "\n" . str_repeat('-', 80) . "\n";
    
    file_put_contents(LOG_FILE_PATH, $logMessage, FILE_APPEND);
}

// Helper function for bot API calls - USES CORRECT METHOD AND PARAMETER NAMES
function callBotAPI($endpoint, $method = 'POST', $data = []) {
    $url = BOT_API_BASE_URL . '/' . ltrim($endpoint, '/');
    
    $headers = [
        'Content-Type: application/json',
        'Token: ' . BOT_API_TOKEN
    ];
    
    $jsonData = json_encode($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Force the HTTP method using CURLOPT_CUSTOMREQUEST
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    
    // Always send data as JSON body (bot API reads from php://input)
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    //curl_close($ch);
    
    yaldaLog("Bot API Call: $method $url", [
        'request_data' => $data,
        'request_json' => $jsonData,
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ]);
    
    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }
    
    $decoded = json_decode($response, true);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => $decoded,
        'http_code' => $httpCode,
        'raw' => $response
    ];
}

// Helper function to send Telegram message via bot - FIXED ACTION NAME
function sendTelegramGiftNotification($chatId, $giftCode, $description) {
    if (!TELEGRAM_NOTIFICATION_ENABLED) return true;
    
    $message = str_replace(
        ['{code}', '{description}'],
        [$giftCode, $description],
        TELEGRAM_NOTIFICATION_MESSAGE_FA
    );
    
    $result = callBotAPI('users', 'POST', [
        'actions' => 'send_message',  // FIXED: use underscore
        'chat_id' => $chatId,         // FIXED: use underscore
        'text' => $message
    ]);
    
    return $result['success'];
}
?>
