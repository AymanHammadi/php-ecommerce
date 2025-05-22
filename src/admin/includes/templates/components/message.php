<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4 text-center" style="max-width: 500px;">
        <h4 class="mb-3"><?= $title ?></h4>
        <p class="<?= $type === 'error' ? 'text-danger' : 'text-success' ?>"><?= $message ?></p>

        <div class="mt-4 d-flex justify-content-center gap-3">
            <?php foreach ($actions as $action): ?>
                <a href="<?= htmlspecialchars($action['url']) ?>" class="btn btn-<?= $action['style'] ?>">
                    <?= htmlspecialchars($action['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
