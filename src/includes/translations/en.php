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
            'manage_title' => 'Users List',
            'add_new' => 'Add New User',
            'no_users' => 'No users found.',
            'add_title' => 'Add User',
            'insert_success' => 'User has been successfully added.',
            'insert_failed' => 'Insert Failed',
            'user_not_found' => 'User not found.',
            'delete_success' => 'User deleted successfully.',
            'cannot_delete_self' => 'You cannot delete your own account.',
            'invalid_action' => 'Invalid Action',
            'invalid_request' => 'You cannot access this page directly.',
            'back_to_users' => 'Back to Users',
            'back_to_add_form' => 'Back to Add Form',
            'back_to_edit_form' => 'Back to Edit Form',
            'add_another' => 'Add Another User',
            'delete_confirm' => 'Delete user',
            'delete_title' => 'Delete User',
            'fields' => [
                'username' => 'Username',
                'password' => 'Password',
                'email' => 'Email',
                'full_name' => 'Full Name',
                'group_id' => 'Group',
                'trust_status' => 'Trust Status',
                'reg_status' => 'Registration Status',
                'reg_date' => 'Registration Date',
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
            'actions' => 'Actions',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'approve' => 'Approve',
        ],

        'categories' => [
            'manage_title' => 'Manage Categories',
            'add_new' => 'Add New Category',
            'add_title' => 'Add Category',
            'edit_title' => 'Edit Category',
            'no_categories' => 'No categories found.',
            'insert_success' => 'Category has been successfully added.',
            'insert_failed' => 'Failed to add category.',
            'update_success' => 'Category updated successfully.',
            'update_failed' => 'Failed to update category.',
            'delete_success' => 'Category deleted successfully.',
            'delete_title' => 'Delete Category',
            'delete_confirm' => 'Are you sure you want to delete category',
            'category_not_found' => 'Category not found.',
            'has_subcategories' => 'Cannot delete category that has subcategories. Please delete or move subcategories first.',
            'visibility_updated' => 'Category visibility updated successfully.',
            'visibility_update_failed' => 'Failed to update category visibility.',
            'invalid_action' => 'Invalid action requested.',
            'invalid_request' => 'You cannot access this page directly.',
            'unexpected_error' => 'An unexpected error occurred.',
            'back_to_categories' => 'Back to Categories',
            'add_another' => 'Add Another Category',
            'back' => 'Back',
            'submit' => 'Save Changes',
            'actions' => 'Actions',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'show' => 'Show',
            'hide' => 'Hide',
            'visible' => 'Visible',
            'hidden' => 'Hidden',
            'no_parent' => '-- No Parent (Main Category) --',
            'fields' => [
                'id' => 'ID',
                'name' => 'Category Name',
                'description' => 'Description',
                'parent' => 'Parent Category',
                'order' => 'Sort Order',
                'visibility' => 'Visibility',
                'created_at' => 'Created Date',
            ],
            'name_help' => 'Enter a unique name for this category',
            'parent_help' => 'Select a parent category to create a subcategory',
            'description_help' => 'Optional description of the category',
            'order_help' => 'Lower numbers appear first in listings',
            'visibility_help' => 'Hidden categories are not visible to customers',
        ],
    ],

];
