<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SupplierInvoice;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = now()->format('Y-m-d');
        $sevenDaysAgo = now()->subDays(6)->startOfDay();

        // 1. Quick Stats Hari Ini
        $todayTransactions = Transaction::whereDate('created_at', $today)->where('status', 'completed');
        $totalTransactionsToday = $todayTransactions->count();
        $revenueToday = (float) $todayTransactions->sum('grand_total');
        $avgTransaction = $totalTransactionsToday > 0 ? $revenueToday / $totalTransactionsToday : 0;
        $newCustomers = Customer::whereDate('created_at', $today)->count();

        // 2. Grafik Omzet 7 Hari Terakhir
        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $formattedDate = $date->format('Y-m-d');
            $dayLabel = $date->translatedFormat('D, d M');

            $dayRevenue = (float) Transaction::whereDate('created_at', $formattedDate)
                ->where('status', 'completed')
                ->sum('grand_total');

            $salesTrend[] = [
                'date' => $formattedDate,
                'label' => $dayLabel,
                'revenue' => $dayRevenue,
            ];
        }

        // 3. Top 5 Produk Terlaris
        $topProducts = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'completed');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product.category')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product ? $item->product->name : 'Produk',
                    'unit' => $item->product ? $item->product->unit : 'pcs',
                    'category' => $item->product && $item->product->category ? $item->product->category->name : '-',
                    'total_sold' => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });

        // 4. Alert Stok Menipis (stok <= min_stock)
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->with('category')
            ->orderBy('stock', 'asc')
            ->limit(6)
            ->get();

        // 5. Transaksi Terbaru
        $recentTransactions = Transaction::with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 6. Ringkasan Tagihan Supplier Belum Lunas & Jatuh Tempo
        $supplierUnpaidTotal = (float) SupplierInvoice::selectRaw('SUM(total_amount - paid_amount) as remaining')->where('status', '!=', 'paid')->value('remaining') ?? 0;
        $supplierUnpaidCount = SupplierInvoice::where('status', '!=', 'paid')->count();
        $supplierDueSoon = SupplierInvoice::with('supplier')
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->orderBy('due_date', 'asc')
            ->limit(4)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'revenue_today' => $revenueToday,
                'transactions_today' => $totalTransactionsToday,
                'avg_transaction' => $avgTransaction,
                'new_customers' => $newCustomers,
            ],
            'sales_trend' => $salesTrend,
            'top_products' => $topProducts,
            'low_stock_products' => $lowStockProducts,
            'recent_transactions' => $recentTransactions,
            'supplier_stats' => [
                'unpaid_total' => $supplierUnpaidTotal,
                'unpaid_count' => $supplierUnpaidCount,
                'due_soon' => $supplierDueSoon,
            ],
        ]);
    }
}
