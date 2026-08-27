<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name', 'asc')
            ->get();

        $categories = Category::orderBy('name', 'asc')->get();
        $customers = Customer::orderBy('name', 'asc')->get();

        return Inertia::render('Pos/Index', [
            'products' => $products,
            'categories' => $categories,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,qris'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.price_type' => ['required', 'in:retail,wholesale'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated, $request) {
                $subtotalSum = 0;
                $itemsToCreate = [];

                foreach ($validated['items'] as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);

                    if ($product->stock < $itemData['quantity']) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi. Sisa stok tersedia: {$product->stock} {$product->unit}.");
                    }

                    $subtotal = $itemData['unit_price'] * $itemData['quantity'];
                    $subtotalSum += $subtotal;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'price_type' => $itemData['price_type'],
                        'purchase_price' => $product->purchase_price,
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $subtotal,
                    ];
                }

                $discount = (float) ($validated['discount_amount'] ?? 0);
                $tax = (float) ($validated['tax_amount'] ?? 0);
                $grandTotal = max(0, $subtotalSum - $discount + $tax);
                $paid = (float) $validated['paid_amount'];
                $change = max(0, $paid - $grandTotal);

                // Generate unique invoice number: INV-YYYYMMDD-XXXX
                $datePrefix = 'INV-'.now()->format('Ymd').'-';
                $lastToday = Transaction::where('invoice_number', 'like', $datePrefix.'%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextSequence = 1;
                if ($lastToday) {
                    $parts = explode('-', $lastToday->invoice_number);
                    $nextSequence = ((int) end($parts)) + 1;
                }

                $invoiceNumber = $datePrefix.str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

                $txn = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => $request->user()?->id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'total_amount' => $subtotalSum,
                    'discount_amount' => $discount,
                    'tax_amount' => $tax,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paid,
                    'change_amount' => $change,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($itemsToCreate as $item) {
                    $txn->items()->create($item);

                    // Deduct stock & log movement
                    $p = Product::find($item['product_id']);
                    $p->decrement('stock', $item['quantity']);

                    StockMovement::create([
                        'product_id' => $p->id,
                        'user_id' => $request->user()?->id,
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'reason' => 'Penjualan Kasir POS',
                        'reference' => $invoiceNumber,
                    ]);
                }

                // Award points if customer exists (1 point per 10,000 rupiah)
                if ($validated['customer_id']) {
                    $customer = Customer::find($validated['customer_id']);
                    if ($customer) {
                        $addedPoints = floor($grandTotal / 10000);
                        $customer->increment('points', $addedPoints);
                    }
                }

                return $txn->load(['items.product', 'customer', 'user']);
            });

            return back()->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Pembayaran berhasil diverifikasi!',
                ],
                'success' => 'Transaksi berhasil diproses!',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            return back()->with([
                'toast' => [
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
