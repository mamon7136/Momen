<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    public function test_merchant_registers_on_free_plan_and_store_is_live(): void
    {
        $this->post($this->platform('/register'), [
            'name' => 'أحمد', 'phone' => '0912345678', 'email' => 'a@x.sd', 'password' => 'secret123', 'password_confirmation' => 'secret123',
            'store_name' => 'متجر أحمد', 'slug' => 'ahmed', 'plan_id' => Plan::where('slug', 'free')->value('id'), 'cycle' => 'monthly',
        ])->assertRedirect(route('dashboard.home'));

        $store = Store::where('slug', 'ahmed')->first();
        $this->assertSame('active', $store->status);
        $this->assertSame('249912345678', User::where('email', 'a@x.sd')->value('phone')); // normalised to intl format
        $this->get($this->shop($store))->assertOk()->assertSee('متجر أحمد');
        $this->get($this->platform('/dashboard'))->assertOk();
    }

    public function test_reserved_and_duplicate_slugs_are_rejected(): void
    {
        $this->makeStore('taken');
        foreach (['admin', 'taken', 'A B'] as $slug) {
            $this->post($this->platform('/register'), [
                'name' => 'x', 'phone' => '0911111111', 'email' => 'x'.md5($slug).'@x.sd', 'password' => 'secret123', 'password_confirmation' => 'secret123',
                'store_name' => 'x', 'slug' => $slug, 'plan_id' => Plan::first()->id, 'cycle' => 'monthly',
            ])->assertSessionHasErrors('slug');
        }
    }

    public function test_paid_plan_with_receipt_is_activated_by_super_admin(): void
    {
        Storage::fake('local');
        Plan::where('slug', 'basic')->update(['trial_days' => 0]);
        $owner = User::create(['name' => 'o', 'email' => 'o@x.sd', 'phone' => '249911000000', 'password' => 'password']);
        $this->actingAs($owner)->post($this->platform('/register/store'), [
            'store_name' => 'Paid', 'slug' => 'paid', 'plan_id' => Plan::where('slug', 'basic')->value('id'), 'cycle' => 'yearly',
        ])->assertRedirect(route('dashboard.billing.index'));

        $store = Store::where('slug', 'paid')->first();
        $this->assertSame('pending', $store->status);
        $this->get($this->shop($store))->assertOk(); // owner can preview a closed store
        auth()->logout();
        $this->get($this->shop($store))->assertStatus(503);

        $this->actingAs($owner)->post($this->platform('/dashboard/billing'), [
            'plan_id' => $store->plan_id, 'cycle' => 'yearly', 'method' => 'mbok', 'reference' => '998877', 'receipt' => UploadedFile::fake()->image('r.jpg'),
        ])->assertSessionHasNoErrors();

        $admin = User::where('is_super_admin', true)->first();
        $sub = Subscription::where('store_id', $store->id)->first();
        $this->actingAs($admin)->post($this->platform('/admin/subscriptions/'.$sub->id.'/approve'))->assertRedirect();

        $store->refresh();
        $this->assertSame('active', $store->status);
        $this->assertTrue($store->subscription_ends_at->greaterThan(now()->addMonths(11)));
    }

    public function test_non_admin_cannot_open_admin_panel(): void
    {
        $store = $this->makeStore();
        $this->actingAs($store->owner)->get($this->platform('/admin'))->assertForbidden();
    }
}
