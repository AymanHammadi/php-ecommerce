<?php
echo "This text is from src/includes/functions/lang file \n";

// Only start session if headers haven't been sent yet
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Global translations array
$translations = [];

// Load translation file based on session or fallback to 'en'
function load_language($lang = null)
{
    global $translations;

    // If no language specified, try to get from session
    if ($lang === null) {
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
    }

    $file = __DIR__ . "/../translations/$lang.php";
    if (file_exists($file)) {
        $translations = include $file;
    } else {
        $translations = include __DIR__ . '/../translations/en.php';
    }

    // Store in session if needed later
    $_SESSION['translations'] = $translations;
    $_SESSION['lang'] = $lang;
}

// Translate a given key
function t($key)
{
    global $translations;

    // Simple key
    if (isset($translations[$key])) {
        return $translations[$key];
    }

    // Dot notation support
    $keys = explode('.', $key);
    $value = $translations;
    foreach ($keys as $k) {
        if (!is_array($value) || !isset($value[$k])) {
            return $key; // fallback to key if not found
        }
        $value = $value[$k];
    }
    return $value;
}

// Initialize language - ensure we use the session language if available
$langCode = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
load_language($langCode);
