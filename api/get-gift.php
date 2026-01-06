<?php
/**
 * Festival System - Get Gift API
 * Provides lottery-based gift distribution with weighted probabilities
 * Version: 1.0.1
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Lottery.php';

$user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : null;

if (empty($user_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'شناسه کاربر الزامی است | User ID is required'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = getDatabaseConnection();

    // Check if user exists and can participate
    $stmt = $pdo->prepare('SELECT last_participation FROM users WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'کاربر یافت نشد | User not found'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check cooldown (24 hours)
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $lastParticipation = $user['last_participation'] ? new DateTime($user['last_participation']) : null;
    $cooldownHours = 24;

    if ($lastParticipation) {
        $hoursSinceLastParticipation = $now->diff($lastParticipation)->h + ($now->diff($lastParticipation)->days * 24);
        if ($hoursSinceLastParticipation < $cooldownHours) {
            $remainingHours = $cooldownHours - $hoursSinceLastParticipation;
            echo json_encode([
                'success' => false,
                'message' => "لطفاً {$remainingHours} ساعت صبر کنید | Please wait {$remainingHours} hours"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Get discount types for lottery
    $stmt = $pdo->query('SELECT id, percent, weight, prefix, target_product FROM discount_types WHERE weight > 0');
    $discountTypes = $stmt->fetchAll();

    if (empty($discountTypes)) {
        echo json_encode([
            'success' => false,
            'message' => 'هیچ تخفیفی موجود نیست | No discounts available'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Build prize array
    $prizes = [];
    foreach ($discountTypes as $discount) {
        $percent = (int)$discount['percent'];
        $code = strtoupper(substr($discount['prefix'], 0, 8)) . mt_rand(1000, 9999);

        $prizes[] = [
            'name' => "{$percent}% تخفیف | {$percent}% Discount",
            'weight' => (float)$discount['weight'],
            'type' => 'prize',
            'code' => $code,
            'percent' => $percent,
            'product' => $discount['target_product'],
            'discount_type_id' => $discount['id']
        ];
    }

    // Add no prize options
    $prizes[] = ['name' => 'بدون جایزه | No Prize', 'weight' => 20.0, 'type' => 'pooch'];
    $prizes[] = ['name' => 'امتحان دوباره | Try Again', 'weight' => 10.0, 'type' => 'respin'];

    // Draw prize
    $result = Lottery::draw($prizes);

    if (!$result) {
        throw new Exception('Lottery draw failed');
    }

    $gift = null;
    $isWinner = false;

    if (isset($result['type']) && $result['type'] === 'prize') {
        $isWinner = true;
        $gift = [
            'type' => 'discount',
            'title' => $result['name'],
            'description' => "کد تخفیف {$result['percent']}% برای محصول: {$result['product']} | {$result['percent']}% discount code for: {$result['product']}",
            'code' => $result['code'],
            'percent' => $result['percent'],
            'product' => $result['product']
        ];

        // Update user participation
        $stmt = $pdo->prepare('UPDATE users SET last_participation = NOW(), total_codes = total_codes + 1 WHERE user_id = ?');
        $stmt->execute([$user_id]);

        // Save discount code
        $stmt = $pdo->prepare('INSERT INTO discounts (code, weight, product_id, panel_id, expiry, created_at, used) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW(), 0)');
        $stmt->execute([
            $result['code'],
            $result['weight'],
            'all',
            '/all'
        ]);

    } else {
        $gift = [
            'type' => $result['type'] === 'respin' ? 'respin' : 'no_prize',
            'title' => $result['name'],
            'description' => $result['type'] === 'respin' ?
                'امتحان دوباره! | Try again!' :
                'متاسفانه جایزه‌ای دریافت نکردید | Unfortunately, no prize this time',
            'code' => null
        ];
    }

    // Log the event
    logEvent('gift_attempt', [
        'user_id' => $user_id,
        'result' => $result['type'],
        'prize_code' => $gift['code'] ?? null,
        'prize_value' => $gift['percent'] ?? 0
    ]);

    echo json_encode([
        'success' => true,
        'gift' => $gift,
        'is_winner' => $isWinner,
        'next_available' => $isWinner ? date('Y-m-d H:i:s', strtotime('+24 hours')) : null
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Get gift error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطا در دریافت هدیه | Error receiving gift: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}