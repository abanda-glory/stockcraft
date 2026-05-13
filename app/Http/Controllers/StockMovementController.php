<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = StockMovement::where('user_id', $userId)
            ->with('product.category')
            ->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search product name or SKU
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%");
            });
        }

        // Date filters (FIXED to match your Blade names)
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $movements = $query->paginate(20)->withQueryString();

        $totalIn = StockMovement::where('user_id', $userId)
            ->where('type', 'in')
            ->sum('quantity');

        $totalOut = StockMovement::where('user_id', $userId)
            ->where('type', 'out')
            ->sum('quantity');

        $products = Product::where('user_id', $userId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('movements.index', compact(
            'movements',
            'totalIn',
            'totalOut',
            'products'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'nullable|string|max:255',
            'reference'  => 'nullable|string|max:100',
            'notes'      => 'nullable|string',
        ]);

        $userId = auth()->id();

        $product = Product::where('id', $data['product_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        // Prevent negative stock
        if ($data['type'] === 'out' && $product->quantity < $data['quantity']) {
            return back()->withErrors([
                'quantity' => "Insufficient stock. Available: {$product->quantity}"
            ])->withInput();
        }

        DB::transaction(function () use ($data, $product, $userId) {

            $movement = new StockMovement($data);
            $movement->user_id = $userId;
            $movement->save();

            StockMovement::create($data);

            if ($data['type'] === 'in') {
                $product->increment('quantity', $data['quantity']);
            } else {
                $product->decrement('quantity', $data['quantity']);
            }
        });

        return back()->with('success', 'Stock movement recorded successfully.');
    }

    public function destroy(StockMovement $movement)
    {
        abort_if($movement->user_id !== auth()->id(), 403);

        $product = Product::where('id', $movement->product_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        DB::transaction(function () use ($movement, $product) {

            if ($movement->type === 'in') {
                $product->decrement('quantity', $movement->quantity);
            } else {
                $product->increment('quantity', $movement->quantity);
            }

            $movement->delete();
        });

        return back()->with('success', 'Movement deleted and stock reversed.');
    }
}
