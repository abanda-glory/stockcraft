@extends('layouts.app')

@section('title', 'Stock Movements')
@section('breadcrumb', 'Track all inventory in/out transactions')

@section('content')
<div class="space-y-5 pt-2" x-data="{ modal: false, movType: 'in' }">

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
            <p class="text-xs font-semibold text-emerald-600 uppercase">Total Stock In</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ number_format($totalIn) }}</p>
        </div>

        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5">
            <p class="text-xs font-semibold text-rose-600 uppercase">Total Stock Out</p>
            <p class="text-3xl font-bold text-rose-700 mt-1">{{ number_format($totalOut) }}</p>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

        @if($movements->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs uppercase">Type</th>
                        <th class="text-left px-4 py-3 text-xs uppercase">Product</th>
                        <th class="text-right px-4 py-3 text-xs uppercase">Qty</th>
                        <th class="text-left px-4 py-3 text-xs uppercase">Reason</th>
                        <th class="text-left px-4 py-3 text-xs uppercase">Reference</th>
                        <th class="text-left px-4 py-3 text-xs uppercase">Date</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @foreach($movements as $mv)
                    <tr class="hover:bg-slate-50">

                        {{-- TYPE --}}
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-lg font-semibold
                                {{ $mv->type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ strtoupper($mv->type) }}
                            </span>
                        </td>

                        {{-- PRODUCT --}}
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $mv->product->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $mv->product->sku ?? '' }}</p>
                        </td>

                        {{-- QTY --}}
                        <td class="px-4 py-3 text-right font-bold
                            {{ $mv->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $mv->type === 'in' ? '+' : '-' }}{{ $mv->quantity }}
                        </td>

                        {{-- REASON --}}
                        <td class="px-4 py-3 text-slate-600">
                            {{ $mv->reason ?? '—' }}
                        </td>

                        {{-- REFERENCE --}}
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $mv->reference ?? '—' }}
                        </td>

                        {{-- DATE --}}
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $mv->created_at->format('d M Y H:i') }}
                        </td>

                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t">
            {{ $movements->links() }}
        </div>

        @else
        <div class="py-16 text-center text-slate-400">
            <p class="text-5xl mb-2">📦</p>
            <p>No stock movements yet</p>
        </div>
        @endif

    </div>

    {{-- MODAL BUTTON --}}
    <div class="flex gap-2">
        <button @click="movType='in'; modal=true"
            class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm">
            Stock In
        </button>

        <button @click="movType='out'; modal=true"
            class="bg-rose-600 text-white px-4 py-2 rounded-lg text-sm">
            Stock Out
        </button>
    </div>

</div>
@endsection