@extends('layouts.app')
@section('title', 'Categories')
@section('breadcrumb', 'Organise your products into categories')

@section('content')
<div class="space-y-5 pt-2"
    x-data="{
        modal: false,
        editing: null,
        name: '',
        color: '#6366f1'
     }">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LIST --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">

                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">All Categories</h2>

                    <button @click="editing = null; name = ''; color = '#6366f1'; modal = true"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                        New Category
                    </button>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                    <div class="px-5 py-4 flex items-center gap-4 hover:bg-slate-50">

                        <span class="w-10 h-10 rounded-xl flex items-center justify-center"
                            style="background-color: {{ $cat->color }}20">
                            <span class="w-4 h-4 rounded-full"
                                style="background-color: {{ $cat->color }}"></span>
                        </span>

                        <div class="flex-1">
                            <p class="font-semibold text-slate-800">{{ $cat->name }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $cat->products_count }} product(s)
                            </p>
                        </div>

                        <div class="flex gap-2">

                            {{-- EDIT --}}
                            <button @click="
                                    editing = {{ $cat->id }};
                                    name = @js($cat->name);
                                    color = '{{ $cat->color }}';
                                    modal = true;
                                "
                                class="p-2 hover:bg-slate-100 rounded-lg">
                                ✏️
                            </button>

                            {{-- DELETE --}}
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                                onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="p-2 hover:bg-red-50 rounded-lg text-red-500">
                                    🗑️
                                </button>
                            </form>

                        </div>

                    </div>
                    @empty
                    <div class="p-10 text-center text-slate-400">
                        No categories yet.
                    </div>
                    @endforelse
                </div>

                @if($categories->hasPages())
                <div class="px-5 py-4 border-t">
                    {{ $categories->links() }}
                </div>
                @endif

            </div>
        </div>

        {{-- QUICK CREATE --}}
        <div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-semibold mb-4">Quick Create</h3>

                <form method="POST" action="{{ route('categories.store') }}">
                    @csrf

                    <input type="text" name="name"
                        class="w-full border rounded-xl px-3 py-2 mb-3"
                        placeholder="Category name">

                    <input type="color" name="color"
                        value="#6366f1"
                        class="w-full h-10 mb-3">

                    <button class="w-full bg-indigo-600 text-white py-2 rounded-xl">
                        Create
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- MODAL --}}
    <div x-show="modal" x-cloak class="fixed inset-0 flex items-center justify-center">

        <div class="absolute inset-0 bg-black/50" @click="modal = false"></div>

        <div class="bg-white p-6 rounded-2xl w-full max-w-sm relative">

            <h2 class="font-bold mb-4" x-text="editing ? 'Edit Category' : 'New Category'"></h2>

            <form method="POST"
                :action="editing ? '/categories/' + editing : '{{ route('categories.store') }}'">

                <template x-if="editing">
                    @method('PUT')
                </template>

                @csrf

                <input type="text"
                    name="name"
                    x-model="name"
                    class="w-full border rounded-xl px-3 py-2 mb-3"
                    placeholder="Category name">

                <input type="color"
                    name="color"
                    x-model="color"
                    class="w-full h-10 mb-4">

                <div class="flex gap-2">
                    <button type="button"
                        @click="modal = false"
                        class="flex-1 border py-2 rounded-xl">
                        Cancel
                    </button>

                    <button class="flex-1 bg-indigo-600 text-white py-2 rounded-xl">
                        Save
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection