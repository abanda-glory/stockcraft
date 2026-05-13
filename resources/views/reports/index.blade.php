@extends('layouts.app')
@section('title', 'Reports')
@section('breadcrumb', 'Insights and analytics for your inventory')

@section('content')
<div class="space-y-6 pt-2">

    {{-- SUMMARY STATS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Products</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($summary['total_products']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock Value (Cost)</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ number_format($summary['total_value']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Potential Revenue</p>
            <p class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($summary['potential_revenue']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Gross Profit (If Sold)</p>
            <p class="text-3xl font-bold text-teal-600 mt-1">{{ number_format($summary['potential_revenue'] - $summary['total_value']) }}</p>
        </div>
    </div>

    {{-- ALERT STRIP --}}
    @if($summary['low_stock'] > 0 || $summary['out_of_stock'] > 0 || $summary['expiring_soon'] > 0 || $summary['expired'] > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex flex-wrap gap-4 items-center">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span class="text-sm font-semibold text-amber-800">Attention required:</span>
        <div class="flex flex-wrap gap-3 text-sm">
            @if($summary['low_stock'] > 0)
            <a href="{{ route('reports.low-stock') }}" class="text-amber-700 hover:underline font-medium">{{ $summary['low_stock'] }} low stock items</a>
            @endif
            @if($summary['out_of_stock'] > 0)
            <a href="{{ route('products.index', ['status' => 'out']) }}" class="text-red-700 hover:underline font-medium">{{ $summary['out_of_stock'] }} out of stock</a>
            @endif
            @if($summary['expiring_soon'] > 0)
            <a href="{{ route('reports.expiry') }}" class="text-orange-700 hover:underline font-medium">{{ $summary['expiring_soon'] }} expiring in 30 days</a>
            @endif
            @if($summary['expired'] > 0)
            <a href="{{ route('reports.expiry') }}" class="text-red-700 hover:underline font-medium">{{ $summary['expired'] }} already expired</a>
            @endif
        </div>
    </div>
    @endif

    {{-- REPORT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('reports.low-stock') }}" class="group bg-white hover:border-amber-300 border border-slate-200 rounded-2xl p-6 transition-all hover:shadow-md">
            <div class="w-12 h-12 bg-amber-100 group-hover:bg-amber-200 rounded-xl flex items-center justify-center mb-4 transition-colors">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1">Low Stock Report</h3>
            <p class="text-sm text-slate-500">Products below reorder level that need restocking.</p>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-2xl font-bold text-amber-600">{{ $summary['low_stock'] }}</span>
                <span class="text-xs text-slate-400 group-hover:text-indigo-600 transition-colors">View →</span>
            </div>
        </a>

        <a href="{{ route('reports.expiry') }}" class="group bg-white hover:border-orange-300 border border-slate-200 rounded-2xl p-6 transition-all hover:shadow-md">
            <div class="w-12 h-12 bg-orange-100 group-hover:bg-orange-200 rounded-xl flex items-center justify-center mb-4 transition-colors">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1">Expiry Report</h3>
            <p class="text-sm text-slate-500">Items expiring soon or already past their date.</p>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-2xl font-bold text-orange-600">{{ $summary['expiring_soon'] + $summary['expired'] }}</span>
                <span class="text-xs text-slate-400 group-hover:text-indigo-600 transition-colors">View →</span>
            </div>
        </a>

        <a href="{{ route('reports.stock-value') }}" class="group bg-white hover:border-indigo-300 border border-slate-200 rounded-2xl p-6 transition-all hover:shadow-md">
            <div class="w-12 h-12 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center mb-4 transition-colors">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1">Stock Value Report</h3>
            <p class="text-sm text-slate-500">Cost value, potential revenue and profit margins.</p>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-lg font-bold text-indigo-600">{{ number_format($summary['total_value'] / 1000, 0) }}K</span>
                <span class="text-xs text-slate-400 group-hover:text-indigo-600 transition-colors">View →</span>
            </div>
        </a>

        <a href="{{ route('reports.movements') }}" class="group bg-white hover:border-teal-300 border border-slate-200 rounded-2xl p-6 transition-all hover:shadow-md">
            <div class="w-12 h-12 bg-teal-100 group-hover:bg-teal-200 rounded-xl flex items-center justify-center mb-4 transition-colors">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1">Movement Report</h3>
            <p class="text-sm text-slate-500">Stock in/out transaction history by date range.</p>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-sm font-medium text-teal-600">By date range</span>
                <span class="text-xs text-slate-400 group-hover:text-indigo-600 transition-colors">View →</span>
            </div>
        </a>
    </div>

    {{-- HEALTH SUMMARY --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h2 class="font-semibold text-slate-800 mb-5">Inventory Health Summary</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @php
            $total = max($summary['total_products'], 1);
            $healthPct = max(0, round((($total - $summary['low_stock'] - $summary['out_of_stock']) / $total) * 100));
            @endphp
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-600">Healthy Stock</span>
                    <span class="font-semibold text-emerald-600">{{ $healthPct }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width:{{ $healthPct }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-600">Low Stock</span>
                    <span class="font-semibold text-amber-600">{{ round(($summary['low_stock'] / $total) * 100) }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-400 rounded-full" style="width:{{ round(($summary['low_stock'] / $total) * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-600">Out of Stock</span>
                    <span class="font-semibold text-red-600">{{ round(($summary['out_of_stock'] / $total) * 100) }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-red-500 rounded-full" style="width:{{ round(($summary['out_of_stock'] / $total) * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-slate-600">Expiring Soon</span>
                    <span class="font-semibold text-orange-600">{{ round(($summary['expiring_soon'] / $total) * 100) }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-orange-400 rounded-full" style="width:{{ round(($summary['expiring_soon'] / $total) * 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection