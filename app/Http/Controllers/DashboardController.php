<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $totalProducts = Product::where('user_id', $userId)->where('is_active', true)->count();
        $outOfStock = Product::where('user_id', $userId)->where('quantity', 0)->count();
        $lowStock = Product::where('user_id', $userId)
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->count();

        $totalStockValue = Product::where('user_id', $userId)
            ->selectRaw('SUM(quantity * buying_price) as total')
            ->value('total') ?? 0;

        $expiringSoon = Product::where('user_id', $userId)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [Carbon::now(), Carbon::now()->addDays(30)])
            ->count();

        $lowStockItems = Product::where('user_id', $userId)
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->with('category')
            ->orderBy('quantity')
            ->limit(5)
            ->get();

        $recentMovements = StockMovement::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->limit(8)
            ->get();

        $categoryStats = Category::where('user_id', $userId)
            ->withCount('products')
            ->withSum('products', 'quantity')
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        // Stock movements for last 7 days chart
        $movementsChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $in = StockMovement::where('user_id', $userId)
                ->where('type', 'in')
                ->whereDate('created_at', $date)
                ->sum('quantity');
            $out = StockMovement::where('user_id', $userId)
                ->where('type', 'out')
                ->whereDate('created_at', $date)
                ->sum('quantity');
            $movementsChart[] = [
                'date' => Carbon::parse($date)->format('D'),
                'in' => $in,
                'out' => $out,
            ];
        }

        return view('dashboard', compact(
            'totalProducts',
            'outOfStock',
            'lowStock',
            'totalStockValue',
            'expiringSoon',
            'lowStockItems',
            'recentMovements',
            'categoryStats',
            'movementsChart'
        ));
    }
}
