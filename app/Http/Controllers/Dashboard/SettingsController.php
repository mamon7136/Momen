<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('dashboard.settings', ['domains' => current_store()->domains()->get()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80', 'phone' => 'nullable|string|max:20', 'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email', 'state' => 'nullable|string', 'city' => 'nullable|string',
            'settings.about' => 'nullable|string|max:2000', 'settings.return_policy' => 'nullable|string|max:3000',
            'settings.facebook' => 'nullable|url', 'settings.instagram' => 'nullable|url', 'settings.tiktok' => 'nullable|url',
            'settings.min_order' => 'nullable|numeric|min:0',
        ]);
        $store = current_store();
        $store->update(collect($data)->except('settings')->all() + ['settings' => array_merge($store->settings ?? [], $data['settings'] ?? [])]);

        return back()->with('status', 'تم حفظ الإعدادات');
    }

    public function addDomain(Request $request)
    {
        $store = current_store();
        if (! $store->plan?->custom_domain) {
            return back()->with('error', 'الدومين الخاص متاح في الباقة الاحترافية');
        }
        $request->merge(['domain' => strtolower(preg_replace('#^https?://|/.*$#', '', trim((string) $request->domain)))]);
        $data = $request->validate(['domain' => ['required', 'regex:/^(?!-)[a-z0-9-]+(\.[a-z0-9-]+)+$/', 'unique:store_domains,domain', 'not_regex:/'.preg_quote(config('platform.domain'), '/').'$/']]);
        $store->domains()->create(['domain' => $data['domain'], 'verification_token' => Str::random(32), 'is_primary' => ! $store->domains()->exists()]);

        return back()->with('status', 'تمت إضافة الدومين. أضف سجلات DNS ثم اضغط تحقق');
    }

    public function verifyDomain(StoreDomain $domain)
    {
        abort_unless($domain->store_id === current_store()->id, 404);
        $txt = collect(@dns_get_record('_platform-verify.'.$domain->domain, DNS_TXT) ?: [])->pluck('txt');
        $cname = collect(@dns_get_record($domain->domain, DNS_CNAME) ?: [])->pluck('target');
        $a = collect(@dns_get_record($domain->domain, DNS_A) ?: [])->pluck('ip');
        $target = config('platform.custom_domain_target');

        $ok = $txt->contains($domain->verification_token) || $cname->contains($target) || $a->contains(gethostbyname($target));
        if (! $ok) {
            return back()->with('error', 'لم يتم العثور على سجلات DNS بعد. قد يستغرق التحديث حتى 24 ساعة');
        }
        $domain->update(['verified_at' => now()]);
        Cache::forget('store-host:'.$domain->domain);

        return back()->with('status', 'تم التحقق من الدومين ✅ — سيتم تفعيل شهادة SSL تلقائياً');
    }
}
