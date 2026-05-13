@extends('layouts.app')
@section('title', 'Stock Value Report')
@section('breadcrumb', 'Inventory valuation and profit analysis')

@section('content')
<div class="space-y-6 pt-2">

    {{-- TOTALS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Cost Value</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($totals['cost_value']) }}</p>
            <p class="text-xs text-slate-400 mt-1">What you paid for current stock</p>
        </div>
        <div class="bg-indigo-50 rounded-2xl border border-indigo-200 p-6">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Potential Revenue</p>
            <p class="text-3xl font-bold text-indigo-700 mt-1">{{ number_format($totals['revenue_value']) }}</p>
            <p class="text-xs text-indigo-400 mt-1">If all current stock is sold</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-6">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Gross Profit</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ number_format($totals['gross_profit']) }}</p>
            <p class="text-xs text-emerald-400 mt-1">Revenue minus cost ({{ $totals['cost_value'] > 0 ? number_format(($totals['gross_profit'] / $totals['revenue_value']) * 100, 1) : 0 }}% margin)</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- BY CATEGORY --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Value by Category</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($byCategory as $cat)
                @php $pct = $totals['cost_value'] > 0 ? ($cat->stock_value / $totals['cost_value']) * 100 : 0; @endphp
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background:{{ $cat->color }}"></span>
                            <span class="text-sm font-medium text-slate-800">{{ $cat->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-slate-800">{{ number_format($cat->stock_value) }}</span>
                            <span class="text-xs text-slate-400 ml-1">cost</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%; background:{{ $cat->color }}"></div>
                        </div>
                        <span class="text-xs text-slate-400 w-12 text-right">{{ number_format($pct, 1) }}%</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-400 mt-1">
                        <span>{{ number_format($cat->total_items) }} units</span>
                        <span>Revenue: {{ number_format($cat->potential_revenue) }}</span>
                    </div>
                </div>
                @empty
                <div class="py-10 text-center text-slate-400 text-sm">No categories found.</div>
                @endforelse
            </div>
        </div>

        {{-- TOP PRODUCTS BY VALUE --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Top Products by Stock Value</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($products->take(10) as $idx => $product)
                @php $val = $product->quantity * $product->buying_price; @endphp
                <div class="px-5 py-3 flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center flex-shrink-0">
                        {{ $idx + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-slate-400">{{ number_format($product->quantity) }} {{ $product->unit }} × {{ number_format($product->buying_price) }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-slate-800">{{ number_format($val) }}</p>
                        <p class="text-xs text-emerald-600">→ {{ number_format($product->quantity * $product->selling_price) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- FULL TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">All Products — Value Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Buy Price</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Sell Price</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock Value</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Potential Rev.</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <p class="font-medium text-slate-800">{{ $product->name }}</p>
                            @if($product->category)
                            <span class="text-xs" style="color:{{ $product->category->color }}">{{ $product->category->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ number_format($product->quantity) }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ number_format($product->buying_price) }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ number_format($product->selling_price) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ number_format($product->stock_value) }}</td>
                        <td class="px-4 py-3 text-right text-indigo-600 font-semibold">{{ number_format($product->potential_revenue) }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-xs font-semibold {{ $product->profit_margin >= 20 ? 'text-emerald-600' : ($product->profit_margin >= 10 ? 'text-amber-600' : 'text-red-600') }}">
                                {{ number_format($product->profit_margin, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                    <tr>
                        <td class="px-5 py-3 font-bold text-slate-800" colspan="4">Totals</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($totals['cost_value']) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-indigo-600">{{ number_format($totals['revenue_value']) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">
                            {{ $totals['revenue_value'] > 0 ? number_format(($totals['gross_profit'] / $totals['revenue_value']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $products->links() }}</div>
    </div>

</div>
@endsection