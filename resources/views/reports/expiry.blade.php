@extends('layouts.app')
@section('title', 'Expiry Report')
@section('breadcrumb', 'Track expiring and expired products')

@section('content')
<div class="space-y-6 pt-2">

    {{-- DAYS FILTER --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <span class="text-sm font-medium text-slate-700">Show items expiring within:</span>
            @foreach([7, 14, 30, 60, 90] as $d)
            <a href="{{ route('reports.expiry', ['days' => $d]) }}"
                class="px-3 py-1.5 rounded-xl text-sm font-medium transition-colors border
                   {{ $days == $d ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                {{ $d }} days
            </a>
            @endforeach
        </form>
    </div>

    {{-- EXPIRING SOON --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-orange-50">
            <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
            <h2 class="font-semibold text-orange-800 text-sm">Expiring Within {{ $days }} Days ({{ $expiringSoon->count() }} items)</h2>
        </div>
        @if($expiringSoon->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Category</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Expiry Date</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Days Left</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Stock Value</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($expiringSoon as $product)
                    @php $daysLeft = now()->diffInDays($product->expiry_date, false); @endphp
                    <tr class="hover:bg-orange-50/50 transition-colors">
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
                        <td class="px-4 py-3.5 text-right font-semibold text-slate-700">{{ number_format($product->quantity) }} {{ $product->unit }}</td>
                        <td class="px-4 py-3.5 text-orange-700 font-medium">{{ $product->expiry_date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-bold
                                {{ $daysLeft <= 7 ? 'bg-red-100 text-red-700' : ($daysLeft <= 14 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ $daysLeft }} days
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right text-slate-600 hidden lg:table-cell">{{ number_format($product->stock_value) }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:underline text-xs font-medium">View →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $expiringSoon->links() }}</div>
        @else
        <div class="py-10 text-center text-slate-400 text-sm">
            No products expiring within {{ $days }} days.
        </div>
        @endif
    </div>

    {{-- ALREADY EXPIRED --}}
    @if($expired->count())
    <div class="bg-white rounded-2xl border border-red-200 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-red-100 bg-red-50">
            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <h2 class="font-semibold text-red-800 text-sm">Already Expired ({{ $expired->count() }} items shown)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Category</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Expired On</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Days Ago</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($expired as $product)
                    <tr class="hover:bg-red-50/50 transition-colors">
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
                        <td class="px-4 py-3.5 text-right font-semibold text-slate-700">{{ number_format($product->quantity) }} {{ $product->unit }}</td>
                        <td class="px-4 py-3.5 text-red-700 font-medium">{{ $product->expiry_date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5 text-xs text-red-600 font-semibold">{{ now()->diffInDays($product->expiry_date) }} days ago</td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:underline text-xs font-medium">View →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection