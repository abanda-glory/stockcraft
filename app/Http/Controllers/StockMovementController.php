<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::where('user_id', auth()->id())
            ->with('product.category')
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->whereHas(
                'product',
                fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(20)->withQueryString();

        $totalIn  = StockMovement::where('user_id', auth()->id())->where('type', 'in')->sum('quantity');
        $totalOut = StockMovement::where('user_id', auth()->id())->where('type', 'out')->sum('quantity');

        $products = Product::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('movements.index', compact('movements', 'totalIn', 'totalOut', 'products'));
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
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::where('id', $data['product_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($data['type'] === 'out' && $product->quantity < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $product->quantity])->withInput();
        }

        $data['user_id'] = auth()->id();
        StockMovement::create($data);

        // Update product quantity
        if ($data['type'] === 'in') {
            $product->increment('quantity', $data['quantity']);
        } else {
            $product->decrement('quantity', $data['quantity']);
        }

        return back()->with('success', 'Stock movement recorded successfully.');
    }

    public function destroy(StockMovement $movement)
    {
        abort_if($movement->user_id !== auth()->id(), 403);
        $movement->delete();
        return back()->with('success', 'Movement deleted.');
    }
}
