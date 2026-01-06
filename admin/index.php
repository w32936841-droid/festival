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
                            <div class="password-input-wrapper">
                                <input type="password" name="password" id="password" class="login-input" placeholder="Password" required autocomplete="current-password" />
                                <button type="button" class="password-toggle" id="password-toggle" aria-label="Toggle password visibility">
                                    👁️
                                </button>
                            </div>
                        </label>

                        <button type="submit" name="login" class="btn btn-primary login-btn">Sign In</button>
                    </form>
                </div>
            </section>
        </main>

        <script>
            // Password visibility toggle functionality
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('password');
                const passwordToggle = document.getElementById('password-toggle');

                if (passwordInput && passwordToggle) {
                    passwordToggle.addEventListener('click', function(e) {
                        e.preventDefault();

                        // Toggle input type
                        const isPassword = passwordInput.type === 'password';
                        passwordInput.type = isPassword ? 'text' : 'password';

                        // Update button icon
                        this.textContent = isPassword ? '🙈' : '👁️';

                        // Update aria-label
                        this.setAttribute('aria-label',
                            isPassword ? 'Hide password' : 'Show password'
                        );
                    });
                }
            });
        </script>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-panel.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
    <!-- SVG Gradient Definitions for Circular Progress -->
    <svg width="0" height="0" style="position: absolute;">
        <defs>
            <linearGradient id="circular-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />
            </linearGradient>
        </defs>
    </svg>

    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="floating-particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-layout">
        <!-- Modern Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="../assets/logo.png" alt="Festival Logo">
                    <div>
                        <div class="sidebar-title">Festival Admin</div>
                        <div class="sidebar-subtitle">Management Panel</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="#dashboard" class="nav-link active" data-section="dashboard">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#discounts" class="nav-link" data-section="discounts">
                        <i class="fas fa-tags"></i>
                        <span>Discount Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#themes" class="nav-link" data-section="themes">
                        <i class="fas fa-palette"></i>
                        <span>Festival Themes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#users" class="nav-link" data-section="users">
                        <i class="fas fa-users"></i>
                        <span>Users List</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="version-info">
                    <i class="fas fa-code-branch"></i>
                    <span>Version <?php echo htmlspecialchars(FEST_VERSION); ?></span>
                </div>
                <button class="logout-btn" onclick="window.location.href='?logout=1'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-content" id="admin-content">
            <div class="content-header">
                <h1 class="page-title" id="page-title">Dashboard</h1>
                <p class="page-subtitle" id="page-subtitle">Welcome to Festival Admin Panel</p>
            </div>

            <!-- Dashboard Section -->
            <section id="dashboard-section" class="glass-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h2 class="card-title">Dashboard Overview</h2>
                </div>

                <div class="time-filters">
                    <button class="time-btn active" data-range="1h">
                        <i class="fas fa-clock"></i> 1 hour
                    </button>
                    <button class="time-btn" data-range="6h">
                        <i class="fas fa-clock"></i> 6 hours
                    </button>
                    <button class="time-btn" data-range="12h">
                        <i class="fas fa-clock"></i> 12 hours
                    </button>
                    <button class="time-btn" data-range="24h">
                        <i class="fas fa-clock"></i> 24 hours
                    </button>
                    <button class="time-btn" data-range="7d">
                        <i class="fas fa-calendar-week"></i> 7 days
                    </button>
                    <button class="time-btn" data-range="30d">
                        <i class="fas fa-calendar-alt"></i> 30 days
                    </button>
                </div>

                <div id="stats-container">
                    <div class="loading">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Loading statistics...</p>
                    </div>
                </div>

                <div class="activity-feed" id="recent-activity" style="display: none;">
                    <h4>Recent Activity</h4>
                    <div id="activity-list"></div>
                </div>
            </section>

            <!-- Discounts Section -->
            <section id="discounts-section" class="glass-card" style="display: none;">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h2 class="card-title">Discount Management</h2>
                </div>

                <form id="discount-form" class="glass-form">
                    <div class="form-group">
                        <label class="form-label">Discount Percentage</label>
                        <input type="number" name="percent" min="1" max="90" class="form-input" required placeholder="e.g., 50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Probability Weight (higher = better chance)</label>
                        <input type="number" name="weight" min="0" step="0.1" class="form-input" required placeholder="e.g., 10.0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Code Prefix</label>
                        <input type="text" name="prefix" class="form-input" required placeholder="e.g., YALDA, FESTIVAL">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Hours</label>
                        <select name="expiry_hours" class="form-select" required>
                            <option value="12">12 hours</option>
                            <option value="24">24 hours</option>
                            <option value="48">48 hours</option>
                            <option value="72">72 hours</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Product</label>
                        <input type="text" name="target_product" class="form-input" value="30GB 30-day" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>Add Discount Type</span>
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
            <section id="themes-section" class="glass-card" style="display: none;">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h2 class="card-title">Festival Themes</h2>
                </div>

                <form id="theme-form" class="glass-form">
                    <div class="form-group">
                        <label class="form-label">Theme Name</label>
                        <input type="text" name="name" class="form-input" required placeholder="e.g., Yalda Night, Christmas">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Guide Text</label>
                        <textarea name="guide_text" rows="3" class="form-input" required placeholder="Game guide text for users"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Falling Objects (comma separated)</label>
                        <input type="text" name="falling_objects" class="form-input" value="🍎,🍉" required placeholder="🍎,🍉,❄️">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Explosion Effect</label>
                        <select name="explosion_effect" class="form-select" required>
                            <option value="seeds">🌰 Seeds (pomegranate/watermelon)</option>
                            <option value="snow">❄️ Snow (Christmas)</option>
                            <option value="sparkles">✨ Sparkles</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Color Palette</label>
                        <div class="color-grid">
                            <input type="color" name="primary_color" value="#dc2626" class="color-input" title="Primary Color">
                            <input type="color" name="secondary_color" value="#16a34a" class="color-input" title="Secondary Color">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>Create New Theme</span>
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
            <section id="users-section" class="glass-card" style="display: none;">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="card-title">Users Management</h2>
                </div>

                <div id="users-list">
                    <div class="loading">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Loading users...</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modals -->
    <div id="edit-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Edit Item</h3>
                <button class="modal-close" id="modal-close">&times;</button>
            </div>
            <form id="edit-form" class="glass-form">
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

        // Global variables for real-time updates
        let realtimeInterval = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin panel initializing...');

            // Initialize GSAP animations
            gsap.set('.glass-card', { opacity: 0, y: 30 });
            gsap.set('.stat-card', { opacity: 0, scale: 0.9 });
            gsap.set('.nav-link', { x: -20, opacity: 0 });

            // Animate elements on load
            gsap.to('.glass-card', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                stagger: 0.1,
                ease: 'power2.out'
            });

            gsap.to('.nav-link', {
                x: 0,
                opacity: 1,
                duration: 0.6,
                stagger: 0.05,
                delay: 0.2,
                ease: 'power2.out'
            });

            console.log('Checking admin session...');

            // Simple session check - if we reached here, PHP already checked the session
            console.log('Session OK, loading admin panel');

            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const adminSidebar = document.getElementById('admin-sidebar');
            const adminContent = document.getElementById('admin-content');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    adminSidebar.classList.toggle('mobile-open');
                    adminContent.classList.toggle('expanded');
                });
            }

            // Modal interactions
            const modalClose = document.getElementById('modal-close');
            const editModal = document.getElementById('edit-modal');

            if (modalClose) {
                modalClose.addEventListener('click', function() {
                    gsap.to(editModal, {
                        opacity: 0,
                        duration: 0.3,
                        onComplete: () => {
                            editModal.classList.remove('active');
                        }
                    });
                });
            }

            // Click outside modal to close
            if (editModal) {
                editModal.addEventListener('click', function(e) {
                    if (e.target === editModal) {
                        gsap.to(editModal, {
                            opacity: 0,
                            duration: 0.3,
                            onComplete: () => {
                                editModal.classList.remove('active');
                            }
                        });
                    }
                });
            }

            setupNavigation();
            setupTimeFilters();
            loadDashboard();
            setupForms();

            // Start real-time updates for dashboard
            startRealtimeUpdates();
        });

        // Real-time dashboard updates
        function startRealtimeUpdates() {
            // Update every 30 seconds
            realtimeInterval = setInterval(() => {
                if (currentSection === 'dashboard') {
                    updateDashboardRealtime();
                }
            }, 30000);
        }

        function stopRealtimeUpdates() {
            if (realtimeInterval) {
                clearInterval(realtimeInterval);
                realtimeInterval = null;
            }
        }

        // Real-time dashboard update (only stats, not activity)
        async function updateDashboardRealtime() {
            try {
                const response = await apiFetch(`../api/admin-api.php?action=dashboard_stats&range=${currentTimeRange}`);
                if (!response.ok) return;

                const data = await response.json();
                if (data.success) {
                    updateServerMetrics(data.data.server_stats);
                    updateUserStats(data.data.participants, data.data.prizes_won);
                }
            } catch (error) {
                console.log('Real-time update failed:', error);
            }
        }

        function updateServerMetrics(serverStats) {
            const cpuPercent = serverStats.cpu || 0;
            const ramPercent = serverStats.ram || 0;
            const diskPercent = serverStats.disk || 0;

            // Update CPU
            const cpuCircle = document.querySelector('.cpu-circle');
            const cpuValue = document.querySelector('.cpu-value');
            if (cpuCircle && cpuValue) {
                const cpuOffset = 283 - (283 * cpuPercent / 100);
                gsap.to(cpuCircle, {
                    strokeDashoffset: cpuOffset,
                    duration: 1,
                    ease: 'power2.out'
                });
                cpuValue.textContent = cpuPercent + '%';
            }

            // Update RAM
            const ramCircle = document.querySelector('.ram-circle');
            const ramValue = document.querySelector('.ram-value');
            if (ramCircle && ramValue) {
                const ramOffset = 283 - (283 * ramPercent / 100);
                gsap.to(ramCircle, {
                    strokeDashoffset: ramOffset,
                    duration: 1,
                    ease: 'power2.out'
                });
                ramValue.textContent = ramPercent + '%';
            }

            // Update Disk
            const diskCircle = document.querySelector('.disk-circle');
            const diskValue = document.querySelector('.disk-value');
            if (diskCircle && diskValue) {
                const diskOffset = 283 - (283 * diskPercent / 100);
                gsap.to(diskCircle, {
                    strokeDashoffset: diskOffset,
                    duration: 1,
                    ease: 'power2.out'
                });
                diskValue.textContent = diskPercent + '%';
            }
        }

        function updateUserStats(participants, prizesWon) {
            const participantCards = document.querySelectorAll('.user-stats .stat-card .stat-value');
            if (participantCards.length >= 1) {
                // Animate participant count
                gsap.to({ val: parseInt(participantCards[0].textContent) || 0 }, {
                    val: participants || 0,
                    duration: 1,
                    ease: 'power2.out',
                    onUpdate: function() {
                        participantCards[0].textContent = Math.floor(this.val);
                    }
                });
            }

            if (participantCards.length >= 2) {
                // Animate prizes won count
                gsap.to({ val: parseInt(participantCards[1].textContent) || 0 }, {
                    val: prizesWon || 0,
                    duration: 1,
                    ease: 'power2.out',
                    onUpdate: function() {
                        participantCards[1].textContent = Math.floor(this.val);
                    }
                });
            }
        }

        // Navigation
        function setupNavigation() {
            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');

                    // Smooth page transition
                    gsap.to('.glass-card', {
                        opacity: 0,
                        y: -20,
                        duration: 0.3,
                        onComplete: () => {
                            switchSection(section);
                            gsap.fromTo('.glass-card',
                                { opacity: 0, y: 20 },
                                { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' }
                            );
                        }
                    });
                });
            });
        }

        function switchSection(sectionName) {
            // Stop real-time updates when leaving dashboard
            if (currentSection === 'dashboard' && sectionName !== 'dashboard') {
                stopRealtimeUpdates();
            }

            // Hide all sections
            document.querySelectorAll('.glass-card').forEach(card => {
                card.style.display = 'none';
            });

            // Remove active class from nav
            document.querySelectorAll('.nav-link').forEach(link => {
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

            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'discounts': 'Discount Management',
                'themes': 'Festival Themes',
                'users': 'Users Management'
            };
            const subtitles = {
                'dashboard': 'Welcome to Festival Admin Panel',
                'discounts': 'Create and manage discount codes',
                'themes': 'Customize festival themes and animations',
                'users': 'Monitor user activity and participation'
            };

            document.getElementById('page-title').textContent = titles[sectionName] || 'Admin Panel';
            document.getElementById('page-subtitle').textContent = subtitles[sectionName] || 'Manage your festival system';

            currentSection = sectionName;

            // Load section data
            switch(sectionName) {
                case 'dashboard':
                    loadDashboard();
                    // Restart real-time updates when returning to dashboard
                    if (!realtimeInterval) {
                        startRealtimeUpdates();
                    }
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

            // Calculate circular progress values (283 is the circumference of the circle)
            const cpuPercent = data.server_stats.cpu || 0;
            const ramPercent = data.server_stats.ram || 0;
            const diskPercent = data.server_stats.disk || 0;

            const cpuOffset = 283 - (283 * cpuPercent / 100);
            const ramOffset = 283 - (283 * ramPercent / 100);
            const diskOffset = 283 - (283 * diskPercent / 100);

            container.innerHTML = `
                <!-- Server Metrics Section -->
                <div class="server-metrics">
                    <div class="metric-card">
                        <div class="metric-title">CPU Usage</div>
                        <div class="circular-progress">
                            <svg>
                                <circle class="bg-circle" cx="60" cy="60" r="45"></circle>
                                <circle class="progress-circle cpu-circle" cx="60" cy="60" r="45" data-percent="${cpuPercent}"></circle>
                            </svg>
                            <div class="metric-value cpu-value">${cpuPercent}%</div>
                        </div>
                        <div class="metric-label">Processor Load</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-title">Memory Usage</div>
                        <div class="circular-progress">
                            <svg>
                                <circle class="bg-circle" cx="60" cy="60" r="45"></circle>
                                <circle class="progress-circle ram-circle" cx="60" cy="60" r="45" data-percent="${ramPercent}"></circle>
                            </svg>
                            <div class="metric-value ram-value">${ramPercent}%</div>
                        </div>
                        <div class="metric-label">RAM Consumption</div>
                    </div>

                    <div class="metric-card">
                        <div class="metric-title">Disk Usage</div>
                        <div class="circular-progress">
                            <svg>
                                <circle class="bg-circle" cx="60" cy="60" r="45"></circle>
                                <circle class="progress-circle disk-circle" cx="60" cy="60" r="45" data-percent="${diskPercent}"></circle>
                            </svg>
                            <div class="metric-value disk-value">${diskPercent}%</div>
                        </div>
                        <div class="metric-label">Storage Used</div>
                    </div>
                </div>

                <!-- User Statistics Section -->
                <div class="user-stats">
                    <div class="stat-card">
                        <div class="stat-value">${data.participants || 0}</div>
                        <div class="stat-label">Total Participants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.prizes_won || 0}</div>
                        <div class="stat-label">Prizes Awarded</div>
                    </div>
                </div>
            `;

            // Recent activity
            if (data.recent_activity && data.recent_activity.length > 0) {
                const activityList = document.getElementById('activity-list');
                activityList.innerHTML = data.recent_activity.map(activity => `
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-${activity.action === 'won_prize' ? 'trophy' : activity.action === 'participated' ? 'gamepad' : 'user'}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">
                                <strong>${activity.user_id}</strong> ${activity.action}
                                ${activity.prize_code ? `<code>${activity.prize_code}</code>` : ''}
                            </div>
                            <div class="activity-time">${new Date(activity.timestamp).toLocaleString('fa-IR')}</div>
                        </div>
                    </div>
                `).join('');
                document.getElementById('recent-activity').style.display = 'block';
            }

            // Animate metric cards
            gsap.fromTo('.metric-card',
                { opacity: 0, scale: 0.8, y: 30 },
                {
                    opacity: 1,
                    scale: 1,
                    y: 0,
                    duration: 0.8,
                    stagger: 0.15,
                    ease: 'back.out(1.7)',
                    delay: 0.1
                }
            );

            // Animate user stat cards
            gsap.fromTo('.user-stats .stat-card',
                { opacity: 0, scale: 0.8, y: 20 },
                {
                    opacity: 1,
                    scale: 1,
                    y: 0,
                    duration: 0.6,
                    stagger: 0.1,
                    ease: 'back.out(1.7)',
                    delay: 0.4
                }
            );

            // Animate circular progress
            document.querySelectorAll('.progress-circle').forEach((circle, index) => {
                const percent = parseInt(circle.getAttribute('data-percent'));
                const targetOffset = 283 - (283 * percent / 100);

                gsap.fromTo(circle,
                    { strokeDashoffset: 283 },
                    {
                        strokeDashoffset: targetOffset,
                        duration: 1.5,
                        ease: 'power2.out',
                        delay: 0.6 + (index * 0.2)
                    }
                );
            });
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
                <div class="theme-item">
                    <div>
                        <div>
                            <h4>${theme.name} ${theme.active ? '(Active)' : ''}</h4>
                            <p>${theme.guide_text}</p>
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

            const icon = type === 'success' ? 'check-circle' :
                        type === 'error' ? 'exclamation-triangle' : 'info-circle';
            alert.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;

            document.querySelector('.admin-content').insertBefore(alert, document.querySelector('.admin-content').firstChild);

            // Animate alert entrance
            gsap.fromTo(alert,
                { opacity: 0, y: -20, scale: 0.9 },
                { opacity: 1, y: 0, scale: 1, duration: 0.5, ease: 'back.out(1.7)' }
            );

            setTimeout(() => {
                gsap.to(alert, {
                    opacity: 0,
                    y: -20,
                    duration: 0.3,
                    onComplete: () => alert.remove()
                });
            }, 5000);
        }
    </script>
</body>
</html>

