<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', $s)))
            ->latest()->paginate(20)->withQueryString();

        return view('dashboard.products.index', ['products' => $products, 'uncategorized' => Product::whereNull('category_id')->count()]);
    }

    public function create()
    {
        $store = current_store();
        if ($store->plan?->max_products && Product::count() >= $store->plan->max_products) {
            return redirect()->route('dashboard.billing.index')->with('error', 'وصلت للحد الأقصى من المنتجات في باقتك، قم بالترقية');
        }

        return view('dashboard.products.form', ['product' => new Product(['type' => 'simple', 'is_active' => true, 'track_stock' => true]),
            'categories' => Category::orderBy('name')->get(), 'branches' => Branch::all()]);
    }

    public function edit(Product $product)
    {
        return view('dashboard.products.form', ['product' => $product->load('variants', 'stockLevels'),
            'categories' => Category::orderBy('name')->get(), 'branches' => Branch::all()]);
    }

    protected function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:160',
            'type' => 'required|in:simple,variable,digital',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('store_id', current_store()->id)],
            'sku' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:20000',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'price_currency' => 'required|in:SDG,USD',
            'weight_kg' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'images.*' => 'image|max:3072',
            'digital' => 'nullable|file|max:51200',
            'variants' => 'nullable|array',
            'variants.*.options' => 'nullable|string|max:200',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'stock' => 'nullable|integer|min:0',
        ], [], ['name' => 'اسم المنتج', 'price' => 'السعر']);
    }

    public function store(Request $request, InventoryService $inventory)
    {
        $data = $this->validated($request);
        $product = Product::create($this->attributes($request, $data) + ['slug' => $this->slug($data['name'])]);
        $this->syncVariants($product, $request, $inventory);

        return redirect()->route('dashboard.products.index')->with('status', 'تمت إضافة المنتج');
    }

    public function update(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $this->validated($request, $product);
        $product->update($this->attributes($request, $data, $product));
        $this->syncVariants($product, $request, $inventory);

        return redirect()->route('dashboard.products.index')->with('status', 'تم تحديث المنتج');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('status', 'تم حذف المنتج');
    }

    /** Inline edit from the products list: name, price and (simple products) stock. */
    public function quick(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate(['name' => 'required|string|max:160', 'price' => 'required|numeric|min:0', 'stock' => 'nullable|integer|min:0']);
        $product->update(['name' => $data['name'], 'price' => $data['price']]);
        if ($product->type === 'simple' && $request->filled('stock')) {
            $product->update(['track_stock' => true]);
            $inventory->set($product, (int) $data['stock'], null, null, $request->user()->id);
        }

        return back()->with('status', 'تم حفظ "'.$product->name.'"');
    }

    public function stock(Request $request, Product $product, InventoryService $inventory)
    {
        $data = $request->validate(['branch_id' => 'required|integer', 'quantity' => 'required|integer|min:0', 'variant_id' => 'nullable|integer']);
        $inventory->set($product, $data['quantity'], $data['variant_id'] ?? null, Branch::findOrFail($data['branch_id']), $request->user()->id);

        return back()->with('status', 'تم تحديث المخزون');
    }

    protected function attributes(Request $request, array $data, ?Product $product = null): array
    {
        $images = $product?->images ?? [];
        foreach ($request->file('images', []) as $file) {
            $images[] = $file->store('stores/'.current_store()->id.'/products', 'public');
        }
        if ($request->filled('remove_images')) {
            $images = array_values(array_diff($images, (array) $request->remove_images));
        }

        return [
            'name' => $data['name'], 'type' => $data['type'], 'category_id' => $data['category_id'] ?? null,
            'sku' => $data['sku'] ?? null, 'description' => $data['description'] ?? null,
            'price' => $data['price'], 'compare_price' => $data['compare_price'] ?? null, 'cost' => $data['cost'] ?? null,
            'price_currency' => $data['price_currency'], 'weight_kg' => $data['weight_kg'] ?? null,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 3,
            'track_stock' => $request->boolean('track_stock') && $data['type'] !== 'digital',
            'is_active' => $request->boolean('is_active'), 'is_featured' => $request->boolean('is_featured'),
            'images' => $images,
            'digital_file' => $request->hasFile('digital') ? $request->file('digital')->store('digital/'.current_store()->id, 'local') : $product?->digital_file,
        ];
    }

    /** Variants entered as "المقاس:L، اللون:أحمر" per row — simple for merchants, structured in DB. */
    protected function syncVariants(Product $product, Request $request, InventoryService $inventory): void
    {
        if ($product->type !== 'variable') {
            if ($request->filled('stock')) {
                $inventory->set($product, (int) $request->stock, null, null, $request->user()->id);
            }

            return;
        }
        foreach ((array) $request->input('variants', []) as $row) {
            if (empty($row['options'])) {
                continue;
            }
            $options = collect(preg_split('/[،,]/u', $row['options']))->mapWithKeys(function ($pair) {
                [$k, $v] = array_pad(array_map('trim', explode(':', $pair, 2)), 2, null);

                return $v === null ? ['الخيار' => $k] : [$k => $v];
            })->all();
            $variant = $product->variants()->get()->first(fn ($v) => $v->options == $options)
                ?? $product->variants()->create(['store_id' => $product->store_id, 'options' => $options]);
            $variant->update(['price' => ($row['price'] ?? '') !== '' ? $row['price'] : null]);
            if (isset($row['stock']) && $row['stock'] !== '') {
                $inventory->set($product, (int) $row['stock'], $variant->id, null, $request->user()->id);
            }
        }
    }

    protected function slug(string $name): string
    {
        $base = Str::slug($name) ?: 'p';
        $base = preg_match('/^[a-z0-9-]+$/', $base) ? $base : 'p';
        $slug = $base.'-'.Str::lower(Str::random(5));

        return $slug;
    }
}
