<?php
// version: 0.3 - Get Gift API with full features
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load database config
require_once __DIR__ . '/../config.php';

// Get user_id from request
$user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : null;

if (empty($user_id)) {
    echo json_encode([
        'success' => false, 
        'message' => 'شناسه کاربر الزامی است'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 1. Get all ACTIVE gifts with weights > 0
    $stmt = $pdo->query("SELECT id, type, title, description, weight FROM gifts WHERE weight > 0 AND is_active = 1");
    $gifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($gifts)) {
        echo json_encode([
            'success' => false, 
            'message' => 'هیچ هدیه‌ای موجود نیست'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 2. Calculate total weight
    $total_weight = array_sum(array_column($gifts, 'weight'));
    
    // 3. Generate random number between 1 and total_weight
    $random = rand(1, $total_weight);
    
    // 4. Select gift based on weight
    $cumulative = 0;
    $selected_gift = null;
    
    foreach ($gifts as $gift) {
        $cumulative += $gift['weight'];
        if ($random <= $cumulative) {
            $selected_gift = $gift;
            break;
        }
    }
    
    // 5. Generate unique gift code
    $gift_code = strtoupper('YALDA-' . bin2hex(random_bytes(4)));
    
    // 6. Get client info for logging
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // 7. Log the gift in gift_logs
    $stmt = $pdo->prepare(
        "INSERT INTO gift_logs (user_id, gift_id, gift_code, client_ip, user_agent) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $user_id, 
        $selected_gift['id'], 
        $gift_code,
        $client_ip,
        $user_agent
    ]);
    
    // 8. Return response
    echo json_encode([
        'success' => true,
        'gift' => [
            'type' => $selected_gift['type'],
            'title' => $selected_gift['title'],
            'description' => $selected_gift['description'],
            'code' => $gift_code
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'خطا در دریافت هدیه: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}