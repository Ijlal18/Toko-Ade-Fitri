<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Transaction::with(['customer', 'user', 'items.product']);

        if ($search = $request->input('search')) {
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($method = $request->input('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $customers = Customer::orderBy('name', 'asc')->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'customers' => $customers,
            'filters' => $request->only(['search', 'payment_method', 'date']),
        ]);
    }

    public function show(Transaction $transaction): Response
    {
        $transaction->load(['customer', 'user', 'items.product.category']);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:cash,qris'],
            'status' => ['required', 'in:completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($transaction, $validated, $request) {
            $oldStatus = $transaction->status;
            $newStatus = $validated['status'];

            if ($oldStatus === 'completed' && $newStatus === 'cancelled') {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => $request->user()?->id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'reason' => 'Pembatalan Transaksi',
                            'reference' => $transaction->invoice_number,
                        ]);
                    }
                }
            }

            $transaction->update($validated);
        });

        return back()->with('success', 'Data transaksi berhasil diperbarui!');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction, $request) {
            if ($transaction->status === 'completed') {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => $request->user()?->id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'reason' => 'Penghapusan Transaksi',
                            'reference' => $transaction->invoice_number,
                        ]);
                    }
                }
            }

            $transaction->items()->delete();
            $transaction->delete();
        });

        return back()->with('success', 'Transaksi berhasil dihapus dan stok produk telah dikembalikan!');
    }
}
