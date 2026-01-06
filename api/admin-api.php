<?php
// Admin API for Festival System v1.0.1

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

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access'], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getDatabaseConnection();

    switch ($action) {
        case 'get_translations':
            getTranslations();
            break;

        case 'dashboard_stats':
            getDashboardStats($pdo);
            break;

        case 'get_discounts':
            getDiscountTypes($pdo);
            break;

        case 'create_discount':
            createDiscountType($pdo);
            break;

        case 'update_discount':
            updateDiscountType($pdo);
            break;

        case 'delete_discount':
            deleteDiscountType($pdo);
            break;

        case 'get_themes':
            getThemes($pdo);
            break;

        case 'create_theme':
            createTheme($pdo);
            break;

        case 'update_theme':
            updateTheme($pdo);
            break;

        case 'activate_theme':
            activateTheme($pdo);
            break;

        case 'get_users':
            getUsers($pdo);
            break;

        case 'get_user_details':
            getUserDetails($pdo);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

function getDashboardStats($pdo) {
    // Get time range from request
    $timeRange = $_GET['range'] ?? '24h';

    $timeCondition = match($timeRange) {
        '1h' => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
        '6h' => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 6 HOUR)',
        '12h' => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 12 HOUR)',
        '24h' => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)',
        '7d' => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
        '30d' => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
        default => 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'
    };

    // Total participants
    $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM logs WHERE 1=1 $timeCondition");
    $participants = $stmt->fetch()['total'];

    // Total prizes won
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM logs WHERE prize_code IS NOT NULL AND prize_code != '' $timeCondition");
    $prizesWon = $stmt->fetch()['total'];

    // Prize distribution
    $stmt = $pdo->query("SELECT prize_value, COUNT(*) as count FROM logs WHERE prize_value IS NOT NULL $timeCondition GROUP BY prize_value");
    $prizeDistribution = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Recent activity (last 10)
    $stmt = $pdo->query("SELECT user_id, action, prize_code, timestamp FROM logs WHERE 1=1 $timeCondition ORDER BY timestamp DESC LIMIT 10");
    $recentActivity = $stmt->fetchAll();

    // CPU/RAM monitoring simulation (in real implementation, this would come from server monitoring)
    $serverStats = [
        'cpu' => rand(10, 85),
        'ram' => rand(20, 90),
        'disk' => rand(15, 75)
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'participants' => $participants,
            'prizes_won' => $prizesWon,
            'prize_distribution' => $prizeDistribution,
            'recent_activity' => $recentActivity,
            'server_stats' => $serverStats,
            'time_range' => $timeRange
        ]
    ], JSON_UNESCAPED_UNICODE);
}

function getDiscountTypes($pdo) {
    $stmt = $pdo->query("SELECT * FROM discount_types ORDER BY created_at DESC");
    $discounts = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $discounts], JSON_UNESCAPED_UNICODE);
}

function createDiscountType($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['percent', 'weight', 'prefix', 'expiry_hours'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO discount_types
        (percent, max_users, allowed_users, expiry_hours, purchase_type, usage_type, per_user_limit, target_panel, target_product, weight, prefix)
        VALUES (?, 1, 'allusers', ?, 'first', 'renewal', 1, '/all', ?, ?, ?)
    ");

    $stmt->execute([
        $data['percent'],
        $data['expiry_hours'],
        $data['target_product'] ?? '30GB 30-day',
        $data['weight'],
        $data['prefix']
    ]);

    echo json_encode(['success' => true, 'message' => 'Discount type created successfully'], JSON_UNESCAPED_UNICODE);
}

function updateDiscountType($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Discount ID required'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE discount_types SET
        percent = ?, expiry_hours = ?, target_product = ?, weight = ?, prefix = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $data['percent'],
        $data['expiry_hours'],
        $data['target_product'] ?? '30GB 30-day',
        $data['weight'],
        $data['prefix'],
        $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Discount type updated successfully'], JSON_UNESCAPED_UNICODE);
}

function deleteDiscountType($pdo) {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Discount ID required'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM discount_types WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Discount type deleted successfully'], JSON_UNESCAPED_UNICODE);
}

