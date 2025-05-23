<?php
include 'includes/functions/auth.php';
include 'config.php';
$pageTitle = 'Dashboard';

requireAdmin();

include $templates . 'header.php';
?>

<?php if (isset($_SESSION['login_success'])): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="loginToast" class="toast align-items-center text-white bg-success border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?= t('logged_success') ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>


    <div class="container min-vh-100">
        <h1>Welcome to Admin Dashboard, <?= htmlspecialchars($_SESSION['username']) ?></h1>

        <a href="logout.php">Logout</a>
        <a href="/">Home</a>
    </div>

<?php include $templates . 'footer.php'; ?>
<?php
