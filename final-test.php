<?php
// Final comprehensive test for Festival System
require_once 'config.php';

echo "<h1>🎪 Final System Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .test{background:#f0f0f0;padding:10px;margin:10px;border-radius:5px;}</style>";

// Test database
echo "<div class='test'><h3>Database Test</h3>";
try {
    $pdo = getDatabaseConnection();
    echo "✅ Database connected<br>";

    // Test tables
    $tables = ['themes', 'users', 'logs', 'discount_types', 'discounts'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
        echo "✅ Table `$table` exists<br>";
    }

    // Test theme
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM themes WHERE active = 1");
    $themeCount = $stmt->fetch()['count'];
    echo "✅ Active themes: $themeCount<br>";

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
echo "</div>";

// Test session
echo "<div class='test'><h3>Session Test</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session data: <pre>" . json_encode($_SESSION, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test files
echo "<div class='test'><h3>File Test</h3>";
$files = [
    'index.html',
    'js/game-v0.3.js',
    'css/style.css',
    'api/validate.php',
    'api/participate.php',
    'admin/index.php'
];
foreach ($files as $file) {
    echo (file_exists($file) ? "✅" : "❌") . " $file<br>";
}
echo "</div>";

// Test config
echo "<div class='test'><h3>Config Test</h3>";
echo "BOT_API_TOKEN set: " . (!empty(BOT_API_TOKEN) && BOT_API_TOKEN !== 'REPLACE_WITH_TOKEN' ? "✅" : "❌") . "<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "</div>";

echo "<hr><p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Test admin panel login</li>";
echo "<li>Test main page fruit falling</li>";
echo "<li>Test user validation with real bot</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "</ol>";
?>
