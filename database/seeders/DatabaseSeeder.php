<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\StorePaymentMethod;
use App\Models\Theme;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\StoreProvisioner;
use App\Support\Tenancy\CurrentStore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlatformSeeder::class);

        if (! app()->isProduction() || env('SEED_DEMO')) {
            $this->demo();
        }
    }

    protected function demo(): void
    {
        $owner = User::firstOrCreate(['email' => 'merchant@demo.sd'], ['name' => 'تاجر تجريبي', 'phone' => '249912345678', 'password' => 'password']);
        if ($owner->ownedStores()->exists()) {
            return;
        }
        $store = app(StoreProvisioner::class)->provision($owner, ['store_name' => 'متجر النيل', 'slug' => 'demo', 'phone' => '249912345678'],
            Plan::where('slug', 'pro')->first(), 'monthly', Theme::first());
        $store->update(['status' => 'active', 'subscription_ends_at' => now()->addYear()]);

        app(CurrentStore::class)->runAs($store, function () {
            StorePaymentMethod::where('driver', 'mbok')->update(['is_active' => true, 'config' => ['account_number' => '1234567', 'account_name' => 'متجر النيل']]);
            StorePaymentMethod::create(['driver' => 'bank_transfer', 'label' => 'تحويل بنكي', 'config' => ['account_number' => '7654321', 'account_name' => 'متجر النيل', 'bank_name' => 'بنك فيصل الإسلامي']]);
            foreach ([['الخرطوم', 'أم درمان', null, 3500], ['الخرطوم', 'بحري', null, 3500], ['البحر الأحمر', 'بورتسودان', null, 12000], ['الجزيرة', 'ود مدني', null, 8000]] as [$s, $c, $a, $p]) {
                ShippingZone::create(['state' => $s, 'city' => $c, 'area' => $a, 'price' => $p, 'eta' => $s === 'الخرطوم' ? 'خلال 24 ساعة' : '2-4 أيام']);
            }
            $img = function (string $key) {
                $path = 'stores/'.current_store()->id.'/products/'.$key.'.jpg';
                Storage::disk('public')->put($path, file_get_contents(database_path('seeders/demo/'.$key.'.jpg')));

                return [$path];
            };
            $cats = [];
            foreach ([['fashion', 'أزياء'], ['perfume', 'عطور وبخور'], ['accessories', 'إكسسوارات'], ['electronics', 'إلكترونيات']] as $i => [$slug, $name]) {
                $cats[$slug] = Category::create(['name' => $name, 'slug' => $slug, 'sort' => $i]);
            }
            $inv = app(InventoryService::class);
            $items = [
                ['jalabiya', 'جلابية رجالي قطن مصري', 45000, 55000, 'fashion', 20, true],
                ['thobe', 'ثوب سوداني نسائي مطرز', 85000, null, 'fashion', 10, true],
                ['scarf', 'طرحة شيفون ناعمة', 18000, 22000, 'fashion', 30, false],
                ['shoes', 'حذاء رياضي خفيف', 60000, null, 'fashion', 12, true],
                ['bakhoor', 'بخور تيمان فاخر', 15000, null, 'perfume', 50, true],
                ['dilka', 'دلكة سودانية أصلية', 12000, null, 'perfume', 2, false],
                ['perfume', 'عطر خمرة الورد', 38000, 45000, 'perfume', 15, true],
                ['watch', 'ساعة يد كلاسيكية', 95000, 120000, 'accessories', 8, true],
                ['bag', 'شنطة يد جلد طبيعي', 70000, null, 'accessories', 6, true],
                ['sunglasses', 'نظارة شمسية UV400', 25000, null, 'accessories', 0, false],
                ['headphones', 'سماعة بلوتوث لاسلكية', 55000, 65000, 'electronics', 14, true],
                ['phone', 'جوال ذكي 128 جيجا', 420000, null, 'electronics', 4, false],
            ];
            foreach ($items as $n => [$key, $name, $price, $compare, $cat, $qty, $featured]) {
                $prod = Product::create(['name' => $name, 'slug' => 'product-'.($n + 1), 'price' => $price, 'compare_price' => $compare, 'cost' => round($price * 0.6),
                    'category_id' => $cats[$cat]->id, 'is_featured' => $featured, 'sales_count' => [12, 4, 9, 2, 30, 7, 15, 6, 3, 1, 11, 2][$n],
                    'images' => $img($key), 'description' => 'منتج أصلي بجودة عالية، متوفر للتوصيل لكل الولايات.']);
                $inv->set($prod, $qty);
            }
            $v = Product::create(['name' => 'تيشيرت قطني بشعار', 'slug' => 'tshirt', 'type' => 'variable', 'price' => 20000, 'category_id' => $cats['fashion']->id, 'images' => $img('tshirt')]);
            foreach (['S', 'M', 'L'] as $size) {
                $variant = $v->variants()->create(['store_id' => $v->store_id, 'options' => ['المقاس' => $size]]);
                $inv->set($v, 5, $variant->id);
            }
            Coupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10]);
        });
    }
}
