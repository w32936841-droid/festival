<?php
session_start();

echo "<h1>Admin Session Check</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session data: " . json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Is logged in: " . (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true ? 'YES' : 'NO') . "\n";
echo "</pre>";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<p style='color: red;'>شما لاگین نکرده‌اید!</p>";
    echo "<a href='admin/'>رفتن به پنل ادمین</a>";
} else {
    echo "<p style='color: green;'>شما لاگین کرده‌اید!</p>";
    echo "<a href='admin/?logout=1'>خروج از پنل ادمین</a>";
}
?>
