<?php

use App\Models\Store;
use App\Support\Icons;
use App\Support\Tenancy\CurrentStore;
use Illuminate\Support\HtmlString;

if (! function_exists('money')) {
    function money(float|string|null $amount, string $currency = 'SDG'): string
    {
        $symbol = $currency === 'SDG' ? 'ج.س' : $currency;

        return number_format((float) $amount, 0)."\u{00A0}".$symbol; // nbsp: never wrap the currency
    }
}

if (! function_exists('current_store')) {
    function current_store(): ?Store
    {
        return app(CurrentStore::class)->get();
    }
}

if (! function_exists('sroute')) {
    /** Storefront route helper that works in both host mode and /s/{slug} path mode. */
    function sroute(string $name, array $params = []): string
    {
        $tenancy = app(CurrentStore::class);
        if ($tenancy->mode() === 'path') {
            return route('path.store.'.$name, ['store_slug' => $tenancy->get()->slug] + $params);
        }

        return route('store.'.$name, $params);
    }
}

if (! function_exists('icon')) {
    function icon(string $name, int $size = 20, string $class = ''): HtmlString
    {
        return new HtmlString(Icons::svg($name, $size, $class));
    }
}

if (! function_exists('theme_view')) {
    /** Resolve a storefront view from the active store's theme, falling back to the base theme. */
    function theme_view(string $name): string
    {
        $slug = current_store()?->theme?->slug ?? 'nile';
        $view = 'themes.'.$slug.'.'.$name;

        return view()->exists($view) ? $view : 'themes.nile.'.$name;
    }
}

if (! function_exists('initials')) {
    function initials(?string $name): string
    {
        $parts = preg_split('/\s+/u', trim((string) $name));

        $second = isset($parts[1]) ? preg_replace('/^ال/u', '', $parts[1]) : '';

        return mb_substr($parts[0] ?? '?', 0, 1).($second !== '' ? mb_substr($second, 0, 1) : '');
    }
}

if (! function_exists('sroute_for')) {
    /** Public storefront URL for a store from outside the storefront (dashboard links). */
    function sroute_for(Store $store, string $name, array $params = []): string
    {
        $path = match ($name) {
            'product' => '/p/'.$params['slug'],
            'category' => '/c/'.$params['slug'],
            default => '/',
        };

        return rtrim($store->primaryUrl(), '/').$path;
    }
}

if (! function_exists('theme_hero')) {
    /** Hero image for a section: merchant upload, else the theme's bundled artwork. */
    function theme_hero(array $section): ?string
    {
        if (! empty($section['image'])) {
            return asset('storage/'.$section['image']);
        }
        $slug = current_store()?->theme?->slug ?? 'nile';

        return file_exists(public_path('themes/'.$slug.'/hero.jpg')) ? asset('themes/'.$slug.'/hero.jpg') : null;
    }
}
