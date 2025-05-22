<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4 text-center" style="max-width: 500px;">
        <h4 class="mb-3"><?= $title ?></h4>
        <div class="alert <?= $type === 'error' ? 'alert-danger' : 'alert-success' ?>"><?= $message ?></div>

        <div class="mt-4 d-flex justify-content-center gap-3">
            <?php foreach ($actions as $action): ?>
                <a href="<?= htmlspecialchars($action['url']) ?>" class="btn btn-<?= $action['style'] ?>">
                    <?= htmlspecialchars($action['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
