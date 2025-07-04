<?php
global $templates, $pdo, $components;

/**
 * Admin Users Management Page
 *
 * This file handles all user management operations including:
 * - Listing all users
 * - Adding new users
 * - Editing existing users
 * - Updating user information
 * - Deleting users
 * - Approving users
 */

// Include required files
require_once 'config.php';
require_once 'includes/functions/auth.php';
requireAdmin(); // only admins can access this page

// Set page title for header
$pageTitle = t('admin.users.manage_title');

// Include validation functions
require_once __DIR__ . '/includes/functions/validation.php';

// Include header template
include $templates . 'header.php';

// Get the operation (default to 'Manage')
$do = $_GET['do'] ?? 'Manage';

// Route to appropriate handler
try {
    switch ($do) {
        case 'Manage':
            handleManageUsers();
            break;
        case 'Add':
            handleAddUser();
            break;
        case 'Insert':
            handleInsertUser();
            break;
        case 'Edit':
            handleEditUser();
            break;
        case 'Update':
            handleUpdateUser();
            break;
        case 'Delete':
            handleDeleteUser();
            break;
        case 'Approve':
            handleApproveUser();
            break;
        default:
            handleInvalidAction();
            break;
    }
} catch (Exception $e) {
    error_log("User management error: " . $e->getMessage());
    show_error_message(t('admin.users.unexpected_error'), 'users?do=Manage');
}

// Include footer template
include $templates . 'footer.php';

// =============================================================================
// HANDLER FUNCTIONS
// =============================================================================

/**
 * Handle the Manage Users page - displays list of users
 */
function handleManageUsers(): void
{
    global $pdo, $components;

    $users = getUsersList();
    renderUsersTable($users);
    include $components . 'confirm_modal.php';
}

/**
 * Handle the Add User page - displays add user form
 */
function handleAddUser(): void
{
    renderUserForm('add');
}

/**
 * Handle Insert User action - processes new user creation
 */
function handleInsertUser(): void
{
    if (!is_post_request()) {
        show_error_message(t('admin.users.invalid_request'), 'users?do=Manage');
        return;
    }

    $userData = collectUserFormData();
    $errors = validateUserData($userData, 'insert');

    if (!empty($errors)) {
        show_validation_errors($errors, 'users.php?do=Add');
        return;
    }

    if (insertUser($userData)) {
        show_success_message(
            t('admin.users.add_title'),
            t('admin.users.insert_success'),
            [
                ['label' => t('admin.users.add_another'), 'url' => 'users.php?do=Add', 'style' => 'primary'],
                ['label' => t('admin.users.back_to_users'), 'url' => 'users?do=Manage', 'style' => 'secondary']
            ]
        );
    } else {
        show_error_message(t('admin.users.insert_failed'), 'users.php?do=Add');
    }
}

/**
 * Handle Edit User page - displays edit user form
 */
function handleEditUser(): void
{
    $userId = get_id_from_request();
    $user = getUserById($userId);

    if (!$user) {
        show_error_message(t('admin.users.user_not_found'), 'users?do=Manage');
        return;
    }

    renderUserForm('edit', $user);
}

/**
 * Handle Update User action - processes user updates
 */
function handleUpdateUser(): void
{
    if (!is_post_request()) {
        show_error_message(t('admin.users.invalid_request'), 'users?do=Manage');
        return;
    }

    $userData = collectUserFormData();
    $errors = validateUserData($userData, 'update');

    if (!empty($errors)) {
        show_validation_errors($errors, 'users.php?do=Edit&id=' . $userData['user_id']);
        return;
    }

    if (updateUser($userData)) {
        show_success_message(
            t('admin.users.update_success'),
            '',
            [],
            'users?do=Manage',
            2
        );
    } else {
        show_error_message(t('admin.users.update_failed'), 'users.php?do=Edit&id=' . $userData['user_id']);
    }
}

/**
 * Handle Delete User action
 */
