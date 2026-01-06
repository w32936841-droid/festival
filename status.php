<?php
// System Status Check for Festival Reward System
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎪 Festival System Status</title>
    <style>
        body {
            font-family: 'Vazir', 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #0b1020 0%, #1a1a2e 100%);
            color: #e2e8f0;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 { text-align: center; color: #60a5fa; margin-bottom: 30px; }
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .status-success { border-color: #10b981; background: rgba(16, 185, 129, 0.1); }
        .status-error { border-color: #ef4444; background: rgba(239, 68, 68, 0.1); }
        .status-warning { border-color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
        .status-icon { font-size: 20px; }
        .status-text { flex: 1; margin: 0 15px; }
        .next-steps { margin-top: 30px; padding: 20px; background: rgba(96, 165, 250, 0.1); border-radius: 10px; border-left: 4px solid #60a5fa; }
        .next-steps h3 { color: #60a5fa; margin-top: 0; }
        .next-steps ul { margin: 10px 0; }
        .next-steps li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎪 Festival System Status</h1>

        <?php
        $checks = [];
        $all_good = true;

        // Check PHP version
        $php_version = phpversion();
        $php_ok = version_compare($php_version, '7.4', '>=');
        $checks[] = [
            'name' => 'PHP Version',
            'status' => $php_ok ? 'success' : 'error',
            'value' => $php_version,
            'message' => $php_ok ? '✅ Compatible' : '❌ Minimum PHP 7.4 required'
        ];
        if (!$php_ok) $all_good = false;

        // Check database connection
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->query("SELECT 1");
            $db_ok = true;
            $db_message = "✅ Connection successful";
        } catch (Exception $e) {
            $db_ok = false;
            $db_message = "❌ Error: " . $e->getMessage();
            $all_good = false;
        }
        $checks[] = [
            'name' => 'Database Connection',
            'status' => $db_ok ? 'success' : 'error',
            'value' => $db_ok ? 'Connected' : 'Error',
            'message' => $db_message
        ];

        // Check tables
        $tables = ['themes', 'users', 'logs', 'discount_types', 'discounts'];
        $tables_ok = true;
        $missing_tables = [];
        if ($db_ok) {
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
                    // Table exists
                } catch (Exception $e) {
                    $tables_ok = false;
                    $missing_tables[] = $table;
                }
            }
        }
        $checks[] = [
            'name' => 'Database Tables',
            'status' => $tables_ok ? 'success' : 'error',
            'value' => $tables_ok ? 'All present' : 'Missing tables: ' . implode(', ', $missing_tables),
            'message' => $tables_ok ? '✅ Tables created' : '❌ Run: table.php'
        ];
        if (!$tables_ok) $all_good = false;

        // Check theme
        $theme_ok = false;
        if ($db_ok && $tables_ok) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM themes WHERE active = 1");
                $theme_count = $stmt->fetch()['count'];
                $theme_ok = $theme_count > 0;
            } catch (Exception $e) {
                $theme_ok = false;
            }
        }
        $checks[] = [
            'name' => 'Active Theme',
            'status' => $theme_ok ? 'success' : 'warning',
            'value' => $theme_ok ? 'Present' : 'Missing',
            'message' => $theme_ok ? '✅ Theme ready' : '⚠️ Default theme not created'
        ];

        // Check bot token
        $bot_token = defined('BOT_API_TOKEN') && BOT_API_TOKEN !== 'REPLACE_WITH_TOKEN';
        $checks[] = [
            'name' => 'Telegram Bot Token',
            'status' => $bot_token ? 'success' : 'warning',
            'value' => $bot_token ? 'Configured' : 'Not configured',
            'message' => $bot_token ? '✅ Ready to connect' : '⚠️ Configure token in config.php'
        ];

        // Check required files
        $required_files = [
            'index.html',
            'js/game-v0.3.js',
            'css/style.css',
            'api/validate.php',
            'api/participate.php',
            'admin/index.php'
        ];
        $files_ok = true;
        $missing_files = [];
        foreach ($required_files as $file) {
            if (!file_exists($file)) {
                $files_ok = false;
                $missing_files[] = $file;
            }
        }
        $checks[] = [
            'name' => 'Essential Files',
            'status' => $files_ok ? 'success' : 'error',
            'value' => $files_ok ? 'All present' : 'Missing files: ' . count($missing_files),
            'message' => $files_ok ? '✅ Files ready' : '❌ Upload missing files'
        ];
        if (!$files_ok) $all_good = false;

        // Display results
        foreach ($checks as $check) {
            echo "<div class='status-item status-{$check['status']}'>";
            echo "<span class='status-icon'>";
            if ($check['status'] === 'success') echo '✅';
            elseif ($check['status'] === 'error') echo '❌';
            else echo '⚠️';
            echo "</span>";
            echo "<span class='status-text'>{$check['name']}: {$check['value']}</span>";
            echo "<small>{$check['message']}</small>";
            echo "</div>";
        }
        ?>

        <?php if ($all_good): ?>
            <div class="next-steps">
                <h3>🎉 System is ready!</h3>
                <p>Everything is set up properly. Now you can:</p>
                <ul>
                    <li><a href="index.html" style="color: #60a5fa;">Open the main festival page</a></li>
                    <li><a href="admin/" style="color: #60a5fa;">Configure the admin panel</a></li>
                    <li>Change admin password (<code>ADMIN_PASSWORD_CHANGE.md</code>)</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="next-steps">
                <h3>🔧 Required Actions</h3>
                <ul>
                    <?php if (!$db_ok): ?>
                        <li>Check database settings in <code>config.php</code></li>
                    <?php endif; ?>
                    <?php if (!$tables_ok): ?>
                        <li>Run <code>table.php</code> file</li>
                    <?php endif; ?>
                    <?php if (!$files_ok): ?>
                        <li>Upload missing files: <?php echo implode(', ', $missing_files); ?></li>
                    <?php endif; ?>
                    <li>Read full guide in <code>DEPLOYMENT_GUIDE.md</code></li>
                </ul>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px; color: #94a3b8;">
            <small>Festival System v<?php echo FEST_VERSION; ?> | Checked on: <?php echo date('Y-m-d H:i:s'); ?></small>
        </div>
    </div>
</body>
</html>
