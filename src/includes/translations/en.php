<?php
return [

    // ===== Shared Words =====
    'site' => [
        'title' => 'PHP E-commerce',
    ],
    'close' => 'Close',
    'invalid_email_or_password' => 'Invalid email or password.',
    'access_denied_not_admin' => 'Access denied: Admins only.',
    'logged_success' => 'Logged in successfully!',

    'login' => [
        'title' => 'Login',
        'email_label' => 'Email address',
        'password_label' => 'Password',
        'button' => 'Login',
    ],


    // ===== Public (Customer-facing) =====
    'public' => [
        // Add customer-specific labels later
    ],

    // ===== Admin Panel =====
    'admin' => [

        'nav' => [
            'home' => 'Home',
            'dashboard' => 'Dashboard',
            'orders' => 'Orders',
            'products' => 'Products',
            'categories' => 'Categories',
            'users' => 'Users',
            'edit_profile' => 'Edit profile',
            'settings' => 'Settings',
            'logout' => 'Log Out',
        ],
        'users' => [
            'edit_title' => 'Edit User',
            'update_success' => 'User updated successfully.',
            'update_failed' => 'Failed to update user.',
            'fields' => [
                'username' => 'Username',
                'password' => 'Password',
                'email' => 'Email',
                'full_name' => 'Full Name',
                'group_id' => 'Group',
                'trust_status' => 'Trust Status',
                'reg_status' => 'Registration Status',
            ],
            'groups' => [
                'user' => 'User',
                'admin' => 'Admin',
            ],
            'trust' => [
                'trusted' => 'Trusted',
                'untrusted' => 'Untrusted',
            ],
            'reg' => [
                'pending' => 'Pending',
                'approved' => 'Approved',
            ],
            'submit' => 'Save Changes',
        ],
    ],

];
