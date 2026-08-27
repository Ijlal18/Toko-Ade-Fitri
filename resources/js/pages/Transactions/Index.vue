<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Edit3, Eye, Printer, Receipt, Search, Trash2, X } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Customer {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

interface TransactionItem {
    id: number;
    quantity: number;
    unit_price: number;
    subtotal: number;
    price_type: string;
    product?: {
        name: string;
        unit: string;
    };
}

interface Transaction {
    id: number;
    invoice_number: string;
    customer_id?: number | null;
    total_amount: number;
    discount_amount: number;
    tax_amount: number;
    grand_total: number;
    paid_amount: number;
    change_amount: number;
    payment_method: string;
    status: string;
    notes?: string | null;
    created_at: string;
    customer?: Customer;
    user?: User;
    items?: TransactionItem[];
}

const props = defineProps<{
    transactions: {
        data: Transaction[];
        links: any[];
        total: number;
    };
    customers?: Customer[];
    filters: {
        search?: string;
        payment_method?: string;
        date?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const paymentFilter = ref(props.filters.payment_method || '');
const dateFilter = ref(props.filters.date || '');

const applyFilters = () => {
    router.get(
        '/transactions',
        {
            search: searchQuery.value,
            payment_method: paymentFilter.value,
            date: dateFilter.value,
        },
        { preserveState: true }
    );
};

// Detail Invoice Modal State
const selectedTransaction = ref<Transaction | null>(null);
const showDetailModal = ref(false);

const openDetailModal = (tx: Transaction) => {
    selectedTransaction.value = tx;
    showDetailModal.value = true;
};

const printReceipt = () => {
    window.print();
    showDetailModal.value = false;
};

const handleAfterPrint = () => {
    showDetailModal.value = false;
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('afterprint', handleAfterPrint);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('afterprint', handleAfterPrint);
    }
});

// Edit Transaction Modal State
const showEditModal = ref(false);
const editingTransaction = ref<Transaction | null>(null);

const editForm = useForm({
    customer_id: null as number | null,
    payment_method: 'cash',
    status: 'completed',
    notes: '',
});

const openEditModal = (tx: Transaction) => {
    editingTransaction.value = tx;
    editForm.clearErrors();
    editForm.customer_id = tx.customer_id || tx.customer?.id || null;
    editForm.payment_method = tx.payment_method || 'cash';
    editForm.status = tx.status || 'completed';
    editForm.notes = tx.notes || '';
    showEditModal.value = true;
};

const submitEditForm = () => {
    if (!editingTransaction.value) return;
    editForm.put(`/transactions/${editingTransaction.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

// Delete Transaction Modal State
const showDeleteModal = ref(false);
const deletingTransaction = ref<Transaction | null>(null);

const openDeleteModal = (tx: Transaction) => {
    deletingTransaction.value = tx;
    showDeleteModal.value = true;
};

const confirmDeleteTransaction = () => {
    if (!deletingTransaction.value) return;
    router.delete(`/transactions/${deletingTransaction.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const formatDateTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};
</script>

<template>
    <Head title="Riwayat Transaksi - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Riwayat Transaksi Penjualan</h1>
                <p class="text-xs text-slate-500">Daftar invoice transaksi, metode bayar, dan re-print struk belanja</p>
            </div>
            <Link
                href="/pos"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Receipt class="h-4 w-4" /> Buka Kasir POS
            </Link>
        </div>

        <!-- Filters Bar -->
        <div class="flex flex-col sm:flex-row items-center gap-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Cari nomor invoice..."
                    class="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-4 text-xs font-semibold focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                />
            </div>

            <select
                v-model="paymentFilter"
                @change="applyFilters"
                class="w-full sm:w-48 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Pembayaran</option>
                <option value="cash">Tunai / Cash</option>
                <option value="qris">QRIS</option>
                <option value="transfer">Transfer Bank</option>
                <option value="debit">Debit/Kartu</option>
            </select>

            <input
                v-model="dateFilter"
                @change="applyFilters"
                type="date"
                class="w-full sm:w-44 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            />
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">No. Invoice</th>
                        <th class="p-4">Tanggal & Waktu</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Kasir Staff</th>
                        <th class="p-4">Metode Bayar</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Grand Total</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ tx.invoice_number }}
                        </td>
                        <td class="p-4 text-slate-500 font-mono text-[11px]">
                            {{ formatDateTime(tx.created_at) }}
                        </td>
                        <td class="p-4 font-bold text-slate-800 dark:text-slate-200">
                            {{ tx.customer?.name || 'Pelanggan Umum' }}
                        </td>
                        <td class="p-4 text-slate-500 font-medium">
                            {{ tx.user?.name || 'Kasir' }}
                        </td>
                        <td class="p-4">
                            <span class="rounded bg-slate-100 px-2 py-0.5 font-bold uppercase text-[10px] text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ tx.payment_method }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span
                                class="rounded px-2 py-0.5 font-black uppercase text-[9px]"
                                :class="tx.status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'"
                            >
                                {{ tx.status === 'cancelled' ? 'DIBATALKAN' : 'SELESAI' }}
                            </span>
                        </td>
                        <td class="p-4 font-black text-slate-900 dark:text-white font-mono">
                            {{ formatRupiah(tx.grand_total) }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button
                                    @click="openDetailModal(tx)"
                                    title="Detail / Struk"
                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-600 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-400"
                                >
                                    <Eye class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    @click="openEditModal(tx)"
                                    title="Edit Transaksi"
                                    class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-1 text-xs font-bold text-amber-600 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-400"
                                >
                                    <Edit3 class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    @click="openDeleteModal(tx)"
                                    title="Hapus Transaksi"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-100 dark:bg-red-950 dark:text-red-400"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="transactions.data.length === 0">
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            Belum ada riwayat transaksi recorded.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DETAIL / RE-PRINT INVOICE MODAL -->
    <div v-if="showDetailModal && selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
            <!-- Printable Struk thermal 80mm area -->
            <div id="printable-receipt" class="font-mono text-xs text-slate-800 space-y-3 p-4 border border-dashed border-slate-300 rounded-xl bg-slate-50 dark:bg-slate-950 dark:text-slate-200 dark:border-slate-800 shadow-inner">
                <!-- Header -->
                <div class="text-center border-b-2 border-dashed border-slate-300 dark:border-slate-700 pb-3 space-y-0.5">
                    <h2 class="font-black text-base tracking-wider text-slate-900 dark:text-white uppercase">TOKO ADE FITRI</h2>
                    <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Toko Bahan Kue & Plastik (Grosir & Eceran)</p>
                    <p class="text-[9px] text-slate-500 dark:text-slate-400">Pasar Karangampel Blok D4-D6 • WA: 082315580021</p>
                </div>

                <!-- Transaction Info Grid -->
                <div class="text-[10px] space-y-1 border-b border-dashed border-slate-300 dark:border-slate-700 pb-2.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">No. Invoice</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-white">{{ selectedTransaction.invoice_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Waktu</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-300">{{ formatDateTime(selectedTransaction.created_at) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Kasir</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-300">{{ selectedTransaction.user?.name || 'Kasir' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Pelanggan</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-300">{{ selectedTransaction.customer?.name || 'Pelanggan Umum' }}</span>
                    </div>
                </div>

                <!-- Items list -->
                <div class="space-y-2 border-b-2 border-dashed border-slate-300 dark:border-slate-700 pb-3 text-[11px]">
                    <div v-for="item in selectedTransaction.items" :key="item.id" class="space-y-0.5">
                        <p class="font-bold text-slate-900 dark:text-white leading-tight">{{ item.product?.name || 'Produk' }}</p>
                        <div class="flex justify-between text-[10px] text-slate-600 dark:text-slate-400">
                            <span>
                                {{ item.quantity }} x {{ formatRupiah(item.unit_price) }}
                                <span class="text-[9px] font-bold uppercase px-1 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ml-1">
                                    {{ item.price_type === 'wholesale' ? 'Grosir' : 'Eceran' }}
                                </span>
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ formatRupiah(item.subtotal) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Totals -->
                <div class="space-y-1 text-[11px] border-b-2 border-dashed border-slate-300 dark:border-slate-700 pb-2.5">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-mono">{{ formatRupiah(selectedTransaction.total_amount) }}</span>
                    </div>
                    <div v-if="selectedTransaction.discount_amount > 0" class="flex justify-between text-red-600 dark:text-red-400">
                        <span>Potongan / Diskon</span>
                        <span class="font-mono">-{{ formatRupiah(selectedTransaction.discount_amount) }}</span>
                    </div>
                    <div v-if="selectedTransaction.tax_amount > 0" class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Pajak (PPN)</span>
                        <span class="font-mono">+{{ formatRupiah(selectedTransaction.tax_amount) }}</span>
                    </div>
                    <div class="flex justify-between font-black text-xs pt-1.5 border-t border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                        <span>TOTAL BAYAR</span>
                        <span class="text-sm font-black text-blue-600 dark:text-blue-400 font-mono">{{ formatRupiah(selectedTransaction.grand_total) }}</span>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-600 dark:text-slate-400 pt-1">
                        <span>Metode Bayar ({{ selectedTransaction.payment_method.toUpperCase() }})</span>
                        <span class="font-mono font-bold">{{ formatRupiah(selectedTransaction.paid_amount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-[11px] text-emerald-600 dark:text-emerald-400">
                        <span>Kembali</span>
                        <span class="font-mono font-black text-xs">{{ formatRupiah(selectedTransaction.change_amount) }}</span>
                    </div>
                </div>

                <!-- Footer Message -->
                <div class="text-center text-[9px] text-slate-500 dark:text-slate-400 pt-1 space-y-0.5">
                    <p class="font-bold tracking-wider text-slate-700 dark:text-slate-300">*** TERIMA KASIH ***</p>
                    <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                    <p class="text-[8px] text-slate-400">Simpan struk ini sebagai bukti transaksi sah</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-1">
                <button
                    @click="showDetailModal = false"
                    class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 py-3 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 transition-all cursor-pointer select-none"
                >
                    Tutup
                </button>
                <button
                    @click="printReceipt"
                    class="flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 py-3 text-xs font-extrabold text-white shadow-lg shadow-blue-600/30 transition-all cursor-pointer select-none"
                >
                    <Printer class="h-4 w-4" /> Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <!-- EDIT TRANSACTION MODAL -->
    <div v-if="showEditModal && editingTransaction" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Edit3 class="h-4 w-4 text-amber-500" /> Edit Transaksi
                    </h3>
                    <p class="text-xs text-slate-500 font-mono">{{ editingTransaction.invoice_number }}</p>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitEditForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Pelanggan</label>
                    <select
                        v-model="editForm.customer_id"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    >
                        <option :value="null">-- Pelanggan Umum --</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Metode Bayar</label>
                        <select
                            v-model="editForm.payment_method"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option value="cash">Tunai / Cash</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select
                            v-model="editForm.status"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option value="completed">Selesai (Completed)</option>
                            <option value="cancelled">Dibatalkan (Restore Stok)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Transaksi</label>
                    <textarea
                        v-model="editForm.notes"
                        rows="2"
                        placeholder="Catatan penyesuaian transaksi..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-medium text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    ></textarea>
                </div>

                <div class="flex gap-2 pt-3">
                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="w-1/3 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 dark:border-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="editForm.processing"
                        class="w-2/3 rounded-xl bg-amber-600 hover:bg-amber-700 py-2.5 text-xs font-bold text-white shadow-lg shadow-amber-600/30"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE TRANSACTION CONFIRMATION MODAL -->
    <div v-if="showDeleteModal && deletingTransaction" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center gap-3 rounded-xl bg-red-50 p-3 text-red-900 dark:bg-red-950/80 dark:text-red-200 border border-red-200 dark:border-red-800">
                <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400 shrink-0" />
                <div>
                    <h4 class="font-bold text-xs">Hapus Transaksi {{ deletingTransaction.invoice_number }}?</h4>
                    <p class="text-[11px] text-red-700 dark:text-red-300">Tindakan ini akan menghapus data transaksi dan secara otomatis mengembalikan stok barang ke database.</p>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button
                    @click="showDeleteModal = false"
                    class="w-1/2 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100"
                >
                    Batal
                </button>
                <button
                    @click="confirmDeleteTransaction"
                    class="w-1/2 rounded-xl bg-red-600 hover:bg-red-700 py-2.5 text-xs font-bold text-white shadow-md shadow-red-600/30"
                >
                    Ya, Hapus Transaksi
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: 80mm auto;
        margin: 2mm 4mm;
    }
    body * {
        visibility: hidden !important;
    }
    #printable-receipt,
    #printable-receipt * {
        visibility: visible !important;
    }
    #printable-receipt {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 72mm !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        font-family: 'Courier New', Courier, monospace !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
    }
    #printable-receipt h2,
    #printable-receipt p,
    #printable-receipt span,
    #printable-receipt div {
        color: black !important;
        background: transparent !important;
    }
}
</style>
