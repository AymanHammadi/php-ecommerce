<?php
session_start();
include '../../config.php'; // adjust path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $pdo->prepare("SELECT user_id, username, email, password, group_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['group_id'] == 1) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['group_id'] = $user['group_id'];
            header('Location: ../../dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = "Access denied: not admin.";
        }
    } else {
        $_SESSION['error'] = "Invalid email or password.";
    }
    header('Location: ../../index.php');
    exit;
}
