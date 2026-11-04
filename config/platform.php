<?php

use App\Services\Payments\Drivers\BankTransfer;
use App\Services\Payments\Drivers\CashOnDelivery;
use App\Services\Payments\Drivers\Mbok;
use App\Services\Payments\Drivers\SyberPay;

return [
    'name' => env('PLATFORM_NAME', 'مومن'),

    // Main platform domain, e.g. "platform.sd". Stores live on {slug}.platform.sd
    'domain' => env('PLATFORM_DOMAIN', 'localhost'),
    'scheme' => env('PLATFORM_SCHEME', 'https'),

    // "host"  => store.platform.sd (needs wildcard DNS + wildcard subdomain on DirectAdmin)
    // "path"  => platform.sd/s/store  (works on any shared hosting immediately)
    'storefront_mode' => env('STOREFRONT_MODE', 'host'),

    // IP / CNAME merchants point their custom domain to
    'custom_domain_target' => env('CUSTOM_DOMAIN_TARGET', 'stores.platform.sd'),

    // Platform's own receiving accounts (for subscription fees)
    'billing' => [
        'bank_name' => env('BILLING_BANK_NAME', 'بنك الخرطوم'),
        'account_name' => env('BILLING_ACCOUNT_NAME', 'شركة المنصة'),
        'account_number' => env('BILLING_ACCOUNT_NUMBER', '0000000'),
        'whatsapp' => env('BILLING_WHATSAPP', '249900000000'),
    ],

    'roles' => [
        'owner' => ['*'],
        'manager' => ['products', 'orders', 'customers', 'coupons', 'shipping', 'reports', 'payments', 'theme'],
        'staff' => ['products', 'orders'],
        'accountant' => ['orders', 'payments', 'reports', 'wallet'],
        'shipping' => ['orders', 'shipping'],
    ],

    'role_labels' => [
        'owner' => 'المالك', 'manager' => 'مدير', 'staff' => 'موظف', 'accountant' => 'محاسب', 'shipping' => 'مسؤول شحن',
    ],

    'payment_drivers' => [
        'cod' => CashOnDelivery::class,
        'bank_transfer' => BankTransfer::class,
        'mbok' => Mbok::class,
        'syberpay' => SyberPay::class,
    ],

    // Default page-builder settings; themes and merchants override
    'theme_defaults' => [
        'colors' => ['primary' => '#0f766e', 'secondary' => '#f59e0b', 'background' => '#ffffff', 'text' => '#111827'],
        'font' => 'Tajawal',
        'announcement' => null,
        'sections' => [
            ['type' => 'hero', 'title' => 'أهلاً بك في متجرنا', 'subtitle' => 'أفضل المنتجات بأفضل الأسعار', 'image' => null],
            ['type' => 'categories'],
            ['type' => 'featured_products', 'title' => 'منتجات مميزة'],
            ['type' => 'latest_products', 'title' => 'وصل حديثاً'],
        ],
        'footer_text' => null,
    ],

    // Sudanese states and main cities (used for shipping zones & checkout)
    'states' => [
        'الخرطوم' => ['الخرطوم', 'أم درمان', 'بحري', 'شرق النيل', 'جبل أولياء'],
        'الجزيرة' => ['ود مدني', 'الحصاحيصا', 'المناقل', 'رفاعة'],
        'البحر الأحمر' => ['بورتسودان', 'سواكن', 'طوكر'],
        'نهر النيل' => ['الدامر', 'عطبرة', 'شندي', 'بربر'],
        'الشمالية' => ['دنقلا', 'مروي', 'كريمة', 'وادي حلفا'],
        'كسلا' => ['كسلا', 'حلفا الجديدة'],
        'القضارف' => ['القضارف', 'الفاو'],
        'سنار' => ['سنجة', 'سنار', 'الدندر'],
        'النيل الأبيض' => ['ربك', 'كوستي', 'الدويم'],
        'النيل الأزرق' => ['الدمازين', 'الروصيرص'],
        'شمال كردفان' => ['الأبيض', 'أم روابة', 'بارا'],
        'جنوب كردفان' => ['كادقلي', 'الدلنج'],
        'غرب كردفان' => ['الفولة', 'النهود', 'بابنوسة'],
        'شمال دارفور' => ['الفاشر', 'مليط', 'كتم'],
        'جنوب دارفور' => ['نيالا', 'الضعين'],
        'غرب دارفور' => ['الجنينة'],
        'وسط دارفور' => ['زالنجي'],
        'شرق دارفور' => ['الضعين'],
    ],
];
