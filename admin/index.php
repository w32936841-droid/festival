<?php
// Simple authentication (replace with proper auth system in production)
session_start();

require_once __DIR__ . '/../config.php';

$admin_username = 'admin';
$admin_password = 'festival2024'; // Change this in production!

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        // Redirect to main admin panel
        header('Location: index.php?logged_in=1');
        exit;
    } else {
        $login_error = 'Invalid username or password';
    }
}

// Force redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Show redesigned login form (uses external CSS file)
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Festival System</title>
        <link rel="stylesheet" href="../css/admin-login.css">
    </head>
    <body>
        <main class="admin-login-main" data-lang="fa">
            <section class="login-wrapper">
                <div class="login-card glass-card">
                    <h1 class="login-title">Login</h1>

                    <?php if (isset($login_error)): ?>
                        <div class="login-error"><?php echo htmlspecialchars($login_error); ?></div>
                    <?php endif; ?>

                    <form method="post" class="login-form" novalidate>
                        <label class="input-label">
                            <span class="label-text">Username</span>
                            <input type="text" name="username" class="login-input" placeholder="Username" required autocomplete="username" />
                        </label>

                        <label class="input-label">
                            <span class="label-text">Password</span>
                            <input type="password" name="password" class="login-input" placeholder="Password" required autocomplete="current-password" />
                        </label>

                        <button type="submit" name="login" class="btn btn-primary login-btn">Sign In</button>
                    </form>
                </div>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Admin Panel - v<?php echo FEST_VERSION; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #0b1020 0%, #1a1a2e 100%);
            color: #e2e8f0;
            direction: rtl;
        }
        .layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            padding: 25px 20px;
            box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar h2 {
            font-size: 22px;
            margin: 0 0 25px;
            color: #f1f5f9;
            text-align: center;
            border-bottom: 2px solid #475569;
            padding-bottom: 15px;
        }
        .sidebar nav { margin-bottom: 30px; }
        .sidebar a {
            color: #cbd5e1;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            transform: translateX(-5px);
        }
        .sidebar a i { margin-left: 10px; width: 20px; }
        .sidebar .version {
            font-size: 12px;
            opacity: 0.7;
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #475569;
        }
        .content {
            flex: 1;
            margin-right: 280px;
            padding: 30px;
            overflow-x: auto;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .card h3 {
            margin: 0 0 20px;
            color: #f1f5f9;
            font-size: 24px;
            border-bottom: 2px solid #475569;
            padding-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .stat-card h4 {
            margin: 0 0 10px;
            font-size: 14px;
            opacity: 0.9;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .time-filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .time-btn {
            padding: 8px 16px;
            border: 1px solid #475569;
            background: transparent;
            color: #cbd5e1;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .time-btn.active, .time-btn:hover {
            background: #3b82f6;
            border-color: #3b82f6;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            padding: 15px;
        }
        .activity-item {
            padding: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .activity-item:last-child { border-bottom: none; }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #e2e8f0;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #475569;
            border-radius: 6px;
            background: rgba(255,255,255,0.05);
            color: #e2e8f0;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255,255,255,0.02);
            border-radius: 8px;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .table th {
            background: rgba(255,255,255,0.05);
            font-weight: 600;
            color: #f1f5f9;
        }
        .table tr:hover {
            background: rgba(255,255,255,0.02);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal h3 { margin-top: 0; }
        .close-btn {
            float: left;
            background: none;
            border: none;
            color: #cbd5e1;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
        }
        .server-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .server-stat {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #ef4444 100%);
            transition: width 0.3s ease;
        }
        .loading { text-align: center; padding: 20px; }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255,255,255,0.1);
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; }
        .theme-preview {
            display: inline-block;
            width: 100px;
            height: 60px;
            border-radius: 6px;
            margin: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .theme-preview.active { border-color: #3b82f6; }
        .color-picker {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .color-input {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; height: auto; }
            .content { margin-right: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .time-filters { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>🎪 Festival Admin</h2>
            <nav>
                <a href="#dashboard" class="active" data-section="dashboard">
                    <i class="fas fa-chart-line"></i>Dashboard
                </a>
                <a href="#discounts" data-section="discounts">
                    <i class="fas fa-tags"></i>Discount Management
                </a>
                <a href="#themes" data-section="themes">
                    <i class="fas fa-palette"></i>Festival Themes
                </a>
                <a href="#users" data-section="users">
                    <i class="fas fa-users"></i>Users List
                </a>
                <a href="?logout=1" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                    <i class="fas fa-sign-out-alt"></i>Logout
                </a>
            </nav>
            <div class="version">
                Version: <?php echo htmlspecialchars(FEST_VERSION); ?>
            </div>
        </aside>

        <main class="content">
            <!-- Dashboard Section -->
            <section id="dashboard-section" class="card">
                <h3><i class="fas fa-chart-line"></i> Dashboard</h3>

                <div class="time-filters">
                    <button class="time-btn active" data-range="1h">1 hour</button>
                    <button class="time-btn" data-range="6h">6 hours</button>
                    <button class="time-btn" data-range="12h">12 hours</button>
                    <button class="time-btn" data-range="24h">24 hours</button>
                    <button class="time-btn" data-range="7d">7 days</button>
                    <button class="time-btn" data-range="30d">30 days</button>
                </div>

                <div id="stats-container">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading statistics...</p>
                    </div>
                </div>

                <div class="recent-activity" id="recent-activity" style="display: none;">
                    <h4>Recent Activity</h4>
                    <div id="activity-list"></div>
                </div>
            </section>

            <!-- Discounts Section -->
            <section id="discounts-section" class="card" style="display: none;">
                <h3><i class="fas fa-tags"></i> Discount Management</h3>

                <form id="discount-form">
                    <div class="form-group">
                        <label>Discount Percentage:</label>
                        <input type="number" name="percent" min="1" max="90" required>
                    </div>
                    <div class="form-group">
                        <label>Probability Weight (higher number = better chance):</label>
                        <input type="number" name="weight" min="0" step="0.1" required>
                    </div>
                    <div class="form-group">
                        <label>Code Prefix:</label>
                        <input type="text" name="prefix" required placeholder="Example: YALDA">
                    </div>
                    <div class="form-group">
                        <label>Expiry Hours:</label>
                        <select name="expiry_hours" required>
                            <option value="12">12 hours</option>
                            <option value="24">24 hours</option>
                            <option value="48">48 hours</option>
                            <option value="72">72 hours</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Product:</label>
                        <input type="text" name="target_product" value="30GB 30-day" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>Add Discount Type
                    </button>
                </form>

                <div id="discount-status"></div>

                <div id="discounts-list">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading discounts...</p>
                    </div>
                </div>
            </section>

            <!-- Themes Section -->
            <section id="themes-section" class="card" style="display: none;">
                <h3><i class="fas fa-palette"></i> Festival Themes</h3>

                <form id="theme-form">
                    <div class="form-group">
                        <label>Theme Name:</label>
                        <input type="text" name="name" required placeholder="Example: Yalda Night">
                    </div>
                    <div class="form-group">
                        <label>Guide Text:</label>
                        <textarea name="guide_text" rows="3" required placeholder="Game guide text"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Falling Objects (comma separated):</label>
                        <input type="text" name="falling_objects" value="🍎,🍉" required>
                    </div>
                    <div class="form-group">
                        <label>Explosion Effect:</label>
                        <select name="explosion_effect" required>
                            <option value="seeds">Seeds (pomegranate/watermelon)</option>
                            <option value="snow">Snow (Christmas)</option>
                            <option value="sparkles">Sparkles</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Color Palette:</label>
                        <div class="color-picker">
                            <input type="color" name="primary_color" value="#dc2626" class="color-input">
                            <input type="color" name="secondary_color" value="#16a34a" class="color-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>Create New Theme
                    </button>
                </form>

                <div id="theme-status"></div>

                <div id="themes-list">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading themes...</p>
                    </div>
                </div>
            </section>

            <!-- Users Section -->
            <section id="users-section" class="card" style="display: none;">
                <h3><i class="fas fa-users"></i> Users List</h3>

                <div id="users-list">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading users...</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modals -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <button class="close-btn">&times;</button>
            <h3 id="modal-title">Edit</h3>
            <form id="edit-form">
                <!-- Dynamic content will be inserted here -->
            </form>
        </div>
    </div>

    <script>
        // Global variables
        let currentSection = 'dashboard';
        let currentTimeRange = '24h';

        // Helper function for API calls with credentials
        async function apiFetch(url, options = {}) {
            console.log('Making API call to:', url);
            return fetch(url, {
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            }).then(response => {
                console.log('API response status:', response.status);
                return response;
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin panel initializing...');

            console.log('Checking admin session...');

            // Simple session check - if we reached here, PHP already checked the session
            console.log('Session OK, loading admin panel');
            setupNavigation();
            setupTimeFilters();
            loadDashboard();
            setupForms();
        });

        // Navigation
        function setupNavigation() {
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    switchSection(section);
                });
            });
        }

        function switchSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.card').forEach(card => {
                card.style.display = 'none';
            });

            // Remove active class from nav
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
            });

            // Show selected section
            const sectionElement = document.getElementById(sectionName + '-section');
            if (sectionElement) {
                sectionElement.style.display = 'block';
            }
            const navElement = document.querySelector(`[data-section="${sectionName}"]`);
            if (navElement) {
                navElement.classList.add('active');
            }

            currentSection = sectionName;

            // Load section data
            switch(sectionName) {
                case 'dashboard':
                    loadDashboard();
                    break;
                case 'discounts':
                    loadDiscounts();
                    break;
                case 'themes':
                    loadThemes();
                    break;
                case 'users':
                    loadUsers();
                    break;
            }
        }

        // Time filters
        function setupTimeFilters() {
            document.querySelectorAll('.time-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentTimeRange = this.getAttribute('data-range');
                    loadDashboard();
                });
            });
        }

        // Dashboard
        async function loadDashboard() {
            try {
                console.log('Loading dashboard with range:', currentTimeRange);
                const response = await apiFetch(`../api/admin-api.php?action=dashboard_stats&range=${currentTimeRange}`);
                console.log('Dashboard response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Dashboard data:', data);

                if (data.success) {
                    console.log('Dashboard loaded successfully:', data);
                    renderDashboard(data.data);
                } else {
                    showError('Error loading dashboard');
                }
            } catch (error) {
                console.error('Dashboard error:', error);
                showError('Connection error');
            }
        }

        function renderDashboard(data) {
            const container = document.getElementById('stats-container');

            container.innerHTML = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Participants</h4>
                        <div class="value">${data.participants || 0}</div>
                    </div>
                    <div class="stat-card">
                        <h4>Prizes Won</h4>
                        <div class="value">${data.prizes_won || 0}</div>
                    </div>
                    <div class="stat-card">
                        <h4>Server CPU</h4>
                        <div class="value">${data.server_stats.cpu}%</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${data.server_stats.cpu}%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <h4>Server RAM</h4>
                        <div class="value">${data.server_stats.ram}%</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${data.server_stats.ram}%"></div>
                        </div>
                    </div>
                </div>

                <div class="server-stats">
                    <div class="server-stat">
                        <strong>CPU</strong><br>${data.server_stats.cpu}%
                    </div>
                    <div class="server-stat">
                        <strong>RAM</strong><br>${data.server_stats.ram}%
                    </div>
                    <div class="server-stat">
                        <strong>Disk</strong><br>${data.server_stats.disk}%
                    </div>
                </div>
            `;

            // Recent activity
            if (data.recent_activity && data.recent_activity.length > 0) {
                const activityList = document.getElementById('activity-list');
                activityList.innerHTML = data.recent_activity.map(activity => `
                    <div class="activity-item">
                        <div>
                            <strong>${activity.user_id}</strong>
                            <span>${activity.action}</span>
                            ${activity.prize_code ? `<code>${activity.prize_code}</code>` : ''}
                        </div>
                        <small>${new Date(activity.timestamp).toLocaleString('fa-IR')}</small>
                    </div>
                `).join('');
                document.getElementById('recent-activity').style.display = 'block';
            }
        }

        // Discounts
        async function loadDiscounts() {
            try {
                const response = await apiFetch('../api/admin-api.php?action=get_discounts');
                const data = await response.json();

                if (data.success) {
                    renderDiscounts(data.data);
                } else {
                    showError('Error loading discounts');
                }
            } catch (error) {
                console.error('Discounts error:', error);
                showError('Connection error');
            }
        }

        function renderDiscounts(discounts) {
            const container = document.getElementById('discounts-list');

            if (discounts.length === 0) {
                container.innerHTML = '<p>No discounts found.</p>';
                return;
            }

            const table = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Percent</th>
                            <th>Weight</th>
                            <th>Prefix</th>
                            <th>Expiry</th>
                            <th>Product</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${discounts.map(discount => `
                            <tr>
                                <td>${discount.percent}%</td>
                                <td>${discount.weight}</td>
                                <td>${discount.prefix}</td>
                                <td>${discount.expiry_hours}h</td>
                                <td>${discount.target_product}</td>
                                <td>
                                    <button class="btn btn-success" onclick="editDiscount(${discount.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="deleteDiscount(${discount.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = table;
        }

        // Themes
        async function loadThemes() {
            try {
                const response = await apiFetch('../api/admin-api.php?action=get_themes');
                const data = await response.json();

                if (data.success) {
                    renderThemes(data.data);
                } else {
                    showError('Error loading themes');
                }
            } catch (error) {
                console.error('Themes error:', error);
                showError('Connection error');
            }
        }

        function renderThemes(themes) {
            const container = document.getElementById('themes-list');

            if (themes.length === 0) {
                container.innerHTML = '<p>No themes found.</p>';
                return;
            }

            const themeList = themes.map(theme => `
                <div class="theme-item" style="margin: 20px 0; padding: 15px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="margin: 0 0 10px;">${theme.name} ${theme.active ? '(Active)' : ''}</h4>
                            <p style="margin: 5px 0; color: #cbd5e1;">${theme.guide_text}</p>
                            <small>Objects: ${JSON.parse(theme.falling_objects || '[]').join(', ')}</small>
                        </div>
                        <div>
                            ${!theme.active ? `
                                <button class="btn btn-primary" onclick="activateTheme(${theme.id})">
                                    <i class="fas fa-check"></i> Activate
                                </button>
                            ` : ''}
                            <button class="btn btn-success" onclick="editTheme(${theme.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            container.innerHTML = themeList;
        }

        // Users
        async function loadUsers() {
            try {
                const response = await apiFetch('../api/admin-api.php?action=get_users');
                const data = await response.json();

                if (data.success) {
                    renderUsers(data.data);
                } else {
                    showError('Error loading users');
                }
            } catch (error) {
                console.error('Users error:', error);
                showError('Connection error');
            }
        }

        function renderUsers(users) {
            const container = document.getElementById('users-list');

            if (users.length === 0) {
                container.innerHTML = '<p>No users found.</p>';
                return;
            }

            const table = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Total Prizes</th>
                            <th>Last Participation</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${users.map(user => `
                            <tr>
                                <td>${user.user_id}</td>
                                <td>${user.total_codes}</td>
                                <td>${user.last_participation ? new Date(user.last_participation).toLocaleString('en-US') : 'None'}</td>
                                <td>${user.expiry_date ? new Date(user.expiry_date).toLocaleString('fa-IR') : '-'}</td>
                                <td>
                                    <button class="btn btn-primary" onclick="viewUserDetails('${user.user_id}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            container.innerHTML = table;
        }

        // Form handlers
        function setupForms() {
            // Discount form
            document.getElementById('discount-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await apiFetch('../api/admin-api.php?action=create_discount', {
                        method: 'POST',
                        body: JSON.stringify({
                            percent: formData.get('percent'),
                            weight: formData.get('weight'),
                            prefix: formData.get('prefix'),
                            expiry_hours: formData.get('expiry_hours'),
                            target_product: formData.get('target_product')
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showSuccess('Discount created successfully');
                        this.reset();
                        loadDiscounts();
                    } else {
                        showError(data.message || 'Error creating discount');
                    }
                } catch (error) {
                    console.error('Create discount error:', error);
                    showError('Connection error');
                }
            });

            // Theme form
            document.getElementById('theme-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await apiFetch('../api/admin-api.php?action=create_theme', {
                        method: 'POST',
                        body: JSON.stringify({
                            name: formData.get('name'),
                            guide_text: formData.get('guide_text'),
                            falling_objects: formData.get('falling_objects').split(','),
                            explosion_effect: formData.get('explosion_effect'),
                            color_palette: [
                                formData.get('primary_color'),
                                formData.get('secondary_color')
                            ]
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showSuccess('Theme created successfully');
                        this.reset();
                        loadThemes();
                    } else {
                        showError(data.message || 'Error creating theme');
                    }
                } catch (error) {
                    console.error('Create theme error:', error);
                    showError('Connection error');
                }
            });

            // Modal close
            document.querySelectorAll('.close-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.modal').forEach(modal => {
                        modal.classList.remove('active');
                    });
                });
            });
        }

        // Action functions
        async function editDiscount(id) {
            // Get discount data first
            try {
                const response = await apiFetch('../api/admin-api.php?action=get_discounts');
                const data = await response.json();

                if (data.success) {
                    const discount = data.data.find(d => d.id == id);
                    if (discount) {
                        showEditModal('discount', discount);
                    }
                }
            } catch (error) {
                console.error('Error fetching discount:', error);
                showError('Error loading discount information');
            }
        }

        function showEditModal(type, data) {
            const modal = document.getElementById('edit-modal');
            const modalTitle = document.getElementById('modal-title');
            const editForm = document.getElementById('edit-form');

            if (type === 'discount') {
                modalTitle.textContent = 'Edit Discount';
                editForm.innerHTML = `
                    <input type="hidden" name="id" value="${data.id}">
                    <div class="form-group">
                        <label>Discount Percentage:</label>
                        <input type="number" name="percent" min="1" max="90" value="${data.percent}" required>
                    </div>
                    <div class="form-group">
                        <label>Probability Weight:</label>
                        <input type="number" name="weight" min="0" step="0.1" value="${data.weight}" required>
                    </div>
                    <div class="form-group">
                        <label>Code Prefix:</label>
                        <input type="text" name="prefix" value="${data.prefix}" required>
                    </div>
                    <div class="form-group">
                        <label>Expiry Hours:</label>
                        <select name="expiry_hours" required>
                            <option value="12" ${data.expiry_hours == 12 ? 'selected' : ''}>12 hours</option>
                            <option value="24" ${data.expiry_hours == 24 ? 'selected' : ''}>24 hours</option>
                            <option value="48" ${data.expiry_hours == 48 ? 'selected' : ''}>48 hours</option>
                            <option value="72" ${data.expiry_hours == 72 ? 'selected' : ''}>72 hours</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Product:</label>
                        <input type="text" name="target_product" value="${data.target_product}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                `;
            } else if (type === 'theme') {
                modalTitle.textContent = 'Edit Theme';
                editForm.innerHTML = `
                    <input type="hidden" name="id" value="${data.id}">
                    <div class="form-group">
                        <label>Theme Name:</label>
                        <input type="text" name="name" value="${data.name}" required>
                    </div>
                    <div class="form-group">
                        <label>Guide Text:</label>
                        <textarea name="guide_text" rows="3" required>${data.guide_text}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Falling Objects:</label>
                        <input type="text" name="falling_objects" value="${JSON.parse(data.falling_objects || '[]').join(', ')}" required>
                    </div>
                    <div class="form-group">
                        <label>Explosion Effect:</label>
                        <select name="explosion_effect" required>
                            <option value="seeds" ${data.explosion_effect === 'seeds' ? 'selected' : ''}>Seeds</option>
                            <option value="snow" ${data.explosion_effect === 'snow' ? 'selected' : ''}>Snow</option>
                            <option value="sparkles" ${data.explosion_effect === 'sparkles' ? 'selected' : ''}>Sparkles</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                `;
            }

            // Handle form submission
            editForm.onsubmit = async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    let endpoint, bodyData;
                    if (type === 'discount') {
                        endpoint = 'update_discount';
                        bodyData = {
                            id: formData.get('id'),
                            percent: formData.get('percent'),
                            weight: formData.get('weight'),
                            prefix: formData.get('prefix'),
                            expiry_hours: formData.get('expiry_hours'),
                            target_product: formData.get('target_product')
                        };
                    } else {
                        endpoint = 'update_theme';
                        bodyData = {
                            id: formData.get('id'),
                            name: formData.get('name'),
                            guide_text: formData.get('guide_text'),
                            falling_objects: formData.get('falling_objects').split(',').map(s => s.trim()),
                            explosion_effect: formData.get('explosion_effect')
                        };
                    }

                    const response = await apiFetch(`../api/admin-api.php?action=${endpoint}`, {
                        method: 'POST',
                        body: JSON.stringify(bodyData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        showSuccess('Changes saved successfully');
                        modal.classList.remove('active');
                        // Refresh the current section
                        if (type === 'discount') loadDiscounts();
                        else loadThemes();
                    } else {
                        showError(result.message || 'Error saving changes');
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    showError('Connection error');
                }
            };

            modal.classList.add('active');
        }

        async function deleteDiscount(id) {
            if (!confirm('Are you sure you want to delete this discount?')) return;

            try {
                const response = await apiFetch('../api/admin-api.php?action=delete_discount', {
                    method: 'POST',
                    body: new URLSearchParams({ id: id }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess('Discount deleted successfully');
                    loadDiscounts();
                } else {
                    showError(data.message || 'Error deleting discount');
                }
            } catch (error) {
                console.error('Delete discount error:', error);
                showError('Connection error');
            }
        }

        async function activateTheme(id) {
            try {
                const response = await apiFetch('../api/admin-api.php?action=activate_theme', {
                    method: 'POST',
                    body: new URLSearchParams({ id: id }),
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess('Theme activated successfully');
                    loadThemes();
                } else {
                    showError(data.message || 'Error activating theme');
                }
            } catch (error) {
                console.error('Activate theme error:', error);
                showError('Connection error');
            }
        }

        async function editTheme(id) {
            // Get theme data first
            try {
                const response = await apiFetch('../api/admin-api.php?action=get_themes');
                const data = await response.json();

                if (data.success) {
                    const theme = data.data.find(t => t.id == id);
                    if (theme) {
                        showEditModal('theme', theme);
                    }
                }
            } catch (error) {
                console.error('Error fetching theme:', error);
                showError('Error loading theme information');
            }
        }

        async function viewUserDetails(userId) {
            // Implementation for viewing user details
                showSuccess('User details feature will be added soon');
        }

        // Utility functions
        function showSuccess(message) {
            showAlert(message, 'success');
        }

        function showError(message) {
            showAlert(message, 'error');
        }

        function showAlert(message, type) {
            // Remove existing alerts
            document.querySelectorAll('.alert').forEach(alert => alert.remove());

            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;

            document.querySelector('.content').insertBefore(alert, document.querySelector('.content').firstChild);

            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>

