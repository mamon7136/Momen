<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function home()
    {
        return view('storefront.home', [
            'categories' => Category::whereNull('parent_id')->orderBy('sort')->get(),
            'featured' => Product::where('is_active', true)->where('is_featured', true)->latest()->limit(8)->get(),
            'latest' => Product::where('is_active', true)->latest()->limit(12)->get(),
        ]);
    }

    public function products(Request $request)
    {
        $products = Product::where('is_active', true)
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'), fn ($q) => $q->latest())
            ->paginate(24)->withQueryString();

        return view('storefront.products', ['products' => $products, 'title' => $request->q ? 'نتائج البحث: '.$request->q : 'كل المنتجات']);
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return view('storefront.products', ['products' => $category->products()->where('is_active', true)->latest()->paginate(24), 'title' => $category->name]);
    }

    public function product(string $slug)
    {
        $product = Product::with('variants', 'category')->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $related = Product::where('is_active', true)->where('category_id', $product->category_id)->whereKeyNot($product->id)->limit(4)->get();

        return view('storefront.product', compact('product', 'related'));
    }
}
