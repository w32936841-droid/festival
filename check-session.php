<?php
session_start();

echo "<h1>Session Check</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session data: " . json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n";
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "HTTP Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'none') . "\n";
echo "HTTP Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'none') . "\n";

// Test admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $admin_username = 'admin';
    $admin_password = 'festival2024';

    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        echo "Login successful!\n";
        header("Location: check-session.php");
        exit;
    } else {
        echo "Login failed!\n";
    }
}

// Show login form if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    <?php
} else {
    echo "You are logged in!\n";
    echo "<a href='?logout=1'>Logout</a>\n";
}

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = array(); // Clear session data
    session_destroy();
    session_start(); // Start new session for redirect
    header("Location: check-session.php");
    exit;
}

echo "</pre>";
?>
