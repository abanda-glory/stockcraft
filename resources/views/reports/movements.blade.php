@extends('layouts.app')
@section('title', 'Movement Report')
@section('breadcrumb', 'Stock in/out history by date range')

@section('content')
<div class="space-y-5 pt-2">

    {{-- DATE FILTER --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">Apply</button>
            <div class="flex gap-2">
                @foreach([['Last 7 days', now()->subDays(7)->format('Y-m-d'), now()->format('Y-m-d')],['Last 30 days', now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d')],['This month', now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')]] as [$label, $f, $t])
                <a href="{{ route('reports.movements', ['from' => $f, 'to' => $t]) }}"
                    class="border border-slate-300 hover:bg-slate-50 text-slate-600 px-3 py-2 rounded-xl text-xs font-medium transition-colors">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </form>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Total In</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ number_format($stockIn) }}</p>
            <p class="text-xs text-emerald-500 mt-1">Units received in period</p>
        </div>
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5">
            <p class="text-xs font-semibold text-rose-600 uppercase tracking-wide">Total Out</p>
            <p class="text-3xl font-bold text-rose-700 mt-1">{{ number_format($stockOut) }}</p>
            <p class="text-xs text-rose-500 mt-1">Units dispatched in period</p>
        </div>
    </div>

    {{-- MOVEMENTS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">
                Movements: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </h2>
            <span class="text-xs text-slate-400">{{ $movements->total() }} records</span>
        </div>
        @if($movements->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Product</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Category</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Reason</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($movements as $mv)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold
                                {{ $mv->type === 'in' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ strtoupper($mv->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $mv->product->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $mv->product->sku ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($mv->product?->category)
                            <span class="text-xs font-medium" style="color:{{ $mv->product->category->color }}">{{ $mv->product->category->name }}</span>
                            @else <span class="text-slate-300 text-xs">—</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $mv->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $mv->type === 'in' ? '+' : '-' }}{{ number_format($mv->quantity) }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 hidden lg:table-cell">{{ $mv->reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $mv->created_at->format('d M Y') }}
                            <span class="text-slate-400">{{ $mv->created_at->format('H:i') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">{{ $movements->links() }}</div>
        @else
        <div class="py-16 text-center">
            <div class="text-5xl mb-3">📭</div>
            <h3 class="text-slate-800 font-semibold mb-1">No movements in this period</h3>
            <p class="text-slate-400 text-sm">Try expanding your date range.</p>
        </div>
        @endif
    </div>

</div>
@endsection