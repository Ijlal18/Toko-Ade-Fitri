<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StockMovement::with(['product', 'user']);

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();

        return Inertia::render('Stock/Index', [
            'movements' => $movements,
            'products' => $products,
            'filters' => $request->only(['product_id', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::findOrFail($validated['product_id']);

            if ($validated['type'] === 'in') {
                $product->increment('stock', abs($validated['quantity']));
            } elseif ($validated['type'] === 'out') {
                $product->decrement('stock', abs($validated['quantity']));
            } else {
                // Adjustment: quantity represents new target stock or relative delta
                $product->update(['stock' => max(0, $validated['quantity'])]);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'],
                'reference' => $validated['reference'] ?? null,
            ]);
        });

        return back()->with('success', 'Penyesuaian stok berhasil dicatat!');
    }
}
