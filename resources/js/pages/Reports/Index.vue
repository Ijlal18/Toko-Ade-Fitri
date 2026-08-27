<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    BarChart3,
    Calendar,
    CreditCard,
    DollarSign,
    Download,
    Filter,
    Package,
    TrendingUp,
} from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Summary {
    total_revenue: number;
    total_transactions: number;
    gross_profit: number;
    period: string;
    start_date: string;
    end_date: string;
    supplier_invoices_total?: number | null;
    supplier_paid_total?: number | null;
}

interface DailyData {
    date: string;
    revenue: number;
    total_tx: number;
}

interface PaymentBreakdown {
    payment_method: string;
    total_tx: number;
    revenue: number;
}

interface TopProduct {
    product_id: number;
    total_quantity: number;
    total_revenue: number;
    product?: {
        name: string;
        unit: string;
        category?: { name: string };
    };
}

const props = defineProps<{
    summary: Summary;
    daily_data: DailyData[];
    payment_breakdown: PaymentBreakdown[];
    top_products: TopProduct[];
}>();

const periodFilter = ref(props.summary.period || '7days');
const startDateInput = ref(props.summary.start_date);
const endDateInput = ref(props.summary.end_date);

const applyFilter = () => {
    router.get(
        '/reports',
        {
            period: periodFilter.value,
            start_date: startDateInput.value,
            end_date: endDateInput.value,
        },
        { preserveState: true }
    );
};

const exportCsv = () => {
    const url = `/reports/export-csv?start_date=${startDateInput.value}&end_date=${endDateInput.value}`;
    window.location.href = url;
};

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Laporan & Keuangan - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Laporan & Analisis Bisnis</h1>
                <p class="text-xs text-slate-500">Laporan omzet, estimasi keuntungan bersih, dan performa produk toko</p>
            </div>
            <button
                @click="exportCsv"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-emerald-600/25 transition-all"
            >
                <Download class="h-4 w-4" /> Export CSV / Excel
            </button>
        </div>

        <!-- Period Selector Controls -->
        <div class="flex flex-col sm:flex-row items-center gap-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <select
                v-model="periodFilter"
                @change="applyFilter"
                class="w-full sm:w-48 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="today">Hari Ini</option>
                <option value="7days">7 Hari Terakhir</option>
                <option value="30days">30 Hari Terakhir</option>
                <option value="custom">Rentang Tanggal Custom</option>
            </select>

            <div v-if="periodFilter === 'custom'" class="flex items-center gap-2 w-full sm:w-auto">
                <input
                    v-model="startDateInput"
                    type="date"
                    class="rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                />
                <span class="text-xs text-slate-400">s/d</span>
                <input
                    v-model="endDateInput"
                    type="date"
                    class="rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                />
                <button
                    @click="applyFilter"
                    class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white shadow-md"
                >
                    Terapkan
                </button>
            </div>

            <div class="ml-auto text-xs font-medium text-slate-500 flex items-center gap-1.5">
                <Calendar class="h-3.5 w-3.5 text-blue-600" />
                Periode: {{ formatDate(summary.start_date) }} - {{ formatDate(summary.end_date) }}
            </div>
        </div>

        <!-- Summary Metrics -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-blue-100 bg-linear-to-br from-blue-50/80 to-white p-5 shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Omzet Penjualan</span>
                <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                    {{ formatRupiah(summary.total_revenue) }}
                </p>
                <p class="mt-1 text-xs text-slate-500 font-medium">Omzet dari {{ summary.total_transactions }} transaksi</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-linear-to-br from-emerald-50/80 to-white p-5 shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Estimasi Laba Kotor</span>
                <p class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    {{ formatRupiah(summary.gross_profit) }}
                </p>
                <p class="mt-1 text-xs text-slate-500 font-medium">Selisih harga jual vs modal beli</p>
            </div>

            <div class="rounded-2xl border border-purple-100 bg-linear-to-br from-purple-50/80 to-white p-5 shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <span class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Tagihan Supplier Masuk</span>
                <p class="mt-2 text-2xl font-black text-purple-700 dark:text-purple-300">
                    {{ formatRupiah(summary.supplier_invoices_total || 0) }}
                </p>
                <p class="mt-1 text-xs text-slate-500 font-medium">Total invoice supplier diterima</p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-linear-to-br from-amber-50/80 to-white p-5 shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Pelunasan Tagihan Supplier</span>
                <p class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400">
                    {{ formatRupiah(summary.supplier_paid_total || 0) }}
                </p>
                <p class="mt-1 text-xs text-slate-500 font-medium">Angsuran/cicilan dibayar periode ini</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Rincian Penjualan Per Hari -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <BarChart3 class="h-4 w-4 text-blue-600" /> Rincian Omzet Harian
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                            <tr>
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Jumlah Transaksi</th>
                                <th class="p-3 text-right">Omzet Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="d in daily_data" :key="d.date" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                                <td class="p-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ formatDate(d.date) }}
                                </td>
                                <td class="p-3 font-semibold text-slate-600">
                                    {{ d.total_tx }} transaksi
                                </td>
                                <td class="p-3 text-right font-extrabold text-blue-600 font-mono">
                                    {{ formatRupiah(d.revenue) }}
                                </td>
                            </tr>
                            <tr v-if="daily_data.length === 0">
                                <td colspan="3" class="p-6 text-center text-slate-400">Tidak ada data harian pada periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Metode Pembayaran Breakdown -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <CreditCard class="h-4 w-4 text-emerald-600" /> Distribusi Metode Pembayaran
                </h3>
                <div class="space-y-3">
                    <div
                        v-for="pm in payment_breakdown"
                        :key="pm.payment_method"
                        class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40"
                    >
                        <div>
                            <span class="text-xs font-black uppercase text-slate-800 dark:text-slate-200">
                                {{ pm.payment_method }}
                            </span>
                            <p class="text-[10px] text-slate-400">{{ pm.total_tx }} transaksi</p>
                        </div>
                        <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ formatRupiah(pm.revenue) }}
                        </span>
                    </div>

                    <div v-if="payment_breakdown.length === 0" class="py-8 text-center text-xs text-slate-400">
                        Belum ada data pembayaran.
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Products in Period -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <Package class="h-4 w-4 text-blue-600" /> Top 10 Produk Terlaris Periode Ini
            </h3>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3">Rank</th>
                        <th class="p-3">Nama Barang</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Total Qty Terjual</th>
                        <th class="p-3 text-right">Total Nilai Penjualan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="(p, i) in top_products" :key="p.product_id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="p-3 font-bold text-blue-600 font-mono">#{{ i + 1 }}</td>
                        <td class="p-3 font-bold text-slate-900 dark:text-white">
                            {{ p.product?.name || 'Produk' }}
                        </td>
                        <td class="p-3 text-slate-500">
                            {{ p.product?.category?.name || '-' }}
                        </td>
                        <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                            {{ p.total_quantity }} {{ p.product?.unit || 'pcs' }}
                        </td>
                        <td class="p-3 text-right font-extrabold text-emerald-600 font-mono">
                            {{ formatRupiah(p.total_revenue) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
