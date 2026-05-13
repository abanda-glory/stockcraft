@extends('layouts.app')
@section('title', 'Products')
@section('breadcrumb', 'Manage your inventory items')

@section('header-actions')
<a href="{{ route('products.create') }}" class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Add Product
</a>
@endsection

@section('content')
<div class="space-y-4 pt-2" x-data="{ importModal: false }">

    {{-- FILTERS --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or SKU…"
                    class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
            </div>
            <div>
                <select name="category" class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">All Status</option>
                    <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                </select>
            </div>
            <div>
                <select name="business_type" class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">All Types</option>
                    @foreach(['supermarket','pharmacy','electronics','restaurant','fashion','hardware'] as $bt)
                    <option value="{{ $bt }}" {{ request('business_type') === $bt ? 'selected' : '' }}>{{ ucfirst($bt) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">Filter</button>
            @if(request()->hasAny(['search','category','status','business_type']))
            <a href="{{ route('products.index') }}" class="text-slate-500 hover:text-slate-800 px-3 py-2 text-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- ACTIONS BAR --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $products->total() }} products found</p>
        <div class="flex items-center gap-2">
            <button @click="importModal = true" class="flex items-center gap-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
                Import CSV
            </button>
            <a href="{{ route('products.export') }}" class="flex items-center gap-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3 3m0 0l-3-3m3 3V10" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- PRODUCTS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        @if($products->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Category</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Buy Price</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Sell Price</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden xl:table-cell">Expiry</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                                @else
                                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0 text-slate-400 text-lg">
                                    📦
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-medium text-slate-900 truncate max-w-48">{{ $product->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $product->sku }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            @if($product->category)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                style="background-color:{{ $product->category->color }}20; color:{{ $product->category->color }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color:{{ $product->category->color }}"></span>
                                {{ $product->category->name }}
                            </span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-bold {{ $product->quantity == 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-amber-600' : 'text-slate-800') }}">
                                {{ number_format($product->quantity) }}
                            </span>
                            <span class="text-xs text-slate-400"> {{ $product->unit }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right text-slate-600 hidden lg:table-cell">{{ number_format($product->buying_price) }}</td>
                        <td class="px-4 py-3.5 text-right font-medium text-slate-800 hidden lg:table-cell">{{ number_format($product->selling_price) }}</td>
                        <td class="px-4 py-3.5 hidden xl:table-cell">
                            @if($product->expiry_date)
                            <span class="text-xs {{ $product->isExpired() ? 'text-red-600 font-medium' : ($product->isExpiringSoon() ? 'text-orange-600 font-medium' : 'text-slate-500') }}">
                                {{ $product->expiry_date->format('d M Y') }}
                                @if($product->isExpired()) <span class="ml-1">(Expired)</span>
                                @elseif($product->isExpiringSoon()) <span class="ml-1">(Soon)</span>
                                @endif
                            </span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($product->quantity == 0)
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-700">Out of Stock</span>
                            @elseif($product->isLowStock())
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Low Stock</span>
                            @elseif($product->isExpired())
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-700">Expired</span>
                            @elseif($product->isExpiringSoon())
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-orange-100 text-orange-700">Expiring</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">Good</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1 justify-end">
                                <a href="{{ route('products.show', $product) }}" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-800 transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-indigo-600 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-500 hover:text-red-600 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @else
        <div class="py-20 text-center">
            <div class="text-5xl mb-4">📦</div>
            <h3 class="text-slate-800 font-semibold mb-1">No products yet</h3>
            <p class="text-slate-400 text-sm mb-5">Generate your first inventory batch or add products manually.</p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('generator.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">Generate Inventory</a>
                <a href="{{ route('products.create') }}" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">Add Manually</a>
            </div>
        </div>
        @endif
    </div>

    {{-- IMPORT MODAL --}}
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="importModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="font-bold text-slate-900 mb-1">Import Products via CSV</h3>
            <p class="text-sm text-slate-500 mb-4">Columns: Name, SKU, Category, Buying Price, Selling Price, Quantity, Reorder Level, Unit, Expiry Date, Business Type</p>
            <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="csv_file" accept=".csv,.txt" required
                    class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm mb-4">
                <div class="flex gap-3">
                    <button type="button" @click="importModal = false" class="flex-1 border border-slate-300 rounded-xl py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 text-sm font-medium transition-colors">Import</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection