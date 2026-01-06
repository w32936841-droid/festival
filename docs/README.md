# 🎪 Festival Reward System v0.2

A complete interactive festival reward system with admin panel, featuring weighted probability lottery, Telegram bot integration, and beautiful animations. Now in English only with developer-friendly internationalization.

## 📋 Changelog

### v0.2 - English Only & Internationalization Ready (Current)
- 🌍 **Complete Persian Removal**: All Persian text, comments, and UI elements removed
- 🇺🇸 **English-Only Interface**: Clean, single-language user experience
- 🔧 **Simplified i18n System**: Developer-friendly internationalization with 337+ translation keys
- 🧹 **Code Cleanup**: Removed multilingual complexity and unnecessary files
- 🚀 **Production Ready**: Streamlined codebase for easier maintenance and deployment

### v1.0.2 - Enhanced Release
- ✨ **Complete CRUD Operations**: Full edit functionality for discounts and themes in admin panel
- 🎨 **Dynamic Theme System**: Real-time theme loading with custom falling objects and explosion effects
- 🌐 **Language Toggle**: Persian/English switching with RTL/LTR support
- 🔧 **API Improvements**: Fixed JavaScript API calls and enhanced error handling
- 🎯 **Enhanced Lottery**: Improved prize distribution with multiple outcome types
- 📱 **Mobile Optimization**: Better touch interface and responsive animations
- 🐛 **Bug Fixes**: Resolved variable scoping, API endpoints, modal positioning, and fruit clicking issues
- 🎪 **Interactive Fruits**: Clickable falling fruits with shatter effects and particle explosions
- 💎 **Glass Morphism**: Beautiful glass-like UI with backdrop blur effects

### v1.0.1 - Complete Release
- ✅ **Full Admin Panel**: 4-section dashboard with statistics and management
- 🎲 **Weighted Lottery System**: Server-side probability calculation
- 🤖 **Bot Integration**: Telegram API for user validation and discount creation
- 🎨 **Theme Management**: Customizable festivals with animations
- 📊 **Analytics**: User tracking and participation logs

## 🌟 Features

### 🎮 User Experience
- **Interactive Falling Objects Game**: Click on falling fruits/pomegranates/watermelons to win prizes
- **Real-time Validation**: Telegram User ID validation via bot API
- **Enhanced Animations**: Beautiful explosion effects with particle systems
- **English-Only Interface**: Clean, professional single-language experience
- **Mobile Responsive**: Works perfectly on all devices
- **Developer-Friendly i18n**: Easy to add new languages with 337+ translation keys

### 🎯 Lottery System
- **Weighted Probabilities**: Configure different win chances for each prize type
- **Server-side Security**: Lottery calculation happens on backend to prevent manipulation
- **Multiple Outcomes**: Prizes, "Try Again" (respin), and "No Prize"
- **24-hour Cooldown**: Prevents spam participation

### 👨‍💼 Admin Panel
- **Dashboard**: Real-time statistics, server monitoring, activity logs
- **Discount Management**: Create/edit/delete discount types with custom probabilities
- **Theme Management**: Configure logos, backgrounds, falling objects, colors
- **User Management**: Track participants, view activity logs, manage cooldowns

### 🤖 Bot Integration
- **User Validation**: Check if users exist in your Telegram bot system
- **Auto Discount Creation**: Automatically create discount codes in bot system
- **Real-time Notifications**: Send prize notifications to users
- **Product Management**: Integration with bot's product catalog

## 🚀 Quick Start

### 1. Check System Status
First, check if everything is set up correctly:
```
https://yourdomain.com/utils/status.php
```

### 2. Database Setup
Run the table creation script:
```
https://yourdomain.com/utils/table.php
```

### 3. Configuration
Edit `config.php` with your settings:
```php
// Database settings
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_db_user');
define('DB_PASSWORD', 'your_db_password');

// Bot API settings (required for user validation)
define('BOT_API_TOKEN', 'your_actual_bot_token_here');
```

### 4. Change Admin Password
Read `docs/ADMIN_PASSWORD_CHANGE.md` and change the default admin credentials.

### 5. Access Points
- **User Interface**: `index.html`
- **Admin Panel**: `admin/index.php`
- **System Status**: `utils/status.php`
- **API Endpoints**: `api/` directory

### 📖 For detailed deployment instructions, see `DEPLOYMENT_GUIDE.md`

## 📊 Database Schema

### Core Tables
- `themes` - Festival theme configurations
- `users` - Participant tracking and cooldowns
- `logs` - Activity logging and analytics
- `discount_types` - Prize configurations with weights
- `discounts` - Generated discount codes

## 🎨 Admin Panel Usage

### Dashboard
- View real-time statistics (participants, prizes won)
- Monitor server resources (CPU, RAM, Disk)
- Browse recent activity logs
- Filter by time ranges (1h, 6h, 12h, 24h, 7d, 30d)

