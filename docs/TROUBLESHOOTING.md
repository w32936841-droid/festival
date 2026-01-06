# 🔧 Festival System Troubleshooting Guide

## 🚨 Common Issues and Solutions

### 1. "Connection Error" Issue

#### Possible Causes:
- Incorrect CORS settings
- Host firewall issues
- Wrong API paths

#### Solutions:
1. Open `debug-api.php` in browser
2. Click "Test Validate" button
3. If still errors, check CORS settings

#### CORS Test:
```bash
# Run in browser console
fetch('/api/validate.php', {
    method: 'OPTIONS'
}).then(r => console.log('CORS OK:', r.status));
```

### 2. Admin Panel Not Working

#### Possible Causes:
- Session not set
- Credentials not sent
- CORS issues

#### Solutions:
1. Open `utils/check-session.php`
2. Login with username `admin` and password `festival2024`
3. If login successful, test admin panel

#### Session Test:
- If login works but API doesn't, problem is in credentials
- Open `debug-api.php` and try "Test Admin Themes"

### 3. Default Theme Not Displayed

#### Possible Causes:
- Themes table is empty
- table.php not executed

#### Solutions:
1. Run `table.php` again
2. Open `debug-api.php` and check "Test Admin Themes"
3. If theme exists but not displayed, refresh admin panel

### 4. میوه‌ها ریزش نمی‌کنند

#### علل ممکن:
- فایل JavaScript بارگذاری نمی‌شود
- خطای JavaScript در کنسول

#### راه‌حل‌ها:
1. F12 را فشار دهید و تب Console را چک کنید
2. اگر خطای JavaScript وجود دارد، فایل را reload کنید
3. مطمئن شوید فایل `js/game-v0.3.js` بارگذاری می‌شود

### 5. فونت یا استایل کار نمی‌کند

#### علل ممکن:
- فایل CSS بارگذاری نمی‌شود
- مسیر فایل‌ها اشتباه است

#### راه‌حل‌ها:
1. F12 را فشار دهید و تب Network را چک کنید
2. مطمئن شوید فایل‌های CSS و JS با کد 200 بارگذاری می‌شوند

## 🛠 ابزارهای دیباگ

### فایل‌های تست موجود:
- `status.php` - وضعیت کلی سیستم
- `debug-api.php` - تست APIها
- `check-session.php` - تست Session پنل ادمین

### دستورات مفید کنسول مرورگر:

```javascript
// تست اتصال به API
fetch('/api/validate.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({telegram_id: '123456789'})
}).then(r => r.json()).then(d => console.log(d));

// تست پنل ادمین
fetch('/api/admin-api.php?action=get_themes', {
    credentials: 'include'
}).then(r => r.json()).then(d => console.log(d));
```

## 📞 مراحل عیب‌یابی گام به گام

### مرحله ۱: تست پایه
1. `status.php` را باز کنید
2. اگر دیتابیس و فایل‌ها OK نیستند، مشکل پایه‌ای وجود دارد

### مرحله ۲: تست API
1. `debug-api.php` را باز کنید
2. هر دکمه را تست کنید
3. اگر APIها کار نمی‌کنند، مشکل در CORS یا تنظیمات سرور است

### مرحله ۳: تست پنل ادمین
1. `check-session.php` را باز کنید
2. لاگین کنید
3. اگر لاگین OK است اما پنل نه، مشکل در credentials است

### مرحله ۴: تست Frontend
1. صفحه اصلی را باز کنید
2. F12 را فشار دهید و Console را چک کنید
3. اگر خطای JS وجود دارد، فایل را reload کنید

## 🔍 لاگ‌های مهم

### PHP Error Log
در CPanel، به بخش Error Log بروید و خطاها را چک کنید.

### JavaScript Console
F12 → Console → خطاها و warningها را چک کنید.

### Network Tab
F12 → Network → مطمئن شوید همه فایل‌ها با کد 200 بارگذاری می‌شوند.

## 📧 درخواست پشتیبانی

اگر مشکل حل نشد:
1. نتیجه همه فایل‌های تست را ارسال کنید
2. لاگ‌های کنسول مرورگر را کپی کنید
3. نسخه مرورگر و سیستم عامل خود را اعلام کنید
4. تنظیمات هاست (CPanel/MySQL) را چک کنید

## ✅ چک‌لیست نهایی

- [ ] `table.php` اجرا شده
- [ ] `status.php` همه چیز سبز نشان می‌دهد
- [ ] `debug-api.php` همه APIها کار می‌کنند
- [ ] `check-session.php` لاگین موفق است
- [ ] پنل ادمین بدون خطا بارگذاری می‌شود
- [ ] صفحه اصلی میوه‌ها را نشان می‌دهد
- [ ] ورودی کاربر API را صدا می‌زند

اگر همه این موارد OK هستند، سیستم آماده استفاده است! 🎊
