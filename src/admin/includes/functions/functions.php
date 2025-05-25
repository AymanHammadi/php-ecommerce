<?php
function getTitle()
{
    global $pageTitle;
    if (isset($pageTitle)) {
        return htmlspecialchars($pageTitle);
    } else {
        return 'PHP E-commerce';
    }
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function record_exists(string $table, string $id_column, int $id): bool
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT 1 FROM `$table` WHERE `$id_column` = ? LIMIT 1");
    $stmt->execute([$id]);

    return (bool)$stmt->fetchColumn();
}


function delete_entity(array $options): void
{
    global $pdo, $components;

    $table = $options['table'];
    $id_column = $options['id_column'];
    $id = (int)$options['id'];
    $redirect_url = $options['redirect_url'] ?? 'index.php';
    $redirect_delay = $options['redirect_delay'] ?? 3;
    $title = $options['not_found_title'] ?? 'Item not found';
    $success_title = $options['success_title'] ?? 'Deleted';
    $success_message = $options['success_message'] ?? 'Item was deleted successfully';
    $self_protect_id = $options['prevent_self_delete'] ?? null;

    if ($self_protect_id && $id === (int)$self_protect_id) {
        echo '<div class="alert alert-warning text-center mt-5">' . t('admin.users.cannot_delete_self') . '</div>';
        return;
    }

    if (!record_exists($table, $id_column, $id)) {
        $type = 'error';
        $title = t($title);
        include $components . 'message.php';
        return;
    }

    $delete_stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$id_column` = ?");
    $delete_stmt->execute([$id]);

    $type = 'success';
    $title = t($success_title);
    $message = t($success_message);
    include $components . 'message.php';
}
