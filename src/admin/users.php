<?php
require_once 'config.php';
require_once 'includes/functions/auth.php';
requireAdmin();

$pageTitle = 'Users';

include $templates . 'header.php';


$pageTitle = 'Manage Users';

// Get the operation (default to 'Manage')
$do = isset($_GET['do']) ? $_GET['do'] : 'Manage';

// Route to appropriate section
switch ($do) {
    case 'Manage':
        echo "<h1>Manage Users Page</h1>";
        // Here you would list all users from the DB
        break;

    case 'Add':
        echo "<h1>Add New User Page</h1>";
        // Show a form to add a new user
        break;

    case 'Insert':
        echo "<h1>Insert User Logic</h1>";
        // Handle the POST request from the Add form
        break;

    case 'Edit':
        // Validate and fetch user data
        $userId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            echo '<div class="alert alert-danger">User not found.</div>';
            break;
        }

        // Edit Form
        ?>
        <div class="container py-5">
            <div class="card shadow-sm mx-auto" style="max-width: 600px;">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Edit User</h4>
                    <form action="?do=Update" method="POST">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control"
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" name="full_name" id="full_name" class="form-control"
                                   value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select name="group_id" id="group_id" class="form-select">
                                <option value="0" <?= $user['group_id'] == 0 ? 'selected' : '' ?>>User</option>
                                <option value="1" <?= $user['group_id'] == 1 ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="trust_status" class="form-label">Trust Status</label>
                            <select name="trust_status" id="trust_status" class="form-select">
                                <option value="0" <?= $user['trust_status'] == 0 ? 'selected' : '' ?>>Untrusted</option>
                                <option value="1" <?= $user['trust_status'] == 1 ? 'selected' : '' ?>>Trusted</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reg_status" class="form-label">Registration Status</label>
                            <select name="reg_status" id="reg_status" class="form-select">
                                <option value="0" <?= $user['reg_status'] == 0 ? 'selected' : '' ?>>Pending</option>
                                <option value="1" <?= $user['reg_status'] == 1 ? 'selected' : '' ?>>Approved</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        break;

    case 'Update':
        echo "<h1>Update User Logic</h1>";
        // Handle the POST request from Edit form
        break;

    case 'Delete':
        echo "<h1>Delete User Logic</h1>";
        // Handle user deletion
        break;

    default:
        echo "<h1>Invalid Action</h1>";
        break;
}

include $templates . 'footer.php';
