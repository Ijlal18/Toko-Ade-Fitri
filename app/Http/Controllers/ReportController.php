<?php

namespace App\Http\Controllers;

use App\Models\SupplierInvoice;
use App\Models\SupplierInvoicePayment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->input('period', '7days'); // today, 7days, 30days, custom
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($period === 'today') {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        } elseif ($period === '30days') {
            $start = now()->subDays(29)->startOfDay();
            $end = now()->endOfDay();
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            // default 7 days
            $period = '7days';
            $start = now()->subDays(6)->startOfDay();
            $end = now()->endOfDay();
        }

        $transactionsQuery = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        $totalRevenue = (float) $transactionsQuery->sum('grand_total');
        $totalTransactions = $transactionsQuery->count();

        // Calculate gross profit (Selling Subtotal - Cost Subtotal)
        $costSum = (float) TransactionItem::whereHas('transaction', function ($q) use ($start, $end) {
            $q->where('status', 'completed')->whereBetween('created_at', [$start, $end]);
        })->sum(DB::raw('purchase_price * quantity'));

        $grossProfit = max(0, $totalRevenue - $costSum);

        // Daily breakdown chart
        $dailyData = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(grand_total) as revenue'),
            DB::raw('COUNT(id) as total_tx')
        )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Payment method breakdown
        $paymentBreakdown = Transaction::select('payment_method', DB::raw('COUNT(id) as total_tx'), DB::raw('SUM(grand_total) as revenue'))
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('payment_method')
            ->get();

        // Top 10 products sold in period
        $topProducts = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->where('status', 'completed')->whereBetween('created_at', [$start, $end]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product.category')
            ->limit(10)
            ->get();

        // Supplier invoices summary in period
        $supplierInvoicesTotal = (float) SupplierInvoice::whereBetween('created_at', [$start, $end])->sum('total_amount');
        $supplierPaidTotal = (float) SupplierInvoicePayment::whereBetween('payment_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->sum('payment_amount');
        $supplierUnpaidTotal = (float) SupplierInvoice::whereBetween('created_at', [$start, $end])->selectRaw('SUM(total_amount - paid_amount) as remaining')->value('remaining') ?? 0;

        return Inertia::render('Reports/Index', [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'gross_profit' => $grossProfit,
                'period' => $period,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'supplier_invoices_total' => $supplierInvoicesTotal,
                'supplier_paid_total' => $supplierPaidTotal,
                'supplier_unpaid_total' => $supplierUnpaidTotal,
            ],
            'daily_data' => $dailyData,
            'payment_breakdown' => $paymentBreakdown,
            'top_products' => $topProducts,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $transactions = Transaction::with(['customer', 'user'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'asc')
            ->get();

        $fileName = "Laporan_Penjualan_{$startDate}_sampai_{$endDate}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No. Invoice', 'Tanggal & Waktu', 'Kasir', 'Pelanggan', 'Metode Bayar', 'Total Pembelian', 'Diskon', 'Pajak', 'Grand Total']);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->invoice_number,
                    $tx->created_at->format('Y-m-d H:i:s'),
                    $tx->user?->name ?? 'Kasir',
                    $tx->customer?->name ?? 'Pelanggan Umum',
                    strtoupper($tx->payment_method),
                    $tx->total_amount,
                    $tx->discount_amount,
                    $tx->tax_amount,
                    $tx->grand_total,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
