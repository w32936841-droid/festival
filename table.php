<?php
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain; charset=utf-8');
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $queries = [
        // Themes
        "CREATE TABLE IF NOT EXISTS themes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            guide_text TEXT,
            logo_path VARCHAR(255),
            background_path VARCHAR(255),
            falling_objects TEXT,
            explosion_effect VARCHAR(100),
            color_palette TEXT,
            header_color VARCHAR(32),
            user_box_color VARCHAR(32),
            active BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        )",
        // Users
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64) NOT NULL UNIQUE,
            codes_received JSON NULL,
            last_participation DATETIME NULL,
            expiry_date DATETIME NULL,
            total_codes INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        // Logs
        "CREATE TABLE IF NOT EXISTS logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(64),
            action VARCHAR(255),
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            prize_code VARCHAR(64) NULL,
            prize_value DECIMAL(10,2) NULL
        )",
        // Discount Types
        "CREATE TABLE IF NOT EXISTS discount_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            percent INT NOT NULL,
            max_users INT NOT NULL DEFAULT 1,
            allowed_users VARCHAR(64) NOT NULL DEFAULT 'allusers',
            expiry_hours INT NOT NULL,
            purchase_type ENUM('first','all') NOT NULL,
            usage_type ENUM('renewal','new','both') NOT NULL,
            per_user_limit INT NOT NULL DEFAULT 1,
            target_panel VARCHAR(64) NOT NULL DEFAULT '/all',
            target_product VARCHAR(128) NOT NULL,
            weight INT NOT NULL DEFAULT 0,
            prefix VARCHAR(32) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        // Discounts (generated codes)
        "CREATE TABLE IF NOT EXISTS discounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(64) NOT NULL,
            weight INT NOT NULL,
            product_id VARCHAR(64) NOT NULL,
            panel_id VARCHAR(64) NOT NULL,
            expiry DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            used INT DEFAULT 0
        )"
    ];

    foreach ($queries as $sql) {
        $pdo->exec($sql);
    }

    // Insert default theme if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM themes");
    $stmt->execute();
    $themeCount = $stmt->fetch()['count'];

    if ($themeCount == 0) {
        $pdo->exec("
            INSERT INTO themes (name, guide_text, logo_path, background_path, falling_objects, explosion_effect, color_palette, header_color, user_box_color, active, created_at)
            VALUES (
                'Default Yalda',
                '🍉 Click on a watermelon or pomegranate to receive your gift! 🍇',
                'assets/logo.png',
                'assets/background.png',
                '[\"🍎\", \"🍉\"]',
                'seeds',
                '[\"rgba(255,255,255,0.3)\", \"rgba(255,255,255,0.1)\"]',
                '#ffffff',
                '#ffffff',
                1,
                NOW()
            )
        ");
        echo "Default theme inserted. ";
    }

    echo "Database tables initialized successfully. " . date('Y-m-d H:i:s');
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB init error: " . $e->getMessage();
}
?>

