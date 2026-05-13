<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', auth()->id())
            ->withCount('products')
            ->orderBy('name')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        Category::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'name' => $data['name']
            ],
            [
                'color' => $data['color']
            ]
        );

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        abort_if($category->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        abort_if($category->user_id !== auth()->id(), 403);

        $category->delete();

        return back()->with('success', 'Category deleted. Products unlinked.');
    }
}
