<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Database\Seeders\ThemeCatalog;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index(Request $request)
    {
        $themes = Theme::where('is_active', true)->get()
            ->when($request->cat, fn ($c, $cat) => $c->filter(fn ($t) => data_get($t->default_settings, 'meta.category') === $cat))
            ->when($request->q, fn ($c, $q) => $c->filter(fn ($t) => str_contains($t->name.' '.implode(' ', data_get($t->default_settings, 'meta.tags', [])), $q)))
            ->sortByDesc(fn ($t) => $t->id === current_store()->theme_id);

        return view('dashboard.themes', ['themes' => $themes, 'categories' => ThemeCatalog::categories(), 'current' => current_store()->theme]);
    }
}