function getThemes($pdo) {
    $stmt = $pdo->query("SELECT * FROM themes ORDER BY active DESC, created_at DESC");
    $themes = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $themes], JSON_UNESCAPED_UNICODE);
}

function createTheme($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $stmt = $pdo->prepare("
        INSERT INTO themes (name, guide_text, logo_path, background_path, falling_objects, explosion_effect, color_palette, header_color, user_box_color)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $fallingObjects = json_encode($data['falling_objects'] ?? ['🍎', '🍉']);
    $colorPalette = json_encode($data['color_palette'] ?? ['#dc2626', '#16a34a']);

    $stmt->execute([
        $data['name'] ?? 'New Theme',
        $data['guide_text'] ?? 'Click on fruits to win prizes!',
        $data['logo_path'] ?? '',
        $data['background_path'] ?? '',
        $fallingObjects,
        $data['explosion_effect'] ?? 'seeds',
        $colorPalette,
        $data['header_color'] ?? '#ffffff',
        $data['user_box_color'] ?? '#ffffff'
    ]);

    echo json_encode(['success' => true, 'message' => 'Theme created successfully'], JSON_UNESCAPED_UNICODE);
}

function updateTheme($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Theme ID required'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $stmt = $pdo->prepare("
        UPDATE themes SET
        name = ?, guide_text = ?, logo_path = ?, background_path = ?,
        falling_objects = ?, explosion_effect = ?, color_palette = ?,
        header_color = ?, user_box_color = ?, updated_at = NOW()
        WHERE id = ?
    ");

    $fallingObjects = json_encode($data['falling_objects'] ?? ['🍎', '🍉']);
    $colorPalette = json_encode($data['color_palette'] ?? ['#dc2626', '#16a34a']);

    $stmt->execute([
        $data['name'] ?? 'Updated Theme',
        $data['guide_text'] ?? 'Click on fruits to win prizes!',
        $data['logo_path'] ?? '',
        $data['background_path'] ?? '',
        $fallingObjects,
        $data['explosion_effect'] ?? 'seeds',
        $colorPalette,
        $data['header_color'] ?? '#ffffff',
        $data['user_box_color'] ?? '#ffffff',
        $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Theme updated successfully'], JSON_UNESCAPED_UNICODE);
}

function activateTheme($pdo) {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Theme ID required'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Deactivate all themes first
    $pdo->exec("UPDATE themes SET active = 0");

    // Activate the selected theme
    $stmt = $pdo->prepare("UPDATE themes SET active = 1 WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Theme activated successfully'], JSON_UNESCAPED_UNICODE);
}

function getUsers($pdo) {
    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 50;
    $offset = ($page - 1) * $limit;

    // Get total count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total = $stmt->fetch()['total'];

    // Get users with pagination
    $stmt = $pdo->prepare("
        SELECT user_id, codes_received, last_participation, expiry_date, total_codes, created_at
        FROM users
        ORDER BY last_participation DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $users,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function getUserDetails($pdo) {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID required'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Get user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Get user's activity log
    $stmt = $pdo->prepare("
        SELECT action, prize_code, prize_value, timestamp
        FROM logs
        WHERE user_id = ?
        ORDER BY timestamp DESC
        LIMIT 100
    ");
    $stmt->execute([$userId]);
    $activity = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'user' => $user,
        'activity' => $activity
    ], JSON_UNESCAPED_UNICODE);
}

function getTranslations() {
    $lang = $_GET['lang'] ?? 'en';
    $translations = getAllTranslations($lang);

    echo json_encode([
        'success' => true,
        'translations' => $translations,
        'lang' => $lang
    ], JSON_UNESCAPED_UNICODE);
}
?>
