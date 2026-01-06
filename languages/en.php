<?php
/**
 * English Language File
 * Festival System Localization
 * Version: 1.0.0
 */

return [
    // ========== GENERAL ==========
    'version' => 'Version',
    'loading' => 'Loading...',
    'hello_world' => 'Hello World',
    'welcome_user' => 'Welcome, {name}!',
    'error' => 'Error',
    'success' => 'Success',
    'warning' => 'Warning',
    'info' => 'Information',
    'confirm' => 'Confirm',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'add' => 'Add',
    'create' => 'Create',
    'update' => 'Update',
    'close' => 'Close',
    'back' => 'Back',
    'next' => 'Next',
    'previous' => 'Previous',
    'continue' => 'Continue',
    'finish' => 'Finish',
    'submit' => 'Submit',
    'reset' => 'Reset',
    'search' => 'Search',
    'filter' => 'Filter',
    'sort' => 'Sort',
    'export' => 'Export',
    'import' => 'Import',

    // ========== FESTIVAL SYSTEM ==========
    'festival_system' => 'Festival System',
    'yalda_festival' => 'Yalda Festival',
    'happy_yalda_night' => 'Happy Yalda Night',
    'yalda_night_festival' => 'Yalda Night Festival',
    'enter_festival' => 'Enter the Festival',
    'ready_receive_gift' => 'Ready to Receive Your Gift?',
    'click_fruit_win' => 'Click on a watermelon or pomegranate to win!',
    'receiving_gift' => 'Receiving gift...',
    'congratulations' => 'Congratulations!',
    'your_gift' => 'Your Gift:',
    'gift_message_sent' => 'Gift message sent to your Telegram',
    'enter_telegram_id' => 'Enter your Telegram User ID',

    // ========== HELP SYSTEM ==========
    'help' => 'Help',
    'help_instructions' => 'Go to the bot and press the "💼 My Account" button or send the "/wallet" command. At the beginning of the bot\'s reply, your User ID is shown.',
    'example' => 'Example',
    'user_id' => 'User ID',
    'paste_from_clipboard' => 'Paste from clipboard',
    'clipboard_access_error' => 'Clipboard access error',

    // ========== VALIDATION MESSAGES ==========
    'user_id_required' => 'User ID is required',
    'invalid_user_id_format' => 'Invalid User ID format',
    'user_id_valid' => 'User ID is valid',
    'user_not_found' => 'User not found in bot system',
    'please_start_bot' => 'Please start the bot first and try again',
    'bot_api_not_configured' => 'Bot API token not configured',
    'configure_bot_token' => 'Please configure BOT_API_TOKEN in config.php',
    'connection_error' => 'Connection error',
    'checking_user_id' => 'Checking User ID...',
    'id_pasted_clipboard' => 'ID pasted from clipboard',

    // ========== LOTTERY SYSTEM ==========
    'try_again' => 'Try again!',
    'try_again_participate' => 'Try again! You can participate again.',
    'no_prize_this_time' => 'Unfortunately, no prize this time',
    'error_receiving_gift' => 'Error receiving gift',

    // ========== ADMIN PANEL ==========
    'admin_login' => 'Admin Login',
    'admin_panel' => 'Admin Panel',
    'dashboard' => 'Dashboard',
    'discount_management' => 'Discount Management',
    'festival_themes' => 'Festival Themes',
    'users_list' => 'Users List',
    'logout' => 'Logout',
    'login' => 'Login',
    'username' => 'Username',
    'password' => 'Password',
    'invalid_credentials' => 'Invalid username or password',

    // ========== DASHBOARD ==========
    'participants' => 'Participants',
    'prizes_won' => 'Prizes Won',
    'server_cpu' => 'Server CPU',
    'server_ram' => 'Server RAM',
    'recent_activity' => 'Recent Activity',
    'loading_statistics' => 'Loading statistics...',

    // ========== DISCOUNT MANAGEMENT ==========
    'discount_percentage' => 'Discount Percentage',
    'probability_weight' => 'Probability Weight (higher number = better chance)',
    'code_prefix' => 'Code Prefix',
    'expiry_hours' => 'Expiry Hours',
    'target_product' => 'Target Product',
    'add_discount_type' => 'Add Discount Type',
    'loading_discounts' => 'Loading discounts...',
    'no_discounts_found' => 'No discounts found',
    'percent' => 'Percent',
    'weight' => 'Weight',
    'prefix' => 'Prefix',
    'expiry' => 'Expiry',
    'product' => 'Product',
    'actions' => 'Actions',
    'confirm_delete_discount' => 'Are you sure you want to delete this discount?',
    'discount_created_success' => 'Discount created successfully',
    'error_creating_discount' => 'Error creating discount',
    'discount_deleted_success' => 'Discount deleted successfully',
    'error_deleting_discount' => 'Error deleting discount',

    // ========== THEME MANAGEMENT ==========
    'theme_name' => 'Theme Name',
    'guide_text' => 'Guide Text',
    'falling_objects' => 'Falling Objects (comma separated)',
    'explosion_effect' => 'Explosion Effect',
    'color_palette' => 'Color Palette',
    'create_new_theme' => 'Create New Theme',
    'loading_themes' => 'Loading themes...',
    'no_themes_found' => 'No themes found',
    'objects' => 'Objects',
    'seeds' => 'Seeds',
    'snow' => 'Snow',
    'sparkles' => 'Sparkles',
    'activate' => 'Activate',
    'active' => 'Active',
    'theme_created_success' => 'Theme created successfully',
    'error_creating_theme' => 'Error creating theme',
    'theme_activated_success' => 'Theme activated successfully',
    'error_activating_theme' => 'Error activating theme',
    'changes_saved_success' => 'Changes saved successfully',
    'error_saving_changes' => 'Error saving changes',
    'error_loading_theme_info' => 'Error loading theme information',
    'edit_theme' => 'Edit Theme',
    'edit_discount' => 'Edit Discount',

    // ========== USER MANAGEMENT ==========
    'user_id' => 'User ID',
    'total_prizes' => 'Total Prizes',
    'last_participation' => 'Last Participation',
    'none' => 'None',
    'loading_users' => 'Loading users...',
    'no_users_found' => 'No users found',
    'user_details_feature_soon' => 'User details feature will be added soon',

    // ========== TIME FILTERS ==========
    '1_hour' => '1 hour',
    '6_hours' => '6 hours',
    '12_hours' => '12 hours',
    '24_hours' => '24 hours',
    '7_days' => '7 days',
    '30_days' => '30 days',

    // ========== SYSTEM STATUS ==========
    'system_status' => 'Festival System Status',
    'system_ready' => 'System is ready!',
    'everything_setup' => 'Everything is set up properly. Now you can:',
    'open_main_page' => 'Open the main festival page',
    'configure_admin_panel' => 'Configure the admin panel',
    'change_admin_password' => 'Change admin password',
    'required_actions' => 'Required Actions',
    'check_database_settings' => 'Check database settings in config.php',
    'run_table_file' => 'Run table.php file',
    'upload_missing_files' => 'Upload missing files',
    'read_deployment_guide' => 'Read full guide in DEPLOYMENT_GUIDE.md',
    'checked_on' => 'Checked on',

    // ========== SERVER STATUS ==========
    'php_version' => 'PHP Version',
    'compatible' => 'Compatible',
    'min_php_required' => 'Minimum PHP 7.4 required',
    'database_connection' => 'Database Connection',
    'connected' => 'Connected',
    'database_tables' => 'Database Tables',
    'all_present' => 'All present',
    'missing_tables' => 'Missing tables',
    'tables_created' => 'Tables created',
    'run_table_php' => 'Run: table.php',
    'active_theme' => 'Active Theme',
    'present' => 'Present',
    'missing' => 'Missing',
    'default_theme_not_created' => 'Default theme not created',
    'theme_ready' => 'Theme ready',
    'telegram_bot_token' => 'Telegram Bot Token',
    'configured' => 'Configured',
    'not_configured' => 'Not configured',
    'ready_to_connect' => 'Ready to connect',
    'configure_token_config' => 'Configure token in config.php',
    'essential_files' => 'Essential Files',
    'files_ready' => 'Files ready',
    'upload_missing_files_action' => 'Upload missing files',

    // ========== API MESSAGES ==========
    'missing_user_id' => 'Missing user_id',
    'too_many_requests' => 'Too many requests. Please wait before trying again.',
    'wait_hours' => 'Please wait {hours} hours between participations.',
    'cooldown' => 'Cooldown',
    'bot_token_not_configured' => 'Bot API token not configured',

    // ========== NOTIFICATIONS ==========
    'prize_won_notification' => '🎉 Congratulations! You won {prize}!',
    'discount_code' => 'Your discount code: {code}',
    'use_code_in_bot' => 'Use this code in the bot.',

    // ========== ERROR MESSAGES ==========
    'api_error' => 'API Error',
    'server_connection_error' => 'Connection error',
    'lottery_draw_failed' => 'Lottery draw failed',
    'database_error' => 'Database error',
    'file_not_found' => 'File not found',
    'permission_denied' => 'Permission denied',
    'invalid_request' => 'Invalid request',
    'session_expired' => 'Session expired',

    // ========== SUCCESS MESSAGES ==========
    'operation_successful' => 'Operation successful',
    'data_saved' => 'Data saved successfully',
    'settings_updated' => 'Settings updated successfully',
    'file_uploaded' => 'File uploaded successfully',
    'user_created' => 'User created successfully',
    'login_successful' => 'Login successful',

    // ========== JAVASCRIPT MESSAGES ==========
    'fruit_clicked' => 'Fruit clicked',
    'demo_fruit_clicked' => 'Demo fruit clicked',
    'game_fruit_clicked' => 'Game fruit clicked',
    'creating_particles' => 'Creating particles',
    'particles_removed' => 'Particles removed',
    'demo_particles_removed' => 'Demo particles removed',
    'fruit_removed' => 'Fruit removed',
    'screen_shake' => 'Screen shake',
    'winner_celebration' => 'Winner celebration',
    'gift_code_copied' => 'Gift code copied',

    // ========== CONFIGURATION ==========
    'change_to_actual_token' => 'Change to actual token',

    // ========== TIME FORMATS ==========
    'date_format' => 'Y-m-d H:i:s',
    'locale_en' => 'en-US',
    'locale_fa' => 'fa-IR',

    // ========== COOLDOWN MESSAGES ==========
    'hours_remaining' => '{hours} hours remaining',
    'participation_limit' => 'Participation limit reached',
    'wait_before_retry' => 'Please wait before trying again',

    // ========== DEBUGGING ==========
    'debug_mode' => 'Debug Mode',
    'js_working' => 'JS ✓',
    'api_response_status' => 'API response status',
    'loading_admin_panel' => 'Loading admin panel...',
    'checking_admin_session' => 'Checking admin session...',
    'session_ok' => 'Session OK',
    'dom_content_loaded' => 'DOM Content Loaded',
    'making_api_call' => 'Making API call to',

    // ========== CSS COMMENTS ==========
    'ensure_clickability' => 'ensure clickability',
    'above_everything' => 'above everything',
    'particles_outside_container' => 'removed so particles outside container are also displayed',

    // ========== HTML PLACEHOLDERS ==========
    'telegram_id_placeholder' => '123456789',

    // ========== BUTTON LABELS ==========
    'paste' => 'Paste',
    'copy' => 'Copy',
    'start_game' => 'Start Game',
    'my_account' => 'My Account',
    'wallet_command' => '/wallet',

    // ========== MODAL TITLES ==========
    'edit_modal_title' => 'Edit',

    // ========== TABLE HEADERS ==========
    'id' => 'ID',
    'name' => 'Name',
    'type' => 'Type',
    'status' => 'Status',
    'date' => 'Date',
    'time' => 'Time',
    'action' => 'Action',

    // ========== PLACEHOLDERS ==========
    'enter_theme_name' => 'Example: Yalda Night',
    'enter_guide_text' => 'Game guide text',
    'enter_falling_objects' => 'apple,orange,leaf',
    'enter_code_prefix' => 'Example: YALDA',
    'enter_target_product' => '30GB 30-day',
    'enter_discount_percent' => '10',

    // ========== ALERTS ==========
    'alert_success' => 'Success',
    'alert_error' => 'Error',
    'alert_warning' => 'Warning',
    'alert_info' => 'Info',

    // ========== LOADING TEXTS ==========
    'loading_data' => 'Loading data...',
    'processing' => 'Processing...',
    'saving' => 'Saving...',
    'deleting' => 'Deleting...',
    'updating' => 'Updating...',

    // ========== CONFIRMATION MESSAGES ==========
    'confirm_delete' => 'Are you sure you want to delete this item?',
    'confirm_logout' => 'Are you sure you want to logout?',
    'confirm_reset' => 'Are you sure you want to reset?',

    // ========== NAVIGATION ==========
    'home' => 'Home',
    'about' => 'About',
    'contact' => 'Contact',
    'help' => 'Help',
    'settings' => 'Settings',
    'profile' => 'Profile',
    'account' => 'Account',

    // ========== PAGINATION ==========
    'page' => 'Page',
    'of' => 'of',
    'first' => 'First',
    'last' => 'Last',
    'showing' => 'Showing',
    'to' => 'to',
    'entries' => 'entries',

    // ========== VALIDATION ==========
    'field_required' => 'This field is required',
    'invalid_email' => 'Invalid email address',
    'invalid_number' => 'Invalid number',
    'too_short' => 'Too short',
    'too_long' => 'Too long',
    'password_mismatch' => 'Passwords do not match',

    // ========== FILE UPLOAD ==========
    'select_file' => 'Select File',
    'upload' => 'Upload',
    'file_too_large' => 'File too large',
    'invalid_file_type' => 'Invalid file type',
    'upload_success' => 'File uploaded successfully',
    'upload_error' => 'Upload error',

    // ========== THEME OBJECTS ==========
    'apple' => '🍎',
    'orange' => '🍊',
    'leaf' => '🍃',
    'snowflake' => '❄',
    'star' => '⭐',
    'heart' => '❤️',
    'firework' => '🎆',
    'watermelon' => '🍉',
    'pomegranate' => '🍑',
    'grape' => '🍇',
    'cherry' => '🍒',
    'strawberry' => '🍓',

    // ========== EXPLOSION EFFECTS ==========
    'seeds_effect' => 'Seeds (pomegranate/watermelon)',
    'snow_effect' => 'Snow (Christmas)',
    'sparkles_effect' => 'Sparkles',

    // ========== DEFAULT VALUES ==========
    'default_theme_name' => 'Default Yalda',
    'default_guide_text' => '🍉 Click on a watermelon or pomegranate to receive your gift! 🍇',
    'default_falling_objects' => '🍎,🍉',
    'default_explosion_effect' => 'seeds',
    'default_code_prefix' => 'YALDA',
    'default_target_product' => '30GB 30-day',
    'default_expiry_hours' => '24',

    // ========== API ENDPOINTS ==========
    'validate_endpoint' => '/api/validate.php',
    'participate_endpoint' => '/api/participate.php',
    'admin_api_endpoint' => '/api/admin-api.php',

    // ========== HTTP STATUS ==========
    'status_ok' => 'OK',
    'status_error' => 'Error',
    'status_success' => 'Success',
    'status_warning' => 'Warning',
    'status_info' => 'Info',

    // ========== BROWSER DETECTION ==========
    'browser_not_supported' => 'Browser not supported',
    'update_browser' => 'Please update your browser',

    // ========== RESPONSIVE DESIGN ==========
    'mobile_menu' => 'Menu',
    'desktop_view' => 'Desktop View',
    'mobile_view' => 'Mobile View',

    // ========== ACCESSIBILITY ==========
    'screen_reader' => 'Screen Reader',
    'high_contrast' => 'High Contrast',
    'font_size' => 'Font Size',
    'language' => 'Language',

    // ========== SOCIAL MEDIA ==========
    'share' => 'Share',
    'tweet' => 'Tweet',
    'like' => 'Like',
    'follow' => 'Follow',

    // ========== ANALYTICS ==========
    'page_views' => 'Page Views',
    'unique_visitors' => 'Unique Visitors',
    'bounce_rate' => 'Bounce Rate',
    'conversion_rate' => 'Conversion Rate',

    // ========== PERFORMANCE ==========
    'load_time' => 'Load Time',
    'response_time' => 'Response Time',
    'uptime' => 'Uptime',
    'downtime' => 'Downtime',

    // ========== SECURITY ==========
    'secure_connection' => 'Secure Connection',
    'ssl_certificate' => 'SSL Certificate',
    'encryption' => 'Encryption',
    'firewall' => 'Firewall',

    // ========== BACKUP ==========
    'backup' => 'Backup',
    'restore' => 'Restore',
    'backup_created' => 'Backup created successfully',
    'restore_completed' => 'Restore completed successfully',

    // ========== MAINTENANCE ==========
    'maintenance' => 'Maintenance',
    'maintenance_mode' => 'Maintenance Mode',
    'system_update' => 'System Update',
    'database_optimization' => 'Database Optimization',
];
