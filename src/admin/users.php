<?php
/**
 * Admin Users Management Page
 *
 * This file handles all user management operations including:
 * - Listing all users
 * - Adding new users
 * - Editing existing users
 * - Updating user information
 * - Deleting users
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

// Route to appropriate section based on operation
switch ($do) {
    case 'Manage':
        // Fetch all users with their information
        $stmt = $pdo->query("SELECT user_id, username, email, full_name, group_id, trust_status, reg_status, registration_date FROM users ORDER BY user_id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="container py-5 min-vh-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0"><?= t('admin.users.manage_title') ?></h2>
                <a href="?do=Add" class="btn btn-success">+ <?= t('admin.users.add_new') ?></a>
            </div>

            <div class="table-responsive">
                <!-- Users table -->
                <table class="table-dashboard table table-hover table-bordered align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th class="">#</th>
                        <th><?= t('admin.users.fields.username') ?></th>
                        <th><?= t('admin.users.fields.email') ?></th>
                        <th><?= t('admin.users.fields.full_name') ?></th>
                        <th><?= t('admin.users.fields.group_id') ?></th>
                        <th><?= t('admin.users.fields.trust_status') ?></th>
                        <th><?= t('admin.users.fields.reg_status') ?></th>
                        <th><?= t('admin.users.fields.reg_date') ?></th>
                        <th class="text-end"><?= t('admin.users.actions') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
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
                                <a href="?do=Edit&id=<?= $user['user_id'] ?>"
                                   class="btn btn-outline-primary btn-sm me-1">
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
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4"><?= t('admin.users.no_users') ?></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>

        <?php
        // Include confirmation modal for delete operations
        include $components . 'confirm_modal.php';
        break;

    case 'Add':
        ?>
        <div class="container py-5 min-vh-100 d-flex justify-content-center align-items-start">
            <div class="edit-user-form card shadow-sm w-100">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4"><?= t('admin.users.add_title') ?></h4>

                    <!-- Add user form -->
                    <form action="?do=Insert" method="POST">
                        <div class="row g-3">
                            <!-- Username field -->
                            <div class="col-md-6">
                                <label for="username" class="form-label"><?= t('admin.users.fields.username') ?></label>
                                <input type="text"
                                       name="username"
                                       data-validate="username"
                                       class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                       required>
                                <div class="valid-feedback"></div>
                                <div class="invalid-feedback">
                                    <?= isset($errors['username']) ? htmlspecialchars($errors['username']) : '' ?>
                                </div>
                            </div>

                            <!-- Password field -->
                            <div class="col-md-6">
                                <label for="password" class="form-label"><?= t('admin.users.fields.password') ?></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light px-3 toggle-password"
                                          style="cursor: pointer;">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    <input type="password"
                                           name="password"
                                           id="password"
                                           data-validate="password"
                                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                           required>
                                </div>
                                <div class="valid-feedback"></div>
                                <div class="invalid-feedback">
                                    <?= isset($errors['password']) ? htmlspecialchars($errors['password']) : '' ?>
                                </div>
                            </div>

                            <!-- Email field -->
                            <div class="col-md-6">
                                <label for="email" class="form-label"><?= t('admin.users.fields.email') ?></label>
                                <input type="email" name="email" id="email"
                                       data-validate="email"
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       required>
                                <div class="valid-feedback"></div>
                                <div class="invalid-feedback">
                                    <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
                                </div>
                            </div>

                            <!-- Full Name field -->
                            <div class="col-md-6">
                                <label for="full_name"
                                       class="form-label"><?= t('admin.users.fields.full_name') ?></label>
                                <input type="text"
                                       name="full_name"
                                       data-validate="full_name"
                                       class="form-control"
                                       required>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Group/Role selection -->
                            <div class="col-md-6">
                                <label for="group_id" class="form-label"><?= t('admin.users.fields.group_id') ?></label>
                                <select name="group_id" id="group_id" class="form-select" data-validate="group_id">
                                    <option value="0"><?= t('admin.users.groups.user') ?></option>
                                    <option value="1"><?= t('admin.users.groups.admin') ?></option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Trust Status selection -->
                            <div class="col-md-6">
                                <label for="trust_status"
                                       class="form-label"><?= t('admin.users.fields.trust_status') ?></label>
                                <select name="trust_status" id="trust_status" class="form-select"
                                        data-validate="trust_status">
                                    <option value="0"><?= t('admin.users.trust.untrusted') ?></option>
                                    <option value="1"><?= t('admin.users.trust.trusted') ?></option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Registration Status selection -->
                            <div class="col-md-6">
                                <label for="reg_status"
                                       class="form-label"><?= t('admin.users.fields.reg_status') ?></label>
                                <select name="reg_status" id="reg_status" class="form-select"
                                        data-validate="reg_status">
                                    <option value="0"><?= t('admin.users.reg.pending') ?></option>
                                    <option value="1"><?= t('admin.users.reg.approved') ?></option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>
                        </div>

                        <!-- Form submission button -->
                        <div class="form-action-bar mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4"><?= t('admin.users.submit') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'Insert':
        // Process new user insertion
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Prevent direct access to this action
            $title = t('admin.users.invalid_action');
            $message = t('admin.users.invalid_request');
            $type = 'error';

            $redirect_url = 'users.php?do=Manage';
            $redirect_delay = 3;

            include 'includes/templates/components/message.php';
            break;
        }

        // Collect and sanitize form data
        $data = [
            'user_id' => isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0,
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'group_id' => (int)($_POST['group_id'] ?? 0),
            'trust_status' => (int)($_POST['trust_status'] ?? 0),
            'reg_status' => (int)($_POST['reg_status'] ?? 0),
            'password' => trim($_POST['password'] ?? '')
        ];

        // Define validation rules
        $rules = [
            'username' => ['required', 'min:4', 'max:20'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ];

        // Ensure username and email are unique
        $unique = [
            'username' => ['table' => 'users', 'id_column' => 'user_id', 'exclude_id' => $data['user_id']],
            'email' => ['table' => 'users', 'id_column' => 'user_id', 'exclude_id' => $data['user_id']],
        ];

        // Validate the data
        $errors = validate($data, $rules, $unique, $pdo);

        // Additional validation for dropdown selections
        if (!in_array($data['trust_status'], [0, 1], true)) {
            $errors['trust_status'][] = 'Invalid trust status.';
        }
        if (!in_array($data['reg_status'], [0, 1], true)) {
            $errors['reg_status'][] = 'Invalid registration status.';
        }

        // If validation errors exist, show error message
        if (!empty($errors)) {
            $title = t('admin.users.insert_failed');
            $type = 'error';
            $message = '<ol>';
            foreach ($errors as $fieldErrors) {
                foreach ((array)$fieldErrors as $err) {
                    $message .= '<li class="text-start">' . htmlspecialchars($err) . '</li>';
                }
            }
            $message .= '</ol>';

            $redirect_url = 'users.php?do=Add';
            $redirect_delay = 3;
            include 'includes/templates/components/message.php';
            break;
        }

        // Hash password for security
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert new user into database
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, group_id, trust_status, reg_status, registration_date)
                           VALUES (:username, :password, :email, :full_name, :group_id, :trust_status, :reg_status, now())");

        $stmt->execute([
            ':username' => $data['username'],
            ':password' => $hashedPassword,
            ':email' => $data['email'],
            ':full_name' => $data['full_name'],
            ':group_id' => $data['group_id'],
            ':trust_status' => $data['trust_status'],
            ':reg_status' => $data['reg_status'],
        ]);

        // Show success message
        $title = t('admin.users.add_title');
        $message = t('admin.users.insert_success');
        $type = 'success';
        $actions = [
            ['label' => t('admin.users.add_another'), 'url' => 'users.php?do=Add', 'style' => 'primary'],
            ['label' => t('admin.users.back_to_users'), 'url' => 'users.php?do=Manage', 'style' => 'secondary']
        ];


        include 'includes/templates/components/message.php';
        break;

    case 'Edit':
        // Get user ID from URL parameter
        $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Fetch user data from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Check if user exists
        if (!$user) {
            echo '<div class="alert alert-danger">' . t('admin.users.user_not_found') . '</div>';
            break;
        }
        ?>

        <div class="container py-5 min-vh-100 d-flex justify-content-center align-items-start">
            <div class="edit-user-form card shadow-sm w-100">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4"><?= t('admin.users.edit_title') ?></h4>

                    <!-- Edit user form -->
                    <form action="?do=Update" method="POST">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">

                        <div class="row g-3">
                            <!-- Username field -->
                            <div class="col-md-6">
                                <label for="username" class="form-label"><?= t('admin.users.fields.username') ?></label>
                                <input type="text"
                                       name="username"
                                       data-validate="username"
                                       class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($user['username']) ?>"
                                       required>
                                <div class="valid-feedback"></div>
                                <div class="invalid-feedback">
                                    <?= isset($errors['username']) ? htmlspecialchars($errors['username']) : '' ?>
                                </div>
                            </div>

                            <!-- Email field -->
                            <div class="col-md-6">
                                <label for="email" class="form-label"><?= t('admin.users.fields.email') ?></label>
                                <input type="email" name="email" id="email"
                                       data-validate="email"
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                                <div class="invalid-feedback">
                                    <?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?>
                                </div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Full Name field -->
                            <div class="col-md-6">
                                <label for="full_name"
                                       class="form-label"><?= t('admin.users.fields.full_name') ?></label>
                                <input type="text"
                                       name="full_name"
                                       data-validate="full_name"
                                       class="form-control"
                                       value="<?= htmlspecialchars($user['full_name']) ?>">
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Group/Role selection -->
                            <div class="col-md-6">
                                <label for="group_id" class="form-label"><?= t('admin.users.fields.group_id') ?></label>
                                <select name="group_id" id="group_id" class="form-select" data-validate="group_id">
                                    <option value="0" <?= $user['group_id'] == 0 ? 'selected' : '' ?>><?= t('admin.users.groups.user') ?></option>
                                    <option value="1" <?= $user['group_id'] == 1 ? 'selected' : '' ?>><?= t('admin.users.groups.admin') ?></option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Trust Status selection -->
                            <div class="col-md-6">
                                <label for="trust_status"
                                       class="form-label"><?= t('admin.users.fields.trust_status') ?></label>
                                <select name="trust_status" id="trust_status" class="form-select"
                                        data-validate="trust_status">
                                    <option value="0" <?= $user['trust_status'] == 0 ? 'selected' : '' ?>><?= t('admin.users.trust.untrusted') ?></option>
                                    <option value="1" <?= $user['trust_status'] == 1 ? 'selected' : '' ?>><?= t('admin.users.trust.trusted') ?></option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>

                            <!-- Registration Status selection -->
                            <div class="col-md-6">
                                <label for="reg_status"
                                       class="form-label"><?= t('admin.users.fields.reg_status') ?></label>
                                <select name="reg_status" id="reg_status" class="form-select"
                                        data-validate="reg_status">
                                    <option value="0" <?= $user['reg_status'] == 0 ? 'selected' : '' ?>><?= t('admin.users.reg.pending') ?></option>
                                    <option value="1" <?= $user['reg_status'] == 1 ? 'selected' : '' ?>><?= t('admin.users.reg.approved') ?></option>
                                </select>
                                <div class="invalid-feedback"></div>
                                <div class="valid-feedback"></div>
                            </div>
                        </div>

                        <!-- Form submission button -->
                        <div class="form-action-bar mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4"><?= t('admin.users.submit') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'Update':
        // Process user update
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Prevent direct access to this action
            $title = t('admin.users.invalid_action');
            $message = t('admin.users.invalid_request');
            $type = 'error';
            $redirect_delay = 3;
            $redirect_url = 'users.php?do=Manage';

            include 'includes/templates/components/message.php';
            break;
        }

        // Collect and sanitize form data
        $data = [
            'user_id' => isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0,
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'group_id' => (int)($_POST['group_id'] ?? 0),
            'trust_status' => (int)($_POST['trust_status'] ?? 0),
            'reg_status' => (int)($_POST['reg_status'] ?? 0),
        ];

        // Define validation rules
        $rules = [
            'username' => ['required', 'min:4', 'max:20'],
            'email' => ['required', 'email'],
        ];

        // Ensure username and email are unique (excluding current user)
        $unique = [
            'username' => ['table' => 'users', 'id_column' => 'user_id', 'exclude_id' => $data['user_id']],
            'email' => ['table' => 'users', 'id_column' => 'user_id', 'exclude_id' => $data['user_id']],
        ];

        // Validate the data
        $errors = validate($data, $rules, $unique, $pdo);

        // Additional validation for dropdown selections
        if (!in_array($data['trust_status'], [0, 1], true)) {
            $errors['trust_status'][] = 'Invalid trust status.';
        }
        if (!in_array($data['reg_status'], [0, 1], true)) {
            $errors['reg_status'][] = 'Invalid registration status.';
        }

        // If validation errors exist, show error message
        if (!empty($errors)) {
            $title = t('admin.users.update_failed');
            $type = 'error';

            $message = '<ol>';
            foreach ($errors as $fieldErrors) {
                foreach ((array)$fieldErrors as $err) {
                    $message .= '<li class="text-start">' . htmlspecialchars($err) . '</li>';
                }
            }
            $message .= '</ol>';

            // Redirect back to edit form after 3 seconds
            $redirect_url = 'users.php?do=Edit&id=' . $data['user_id'];
            $redirect_delay = 3;

            include 'includes/templates/components/message.php';
            break;
        }

        // Update user in database
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, email = ?, full_name = ?, group_id = ?, trust_status = ?, reg_status = ? 
            WHERE user_id = ?
        ");
        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            $data['full_name'],
            $data['group_id'],
            $data['trust_status'],
            $data['reg_status'],
            $data['user_id'],
        ]);

        // Show success or error message
        $title = $success ? t('admin.users.update_success') : t('admin.users.update_failed');
        $type = $success ? 'success' : 'error';

        // Redirect user after 2 seconds
        $redirect_url = $success
            ? 'users.php?do=Manage'
            : 'users.php?do=Edit&id=' . $data['user_id'];

        $redirect_delay = 2;

        include 'includes/templates/components/message.php';
        break;

    case 'Delete':
        delete_entity([
            'table' => 'users',
            'id_column' => 'user_id',
            'id' => $_GET['id'] ?? 0,
            'redirect_url' => 'users.php?do=Manage',
            'redirect_delay' => 3,
            'not_found_title' => 'admin.users.user_not_found',
            'success_title' => 'admin.users.delete_title',
            'success_message' => 'admin.users.delete_success',
            'prevent_self_delete' => $_SESSION['user_id'] ?? null,
        ]);
        break;

    default:
        // Handle invalid actions
        echo "<div class='d-flex flex-column justify-content-center align-content-center container min-vh-100 '>
                <div class='alert alert-danger text-center'>" . t('admin.users.invalid_action') . "</div>
              </div>";
        break;
}

// Include footer template
include $templates . 'footer.php';
