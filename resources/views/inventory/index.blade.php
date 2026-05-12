@extends('layouts.app')
@section('title', 'Generate Inventory')
@section('breadcrumb', 'Auto-create realistic stock for your business type')

@section('content')
<div class="max-w-4xl pt-2" x-data="generator()">

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- FORM --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ route('generator.generate') }}" @submit="loading = true">
                @csrf
                <div class="space-y-5">

                    {{-- Business Type --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            Business Type
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach([
                            ['supermarket', '🛒', 'Supermarket'],
                            ['pharmacy', '💊', 'Pharmacy'],
                            ['electronics', '📱', 'Electronics'],
                            ['restaurant', '🍽️', 'Restaurant'],
                            ['fashion', '👗', 'Fashion'],
                            ['hardware', '🔧', 'Hardware'],
                            ] as [$val, $emoji, $label])
                            <label class="cursor-pointer">
                                <input type="radio" name="business_type" value="{{ $val }}"
                                    x-model="businessType" class="sr-only" {{ old('business_type') === $val ? 'checked' : '' }}>
                                <div :class="businessType === '{{ $val }}'
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'"
                                    class="border-2 rounded-xl p-3 text-center transition-all">
                                    <div class="text-2xl mb-1">{{ $emoji }}</div>
                                    <div class="text-xs font-semibold">{{ $label }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('business_type')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                    </div>

                    {{-- Count & Stock Level --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            Volume & Stock Level
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Number of Products
                                    <span class="font-bold text-indigo-600" x-text="count"></span>
                                </label>
                                <input type="range" name="count" min="10" max="500" step="10"
                                    x-model="count" value="{{ old('count', 50) }}"
                                    class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                <div class="flex justify-between text-xs text-slate-400 mt-1">
                                    <span>10</span><span>250</span><span>500</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Stock Level</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach(['low' => ['Low','1-25 units','text-rose-600','bg-rose-50','border-rose-300'], 'medium' => ['Medium','25-150 units','text-amber-600','bg-amber-50','border-amber-300'], 'high' => ['High','100-500 units','text-emerald-600','bg-emerald-50','border-emerald-300']] as $val => [$label,$sub,$tc,$bg,$bc])
                                    <label class="cursor-pointer">
                                        <input type="radio" name="stock_level" value="{{ $val }}"
                                            x-model="stockLevel" class="sr-only" {{ old('stock_level', 'medium') === $val ? 'checked' : '' }}>
                                        <div :class="stockLevel === '{{ $val }}' ? '{{ $bc }} {{ $bg }}' : 'border-slate-200 bg-white'"
                                            class="border-2 rounded-xl p-3 text-center transition-all">
                                            <p class="text-sm font-semibold {{ $tc }}">{{ $label }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $sub }}</p>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Price Range --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            Price Range (XAF / your currency)
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Min Buying Price</label>
                                <input type="number" name="min_price" value="{{ old('min_price', 500) }}" min="0" step="100"
                                    class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                @error('min_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Max Buying Price</label>
                                <input type="number" name="max_price" value="{{ old('max_price', 50000) }}" min="0" step="100"
                                    class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                @error('max_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Selling price is auto-calculated with a realistic markup per business type.</p>
                    </div>

                    {{-- Options --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                            Options
                        </h2>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" name="include_categories" value="1"
                                        x-model="includeCategories" class="sr-only peer"
                                        {{ old('include_categories', true) ? 'checked' : '' }}>
                                    <div class="w-10 h-6 bg-slate-200 peer-checked:bg-indigo-600 rounded-full transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow peer-checked:translate-x-4 transition-transform"></div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Include Categories</p>
                                    <p class="text-xs text-slate-400">Auto-create and assign product categories</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" name="include_expiry" value="1"
                                        x-model="includeExpiry" class="sr-only peer"
                                        {{ old('include_expiry', true) ? 'checked' : '' }}>
                                    <div class="w-10 h-6 bg-slate-200 peer-checked:bg-indigo-600 rounded-full transition-colors"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow peer-checked:translate-x-4 transition-transform"></div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Include Expiry Dates</p>
                                    <p class="text-xs text-slate-400">For perishables (pharmacy, supermarket, restaurant)</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        :disabled="!businessType || loading"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-4 rounded-2xl transition-colors flex items-center justify-center gap-3 text-base">
                        <template x-if="!loading">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </template>
                        <template x-if="loading">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </template>
                        <span x-text="loading ? 'Generating...' : 'Generate ' + count + ' Products'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- PREVIEW PANEL --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sticky top-24">
                <h3 class="font-semibold text-slate-800 mb-4 text-sm">Generation Preview</h3>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Business</span>
                        <span class="font-semibold text-slate-800 capitalize" x-text="businessType || '—'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Products</span>
                        <span class="font-semibold text-slate-800" x-text="count"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Stock Level</span>
                        <span class="font-semibold text-slate-800 capitalize" x-text="stockLevel"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Categories</span>
                        <span :class="includeCategories ? 'text-emerald-600' : 'text-slate-400'" class="font-semibold" x-text="includeCategories ? 'Yes' : 'No'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Expiry Dates</span>
                        <span :class="includeExpiry ? 'text-emerald-600' : 'text-slate-400'" class="font-semibold" x-text="includeExpiry ? 'Yes' : 'No'"></span>
                    </div>
                </div>

                <hr class="my-4 border-slate-100">

                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">What gets generated</p>
                <ul class="space-y-2 text-xs text-slate-600">
                    @foreach(['Realistic product names per business type', 'Auto-generated unique SKUs', 'Buying + selling prices with markup', 'Random stock quantities in chosen range', 'Reorder levels at 15% of max stock', 'Business-appropriate units (kg, pcs, box…)', 'Expiry dates for perishable categories'] as $feature)
                    <li class="flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>

                <hr class="my-4 border-slate-100">

                <p class="text-xs text-slate-400 text-center">Generation typically takes 2–10 seconds depending on count.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generator() {
        return {
            businessType: '{{ old('
            business_type ', '
            ') }}',
            count: {
                {
                    old('count', 50)
                }
            },
            stockLevel: '{{ old('
            stock_level ', '
            medium ') }}',
            includeCategories: {
                {
                    old('include_categories', 'true')
                }
            },
            includeExpiry: {
                {
                    old('include_expiry', 'true')
                }
            },
            loading: false,
        }
    }
</script>
@endpush
@endsection