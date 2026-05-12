<!-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div> -->

@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 pt-2">

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 col-span-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Products</p>
            <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($totalProducts) }}</p>
            <p class="text-xs text-slate-400 mt-1">Active items</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 col-span-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Stock Value</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ number_format($totalStockValue) }}</p>
            <p class="text-xs text-slate-400 mt-1">At cost price</p>
        </div>
        <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5 col-span-1">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Low Stock</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ number_format($lowStock) }}</p>
            <p class="text-xs text-amber-500 mt-1">Need reorder</p>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-200 p-5 col-span-1">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Out of Stock</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($outOfStock) }}</p>
            <p class="text-xs text-red-400 mt-1">Zero quantity</p>
        </div>
        <div class="bg-orange-50 rounded-2xl border border-orange-200 p-5 col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Expiring Soon</p>
            <p class="text-3xl font-bold text-orange-600 mt-1">{{ number_format($expiringSoon) }}</p>
            <p class="text-xs text-orange-400 mt-1">Within 30 days</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LOW STOCK ALERTS --}}
        <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <h2 class="font-semibold text-slate-800 text-sm">Low Stock Alerts</h2>
                </div>
                <a href="{{ route('reports.low-stock') }}" class="text-xs text-indigo-600 hover:underline font-medium">View all →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($lowStockItems as $item)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $item->name }}</p>
                        <p class="text-xs text-slate-400">{{ $item->sku }}
                            @if($item->category)
                            · <span class="font-medium" style="color:{{ $item->category->color }}">{{ $item->category->name }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold
                            {{ $item->quantity == 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $item->quantity }} {{ $item->unit }}
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">min {{ $item->reorder_level }}</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">
                    <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    All stock levels are healthy!
                </div>
                @endforelse
            </div>
        </div>

        {{-- STOCK MOVEMENT CHART --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Stock Movements — Last 7 Days</h2>
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-indigo-500 inline-block"></span>In</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-rose-400 inline-block"></span>Out</span>
                </div>
            </div>
            <div class="p-5">
                <div class="flex items-end gap-3 h-40">
                    @php $maxVal = max(collect($movementsChart)->max('in'), collect($movementsChart)->max('out'), 1); @endphp
                    @foreach($movementsChart as $day)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full flex items-end gap-0.5 h-28">
                            <div class="flex-1 bg-indigo-500 rounded-t-sm transition-all"
                                style="height: {{ ($day['in'] / $maxVal) * 100 }}%"
                                title="In: {{ $day['in'] }}"></div>
                            <div class="flex-1 bg-rose-400 rounded-t-sm transition-all"
                                style="height: {{ ($day['out'] / $maxVal) * 100 }}%"
                                title="Out: {{ $day['out'] }}"></div>
                        </div>
                        <p class="text-xs text-slate-400">{{ $day['date'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- RECENT MOVEMENTS --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Recent Movements</h2>
                <a href="{{ route('movements.index') }}" class="text-xs text-indigo-600 hover:underline font-medium">View all →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentMovements as $mv)
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
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $mv->product->name ?? 'Deleted product' }}</p>
                        <p class="text-xs text-slate-400">{{ $mv->reason ?? ucfirst($mv->type) }} · {{ $mv->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $mv->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $mv->type === 'in' ? '+' : '-' }}{{ $mv->quantity }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">No movements recorded yet.</div>
                @endforelse
            </div>
        </div>

        {{-- CATEGORY BREAKDOWN --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Category Breakdown</h2>
                <a href="{{ route('categories.index') }}" class="text-xs text-indigo-600 hover:underline font-medium">Manage →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($categoryStats as $cat)
                <div class="px-5 py-3 flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $cat->color }}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800">{{ $cat->name }}</p>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1">
                            @php $pct = $categoryStats->max('products_count') > 0 ? ($cat->products_count / $categoryStats->max('products_count')) * 100 : 0; @endphp
                            <div class="h-1.5 rounded-full" style="width:{{ $pct }}%; background-color:{{ $cat->color }}"></div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="text-sm font-bold text-slate-800">{{ $cat->products_count }}</span>
                        <p class="text-xs text-slate-400">products</p>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">No categories yet. <a href="{{ route('generator.index') }}" class="text-indigo-600 hover:underline">Generate inventory</a> to get started.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('generator.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl p-5 flex flex-col items-center gap-2 text-center transition-colors">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span class="text-sm font-semibold">Generate Inventory</span>
        </a>
        <a href="{{ route('products.create') }}" class="bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col items-center gap-2 text-center text-slate-700 transition-colors">
            <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-sm font-semibold">Add Product</span>
        </a>
        <a href="{{ route('movements.index') }}" class="bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col items-center gap-2 text-center text-slate-700 transition-colors">
            <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
            </svg>
            <span class="text-sm font-semibold">Record Movement</span>
        </a>
        <a href="{{ route('reports.index') }}" class="bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col items-center gap-2 text-center text-slate-700 transition-colors">
            <svg class="w-7 h-7 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm font-semibold">View Reports</span>
        </a>
    </div>

</div>
@endsection
<!-- </x-app-layout> -->