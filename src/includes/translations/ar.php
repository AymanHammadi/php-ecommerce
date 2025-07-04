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

        'categories' => [
            'manage_title' => 'إدارة الفئات',
            'add_new' => 'إضافة فئة جديدة',
            'add_title' => 'إضافة فئة',
            'edit_title' => 'تعديل الفئة',
            'no_categories' => 'لم يتم العثور على فئات.',
            'insert_success' => 'تمت إضافة الفئة بنجاح.',
            'insert_failed' => 'فشل في إضافة الفئة.',
            'update_success' => 'تم تحديث الفئة بنجاح.',
            'update_failed' => 'فشل في تحديث الفئة.',
            'delete_success' => 'تم حذف الفئة بنجاح.',
            'delete_title' => 'حذف الفئة',
            'delete_confirm' => 'هل أنت متأكد من حذف الفئة',
            'category_not_found' => 'الفئة غير موجودة.',
            'has_subcategories' => 'لا يمكن حذف فئة تحتوي على فئات فرعية. يرجى حذف أو نقل الفئات الفرعية أولاً.',
            'visibility_updated' => 'تم تحديث رؤية الفئة بنجاح.',
            'visibility_update_failed' => 'فشل في تحديث رؤية الفئة.',
            'invalid_action' => 'إجراء غير صالح.',
            'invalid_request' => 'لا يمكنك الوصول إلى هذه الصفحة مباشرة.',
            'unexpected_error' => 'حدث خطأ غير متوقع.',
            'back_to_categories' => 'العودة إلى الفئات',
            'add_another' => 'إضافة فئة أخرى',
            'back' => 'العودة',
            'submit' => 'حفظ التغييرات',
            'actions' => 'الإجراءات',
            'edit' => 'تعديل',
            'delete' => 'حذف',
            'show' => 'عرض',
            'hide' => 'إخفاء',
            'visible' => 'مرئية',
            'hidden' => 'مخفية',
            'no_parent' => '-- بدون أصل (فئة رئيسية) --',
            'fields' => [
                'id' => 'المعرف',
                'name' => 'اسم الفئة',
                'description' => 'الوصف',
                'parent' => 'الفئة الأصل',
                'order' => 'ترتيب العرض',
                'visibility' => 'الرؤية',
                'created_at' => 'تاريخ الإنشاء',
            ],
            'name_help' => 'أدخل اسم فريد لهذه الفئة',
            'parent_help' => 'اختر فئة أصل لإنشاء فئة فرعية',
            'description_help' => 'وصف اختياري للفئة',
            'order_help' => 'الأرقام الأقل تظهر أولاً في القوائم',
            'visibility_help' => 'الفئات المخفية غير مرئية للعملاء',
        ],
    ],

];
