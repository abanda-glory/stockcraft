<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

    <div class="min-h-screen flex">

        {{-- SIDEBAR --}}
        <aside class="w-64 bg-slate-900 text-white p-4 space-y-4">
            <h1 class="text-xl font-bold">📦 StockCraft</h1>

            <nav class="space-y-2 text-sm">
                <a href="{{ route('dashboard') }}" class="block hover:bg-slate-800 p-2 rounded">Dashboard</a>
                <a href="{{ route('products.index') }}" class="block hover:bg-slate-800 p-2 rounded">Products</a>
                <a href="{{ route('categories.index') }}" class="block hover:bg-slate-800 p-2 rounded">Categories</a>
                <a href="{{ route('movements.index') }}" class="block hover:bg-slate-800 p-2 rounded">Stock Movements</a>
                <a href="{{ route('reports.index') }}" class="block hover:bg-slate-800 p-2 rounded">Reports</a>
                <a href="{{ route('generator.index') }}" class="block hover:bg-slate-800 p-2 rounded">Generator</a>
            </nav>
        </aside>

        {{-- MAIN AREA --}}
        <div class="flex-1">

            {{-- TOP BAR --}}
            <header class="bg-white shadow px-6 py-4 flex justify-between">
                <div>
                    <h2 class="font-semibold text-lg">@yield('breadcrumb')</h2>
                </div>

                <div>
                    @yield('header-actions')
                </div>
            </header>

            {{-- CONTENT --}}
            <main class="p-6">
                @yield('content')
            </main>

        </div>

    </div>

</body>

</html>