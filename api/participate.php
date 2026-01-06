<?php
/**
 * Festival System - User Participation & Lottery Endpoint
 * Handles user participation, lottery drawing, and prize distribution
 * Version: 1.0.1
 */

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

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Lottery.php';
require_once __DIR__ . '/bot-api.php';

header('Content-Type: application/json; charset=utf-8');

// Basic rate limiting (simple implementation)
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_key = "rate_limit_$client_ip";
$requests_per_minute = 10; // Allow 10 requests per minute

// Simple file-based rate limiting (use Redis/memcached in production)
$rate_file = sys_get_temp_dir() . "/festival_rate_$client_ip.txt";
$current_time = time();

if (file_exists($rate_file)) {
    $rate_data = json_decode(file_get_contents($rate_file), true);
    $minute_start = floor($current_time / 60) * 60;

    if ($rate_data['minute'] === $minute_start) {
        if ($rate_data['count'] >= $requests_per_minute) {
            http_response_code(429);
            echo json_encode([
                'status' => 'error',
                'message' => 'Too many requests. Please wait before trying again.'
            ]);
            exit;
        }
        $rate_data['count']++;
    } else {
        $rate_data = ['minute' => $minute_start, 'count' => 1];
    }
} else {
    $rate_data = ['minute' => floor($current_time / 60) * 60, 'count' => 1];
}

file_put_contents($rate_file, json_encode($rate_data));

$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['user_id']) ? trim($input['user_id']) : null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    // Check if bot token is configured
    if (BOT_API_TOKEN === 'REPLACE_WITH_TOKEN' || empty(BOT_API_TOKEN)) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Bot API token not configured'
        ]);
        exit;
    }

    // Validate user exists in bot system first
    $userCheck = checkTelegramUser($userId);
    if (!$userCheck['valid']) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
                'message' => 'User not found in bot system'
        ]);
        exit;
    }

    // Cooldown: 24 hours
    $stmt = $pdo->prepare('SELECT last_participation, total_codes FROM users WHERE user_id = :uid');
    $stmt->execute([':uid' => $userId]);
    $userRow = $stmt->fetch();

    $now = new DateTime('now', new DateTimeZone('UTC'));
    $cooldownSeconds = 24 * 3600;

    if ($userRow) {
        $lastPart = $userRow['last_participation'] ?? null;
        if ($lastPart) {
            $lastTs = strtotime($lastPart);
            if ($lastTs !== false && ($now->getTimestamp() - $lastTs) < $cooldownSeconds) {
                $remainingHours = ceil(($cooldownSeconds - ($now->getTimestamp() - $lastTs)) / 3600);
                http_response_code(429);
                echo json_encode([
                    'status' => 'cooldown',
                    'message' => "Please wait {$remainingHours} hours between participations."
                ]);
                exit;
            }
        }
    }

    // Build prizes from discount_types table
    $weightsStmt = $pdo->query('SELECT id, percent, weight, prefix, expiry_hours, target_product FROM discount_types WHERE weight > 0');
    $prizes = [];
    $discountTypes = [];

    while ($row = $weightsStmt->fetch()) {
        $percent = (int)($row['percent'] ?? 0);
        $prefix = $row['prefix'] ?? '';
        $prizeName = $percent . '% Discount';

        // Generate unique code
        $code = strtoupper(substr($prefix, 0, 8)) . mt_rand(1000, 9999);

        $prizes[] = [
            'name' => $prizeName,
            'weight' => (float)($row['weight'] ?? 0),
            'type' => 'prize',
            'code' => $code,
            'prefix' => $prefix,
            'discount_type_id' => $row['id'],
            'percent' => $percent,
            'expiry_hours' => $row['expiry_hours'],
            'target_product' => $row['target_product']
        ];

        $discountTypes[$row['id']] = $row;
    }

    // Add No Prize and Try Again options
    $prizes[] = ['name' => 'No Prize', 'weight' => 20.0, 'type' => 'pooch'];
    $prizes[] = ['name' => 'Try Again', 'weight' => 10.0, 'type' => 'respin'];

    // Execute lottery (server-side weighted random selection)
    $result = Lottery::draw($prizes);
    if (!$result) {
        throw new Exception('Lottery draw failed.');
    }

    // Persist participation
    if (!$userRow) {
        // New user
        $stmtIns = $pdo->prepare('INSERT INTO users (user_id, codes_received, last_participation, expiry_date, total_codes) VALUES (:uid, :codes, :lp, :exp, 0)');
        $stmtIns->execute([
            ':uid' => $userId,
            ':codes' => json_encode([]),
            ':lp' => $now->format('Y-m-d H:i:s'),
            ':exp' => null
        ]);
        $currentTotal = 0;
    } else {
        $currentTotal = (int)($userRow['total_codes'] ?? 0);
    }

    $prizeData = null;
    $botDiscountCreated = false;

    if (isset($result['type']) && $result['type'] === 'prize') {
        $prizeCode = $result['code'];
        $discountType = $discountTypes[$result['discount_type_id']] ?? null;

        if ($discountType) {
            // Create discount in bot system
            $expiry = $now->add(new DateInterval('PT' . $discountType['expiry_hours'] . 'H'));

            $botDiscountData = [
                'code' => $prizeCode,
                'price' => $discountType['percent'], // Using percent as price for simplicity
                'limit_discount' => 1,
                'agent' => 'allusers',
                'product_id' => 'all',
                'panel_id' => '/all',
                'time' => $discountType['expiry_hours'],
                'type' => 'all'
            ];

            $botResult = createDiscountInBot($botDiscountData);
            $botDiscountCreated = $botResult['success'] ?? false;

            if (!$botDiscountCreated) {
                // Log the error but don't fail the request
                error_log('Failed to create discount in bot: ' . json_encode($botResult));
            }
        }

        $prizeData = [
            'code' => $prizeCode,
            'name' => $result['name'] ?? 'Discount',
            'percent' => $result['percent'] ?? 0,
            'bot_created' => $botDiscountCreated
        ];

        // Update user's participation data
        $stmtUp = $pdo->prepare('UPDATE users SET last_participation = :lp, expiry_date = NULL, total_codes = total_codes + 1 WHERE user_id = :uid');
        $stmtUp->execute([':lp' => $now->format('Y-m-d H:i:s'), ':uid' => $userId]);

        // Save discount code to database
        $stmtDiscount = $pdo->prepare('INSERT INTO discounts (code, weight, product_id, panel_id, expiry, created_at, used) VALUES (:code, :weight, :product_id, :panel_id, :expiry, NOW(), 0)');
        $stmtDiscount->execute([
            ':code' => $prizeCode,
            ':weight' => (float)($result['weight'] ?? 0),
            ':product_id' => 'all',
            ':panel_id' => '/all',
            ':expiry' => $expiry ? $expiry->format('Y-m-d H:i:s') : null
        ]);

        // Send notification to user via bot
        $notificationMessage = "🎉 Congratulations! You won {$result['name']}!\n\n";
        $notificationMessage .= "Your discount code: {$prizeCode}\n";
        $notificationMessage .= "Use this code in the bot.\n\n";
        $notificationMessage .= "🎪 Festival System";

        sendTelegramNotification($userId, $notificationMessage);

    } else {
        // Handle 'respin' or 'pooch' outcomes
        $type = $result['type'] ?? 'pooch';

        if ($type === 'respin') {
            // Allow another attempt without cooldown
            http_response_code(200);
            echo json_encode([
                'status' => 'respin',
                'message' => 'Try again!',
                'prize' => ['code' => null, 'name' => 'Try Again']
            ]);
            exit;
        }

        // No prize
        $prizeData = ['code' => null, 'name' => $result['name'] ?? 'No Prize'];
    }

    // Log the participation event
    $logStmt = $pdo->prepare('INSERT INTO logs (user_id, action, prize_code, prize_value) VALUES (:uid, :action, :prize_code, :prize_value)');
    $logStmt->execute([
        ':uid' => $userId,
        ':action' => $prizeData['name'] ?? 'participation',
        ':prize_code' => $prizeData['code'] ?? null,
        ':prize_value' => $prizeData['percent'] ?? 0
    ]);

    // Response
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'prize' => $prizeData,
        'total_codes' => $currentTotal + 1,
        'bot_notification_sent' => isset($prizeCode)
    ]);

} catch (Exception $e) {
    error_log('Participation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 

