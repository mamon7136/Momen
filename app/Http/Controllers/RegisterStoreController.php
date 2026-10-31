<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Store;
use App\Models\Theme;
use App\Models\User;
use App\Services\Messaging\WhatsApp;
use App\Services\StoreProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** One-page onboarding: account + store + plan + theme. Paid plans continue to /dashboard/billing. */
class RegisterStoreController extends Controller
{
    public function create(Request $request)
    {
        return view('platform.register', $this->formData($request));
    }

    public function createStore(Request $request)
    {
        return view('platform.register', $this->formData($request) + ['existingUser' => $request->user()]);
    }

    protected function formData(Request $request): array
    {
        return [
            'plans' => Plan::where('is_active', true)->orderBy('sort')->get(),
            'themes' => Theme::where('is_active', true)->get(),
            'selectedPlan' => $request->query('plan'),
        ];
    }

    protected function storeRules(): array
    {
        return [
            'store_name' => 'required|string|max:80',
            'slug' => ['required', 'string', 'regex:/^[a-z0-9][a-z0-9-]{2,30}$/', Rule::notIn(Store::RESERVED_SLUGS), Rule::unique('stores', 'slug')],
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'cycle' => 'required|in:monthly,yearly',
            'theme_id' => ['nullable', Rule::exists('themes', 'id')],
            'state' => 'nullable|string|max:60',
        ];
    }

    public function store(Request $request, StoreProvisioner $provisioner)
    {
        $request->merge(['slug' => strtolower((string) $request->slug), 'phone' => WhatsApp::normalize((string) $request->phone)]);
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'phone' => 'required|string|min:11|max:15|unique:users,phone',
            'email' => 'required|email|max:120|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ] + $this->storeRules(), [], $this->attributes());

        $store = DB::transaction(function () use ($data, $provisioner) {
            $user = User::create(['name' => $data['name'], 'phone' => $data['phone'], 'email' => strtolower($data['email']), 'password' => $data['password']]);
            Auth::login($user, true);

            return $provisioner->provision($user, $data, Plan::find($data['plan_id']), $data['cycle'], Theme::find($data['theme_id'] ?? null));
        });

        return $this->afterProvision($store);
    }

    public function storeStore(Request $request, StoreProvisioner $provisioner)
    {
        $request->merge(['slug' => strtolower((string) $request->slug)]);
        $data = $request->validate($this->storeRules(), [], $this->attributes());
        $store = $provisioner->provision($request->user(), $data + ['phone' => $request->user()->phone], Plan::find($data['plan_id']), $data['cycle'], Theme::find($data['theme_id'] ?? null));

        return $this->afterProvision($store);
    }

    protected function afterProvision(Store $store)
    {
        session(['active_store_id' => $store->id]);

        return $store->status === 'active'
            ? redirect()->route('dashboard.home')->with('status', 'تم إنشاء متجرك بنجاح 🎉 ابدأ بإضافة منتجاتك')
            : redirect()->route('dashboard.billing.index')->with('status', 'تم إنشاء متجرك. أكمل دفع الاشتراك لتفعيله');
    }

    protected function attributes(): array
    {
        return ['name' => 'الاسم', 'phone' => 'رقم الهاتف', 'email' => 'البريد الإلكتروني', 'password' => 'كلمة المرور',
            'store_name' => 'اسم المتجر', 'slug' => 'رابط المتجر', 'plan_id' => 'الباقة'];
    }
}
