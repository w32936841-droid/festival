<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    session_start();
    header("Location: admin-status.php");
    exit;
}

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

echo "<h1>Admin Status Check</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Logged in: " . ($is_logged_in ? 'YES' : 'NO') . "\n";
echo "Session data: " . json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n";
echo "</pre>";

if ($is_logged_in) {
    echo "<p style='color: green;'>✅ پنل ادمین آماده است</p>";
    echo "<a href='admin/'>رفتن به پنل ادمین</a> | ";
    echo "<a href='?logout=1'>خروج</a>";
} else {
    echo "<p style='color: red;'>❌ باید لاگین کنید</p>";
    echo "<a href='admin/'>رفتن به صفحه لاگین</a>";
}
?>
