<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $summary = [
            'total_products'   => Product::where('user_id', $userId)->count(),
            'total_categories' => Category::where('user_id', $userId)->count(),
            'total_value'      => Product::where('user_id', $userId)->selectRaw('SUM(quantity * buying_price) as v')->value('v') ?? 0,
            'potential_revenue' => Product::where('user_id', $userId)->selectRaw('SUM(quantity * selling_price) as v')->value('v') ?? 0,
            'low_stock'        => Product::where('user_id', $userId)->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'reorder_level')->count(),
            'out_of_stock'     => Product::where('user_id', $userId)->where('quantity', 0)->count(),
            'expiring_soon'    => Product::where('user_id', $userId)->whereNotNull('expiry_date')->whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
            'expired'          => Product::where('user_id', $userId)->whereNotNull('expiry_date')->where('expiry_date', '<', now())->count(),
            'total_movements'  => StockMovement::where('user_id', $userId)->count(),
            'movements_in'     => StockMovement::where('user_id', $userId)->where('type', 'in')->sum('quantity'),
            'movements_out'    => StockMovement::where('user_id', $userId)->where('type', 'out')->sum('quantity'),
        ];

        $byBusinessType = Product::where('user_id', $userId)
            ->whereNotNull('business_type')
            ->selectRaw('business_type, COUNT(*) as count, SUM(quantity * buying_price) as value')
            ->groupBy('business_type')
            ->get();

        return view('reports.index', compact('summary', 'byBusinessType'));
    }

    public function lowStock(Request $request)
    {
        $userId  = auth()->id();
        $query   = Product::where('user_id', $userId)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->with('category')
            ->orderBy('quantity');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $includeOutOfStock = $request->boolean('include_out', true);
        if (!$includeOutOfStock) {
            $query->where('quantity', '>', 0);
        }

        $products   = $query->paginate(25)->withQueryString();
        $categories = Category::where('user_id', $userId)->orderBy('name')->get();

        // CSV export
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($products->getCollection(), 'low-stock-report');
        }

        return view('reports.low-stock', compact('products', 'categories'));
    }

    public function expiry(Request $request)
    {
        $userId = auth()->id();
        $days   = (int)$request->get('days', 30);

        $expiringSoon = Product::where('user_id', $userId)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->with('category')
            ->orderBy('expiry_date')
            ->get();

        $expired = Product::where('user_id', $userId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with('category')
            ->orderByDesc('expiry_date')
            ->get();

        if ($request->get('export') === 'csv') {
            $all = $expiringSoon->merge($expired);
            return $this->exportCsv($all, 'expiry-report');
        }

        return view('reports.expiry', compact('expiringSoon', 'expired', 'days'));
    }

    public function stockValue()
    {
        $userId = Auth::id();

        $products = Product::with('category')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->paginate(15);

        $costValue = $products->sum(fn($p) => $p->quantity * $p->buying_price);
        $revenueValue = $products->sum(fn($p) => $p->quantity * $p->selling_price);

        $totals = [
            'cost_value' => $costValue,
            'revenue_value' => $revenueValue,
            'gross_profit' => $revenueValue - $costValue,
        ];

        $byCategory = $products->groupBy('category_id')->map(function ($items) {
            $cat = $items->first()->category;

            return (object) [
                'name' => $cat->name ?? 'Uncategorized',
                'color' => $cat->color ?? '#6366f1',
                'stock_value' => $items->sum(fn($p) => $p->quantity * $p->buying_price),
                'total_items' => $items->sum('quantity'),
                'potential_revenue' => $items->sum(fn($p) => $p->quantity * $p->selling_price),
            ];
        })->values();

        return view('reports.stock-value', compact(
            'products',
            'totals',
            'byCategory'
        ));
    }

    public function movements(Request $request)
    {
        $userId = auth()->id();

        $from = $request->from ?? now()->subDays(30)->format('Y-m-d');
        $to   = $request->to ?? now()->format('Y-m-d');

        $query = StockMovement::where('user_id', $userId)
            ->with('product.category')
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $query->whereBetween('created_at', [
            $from . ' 00:00:00',
            $to . ' 23:59:59'
        ]);

        $movements = $query->paginate(30)->withQueryString();

        $stockIn = StockMovement::where('user_id', $userId)
            ->where('type', 'in')
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ])
            ->sum('quantity');

        $stockOut = StockMovement::where('user_id', $userId)
            ->where('type', 'out')
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ])
            ->sum('quantity');

        return view('reports.movements', compact(
            'movements',
            'from',
            'to',
            'stockIn',
            'stockOut'
        ));
    }

    private function exportCsv($collection, string $filename)
    {
        $csv = "Name,SKU,Category,Quantity,Unit,Buying Price,Selling Price,Stock Value,Expiry Date\n";
        foreach ($collection as $p) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $p->name) . '"',
                $p->sku,
                '"' . ($p->category?->name ?? 'N/A') . '"',
                $p->quantity,
                $p->unit,
                $p->buying_price,
                $p->selling_price,
                round($p->quantity * $p->buying_price, 2),
                $p->expiry_date?->format('Y-m-d') ?? '',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
