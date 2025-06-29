<?php
global $templates;
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

    <div class="dashboard-container">
        <div class="container-fluid px-4 py-4">

            <!-- Welcome, Section -->
            <div class="welcome-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 mb-2 fw-bold">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                        <p class="mb-0 opacity-90">Here's an overview of your business performance today.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                    <span class="badge bg-white bg-opacity-20 text-secondary px-3 py-2">
                        <?= date('M j, Y') ?>
                    </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">$12,340</h3>
                            <p class="text-muted mb-2 small">Total Sales</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="stats-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">25</h3>
                            <p class="text-muted mb-2 small">Orders Today</p>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-box"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-1">148</h3>
                            <p class="text-muted mb-2 small">Products</p>
                        </div>
                    </div>
                </div>
                <a class="col-xl-3 col-md-6" href="users?page=pending">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="stats-icon bg-info bg-opacity-10 text-info">
                                <i class="fas fa-users"></i>
                            </div>
                            <?php
                            $pending_count = 0;
                            $error_message = '';

                            try {
                                // Count pending users (reg_status = 0)
                                $pending_count = aggregate_query('users', 'COUNT', '*', ['reg_status' => 0]);
                            } catch (Exception $e) {
                                // Store error message for display
                                $error_message = htmlspecialchars($e->getMessage());
                            }

                            ?>
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger text-center"><?= $error_message ?></div>
                            <?php else: ?>
                                <h3 class="fw-bold text-dark mb-1"><?= $pending_count ?></h3>
                                <p class="text-muted mb-2 small">Pending Users</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>

            <div class="row g-4">
                <!-- Recent Orders -->
                <div class="col-lg-8">
                    <div class="card content-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="section-header mb-0">Recent Orders</h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-container">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                        <tr class="table-header">
                                            <th class="border-0 py-3">Order ID</th>
                                            <th class="border-0 py-3">Customer</th>
                                            <th class="border-0 py-3">Total</th>
                                            <th class="border-0 py-3">Status</th>
                                            <th class="border-0 py-3">Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td class="border-0 py-3">
                                                <span class="fw-semibold">#12345</span>
                                            </td>
                                            <td class="border-0 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-initials bg-primary bg-opacity-10 text-primary me-3">
                                                        JD
                                                    </div>
                                                    <span>John Doe</span>
                                                </div>
                                            </td>
                                            <td class="border-0 py-3 fw-semibold">$120.50</td>
                                            <td class="border-0 py-3">
                                                <span class="status-pill bg-success text-white">Shipped</span>
                                            </td>
                                            <td class="border-0 py-3 text-muted">2025-05-25</td>
                                        </tr>
                                        <tr>
                                            <td class="border-0 py-3">
                                                <span class="fw-semibold">#12344</span>
                                            </td>
                                            <td class="border-0 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-initials bg-warning bg-opacity-10 text-warning me-3">
                                                        JS
                                                    </div>
                                                    <span>Jane Smith</span>
                                                </div>
                                            </td>
                                            <td class="border-0 py-3 fw-semibold">$80.00</td>
                                            <td class="border-0 py-3">
                                                <span class="status-pill bg-warning text-white">Pending</span>
                                            </td>
                                            <td class="border-0 py-3 text-muted">2025-05-24</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Recent Customers -->
                    <?php
                    try {
                        // Fetch the latest 5 users
                        $users = get_records(
                            table_name: 'users',
                            columns: ['username', 'email', 'registration_date'],
                            order_by: ['registration_date' => 'DESC'],
                            limit: 5
                        );
                        ?>

                        <!-- Recent Customers -->
                        <div class="card content-card mb-4">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="section-header mb-0">Recent Customers</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="list-group list-group-flush">
                                    <?php if (empty($users)): ?>
                                        <div class="list-group-item list-item text-center">
                                            <small class="text-muted">No recent customers found.</small>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($users as $index => $user): ?>
                                            <?php
                                            // Use first two letters of username for initials, or 'U' if empty
                                            $initials = !empty($user['username']) ? strtoupper(substr($user['username'], 0, 2)) : 'U';
                                            // Alternate colors: primary for even index, info for odd
                                            $bg_color = $index % 2 === 0 ? 'primary' : 'info';
                                            // Format join date (e.g., "May 20, 2025")
                                            $join_date = (new DateTime($user['registration_date']))->format('F j, Y');
                                            ?>
                                            <div class="list-group-item list-item d-flex align-items-center">
                                                <div class="user-initials bg-<?php echo $bg_color; ?> bg-opacity-10 text-<?php echo $bg_color; ?> me-3">
                                                    <?php echo htmlspecialchars($initials); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold mb-1"><?php echo htmlspecialchars($user['username']); ?></div>
                                                    <small class="text-muted d-block"><?php echo htmlspecialchars($user['email']); ?></small>
                                                    <small class="text-success">Joined: <?php echo htmlspecialchars($join_date); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        // Show error if query fails
                        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>

                    <!-- Low Stock -->
                    <div class="card content-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h6 class="section-header mb-0">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Low Stock Alert
                            </h6>
                        </div>
                        <div class="card-body pt-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item list-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">Product A</div>
                                        <small class="text-muted">SKU: PA001</small>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">2 left</span>
                                </div>
                                <div class="list-group-item list-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">Product B</div>
                                        <small class="text-muted">SKU: PB002</small>
                                    </div>
                                    <span class="badge bg-warning rounded-pill">5 left</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-5">
                <h5 class="section-header">Quick Actions</h5>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="products.php?do=Add" class="quick-action btn-outline-primary w-100">
                            <i class="fas fa-plus"></i>
                            <span>Add Product</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="orders.php" class="quick-action btn-outline-secondary w-100">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Manage Orders</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="users.php" class="quick-action btn-outline-success w-100">
                            <i class="fas fa-users-cog"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="reports.php" class="quick-action btn-outline-info w-100">
                            <i class="fas fa-chart-bar"></i>
                            <span>View Reports</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php include $templates . 'footer.php'; ?>