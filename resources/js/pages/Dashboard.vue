<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    BarChart2,
    CheckCircle2,
    CreditCard,
    DollarSign,
    Package,
    ShoppingCart,
    TrendingUp,
    Users,
} from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Stats {
    revenue_today: number;
    transactions_today: number;
    avg_transaction: number;
    new_customers: number;
}

interface SalesTrend {
    date: string;
    label: string;
    revenue: number;
}

interface TopProduct {
    id: number;
    name: string;
    unit: string;
    category: string;
    total_sold: number;
    total_revenue: number;
}

interface LowStockProduct {
    id: number;
    name: string;
    stock: number;
    min_stock: number;
    unit: string;
    category?: { name: string };
}

interface RecentTransaction {
    id: number;
    invoice_number: string;
    grand_total: number;
    payment_method: string;
    created_at: string;
    customer?: { name: string };
}

interface SupplierInvoiceDue {
    id: number;
    invoice_number: string;
    total_amount: number;
    paid_amount: number;
    remaining_amount: number;
    due_date?: string;
    supplier?: { name: string };
}

interface SupplierStats {
    unpaid_total: number;
    unpaid_count: number;
    due_soon: SupplierInvoiceDue[];
}

const props = defineProps<{
    stats: Stats;
    sales_trend: SalesTrend[];
    top_products: TopProduct[];
    low_stock_products: LowStockProduct[];
    recent_transactions: RecentTransaction[];
    supplier_stats?: SupplierStats;
}>();

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
};

const maxRevenue = Math.max(...props.sales_trend.map((s) => s.revenue), 100000);
</script>

