<?php
$type = $type ?? 'info'; // 'success', 'error', 'info'
$title = $title ?? '';
$message = $message ?? '';
$redirect_url = $redirect_url ?? null;
$redirect_delay = isset($redirect_delay) ? (int)$redirect_delay : 2;

// Map type to bootstrap alert and fontawesome icon
$alert_class = match ($type) {
    'success' => 'alert-success',
    'error' => 'alert-danger',
    'info' => 'alert-info',
    default => 'alert-secondary',
};

$icon_class = match ($type) {
    'success' => 'fa-circle-check',
    'error' => 'fa-triangle-exclamation',
    'info' => 'fa-info-circle',
    default => 'fa-bell',
};

$escaped_title = htmlspecialchars($title);
$escaped_message = is_string($message) ? $message : '';
$escaped_url = $redirect_url ? htmlspecialchars($redirect_url) : '';
?>

<?php if ($redirect_url): ?>
    <meta http-equiv="refresh" content="<?= $redirect_delay ?>;url=<?= $escaped_url ?>">
    <script>
        setTimeout(() => {
            window.location.href = "<?= addslashes($redirect_url) ?>";
        }, <?= $redirect_delay * 1000 ?>);
    </script>
<?php endif; ?>

<div class="d-flex justify-content-center align-items-center min-vh-100 p-3">
    <div class="alert <?= $alert_class ?> text-center shadow p-4 rounded-4 w-100" style="max-width: 600px;">
        <i class="fa <?= $icon_class ?> fs-1 mb-2"></i>
        <?php if ($title): ?>
            <h4 class="alert-heading"><?= $escaped_title ?></h4>
        <?php endif; ?>

        <div class="mt-3 fs-5">
            <?= $escaped_message ?>
        </div>

        <?php if ($redirect_url): ?>
            <p class="text-muted mt-4 small fst-italic">
                <?= sprintf('You will be redirected in %d seconds...', $redirect_delay) ?>
            </p>
        <?php endif; ?>
    </div>
</div>
