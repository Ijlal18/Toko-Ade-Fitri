<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SupplierInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = SupplierInvoice::with(['supplier', 'items.category', 'items.product', 'payments']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $metrics = [
            'total_amount' => (float) SupplierInvoice::sum('total_amount'),
            'paid_amount' => (float) SupplierInvoice::sum('paid_amount'),
            'unpaid_amount' => (float) SupplierInvoice::selectRaw('SUM(total_amount - paid_amount) as remaining')->value('remaining') ?? 0,
            'due_soon_count' => SupplierInvoice::where('status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->where('due_date', '<=', Carbon::now()->addDays(7))
                ->count(),
        ];

        $suppliers = Supplier::orderBy('name', 'asc')->get(['id', 'name']);
        $categories = Category::orderBy('name', 'asc')->get(['id', 'name']);
        $products = Product::where('is_active', true)->with('category')->orderBy('name', 'asc')->get(['id', 'name', 'category_id', 'purchase_price']);

        return Inertia::render('SupplierInvoices/Index', [
            'invoices' => $invoices,
            'suppliers' => $suppliers,
            'categories' => $categories,
            'products' => $products,
            'metrics' => $metrics,
            'filters' => $request->only(['search', 'status', 'supplier_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'due_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.category_id' => ['nullable', 'exists:categories,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'category_id' => $item['category_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $paidAmount = (float) ($validated['paid_amount'] ?? 0);
            if ($paidAmount >= $totalAmount) {
                $paidAmount = $totalAmount;
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partially_paid';
            } else {
                $status = 'unpaid';
            }

            $invoice = SupplierInvoice::create([
                'invoice_number' => $validated['invoice_number'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_date' => $validated['due_date'] ?? null,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            if ($paidAmount > 0) {
                $invoice->payments()->create([
                    'payment_amount' => $paidAmount,
                    'payment_date' => Carbon::now()->format('Y-m-d'),
                    'payment_method' => 'cash',
                    'notes' => 'Pembayaran awal saat penerimaan tagihan',
                ]);
            }
        });

        return back()->with('success', 'Tagihan supplier berhasil ditambahkan!');
    }

    public function update(Request $request, SupplierInvoice $supplierInvoice): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.category_id' => ['nullable', 'exists:categories,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($supplierInvoice, $validated) {
            $totalAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'category_id' => $item['category_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $existingPaymentsSum = (float) $supplierInvoice->payments()->sum('payment_amount');
            $paidAmount = min($totalAmount, $existingPaymentsSum);

            if ($paidAmount >= $totalAmount && $totalAmount > 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partially_paid';
            } else {
                $status = 'unpaid';
            }

            $supplierInvoice->update([
                'invoice_number' => $validated['invoice_number'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_date' => $validated['due_date'] ?? null,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            $supplierInvoice->items()->delete();
            foreach ($itemsData as $itemData) {
                $supplierInvoice->items()->create($itemData);
            }
        });

        return back()->with('success', 'Data tagihan supplier berhasil diperbarui!');
    }

    public function payInstallment(Request $request, SupplierInvoice $supplierInvoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris,other'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($supplierInvoice, $validated) {
            $paymentAmount = (float) $validated['payment_amount'];

            $supplierInvoice->payments()->create([
                'payment_amount' => $paymentAmount,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalPaid = (float) $supplierInvoice->payments()->sum('payment_amount');
            $totalAmount = (float) $supplierInvoice->total_amount;

            if ($totalPaid >= $totalAmount && $totalAmount > 0) {
                $totalPaid = $totalAmount;
                $status = 'paid';
            } else {
                $status = 'partially_paid';
            }

            $supplierInvoice->update([
                'paid_amount' => $totalPaid,
                'status' => $status,
            ]);
        });

        return back()->with('success', 'Pembayaran angsuran/cicilan berhasil dicatat!');
    }

    public function destroy(SupplierInvoice $supplierInvoice): RedirectResponse
    {
        $supplierInvoice->delete();

        return back()->with('success', 'Tagihan supplier berhasil dihapus!');
    }
}
