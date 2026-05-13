@extends('layouts.app')
@section('title', $product->name)
@section('breadcrumb', 'SKU: ' . $product->sku)

@section('header-actions')
<a href="{{ route('products.edit', $product) }}" class="flex items-center gap-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
    </svg>
    Edit
</a>
@endsection

@section('content')
<div class="space-y-5 pt-2" x-data="{ movementModal: false, movType: 'in' }">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: PRODUCT INFO --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <div class="flex items-start gap-5">
                    @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" class="w-24 h-24 rounded-xl object-cover flex-shrink-0">
                    @else
                    <div class="w-24 h-24 rounded-xl bg-slate-100 flex items-center justify-center text-5xl flex-shrink-0">📦</div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ $product->name }}</h2>
                                <p class="text-slate-400 text-sm mt-0.5">{{ $product->sku }}</p>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                @if($product->quantity == 0)
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-700">Out of Stock</span>
                                @elseif($product->isLowStock())
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">Low Stock</span>
                                @else
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">In Stock</span>
                                @endif
                                @if($product->isExpired())
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-700">Expired</span>
                                @elseif($product->isExpiringSoon())
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-orange-100 text-orange-700">Expiring Soon</span>
                                @endif
                            </div>
                        </div>
                        @if($product->category)
                        <span class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-1 rounded-full text-xs font-medium"
                            style="background:{{ $product->category->color }}20; color:{{ $product->category->color }}">
                            <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $product->category->color }}"></span>
                            {{ $product->category->name }}
                        </span>
                        @endif
                        @if($product->description)
                        <p class="text-slate-500 text-sm mt-3">{{ $product->description }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- STATS GRID --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
                    <p class="text-2xl font-bold text-slate-900">{{ number_format($product->quantity) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $product->unit }} in stock</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($product->stock_value) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Stock value</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ number_format($product->selling_price) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Selling price</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
                    <p class="text-2xl font-bold text-slate-700">{{ number_format($product->profit_margin, 1) }}%</p>
                    <p class="text-xs text-slate-500 mt-0.5">Profit margin</p>
                </div>
            </div>

            {{-- MOVEMENT LOG --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <h3 class="font-semibold text-slate-800 text-sm">Stock Movement History</h3>
                    <button @click="movementModal = true"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Record Movement
                    </button>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($movements as $mv)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $mv->type === 'in' ? 'bg-emerald-100' : 'bg-rose-100' }}">
                            <svg class="w-4 h-4 {{ $mv->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($mv->type === 'in')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                @endif
                            </svg>
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-800">{{ ucfirst($mv->type) }} — {{ $mv->reason ?? 'No reason given' }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $mv->created_at->format('d M Y, H:i') }}
                                @if($mv->reference) · Ref: {{ $mv->reference }} @endif
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0 flex items-center gap-2">
                            <span class="text-sm font-bold {{ $mv->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $mv->type === 'in' ? '+' : '-' }}{{ number_format($mv->quantity) }}
                            </span>
                            <form method="POST" action="{{ route('movements.destroy', $mv) }}" onsubmit="return confirm('Delete and reverse this movement?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 text-slate-300 hover:text-red-500 transition-colors" title="Delete & reverse">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="py-10 text-center text-slate-400 text-sm">No movements recorded for this product yet.</div>
                    @endforelse
                </div>
                @if($movements->hasPages())
                <div class="px-5 py-3 border-t border-slate-100">{{ $movements->links() }}</div>
                @endif
            </div>
        </div>

        {{-- RIGHT: DETAILS --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-800 mb-4 text-sm">Product Info</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Buying Price</dt>
                        <dd class="font-medium text-slate-800">{{ number_format($product->buying_price) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Selling Price</dt>
                        <dd class="font-medium text-slate-800">{{ number_format($product->selling_price) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Unit</dt>
                        <dd class="font-medium text-slate-800">{{ $product->unit }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Reorder At</dt>
                        <dd class="font-medium text-slate-800">{{ $product->reorder_level }} {{ $product->unit }}</dd>
                    </div>
                    @if($product->expiry_date)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Expires</dt>
                        <dd class="font-medium {{ $product->isExpired() ? 'text-red-600' : ($product->isExpiringSoon() ? 'text-orange-600' : 'text-slate-800') }}">
                            {{ $product->expiry_date->format('d M Y') }}
                        </dd>
                    </div>
                    @endif
                    @if($product->business_type)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Business Type</dt>
                        <dd class="font-medium text-slate-800 capitalize">{{ $product->business_type }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Added</dt>
                        <dd class="font-medium text-slate-800">{{ $product->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-800 mb-4 text-sm">Quick Movement</h3>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <button @click="movType = 'in'; movementModal = true"
                        class="flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Stock In
                    </button>
                    <button @click="movType = 'out'; movementModal = true"
                        class="flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium py-2.5 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                        Stock Out
                    </button>
                </div>
                <p class="text-xs text-slate-400 text-center">Current: <strong class="text-slate-700">{{ number_format($product->quantity) }} {{ $product->unit }}</strong></p>
            </div>
        </div>
    </div>

    {{-- MOVEMENT MODAL --}}
    <div x-show="movementModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="movementModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="font-bold text-slate-900 mb-1" x-text="movType === 'in' ? 'Record Stock In' : 'Record Stock Out'"></h3>
            <p class="text-sm text-slate-500 mb-5">{{ $product->name }}</p>
            <form method="POST" action="{{ route('movements.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="type" :value="movType">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" min="1" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Reason</label>
                        <select name="reason" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <template x-if="movType === 'in'">
                                <span>
                                    <option>Purchase/Restock</option>
                                    <option>Return from Customer</option>
                                    <option>Transfer In</option>
                                    <option>Initial Stock</option>
                                    <option>Other</option>
                                </span>
                            </template>
                            <template x-if="movType === 'out'">
                                <span>
                                    <option>Sale</option>
                                    <option>Damaged/Expired</option>
                                    <option>Return to Supplier</option>
                                    <option>Transfer Out</option>
                                    <option>Internal Use</option>
                                    <option>Other</option>
                                </span>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Reference (optional)</label>
                        <input type="text" name="reference" placeholder="Invoice #, PO #, etc."
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="movementModal = false" class="flex-1 border border-slate-300 rounded-xl py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit"
                            :class="movType === 'in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                            class="flex-1 text-white rounded-xl py-2.5 text-sm font-medium transition-colors"
                            x-text="movType === 'in' ? 'Record In' : 'Record Out'"></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection