<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use App\Models\MarketplaceApp;
use App\Models\Plan;
use App\Models\ShippingCompany;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Production-safe base data: plans, themes, admin, apps, rates. */
class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email' => env('ADMIN_EMAIL', 'admin@platform.sd')], [
            'name' => 'مدير المنصة', 'phone' => env('ADMIN_PHONE', '249900000000'), 'password' => env('ADMIN_PASSWORD', 'ChangeMe!2026'),
        ])->forceFill(['is_super_admin' => true])->save();

        foreach ([
            ['slug' => 'free', 'name' => 'المجانية', 'description' => 'لتجربة البيع أونلاين', 'price_monthly' => 0, 'price_yearly' => 0, 'commission_rate' => 3, 'max_products' => 20, 'max_staff' => 1, 'max_branches' => 1, 'sort' => 1,
                'features' => ['الدفع عند الاستلام وبنكك', 'إشعارات واتساب أساسية']],
            ['slug' => 'basic', 'name' => 'الأساسية', 'description' => 'للمتاجر الناشئة', 'price_monthly' => 25000, 'price_yearly' => 250000, 'commission_rate' => 1, 'max_products' => 300, 'max_staff' => 3, 'max_branches' => 2, 'trial_days' => 14, 'sort' => 2,
                'features' => ['كوبونات خصم', 'تقارير المبيعات', 'بوليصات الشحن']],
            ['slug' => 'pro', 'name' => 'الاحترافية', 'description' => 'للمتاجر المتنامية', 'price_monthly' => 60000, 'price_yearly' => 600000, 'commission_rate' => 0, 'max_products' => null, 'max_staff' => 10, 'max_branches' => 10, 'custom_domain' => true, 'trial_days' => 14, 'sort' => 3,
                'features' => ['دومين خاص', 'السلات المتروكة', 'تعدد الفروع والمخازن', 'دعم أولوية']],
        ] as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }

        Theme::whereNotIn('slug', array_keys(ThemeCatalog::all()))->update(['is_active' => false]);
        foreach (ThemeCatalog::all() as $slug => $t) {
            Theme::updateOrCreate(['slug' => $slug], ['name' => $t['name'], 'price' => $t['price'], 'is_active' => true,
                'preview_image' => 'themes/'.$slug.'/preview.jpg', 'default_settings' => $t['settings']]);
        }

        ExchangeRate::updateOrCreate(['currency' => 'USD'], ['rate_to_sdg' => 2600]);
        ExchangeRate::updateOrCreate(['currency' => 'SAR'], ['rate_to_sdg' => 690]);

        foreach ([['مواتر الخرطوم', 'motorcycle'], ['ركشات التوصيل', 'rickshaw'], ['شحن البصات السفرية', 'bus_cargo']] as [$n, $t]) {
            ShippingCompany::firstOrCreate(['name' => $n, 'store_id' => null], ['type' => $t]);
        }

        foreach ([
            ['whatsapp-pro', 'واتساب بزنس المتقدم', 'messaging', 'رسائل قوالب معتمدة، ردود آلية، حملات للعملاء', 10000],
            ['syberpay', 'SyberPay', 'payment', 'الدفع بالبطاقات المصرفية عبر EBS', 0],
            ['abandoned-carts', 'استرجاع السلات المتروكة', 'marketing', 'تذكير آلي عبر واتساب بعد ساعة', 5000],
            ['meta-pixel', 'Facebook / TikTok Pixel', 'marketing', 'تتبع الحملات الإعلانية', 0],
            ['shipping-api', 'ربط شركات الشحن', 'shipping', 'إنشاء الشحنات آلياً لدى شركات التوصيل المتكاملة', 0],
        ] as [$slug, $name, $cat, $desc, $price]) {
            MarketplaceApp::updateOrCreate(['slug' => $slug], ['name' => $name, 'category' => $cat, 'description' => $desc, 'price_monthly' => $price]);
        }
    }
}
