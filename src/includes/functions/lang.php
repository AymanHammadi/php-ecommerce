<?php
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

// Check for language switch request and update session accordingly
if (isset($_GET['lang'])) {
    $allowedLangs = ['en', 'ar',];
    $lang = preg_replace('/[^a-z]/i', '', $_GET['lang']);
    if (in_array($lang, $allowedLangs)) {
        $_SESSION['lang'] = $lang;
    }
}


// Initialize language
$langCode = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
load_language($langCode);

// Function to preserve the current URL
function current_url_with_lang($lang)
{
    $query = $_GET;
    $query['lang'] = $lang;
    return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($query);
}