function handleDeleteUser(): void
{
    delete_entity([
        'table' => 'users',
        'id_column' => 'user_id',
        'id' => $_GET['id'] ?? 0,
        'redirect_url' => 'users?do=Manage',
        'redirect_delay' => 3,
        'not_found_title' => 'admin.users.user_not_found',
        'success_title' => 'admin.users.delete_title',
        'success_message' => 'admin.users.delete_success',
        'prevent_self_delete' => $_SESSION['user_id'] ?? null,
    ]);
}

/**
 * Handle Approve User action
 */
function handleApproveUser(): void
{
    $userId = get_id_from_request();

    if (approveUser($userId)) {
        show_success_message(
            t('admin.users.update_success'),
            '',
            [],
            'users?page=pending',
            2
        );
    } else {
        show_error_message(t('admin.users.update_failed'), 'users?page=pending');
    }
}

/**
 * Handle invalid actions
 */
function handleInvalidAction(): void
{
    handle_invalid_action(t('admin.users.invalid_action'));
}

// =============================================================================
// DATA ACCESS FUNCTIONS
// =============================================================================

/**
 * Get list of users with optional filtering
 */
function getUsersList(): array
{
    global $pdo;

    $query = '';
    if (isset($_GET['page']) && $_GET['page'] === 'pending') {
        $query = 'WHERE reg_status = 0';
    }

    $stmt = $pdo->query("SELECT user_id, username, email, full_name, group_id, trust_status, reg_status, registration_date FROM users $query ORDER BY user_id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user by ID
 */
function getUserById(int $userId): ?array
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

/**
 * Insert new user into database
 */
function insertUser(array $userData): bool
{
    global $pdo;

    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, group_id, trust_status, reg_status, registration_date)
                           VALUES (:username, :password, :email, :full_name, :group_id, :trust_status, :reg_status, now())");

    return $stmt->execute([
        ':username' => $userData['username'],
        ':password' => $hashedPassword,
        ':email' => $userData['email'],
        ':full_name' => $userData['full_name'],
        ':group_id' => $userData['group_id'],
        ':trust_status' => $userData['trust_status'],
        ':reg_status' => $userData['reg_status'],
    ]);
}

/**
 * Update existing user
 */
function updateUser(array $userData): bool
{
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE users 
        SET username = ?, email = ?, full_name = ?, group_id = ?, trust_status = ?, reg_status = ? 
        WHERE user_id = ?
    ");

    return $stmt->execute([
        $userData['username'],
        $userData['email'],
        $userData['full_name'],
        $userData['group_id'],
        $userData['trust_status'],
        $userData['reg_status'],
        $userData['user_id'],
    ]);
}

/**
 * Approve user by setting reg_status to 1
 */
function approveUser(int $userId): bool
{
    global $pdo;

    $stmt = $pdo->prepare("UPDATE users SET reg_status = 1 WHERE user_id = ?");
    return $stmt->execute([$userId]);
}

// =============================================================================
// FORM HANDLING FUNCTIONS
// =============================================================================

/**
 * Collect and sanitize user form data
 */
function collectUserFormData(): array
{
    return [
        'user_id' => isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0,
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'group_id' => (int)($_POST['group_id'] ?? 0),
        'trust_status' => (int)($_POST['trust_status'] ?? 0),
        'reg_status' => (int)($_POST['reg_status'] ?? 0),
        'password' => trim($_POST['password'] ?? '')
    ];
}

/**
 * Validate user data based on operation type
 */
function validateUserData(array $data, string $operation): array
{
    global $pdo;

    $rules = [
        'username' => ['required', 'min:4', 'max:20'],
        'email' => ['required', 'email'],
    ];

    // Add password validation for insert operations
    if ($operation === 'insert') {
        $rules['password'] = ['required', 'min:6'];
    }

    // Ensure username and email are unique
    $unique = [
        'username' => ['table' => 'users', 'id_column' => 'user_id', 'exclude_id' => $data['user_id']],
        'email' => ['table' => 'users', 'id_column' => 'user_id', 'exclude_id' => $data['user_id']],
    ];

    $errors = validate($data, $rules, $unique, $pdo);

    // Additional validation for dropdown selections
    if (!in_array($data['trust_status'], [0, 1], true)) {
        $errors['trust_status'][] = 'Invalid trust status.';
    }
    if (!in_array($data['reg_status'], [0, 1], true)) {
        $errors['reg_status'][] = 'Invalid registration status.';
    }

    return $errors;
}

// =============================================================================
// RENDERING FUNCTIONS
// =============================================================================

/**
 * Render the users table
 */
function renderUsersTable(array $users): void
{
    ?>
    <div class="container py-5 min-vh-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><?= t('admin.users.manage_title') ?></h2>
            <a href="?do=Add" class="btn btn-success">+ <?= t('admin.users.add_new') ?></a>
        </div>
        <div class="card content-card">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="section-header mb-0"><?= t('admin.users.manage_title') ?></h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <?php renderUsersTableHeader(); ?>
                            </thead>
                            <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <?= t('admin.users.no_users') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php renderUserTableRow($user); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render table header
 */
function renderUsersTableHeader(): void
{
    ?>
    <tr class="table-header">
        <th>User ID</th>
        <th><?= t('admin.users.fields.username') ?></th>
        <th><?= t('admin.users.fields.email') ?></th>
        <th><?= t('admin.users.fields.full_name') ?></th>
        <th><?= t('admin.users.fields.group_id') ?></th>
        <th><?= t('admin.users.fields.trust_status') ?></th>
        <th><?= t('admin.users.fields.reg_status') ?></th>
        <th><?= t('admin.users.fields.reg_date') ?></th>
        <th class="text-end"><?= t('admin.users.actions') ?></th>
    </tr>
    <?php
}

/**
 * Render a single user table row
 */
function renderUserTableRow(array $user): void
{
    ?>
    <tr>
        <td><?= htmlspecialchars($user['user_id']) ?></td>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= htmlspecialchars($user['full_name']) ?></td>
        <td><?= $user['group_id'] == 1 ? t('admin.users.groups.admin') : t('admin.users.groups.user') ?></td>
        <td><?= $user['trust_status'] ? t('admin.users.trust.trusted') : t('admin.users.trust.untrusted') ?></td>
        <td><?= $user['reg_status'] ? t('admin.users.reg.approved') : t('admin.users.reg.pending') ?></td>
        <td><?= $user['registration_date'] ?></td>
        <td class="text-end">
            <?php renderUserActionButtons($user); ?>
        </td>
    </tr>
    <?php
}

/**
 * Render action buttons for a user row
 */
function renderUserActionButtons(array $user): void
{
    ?>
    <a href="?do=Edit&id=<?= $user['user_id'] ?>" class="btn btn-outline-primary btn-sm me-1">
        <i class="fas fa-edit me-1"></i><?= t('admin.users.edit') ?>
    </a>
    <a href="users.php?do=Delete&id=<?= $user['user_id'] ?>"
       data-confirm
       data-url="users.php?do=Delete&id=<?= $user['user_id'] ?>"
       data-message="<?= t('admin.users.delete_confirm') ?> '<?= htmlspecialchars($user['full_name']) ?>'?"
       data-btn-text="<?= t('admin.users.delete') ?>"
       data-btn-class="btn-danger"
       data-title="<?= t('admin.users.delete_title') ?>"
       data-precheck="preventSelfDelete"
       data-user-id="<?= $user['user_id'] ?>"
       data-current-id="<?= $_SESSION['user_id'] ?>"
       class="btn btn-outline-danger btn-sm">
        <i class="fas fa-trash-alt me-1"></i><?= t('admin.users.delete') ?>
    </a>
    <?php if (!$user['reg_status']): ?>
    <a href="users?do=Approve&id=<?= $user['user_id'] ?>" class="btn btn-outline-warning btn-sm me-1 approve-btn">
        <?= t('admin.users.approve') ?>
        <i class="fa-solid fa-question"></i>
    </a>
<?php endif; ?>
    <?php
}

/**
 * Render user form (add or edit)
 */
function renderUserForm(string $mode, array $user = []): void
{
    $isEdit = $mode === 'edit';
    $formAction = $isEdit ? '?do=Update' : '?do=Insert';
    $formTitle = $isEdit ? t('admin.users.edit_title') : t('admin.users.add_title');
    ?>
    <div class="container py-5 min-vh-100 d-flex justify-content-center align-items-start">
        <div class="edit-user-form card shadow-sm w-100">
            <div class="card-body">
                <h4 class="card-title text-center mb-4"><?= $formTitle ?></h4>
                <form action="<?= $formAction ?>" method="POST">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <?php renderFormFields($mode, $user); ?>
                    </div>

                    <div class="form-action-bar mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-4"><?= t('admin.users.submit') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render form fields based on mode
 */
function renderFormFields(string $mode, array $user = []): void
{
    $isEdit = $mode === 'edit';
    ?>
    <!-- Username field -->
    <div class="col-md-6">
        <label for="username" class="form-label"><?= t('admin.users.fields.username') ?></label>
        <input type="text"
               name="username"
               data-validate="username"
               class="form-control"
               value="<?= $isEdit ? htmlspecialchars($user['username']) : '' ?>"
               required>
        <div class="valid-feedback"></div>
        <div class="invalid-feedback"></div>
    </div>

    <?php if (!$isEdit): ?>
    <!-- Password field (only for add mode) -->
    <div class="col-md-6">
        <label for="password" class="form-label"><?= t('admin.users.fields.password') ?></label>
        <div class="input-group">
                <span class="input-group-text bg-light px-3 toggle-password" style="cursor: pointer;">
                    <i class="fas fa-eye"></i>
                </span>
            <input type="password"
                   name="password"
                   id="password"
                   data-validate="password"
                   class="form-control"
                   required>
        </div>
        <div class="valid-feedback"></div>
        <div class="invalid-feedback"></div>
    </div>
<?php endif; ?>

    <!-- Email field -->
    <div class="col-md-6">
        <label for="email" class="form-label"><?= t('admin.users.fields.email') ?></label>
        <input type="email"
               name="email"
               id="email"
               data-validate="email"
               class="form-control"
               value="<?= $isEdit ? htmlspecialchars($user['email']) : '' ?>"
               required>
        <div class="valid-feedback"></div>
        <div class="invalid-feedback"></div>
    </div>

    <!-- Full Name field -->
    <div class="col-md-6">
        <label for="full_name" class="form-label"><?= t('admin.users.fields.full_name') ?></label>
        <input type="text"
               name="full_name"
               data-validate="full_name"
               class="form-control"
               value="<?= $isEdit ? htmlspecialchars($user['full_name']) : '' ?>"
               required>
        <div class="invalid-feedback"></div>
        <div class="valid-feedback"></div>
    </div>

    <?php renderSelectFields($isEdit, $user); ?>
    <?php
}

/**
 * Render select fields (dropdowns)
 */
function renderSelectFields(bool $isEdit, array $user = []): void
{
    $selectFields = [
        'group_id' => [
            'label' => 'admin.users.fields.group_id',
            'options' => [
                0 => 'admin.users.groups.user',
                1 => 'admin.users.groups.admin'
            ]
        ],
        'trust_status' => [
            'label' => 'admin.users.fields.trust_status',
            'options' => [
                0 => 'admin.users.trust.untrusted',
                1 => 'admin.users.trust.trusted'
            ]
        ],
        'reg_status' => [
            'label' => 'admin.users.fields.reg_status',
            'options' => [
                0 => 'admin.users.reg.pending',
                1 => 'admin.users.reg.approved'
            ]
        ]
    ];

    foreach ($selectFields as $fieldName => $fieldConfig) {
        $selectedValue = $isEdit ? $user[$fieldName] : 0;
        ?>
        <div class="col-md-6">
            <label for="<?= $fieldName ?>" class="form-label"><?= t($fieldConfig['label']) ?></label>
            <select name="<?= $fieldName ?>" id="<?= $fieldName ?>" class="form-select" data-validate="<?= $fieldName ?>">
                <?php foreach ($fieldConfig['options'] as $value => $labelKey): ?>
                    <option value="<?= $value ?>" <?= $selectedValue == $value ? 'selected' : '' ?>>
                        <?= t($labelKey) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"></div>
            <div class="valid-feedback"></div>
        </div>
        <?php
    }
}

