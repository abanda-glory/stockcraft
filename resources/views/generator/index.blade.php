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
                        <h2 class="font-semibold text-slate-800 mb-4">Business Type</h2>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach([
                            ['supermarket','🛒','Supermarket'],
                            ['pharmacy','💊','Pharmacy'],
                            ['electronics','📱','Electronics'],
                            ['restaurant','🍽️','Restaurant'],
                            ['fashion','👗','Fashion'],
                            ['hardware','🔧','Hardware'],
                            ] as [$val,$emoji,$label])

                            <label class="cursor-pointer">
                                <input type="radio"
                                    name="business_type"
                                    value="{{ $val }}"
                                    x-model="businessType"
                                    class="sr-only">

                                <div class="border-2 rounded-xl p-3 text-center transition-all"
                                    :class="businessType === '{{ $val }}'
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'">

                                    <div class="text-2xl mb-1">{{ $emoji }}</div>
                                    <div class="text-xs font-semibold">{{ $label }}</div>
                                </div>
                            </label>

                            @endforeach
                        </div>

                        @error('business_type')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- COUNT --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-800 mb-4">Volume & Stock Level</h2>

                        <div>
                            <label class="text-sm font-medium text-slate-700">
                                Number of Products:
                                <span class="text-indigo-600 font-bold" x-text="count"></span>
                            </label>

                            <input type="range"
                                name="count"
                                min="10"
                                max="500"
                                step="10"
                                x-model="count"
                                class="w-full accent-indigo-600">
                        </div>

                        <div class="grid grid-cols-3 gap-2 mt-4">
                            @foreach(['low','medium','high'] as $level)
                            <label class="cursor-pointer">
                                <input type="radio"
                                    name="stock_level"
                                    value="{{ $level }}"
                                    x-model="stockLevel"
                                    class="sr-only">

                                <div class="border-2 rounded-xl p-2 text-center"
                                    :class="stockLevel === '{{ $level }}'
                                            ? 'border-indigo-500 bg-indigo-50'
                                            : 'border-slate-200'">

                                    <p class="text-sm font-semibold capitalize">{{ $level }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- PRICE RANGE --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="font-semibold text-slate-800 mb-4">Price Range</h2>

                        <div class="grid grid-cols-2 gap-4">
                            <input type="number"
                                name="min_price"
                                value="{{ old('min_price', 500) }}"
                                class="border rounded-xl p-2">

                            <input type="number"
                                name="max_price"
                                value="{{ old('max_price', 50000) }}"
                                class="border rounded-xl p-2">
                        </div>
                    </div>

                    {{-- OPTIONS --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">

                        <label class="flex items-center gap-3">
                            <input type="checkbox"
                                name="include_categories"
                                value="1"
                                x-model="includeCategories">

                            Include Categories
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox"
                                name="include_expiry"
                                value="1"
                                x-model="includeExpiry">

                            Include Expiry Dates
                        </label>
                    </div>

                    {{-- SUBMIT --}}
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 rounded-xl"
                        :disabled="loading">

                        <span x-text="loading ? 'Generating...' : 'Generate Products'"></span>
                    </button>

                </div>
            </form>
        </div>

        {{-- PREVIEW --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border p-5 sticky top-20">

                <h3 class="font-semibold mb-4">Preview</h3>

                <p>Business: <span x-text="businessType"></span></p>
                <p>Count: <span x-text="count"></span></p>
                <p>Stock: <span x-text="stockLevel"></span></p>
                <p>Categories: <span x-text="includeCategories ? 'Yes' : 'No'"></span></p>
                <p>Expiry: <span x-text="includeExpiry ? 'Yes' : 'No'"></span></p>

            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function generator() {
        return {
            businessType: @json(old('business_type', 'supermarket')),
            count: @json(old('count', 50)),
            stockLevel: @json(old('stock_level', 'medium')),
            includeCategories: @json(old('include_categories', true)),
            includeExpiry: @json(old('include_expiry', true)),
            loading: false,
        }
    }
</script>
@endpush
@endsection