<?php
// Only start session if headers haven't been sent yet
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

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
        redirect('index.php');
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'access_denied_not_admin';
        redirect('index.php');
    }
}
