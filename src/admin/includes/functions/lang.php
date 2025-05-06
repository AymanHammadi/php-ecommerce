<?php
session_start();

$langCode = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
$translations = include __DIR__ . "/../translations/$langCode.php";

function lang($key)
{
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}
