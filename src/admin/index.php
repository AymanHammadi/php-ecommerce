<?php
require_once 'includes/functions/auth.php';
require_once 'config.php';

$noNavbar = true;       // Disable navbar on this page
$pageTitle = 'Login';   // Set page title for header

// Redirect logged-in admins to dashboard
if (isLoggedIn() && isAdmin()) {
    redirect('dashboard');
}

load_language();        // Load language strings

include $templates . 'header.php';


?>

<!-- Page content: centered login form -->
<div class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h4 class="mb-3 text-center"><?= htmlspecialchars(t('login.title')) ?></h4>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars(t($_SESSION['error'])) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"
                        aria-label="<?= htmlspecialchars(t('close')) ?>"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="includes/functions/login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label"><?= htmlspecialchars(t('login.email_label')) ?></label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><?= htmlspecialchars(t('login.password_label')) ?></label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100"><?= htmlspecialchars(t('login.button')) ?></button>
        </form>
    </div>
</div>

<?php include $templates . 'footer.php'; ?>  <!-- Include common footer -->
