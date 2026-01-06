# Changelog

All notable changes to the Festival Reward System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2026-01-06 - English Only Release

### 🌍 Major Changes
- **Complete Persian language removal**: All Persian text, comments, and UI elements removed
- **English-only interface**: Clean, professional single-language user experience
- **Simplified internationalization system**: Ready for easy language additions by developers
- **Code cleanup**: Removed multilingual complexity and unnecessary files

### 🔧 Technical Improvements
- Removed all `data-lang` attributes from HTML files
- Simplified JavaScript without Persian string handling
- Updated all CSS comments to English
- Streamlined admin panel to English only
- Cleaned up language files directory structure

### 🎯 Key Features
- **Monolingual English interface** for better user experience
- **Developer-friendly i18n system** with 337+ translation keys ready for expansion
- **Easy language addition**: Just create new language files and add to AVAILABLE_LANGUAGES
- **Production-ready codebase** without multilingual overhead

### 📦 Files Removed
- `languages/fa.php` - Persian language file
- `languages/add_language.php` - Language addition script
- `languages/test_i18n.php` - Test file
- `languages/USAGE.md` - Usage documentation
- `languages/integration_example.php` - Integration example
- `languages/example_new_language.php` - Example template
- All Persian font imports (Vazirmatn)
- Language toggle functionality from HTML

### 🚀 Migration Guide
- **For Users**: Interface is now in English only
- **For Developers**: i18n system is ready for language additions
- **For Deployments**: No database changes required

### 🤝 Adding New Languages (For Developers)
```bash
# 1. Create new language file
cp languages/en.php languages/de.php

# 2. Translate all keys in de.php
# Edit 337+ translation keys

# 3. Add to i18n system
# Edit languages/i18n.php and add 'de' to AVAILABLE_LANGUAGES

# 4. Test
# Visit: index.html?lang=de
```

## [1.0.2] - 2026-01-XX - Enhanced Release

### ✨ Added
- **Complete CRUD Operations**: Full edit functionality for discounts and themes in admin panel
- **Dynamic Theme System**: Real-time theme loading with custom falling objects and explosion effects
- **Language Toggle**: Persian/English switching with RTL/LTR support
- **API Improvements**: Fixed JavaScript API calls and enhanced error handling
- **Enhanced Lottery**: Improved prize distribution with multiple outcome types
- **Mobile Optimization**: Better touch interface and responsive animations
- **Bug Fixes**: Resolved variable scoping, API endpoints, modal positioning, and fruit clicking issues
- **Interactive Fruits**: Clickable falling fruits with shatter effects and particle explosions
- **Glass Morphism**: Beautiful glass-like UI with backdrop blur effects

## [1.0.1] - 2026-01-XX - Complete Release

### ✅ Added
- **Full Admin Panel**: 4-section dashboard with statistics and management
- **Weighted Lottery System**: Server-side probability calculation
- **Bot Integration**: Telegram API for user validation and discount creation
- **Theme Management**: Customizable festivals with animations
- **Analytics**: User tracking and participation logs

### 🔧 Technical
- Clean config.php with proper database abstraction
- Complete admin API with dashboard statistics
- Enhanced frontend with improved UX and animations
- Bot API integration for real-time discount creation
- Proper error handling and logging throughout

---

## 📋 Version Numbering

We use [Semantic Versioning](https://semver.org/):

- **MAJOR.MINOR.PATCH**
- **0.2.0** = Major feature release (English-only)
- **1.0.1** = First stable release
- **1.0.2** = Enhancement release

## 🎯 Future Releases

### Planned for v0.3.0
- Multi-language support reintroduction
- Advanced admin features
- Performance optimizations

### Planned for v1.1.0
- Advanced analytics dashboard
- Mobile app integration
- Social media sharing

---

**Legend:**
- 🌍 Major language/interface changes
- ✨ New features
- 🔧 Technical improvements
- 🐛 Bug fixes
- 📦 File/removal changes