### Discount Management
1. Click "Discount Management"
2. Add new discount types with:
   - **Discount Percentage**: 1%, 5%, 10%, 20%, 30%, 50%, 90%
   - **Weight**: Higher = better chance (e.g., 90% discount = 0.1, 5% discount = 30)
   - **Code Prefix**: e.g., "YALDA", "FESTIVAL"
   - **Expiry Hours**: 12, 24, 48, 72 hours

### Theme Management
1. Click "Festival Themes"
2. Create themes with:
   - Custom logos and backgrounds
   - Falling objects (fruits, snowflakes, emojis)
   - Color palettes for UI elements
   - Explosion effects (seeds, snow, sparkles)

### User Management
- View all participants
- Track participation history
- Monitor cooldown status
- View prize distribution

## 🎮 User Experience Flow

1. **Landing Page**: User sees falling objects and glass morphism UI
2. **ID Input**: Enter Telegram User ID with paste button and validation
3. **Bot Check**: System validates user exists in Telegram bot
4. **Game Start**: Falling objects speed up, user clicks to win
5. **Prize Reveal**: Animated explosion reveals discount code
6. **Notification**: User receives Telegram notification with code

## 🔧 API Endpoints

### User APIs
- `POST /api/validate.php` - Validate Telegram User ID
- `POST /api/participate.php` - Participate in lottery
- `POST /api/get-gift.php` - Get gift (legacy, use participate.php)

### Admin APIs
- `GET /api/admin-api.php?action=dashboard_stats` - Dashboard statistics
- `GET /api/admin-api.php?action=get_discounts` - List discount types
- `POST /api/admin-api.php?action=create_discount` - Create discount type
- `GET /api/admin-api.php?action=get_themes` - List themes
- `GET /api/admin-api.php?action=get_users` - List users

### Bot Integration
- `TelegramBotAPI::checkUser()` - Validate user existence
- `TelegramBotAPI::createDiscountCode()` - Create discount in bot
- `TelegramBotAPI::sendNotification()` - Send user notifications

## 🎨 Customization

### Themes
Edit theme settings in admin panel:
- **Logo**: Upload custom logo image
- **Background**: Set background image
- **Falling Objects**: Define what falls (🍎 🍉 ❄️)
- **Colors**: Primary/secondary color palette
- **Explosion**: Choose effect (seeds/snow/sparkles)

### Probabilities
Configure weighted lottery in admin panel:
```php
// Example weights
90% discount: 0.1 (rare)
50% discount: 1.0 (uncommon)
20% discount: 5.0 (common)
5% discount: 30.0 (very common)
No Prize: 20.0
Try Again: 10.0
```

### Internationalization (i18n)
Add new languages easily:
```php
// 1. Create languages/de.php with 337 translation keys
// 2. Add 'de' to AVAILABLE_LANGUAGES in languages/i18n.php
// 3. Access: index.html?lang=de

// Example language file structure:
return [
    'hello_world' => 'Hallo Welt',
    'welcome' => 'Willkommen',
    // ... 335+ more keys
];
```

## 🔒 Security Features

- **Server-side Lottery**: Prevents frontend manipulation
- **User Validation**: Telegram bot integration
- **Input Sanitization**: All inputs validated and sanitized
- **Rate Limiting**: 24-hour cooldown system
- **Error Logging**: Comprehensive logging system

## 📱 Mobile Optimization

- Responsive design for all screen sizes
- Touch-friendly interface
- Optimized animations for mobile performance
- Native paste functionality

## 🌐 Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Graceful degradation for older browsers

## 🐛 Troubleshooting

### Common Issues

**Users can't participate:**
- Check bot API token in config.php
- Verify user exists in Telegram bot
- Check 24-hour cooldown

**Animations not working:**
- Ensure modern browser
- Check CSS loading
- Verify JavaScript console for errors

**Admin panel not loading:**
- Check database connection
- Verify table creation with utils/table.php
- Check file permissions

### Debug Mode
Enable debug logging by setting:
```php
define('DEBUG_MODE', true);
```

## 📈 Performance

- Optimized database queries
- Efficient animation handling
- Lazy loading for large datasets
- CDN-ready asset structure

## 🤝 Contributing

### Code Contributions
1. Fork the repository
2. Create feature branch
3. Test thoroughly
4. Submit pull request

### Adding New Languages
1. Copy `languages/en.php` to `languages/{code}.php`
2. Translate all 337+ keys accurately
3. Add language code to `AVAILABLE_LANGUAGES` in `languages/i18n.php`
4. Test with `?lang={code}` parameter
5. Submit pull request with translation

### Translation Quality
- Use proper cultural context
- Maintain technical terminology accuracy
- Test UI layout with new translations
- Ensure RTL support for right-to-left languages

## 📄 License

This project is proprietary software. All rights reserved.

## 🆘 Support

For support:
1. Check version.txt for current version
2. Review error logs in server
3. Test API endpoints manually
4. Check browser console for JavaScript errors

---

**Made with ❤️ for Festival Celebrations**
