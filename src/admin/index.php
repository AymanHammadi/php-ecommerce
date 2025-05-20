<?php
session_start();
include 'config.php';
include $templates . 'header.php';

?>

    <div class="bg-light d-flex align-items-center justify-content-center vh-100">

        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
            <h4 class="mb-3 text-center">Login</h4>
            <form action="includes/functions/login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>


    </div>
<?php include $templates . 'footer.php'; ?>