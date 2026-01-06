<?php
/**
 * Festival System Internationalization (i18n)
 * Central localization system for all text strings
 * Version: 1.0.0
 */

// Default language
define('DEFAULT_LANGUAGE', 'en');

// Available languages (English only)
define('AVAILABLE_LANGUAGES', ['en']);

// Language detection
function detectLanguage() {
    // Check GET parameter
    if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGUAGES)) {
        return $_GET['lang'];
    }

    // Check session
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], AVAILABLE_LANGUAGES)) {
        return $_SESSION['lang'];
    }

    // Check browser language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($browser_lang, AVAILABLE_LANGUAGES)) {
            return $browser_lang;
        }
    }

    return DEFAULT_LANGUAGE;
}

// Set language
function setLanguage($lang) {
    if (in_array($lang, AVAILABLE_LANGUAGES)) {
        $_SESSION['lang'] = $lang;
        return true;
    }
    return false;
}

// Get current language
function getCurrentLanguage() {
    return detectLanguage();
}

// Load language file
function loadLanguageFile($lang) {
    $file = __DIR__ . "/{$lang}.php";
    if (file_exists($file)) {
        return include $file;
    }
    return [];
}

// Get localized text
function __($key, $placeholders = []) {
    $lang = detectLanguage();
    $translations = loadLanguageFile($lang);

    // Fallback to English if translation not found
    if (empty($translations) && $lang !== 'en') {
        $translations = loadLanguageFile('en');
    }

    $text = $translations[$key] ?? $key;

    // Replace placeholders
    if (!empty($placeholders)) {
        foreach ($placeholders as $placeholder => $value) {
            $text = str_replace("{{$placeholder}}", $value, $text);
        }
    }

    return $text;
}

// Get all translations for a language (for admin panel)
function getAllTranslations($lang = null) {
    if ($lang === null) {
        $lang = detectLanguage();
    }
    return loadLanguageFile($lang);
}

// Save translation (for admin panel)
function saveTranslation($key, $value, $lang = null) {
    if ($lang === null) {
        $lang = detectLanguage();
    }

    $file = __DIR__ . "/{$lang}.php";
    $translations = loadLanguageFile($lang);

    $translations[$key] = $value;

    // Save to file
    $content = "<?php\nreturn " . var_export($translations, true) . ";\n";
    return file_put_contents($file, $content) !== false;
}
?>
