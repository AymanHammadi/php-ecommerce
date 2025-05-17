<?php
session_start();
include __DIR__ . '/includes/functions/lang.php';
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}


?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>" dir="<?php echo $_SESSION['lang'] === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="utf-8"/>
    <title><?= t('WELCOME'); ?></title>
</head>
<body>
<h1><?= t('WELCOME'); ?>
</h1>
<a href="#"><?= t('CART'); ?></a>
<br>
<a href="#"><?= t('CHECKOUT'); ?></a>
<a href="?lang=en">English</a> | <a href="?lang=ar">العربية</a>

</body>
