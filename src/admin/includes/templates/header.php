<?php
load_language();
$langDirection = $_SESSION['lang'] === 'en' ? 'ltr' : 'rtl';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($langCode) ?>" dir=<?php echo htmlspecialchars($langDirection) ?>>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(t('site.title') . ' — ' . getTitle()) ?></title>

    <!-- Bootstrap Style based on website direction-->
    <?php if ($langDirection === 'ltr'): ?>
        <link rel="stylesheet" href="<?php echo $css; ?>bootstrap.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo $css; ?>bootstrap.rtl.min.css">
    <?php endif; ?>

    <!--Fontawesome Icon Library-->
    <link rel="stylesheet" href="<?php echo $css; ?>all.min.css">


    <!--Custom Styles-->
    <link rel="stylesheet" href="<?php echo $css; ?>backend.css">
</head>

<body>
<header class="">
    <?php
    // Include the navbar on all pages unless the $noNavbar variable is set
    if (!isset($noNavbar)) {
        include $templates . 'navbar.php';
    }
    ?>
</header>


