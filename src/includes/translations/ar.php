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
            'manage_title' => 'قائمة المستخدمين',
            'add_new' => 'إضافة مستخدم جديد',
            'no_users' => 'لم يتم العثور على مستخدمين.',
            'add_title' => 'إضافة مستخدم',
            'insert_success' => 'تمت إضافة المستخدم بنجاح.',
            'insert_failed' => 'فشلت عملية الإضافة',
            'user_not_found' => 'المستخدم غير موجود.',
            'delete_success' => 'تم حذف المستخدم بنجاح.',
            'cannot_delete_self' => 'لا يمكنك حذف حسابك الخاص.',
            'invalid_action' => 'إجراء غير صالح',
            'invalid_request' => 'لا يمكنك الوصول إلى هذه الصفحة مباشرة.',
            'back_to_users' => 'العودة إلى المستخدمين',
            'back_to_add_form' => 'العودة إلى نموذج الإضافة',
            'back_to_edit_form' => 'العودة إلى نموذج التعديل',
            'add_another' => 'إضافة مستخدم آخر',
            'delete_confirm' => 'حذف المستخدم',
            'delete_title' => 'حذف المستخدم',
            'fields' => [
                'username' => 'اسم المستخدم',
                'password' => 'كلمة المرور',
                'email' => 'البريد الإلكتروني',
                'full_name' => 'الاسم الكامل',
                'group_id' => 'المجموعة',
                'trust_status' => 'حالة الثقة',
                'reg_status' => 'حالة التسجيل',
                'reg_date' => 'تاريخ التسجيل'
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
            'actions' => 'الإجراءات',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'approve' => 'قبول'
        ],
    ],

];
