@extends('layouts.app')
@section('title', 'Edit Product')
@section('breadcrumb', $product->name)

@section('content')
<div class="max-w-3xl pt-2">
    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-semibold text-slate-800">Product Details</h2>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU <span class="text-red-500">*</span></label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Unit</label>
                            <select name="unit" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                @foreach(['pcs','kg','g','L','ml','box','pack','bottle','bag','roll','set','pair','strip','sachet','tube','crate','bunch','meter','yards'] as $u)
                                <option value="{{ $u }}" {{ old('unit', $product->unit) === $u ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                        <select name="category_id" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="">No category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Expiry Date</label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date', $product->expiry_date?->format('Y-m-d')) }}"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-semibold text-slate-800">Pricing</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Buying Price</label>
                            <input type="number" name="buying_price" value="{{ old('buying_price', $product->buying_price) }}" min="0" step="0.01"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Selling Price</label>
                            <input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" step="0.01"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-semibold text-slate-800">Stock Levels</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Current Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $product->quantity) }}" min="0"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Reorder Level</label>
                            <input type="number" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" min="0"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                @if($product->image)
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm">Current Image</h3>
                    <img src="{{ Storage::url($product->image) }}" class="w-full h-32 object-cover rounded-xl mb-3">
                    <label class="cursor-pointer">
                        <span class="block text-xs text-slate-500 mb-1">Replace image:</span>
                        <input type="file" name="image" accept="image/*" class="w-full text-xs">
                    </label>
                </div>
                @else
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm">Product Image</h3>
                    <label class="block cursor-pointer">
                        <div class="border-2 border-dashed border-slate-300 hover:border-indigo-400 rounded-xl p-5 text-center transition-colors">
                            <svg class="w-7 h-7 text-slate-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs text-slate-400">Upload image</p>
                        </div>
                        <input type="file" name="image" accept="image/*" class="sr-only">
                    </label>
                </div>
                @endif

                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-800 mb-3 text-sm">Status</h3>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <div class="w-10 h-6 bg-slate-200 peer-checked:bg-indigo-600 rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow peer-checked:translate-x-4 transition-transform"></div>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Active</span>
                    </label>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl text-sm transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="block text-center text-slate-500 hover:text-slate-800 text-sm mt-3">Cancel</a>
                </div>

                <div class="bg-white rounded-2xl border border-red-100 p-5">
                    <h3 class="font-semibold text-red-600 mb-3 text-sm">Danger Zone</h3>
                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Permanently delete {{ addslashes($product->name) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full border border-red-300 hover:bg-red-50 text-red-600 font-medium py-2.5 rounded-xl text-sm transition-colors">
                            Delete Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection