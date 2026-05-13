<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('user_id', auth()->id())
            ->with('category')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'low'      => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'reorder_level'),
                'out'      => $query->where('quantity', 0),
                'expiring' => $query->whereNotNull('expiry_date')->whereBetween('expiry_date', [now(), now()->addDays(30)]),
                default    => null,
            };
        }

        if ($request->filled('business_type')) {
            $query->where('business_type', $request->business_type);
        }

        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'sku'           => 'required|string|unique:products,sku',
            'description'   => 'nullable|string',
            'buying_price'  => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity'      => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit'          => 'required|string|max:20',
            'expiry_date'   => 'nullable|date',
            'image'         => 'nullable|image|max:2048',
        ]);

        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        $movements = $product->stockMovements()->latest()->paginate(15);
        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'sku'           => 'required|string|unique:products,sku,' . $product->id,
            'description'   => 'nullable|string',
            'buying_price'  => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity'      => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit'          => 'required|string|max:20',
            'expiry_date'   => 'nullable|date',
            'image'         => 'nullable|image|max:2048',
            'is_active'     => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    public function exportCsv()
    {
        $products = Product::where('user_id', auth()->id())->with('category')->get();

        $csv = "Name,SKU,Category,Buying Price,Selling Price,Quantity,Reorder Level,Unit,Expiry Date,Business Type\n";
        foreach ($products as $p) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $p->name) . '"',
                $p->sku,
                '"' . ($p->category?->name ?? '') . '"',
                $p->buying_price,
                $p->selling_price,
                $p->quantity,
                $p->reorder_level,
                $p->unit,
                $p->expiry_date?->format('Y-m-d') ?? '',
                $p->business_type ?? '',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stockcraft-inventory-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getPathname(), 'r');
        $header  = fgetcsv($handle);
        $count   = 0;
        $errors  = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;
            [$name, $sku, $category, $buyingPrice, $sellingPrice, $quantity, $reorderLevel, $unit, $expiry, $businessType] = array_pad($row, 10, null);

            if (empty($name) || empty($sku)) continue;

            // Find or create category
            $categoryId = null;
            if (!empty($category)) {
                $cat = Category::firstOrCreate(
                    ['user_id' => auth()->id(), 'name' => trim($category)],
                    ['color' => '#6366f1']
                );
                $categoryId = $cat->id;
            }

            if (Product::where('sku', $sku)->exists()) {
                $errors[] = "SKU $sku already exists, skipped.";
                continue;
            }

            Product::create([
                'user_id'       => auth()->id(),
                'name'          => trim($name),
                'sku'           => trim($sku),
                'category_id'   => $categoryId,
                'buying_price'  => (float)($buyingPrice ?? 0),
                'selling_price' => (float)($sellingPrice ?? 0),
                'quantity'      => (int)($quantity ?? 0),
                'reorder_level' => (int)($reorderLevel ?? 10),
                'unit'          => trim($unit ?? 'pcs'),
                'expiry_date'   => !empty($expiry) ? $expiry : null,
                'business_type' => trim($businessType ?? ''),
            ]);
            $count++;
        }
        fclose($handle);

        $message = "$count products imported successfully.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' skipped.';
        }

        return redirect()->route('products.index')->with('success', $message);
    }
}
