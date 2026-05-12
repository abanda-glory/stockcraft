<?php

namespace App\Http\Controllers;

use App\Services\InventoryGeneratorService;
use Illuminate\Http\Request;

class GeneratorController extends Controller
{
    public function __construct(private InventoryGeneratorService $generator) {}

    public function index()
    {
        return view('generator.index');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'business_type'      => 'required|in:supermarket,pharmacy,electronics,restaurant,fashion,hardware',
            'count'              => 'required|integer|min:10|max:500',
            'min_price'          => 'required|numeric|min:0',
            'max_price'          => 'required|numeric|gt:min_price',
            'stock_level'        => 'required|in:low,medium,high',
            'include_categories' => 'nullable|boolean',
            'include_expiry'     => 'nullable|boolean',
        ]);

        $validated['include_categories'] = $request->boolean('include_categories');
        $validated['include_expiry']     = $request->boolean('include_expiry');

        $products = $this->generator->generate(auth()->id(), $validated);

        return redirect()->route('products.index')
            ->with('success', count($products) . ' products generated successfully for your ' . ucfirst($validated['business_type']) . '!');
    }
}
