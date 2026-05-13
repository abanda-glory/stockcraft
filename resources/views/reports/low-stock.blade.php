@extends('layouts.app')
@section('title', 'Low Stock Report')
@section('breadcrumb', 'Products at or below reorder level')

@section('header-actions')
<a href="{{ route('reports.low-stock', array_merge(request()->query(), ['export' => 1])) }}"
    class="flex items-center gap-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V10" />
    </svg>
    Export CSV
</a>
@endsection

@section('content')
<div class="space-y-5 pt-2">

    {{-- FILTERS --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <select name="category" class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">Filter</button>
            @if(request()->filled('category'))
            <a href="{{ route('reports.low-stock') }}" class="text-slate-500 text-sm px-3 py-2">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="text-sm text-amber-800"><strong>{{ $products->total() }} products</strong> are at or below their reorder level. Consider restocking these items soon.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        @if($products->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Category</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Current</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Reorder At</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Shortfall</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Buy Price</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                    @php $shortfall = max(0, $product->reorder_level - $product->quantity); @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400">{{ $product->sku }}</p>
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            @if($product->category)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                style="background:{{ $product->category->color }}20; color:{{ $product->category->color }}">
                                {{ $product->category->name }}
                            </span>
                            @else <span class="text-slate-300 text-xs">—</span> @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-bold {{ $product->quantity == 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ number_format($product->quantity) }}
                            </span>
                            <span class="text-xs text-slate-400"> {{ $product->unit }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right text-slate-600">{{ number_format($product->reorder_level) }}</td>
                        <td class="px-4 py-3.5 text-right hidden lg:table-cell">
                            @if($shortfall > 0)
                            <span class="font-semibold text-red-600">-{{ number_format($shortfall) }}</span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right hidden lg:table-cell text-slate-600">{{ number_format($product->buying_price) }}</td>
                        <td class="px-4 py-3.5">
                            @if($product->quantity == 0)
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-700">Out of Stock</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Low Stock</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:underline text-xs font-medium">View →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $products->links() }}</div>
        @else
        <div class="py-20 text-center">
            <div class="text-5xl mb-4">✅</div>
            <h3 class="text-slate-800 font-semibold mb-1">All stock levels are healthy!</h3>
            <p class="text-slate-400 text-sm">No products are currently below their reorder level.</p>
        </div>
        @endif
    </div>
</div>
@endsection