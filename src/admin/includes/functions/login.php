<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../index.php');
}

// Sanitize and validate input
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$email || !$password) {
    $_SESSION['error'] = 'invalid_email_or_password';
    redirect('../../index.php');
}

// Prepare and execute user query
$stmt = $pdo->prepare("SELECT 
                                user_id, username, password, group_id 
                             FROM 
                                users 
                             WHERE 
                                 email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'invalid_email_or_password';
    redirect('../../index.php');
}

// Check if user is admin
if ((int)$user['group_id'] !== 1) {
    $_SESSION['error'] = 'access_denied_not_admin';
    redirect('../../index.php');
}

// Set session data securely
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['group_id'] = (int)$user['group_id'];
$_SESSION['login_success'] = true;

// Redirect to dashboard
redirect('../../dashboard');
