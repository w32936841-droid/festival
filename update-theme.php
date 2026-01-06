<?php
require_once 'config.php';

try {
    $pdo = getDatabaseConnection();

    // Update default theme with glass colors
    $pdo->exec("
        UPDATE themes SET
        color_palette = '[\"rgba(255,255,255,0.3)\", \"rgba(255,255,255,0.1)\"]'
        WHERE name = 'یلدا پیش‌فرض'
    ");

    echo "✅ تم پیش‌فرض با رنگ‌های شیشه‌ای آپدیت شد!\n";
    echo "🎨 رنگ‌های جدید: شفاف/سفید کم‌رنگ برای جلوه شیشه‌ای\n";

} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
}
?>
