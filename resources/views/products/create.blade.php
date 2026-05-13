@extends('layouts.app')
@section('title', 'Add Product')
@section('breadcrumb', 'Create a new inventory item')

@section('content')
<div class="max-w-3xl pt-2">
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- MAIN DETAILS --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-semibold text-slate-800">Product Details</h2>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                            placeholder="e.g. Paracetamol 500mg x16">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU <span class="text-red-500">*</span></label>
                            <input type="text" name="sku" value="{{ old('sku') }}" required
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                placeholder="e.g. PH-PARA-0042">
                            @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Unit</label>
                            <select name="unit" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                @foreach(['pcs','kg','g','L','ml','box','pack','bottle','bag','roll','set','pair','strip','sachet','tube','crate','bunch','meter','yards'] as $u)
                                <option value="{{ $u }}" {{ old('unit','pcs') === $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                        <select name="category_id" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none"
                            placeholder="Optional product description…">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Expiry Date</label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                {{-- PRICING --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-semibold text-slate-800">Pricing</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Buying Price <span class="text-red-500">*</span></label>
                            <input type="number" name="buying_price" value="{{ old('buying_price', 0) }}" min="0" step="0.01" required
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('buying_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Selling Price <span class="text-red-500">*</span></label>
                            <input type="number" name="selling_price" value="{{ old('selling_price', 0) }}" min="0" step="0.01" required
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('selling_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- STOCK --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-semibold text-slate-800">Stock Levels</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Current Quantity <span class="text-red-500">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0" required
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Reorder Level <span class="text-red-500">*</span></label>
                            <input type="number" name="reorder_level" value="{{ old('reorder_level', 10) }}" min="0" required
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <p class="text-xs text-slate-400 mt-1">Alert when stock falls below this</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm">Product Image</h3>
                    <label class="block cursor-pointer">
                        <div class="border-2 border-dashed border-slate-300 hover:border-indigo-400 rounded-xl p-6 text-center transition-colors">
                            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs text-slate-500">Click to upload</p>
                            <p class="text-xs text-slate-400 mt-1">PNG, JPG up to 2MB</p>
                        </div>
                        <input type="file" name="image" accept="image/*" class="sr-only">
                    </label>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm">Publish</h3>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                        Save Product
                    </button>
                    <a href="{{ route('products.index') }}" class="block text-center text-slate-500 hover:text-slate-800 text-sm mt-3 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection