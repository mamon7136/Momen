<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;

/**
 * No-code page builder: colors, font, announcement bar, ordered homepage sections (hero, banners,
 * categories, featured, latest, text). Stored as JSON in stores.theme_settings and rendered by the theme.
 */
class DesignController extends Controller
{
    public const SECTION_TYPES = [
        'hero' => 'بانر رئيسي', 'categories' => 'التصنيفات', 'featured_products' => 'منتجات مميزة',
        'latest_products' => 'أحدث المنتجات', 'banner' => 'صورة إعلانية', 'text' => 'نص حر', 'whatsapp_cta' => 'زر تواصل واتساب', 'features_bar' => 'شريط المزايا (توصيل، دفع آمن...)',
    ];

    public function edit()
    {
        $store = current_store();

        return view('dashboard.design', [
            'config' => $store->themeConfig(),
            'themes' => Theme::where('is_active', true)->get(),
            'types' => self::SECTION_TYPES,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'colors.primary' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'colors.secondary' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'colors.background' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'colors.text' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'font' => 'required|in:Tajawal,Cairo,Almarai,IBM Plex Sans Arabic,Amiri,Noto Kufi Arabic,system',
            'announcement' => 'nullable|string|max:160',
            'footer_text' => 'nullable|string|max:300',
            'sections' => 'nullable|array|max:20',
            'sections.*.type' => 'required|in:'.implode(',', array_keys(self::SECTION_TYPES)),
            'sections.*.title' => 'nullable|string|max:120',
            'sections.*.subtitle' => 'nullable|string|max:300',
            'sections.*.link' => 'nullable|string|max:300',
            'sections.*.image' => 'nullable|string|max:300',
            'section_images.*' => 'nullable|image|max:3072',
            'logo' => 'nullable|image|max:2048',
        ]);

        $store = current_store();
        $sections = collect($data['sections'] ?? [])->map(function ($s, $i) use ($request, $store) {
            if ($file = $request->file("section_images.$i")) {
                $s['image'] = $file->store('stores/'.$store->id.'/design', 'public');
            }

            return $s;
        })->filter(fn ($s) => empty($s['_remove']))->values()->all();

        $store->theme_settings = ['colors' => $data['colors'], 'font' => $data['font'], 'announcement' => $data['announcement'] ?? null,
            'footer_text' => $data['footer_text'] ?? null, 'sections' => $sections];
        if ($request->hasFile('logo')) {
            $store->logo = $request->file('logo')->store('stores/'.$store->id, 'public');
        }
        $store->save();

        return back()->with('status', 'تم حفظ التصميم');
    }

    public function theme(Request $request)
    {
        $data = $request->validate(['theme_id' => 'required|exists:themes,id']);
        abort_unless(Theme::where('is_active', true)->whereKey($data['theme_id'])->exists(), 422);
        current_store()->update(['theme_id' => $data['theme_id'], 'theme_settings' => null]);

        return redirect()->route('dashboard.themes.index')->with('status', 'تم تطبيق الثيم على متجرك 🎉');
    }
}