<template>
    <Head title="Dashboard Overview - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-2xl bg-linear-to-r from-blue-600 via-blue-700 to-indigo-800 p-6 text-white shadow-xl shadow-blue-500/10">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-300 backdrop-blur-md">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem POS Terintegrasi
                </span>
                <h1 class="mt-2 text-2xl font-black tracking-tight md:text-3xl">
                    Dashboard Toko Ade Fitri
                </h1>
                <p class="mt-1 text-sm text-blue-100/90">
                    Grosir & Eceran Bahan Kue dan Plastik • Ringkasan Penjualan & Stok Real-time
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    href="/pos"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition-all hover:scale-[1.02]"
                >
                    <CreditCard class="h-4 w-4" />
                    Buka Kasir POS
                </Link>
            </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Omzet Hari Ini -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Omzet Hari Ini</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                        <DollarSign class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ formatRupiah(stats.revenue_today) }}
                    </p>
                    <p class="mt-1 flex items-center text-xs text-emerald-600 font-medium">
                        <TrendingUp class="mr-1 h-3.5 w-3.5" /> Total Penjualan Bersih
                    </p>
                </div>
            </div>

            <!-- Total Transaksi Hari Ini -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Transaksi Hari Ini</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                        <ShoppingCart class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ stats.transactions_today }} <span class="text-xs font-normal text-slate-400">transaksi</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Struk selesai hari ini</p>
                </div>
            </div>

            <!-- Rata-rata per Transaksi -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Rata-rata Transaksi</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <BarChart2 class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ formatRupiah(stats.avg_transaction) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Nilai rata-rata keranjang</p>
                </div>
            </div>

            <!-- Customer Baru Hari Ini -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Customer Baru</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                        <Users class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ stats.new_customers }} <span class="text-xs font-normal text-slate-400">orang</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Terdaftar hari ini</p>
                </div>
            </div>
        </div>

        <!-- Sales Trend & Low Stock Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Grafik Omzet 7 Hari Terakhir -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Trend Penjualan Harian</h2>
                        <p class="text-xs text-slate-500">Grafik omzet 7 hari terakhir</p>
                    </div>
                    <span class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                        7 Hari Terakhir
                    </span>
                </div>

                <div class="mt-6 flex h-56 items-end gap-3 pt-4">
                    <div
                        v-for="(item, idx) in sales_trend"
                        :key="idx"
                        class="group relative flex flex-1 flex-col items-center h-full justify-end"
                    >
                        <!-- Tooltip -->
                        <div class="absolute -top-10 opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-10 rounded-lg bg-slate-900 text-white text-[10px] font-bold px-2 py-1 shadow-lg whitespace-nowrap">
                            {{ formatRupiah(item.revenue) }}
                        </div>

                        <!-- Bar Container -->
                        <div class="w-full rounded-t-lg bg-slate-100 dark:bg-slate-800 h-full flex items-end overflow-hidden">
                            <div
                                class="w-full rounded-t-lg bg-linear-to-t from-blue-600 to-emerald-400 transition-all duration-500 group-hover:brightness-110"
                                :style="{ height: `${Math.max(8, (item.revenue / maxRevenue) * 100)}%` }"
                            ></div>
                        </div>

                        <span class="mt-2 text-[10px] font-medium text-slate-500 truncate w-full text-center">
                            {{ item.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Alert Stok Menipis -->
            <div class="rounded-2xl border border-amber-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400">
                            <AlertTriangle class="h-4 w-4" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Peringatan Stok</h2>
                            <p class="text-xs text-slate-500">Stok berada pada batas minimum</p>
                        </div>
                    </div>
                    <Link href="/stock" class="text-xs font-bold text-blue-600 hover:underline">
                        Restok
                    </Link>
                </div>

                <div class="mt-4 space-y-3">
                    <div
                        v-for="prod in low_stock_products"
                        :key="prod.id"
                        class="flex items-center justify-between rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50"
                    >
                        <div class="min-w-0 flex-1 pr-2">
                            <p class="truncate text-xs font-bold text-slate-800 dark:text-slate-200">
                                {{ prod.name }}
                            </p>
                            <p class="text-[10px] text-slate-500">
                                {{ prod.category?.name || 'Umum' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-black text-red-700 dark:bg-red-950 dark:text-red-300">
                                Sisa {{ prod.stock }} {{ prod.unit }}
                            </span>
                            <p class="mt-0.5 text-[9px] text-slate-400">Min. {{ prod.min_stock }} {{ prod.unit }}</p>
                        </div>
                    </div>

                    <div v-if="low_stock_products.length === 0" class="py-8 text-center text-xs text-slate-400">
                        <CheckCircle2 class="mx-auto mb-1 h-6 w-6 text-emerald-500" />
                        Semua stok barang aman dan mencukupi!
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products & Recent Transactions -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Top 5 Produk Terlaris -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                            <Package class="h-4 w-4" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Top 5 Produk Terlaris</h2>
                            <p class="text-xs text-slate-500">Berdasarkan volume penjualan</p>
                        </div>
                    </div>
                    <Link href="/products" class="text-xs font-bold text-blue-600 hover:underline">
                        Lihat Produk
                    </Link>
                </div>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="(prod, i) in top_products"
                        :key="prod.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-extrabold"
                                :class="i === 0 ? 'bg-amber-400 text-slate-900' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'"
                            >
                                {{ i + 1 }}
                            </span>
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ prod.name }}
                                </p>
                                <p class="text-[10px] text-slate-400">{{ prod.category }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                {{ prod.total_sold }} {{ prod.unit }} Terjual
                            </p>
                            <p class="text-[10px] text-slate-400">{{ formatRupiah(prod.total_revenue) }}</p>
                        </div>
                    </div>

                    <div v-if="top_products.length === 0" class="py-8 text-center text-xs text-slate-400">
                        Belum ada data produk terjual
                    </div>
                </div>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Aktivitas Transaksi Terbaru</h2>
                    <Link href="/transactions" class="flex items-center text-xs font-bold text-blue-600 hover:underline">
                        Lihat Semua <ArrowUpRight class="ml-1 h-3.5 w-3.5" />
                    </Link>
                </div>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="tx in recent_transactions"
                        :key="tx.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div>
                            <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ tx.invoice_number }}
                            </span>
                            <p class="text-[10px] text-slate-500">
                                {{ tx.customer?.name || 'Pelanggan Umum' }} • {{ tx.payment_method.toUpperCase() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-slate-900 dark:text-white">
                                {{ formatRupiah(tx.grand_total) }}
                            </p>
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-[9px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                Berhasil
                            </span>
                        </div>
                    </div>

                    <div v-if="recent_transactions.length === 0" class="py-8 text-center text-xs text-slate-400">
                        Belum ada transaksi recorded
                    </div>
                </div>
            </div>
        </div>

        <!-- Tagihan Supplier Overview Card -->
        <div v-if="supplier_stats" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800 gap-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                        <CreditCard class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Tagihan Supplier Belum Lunas & Jatuh Tempo</h2>
                        <p class="text-xs text-slate-500">
                            Total Sisa Tagihan: <strong class="text-red-600 font-mono">{{ formatRupiah(supplier_stats.unpaid_total) }}</strong> ({{ supplier_stats.unpaid_count }} tagihan)
                        </p>
                    </div>
                </div>
                <Link href="/supplier-invoices" class="inline-flex items-center gap-1 rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-300">
                    Kelola Tagihan Supplier <ArrowUpRight class="h-3.5 w-3.5" />
                </Link>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="inv in supplier_stats.due_soon"
                    :key="inv.id"
                    class="rounded-xl border border-slate-200 p-3 bg-slate-50/70 dark:bg-slate-800/40 dark:border-slate-800 space-y-1"
                >
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-blue-600 dark:text-blue-400">{{ inv.invoice_number }}</span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            Jatuh Tempo: {{ inv.due_date ? new Date(inv.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) : '-' }}
                        </span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ inv.supplier?.name || 'Supplier' }}</p>
                    <div class="flex justify-between text-[11px] font-mono pt-1 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-500">Sisa Tagihan:</span>
                        <span class="font-bold text-red-600">{{ formatRupiah(inv.remaining_amount) }}</span>
                    </div>
                </div>

                <div v-if="supplier_stats.due_soon.length === 0" class="col-span-full py-6 text-center text-xs text-slate-400">
                    <CheckCircle2 class="mx-auto mb-1 h-5 w-5 text-emerald-500" />
                    Tidak ada tagihan supplier yang mendesak / belum lunas!
                </div>
            </div>
        </div>
    </div>
</template>
