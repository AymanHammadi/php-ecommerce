<?php
return [

    // ===== Shared Words =====
    'site' => [
        'title' => 'متجر PHP الإلكتروني',
    ],
    'close' => 'إغلاق',
    'invalid_email_or_password' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
    'access_denied_not_admin' => 'تم رفض الوصول: للمسؤولين فقط.',
    'logged_success' => 'تم تسجيل الدخول بنجاح',

    'login' => [
        'title' => 'تسجيل الدخول',
        'email_label' => 'عنوان البريد الإلكتروني',
        'password_label' => 'كلمة المرور',
        'button' => 'دخول',
    ],

    // ===== Public (Customer-facing) =====
    'public' => [

    ],

    // ===== Admin Panel =====
    'admin' => [

        'nav' => [
            'home' => 'الصفحة الرئيسية',
            'dashboard' => 'لوحة التحكم',
            'orders' => 'الطلبات',
            'products' => 'المنتجات',
            'categories' => 'الفئات',
            'users' => 'المستخدمون',
            'edit_profile' => 'تعديل الحساب',
            'settings' => 'الإعدادات',
            'logout' => 'تسجيل الخروج',
        ],
        'users' => [
            'edit_title' => 'تعديل المستخدم',
            'update_success' => 'تم تحديث المستخدم بنجاح.',
            'update_failed' => 'فشل في تحديث المستخدم.',
            'fields' => [
                'username' => 'اسم المستخدم',
                'email' => 'البريد الإلكتروني',
                'full_name' => 'الاسم الكامل',
                'group_id' => 'المجموعة',
                'trust_status' => 'حالة الثقة',
                'reg_status' => 'حالة التسجيل',
            ],
            'groups' => [
                'user' => 'مستخدم',
                'admin' => 'مدير',
            ],
            'trust' => [
                'trusted' => 'موثوق',
                'untrusted' => 'غير موثوق',
            ],
            'reg' => [
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
            ],
            'submit' => 'حفظ التغييرات',
        ],
    ],

];
