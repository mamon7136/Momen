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
            $cat = Category::create(['name' => 'ملابس', 'slug' => 'clothes']);
            $cat2 = Category::create(['name' => 'عطور وبخور', 'slug' => 'perfumes']);
            $inv = app(InventoryService::class);
            foreach ([['جلابية رجالي قطن', 45000, $cat, 20, true], ['ثوب سوداني نسائي', 85000, $cat, 10, true], ['بخور تيمان فاخر', 15000, $cat2, 50, false], ['دلكة سودانية', 12000, $cat2, 2, false]] as $i => [$n, $p, $c, $q, $f]) {
                $prod = Product::create(['name' => $n, 'slug' => 'product-'.($i + 1), 'price' => $p, 'cost' => $p * 0.6, 'category_id' => $c->id, 'is_featured' => $f, 'description' => 'منتج سوداني أصلي بجودة عالية.']);
                $inv->set($prod, $q);
            }
            $v = Product::create(['name' => 'تيشيرت قطني', 'slug' => 'tshirt', 'type' => 'variable', 'price' => 20000, 'category_id' => $cat->id]);
            foreach (['S', 'M', 'L'] as $size) {
                $variant = $v->variants()->create(['store_id' => $v->store_id, 'options' => ['المقاس' => $size]]);
                $inv->set($v, 5, $variant->id);
            }
            Coupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10]);
        });
    }
}
