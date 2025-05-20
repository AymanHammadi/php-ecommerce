<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['group_id']) && $_SESSION['group_id'] == 1;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        echo "Access denied: Admins only.";
        exit;
    }
}
