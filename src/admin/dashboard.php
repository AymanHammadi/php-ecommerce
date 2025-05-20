<?php
include 'includes/functions/auth.php';
include 'config.php';
requireAdmin();
include 'includes/templates/header.php';
?>

    <h1>Welcome to Admin Dashboard, <?= htmlspecialchars($_SESSION['username']) ?></h1>

    <a href="logout.php">Logout</a>

<?php include 'includes/templates/footer.php'; ?>
<?php
