<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('dashboard.categories', ['categories' => Category::withCount('products')->orderBy('sort')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:80', 'image' => 'nullable|image|max:2048']);
        Category::create([
            'name' => $data['name'],
            'slug' => (Str::slug($data['name']) ?: 'c').'-'.Str::lower(Str::random(4)),
            'image' => $request->file('image')?->store('stores/'.current_store()->id.'/categories', 'public'),
        ]);

        return back()->with('status', 'تمت إضافة التصنيف');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('status', 'تم حذف التصنيف');
    }
}
