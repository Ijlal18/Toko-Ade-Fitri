<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Building2,
    Calendar,
    CheckCircle2,
    DollarSign,
    Edit3,
    Eye,
    FileSpreadsheet,
    Plus,
    Search,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Category {
    id: number;
    name: string;
}

interface Supplier {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    category_id?: number | null;
    purchase_price: number;
    category?: Category;
}

interface SupplierInvoiceItem {
    id?: number;
    product_id?: number | null;
    category_id?: number | null;
    product_name: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
    category?: Category | null;
    product?: Product | null;
}

interface SupplierInvoicePayment {
    id: number;
    payment_amount: number;
    payment_date: string;
    payment_method: string;
    notes?: string | null;
    created_at: string;
}

interface SupplierInvoice {
    id: number;
    invoice_number: string;
    supplier_id?: number | null;
    total_amount: number;
    paid_amount: number;
    remaining_amount: number;
    due_date?: string | null;
    status: 'unpaid' | 'partially_paid' | 'paid';
    notes?: string | null;
    created_at: string;
    supplier?: Supplier | null;
    items?: SupplierInvoiceItem[];
    payments?: SupplierInvoicePayment[];
}

const props = defineProps<{
    invoices: {
        data: SupplierInvoice[];
        links: any[];
        total: number;
    };
    suppliers: Supplier[];
    categories: Category[];
    products: Product[];
    metrics: {
        total_amount: number;
        paid_amount: number;
        unpaid_amount: number;
        due_soon_count: number;
    };
    filters: {
        search?: string;
        status?: string;
        supplier_id?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const supplierFilter = ref(props.filters.supplier_id || '');

const applyFilters = () => {
    router.get(
        '/supplier-invoices',
        {
            search: searchQuery.value,
            status: statusFilter.value,
            supplier_id: supplierFilter.value,
        },
        { preserveState: true }
    );
};

// Form Modal State (Add/Edit Multi-Item)
const showFormModal = ref(false);
const isEditing = ref(false);
const editingInvoice = ref<SupplierInvoice | null>(null);

const createEmptyItem = () => ({
    product_id: null as number | null,
    category_id: null as number | null,
    product_name: '',
    quantity: 1,
    unit_price: 0,
});

const form = useForm({
    id: null as number | null,
    invoice_number: '',
    supplier_id: null as number | null,
    due_date: '',
    paid_amount: 0,
    notes: '',
    items: [createEmptyItem()] as any[],
});

const addItemRow = () => {
    form.items.push(createEmptyItem());
};

const removeItemRow = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const onProductSelect = (index: number) => {
    const item = form.items[index];
    if (item.product_id) {
        const prod = props.products.find((p) => p.id === item.product_id);
        if (prod) {
            item.product_name = prod.name;
            item.category_id = prod.category_id || null;
            if (!item.unit_price || item.unit_price === 0) {
                item.unit_price = Number(prod.purchase_price);
            }
        }
    }
};

const calculatedTotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
    }, 0);
});

const openAddModal = () => {
    isEditing.value = false;
    editingInvoice.value = null;
    form.reset();
    form.clearErrors();
    form.items = [createEmptyItem()];
    showFormModal.value = true;
};

const openEditModal = (inv: SupplierInvoice) => {
    isEditing.value = true;
    editingInvoice.value = inv;
    form.clearErrors();
    form.id = inv.id;
    form.invoice_number = inv.invoice_number;
    form.supplier_id = inv.supplier_id || null;
    form.due_date = inv.due_date ? inv.due_date.split('T')[0] : '';
    form.paid_amount = Number(inv.paid_amount);
    form.notes = inv.notes || '';

    if (inv.items && inv.items.length > 0) {
        form.items = inv.items.map((it) => ({
            product_id: it.product_id || null,
            category_id: it.category_id || null,
            product_name: it.product_name,
            quantity: it.quantity,
            unit_price: Number(it.unit_price),
        }));
    } else {
        form.items = [createEmptyItem()];
    }

    showFormModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && form.id) {
        form.put(`/supplier-invoices/${form.id}`, {
            onSuccess: () => {
                showFormModal.value = false;
            },
        });
    } else {
        form.post('/supplier-invoices', {
            onSuccess: () => {
                showFormModal.value = false;
            },
        });
    }
};

// Detail Modal State
const showDetailModal = ref(false);
const selectedInvoice = ref<SupplierInvoice | null>(null);

const openDetailModal = (inv: SupplierInvoice) => {
    selectedInvoice.value = inv;
    showDetailModal.value = true;
};

// Pay Installment Modal State
const showPayModal = ref(false);
const payingInvoice = ref<SupplierInvoice | null>(null);

const payForm = useForm({
    payment_amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    notes: '',
});

const openPayModal = (inv: SupplierInvoice) => {
    payingInvoice.value = inv;
    payForm.clearErrors();
    payForm.payment_amount = inv.remaining_amount;
    payForm.payment_date = new Date().toISOString().split('T')[0];
    payForm.payment_method = 'cash';
    payForm.notes = '';
    showPayModal.value = true;
};

const submitPayForm = () => {
    if (!payingInvoice.value) return;
    payForm.post(`/supplier-invoices/${payingInvoice.value.id}/pay`, {
        onSuccess: () => {
            showPayModal.value = false;
        },
    });
};

// Delete Confirmation Modal State
const showDeleteModal = ref(false);
const deletingInvoice = ref<SupplierInvoice | null>(null);

const openDeleteModal = (inv: SupplierInvoice) => {
    deletingInvoice.value = inv;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!deletingInvoice.value) return;
    router.delete(`/supplier-invoices/${deletingInvoice.value.id}`, {
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
    }).format(val || 0);
};

const formatDate = (dateStr?: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Tagihan Supplier - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Tagihan Produk Supplier</h1>
                <p class="text-xs text-slate-500">Pencatatan invoice barang dari supplier, kategori per barang, & histori angsuran/cicilan</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Catat Tagihan Baru
            </button>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-bold text-slate-500">Total Tagihan Masuk</p>
                <p class="text-xl font-black text-slate-900 dark:text-white font-mono mt-1">
                    {{ formatRupiah(metrics.total_amount) }}
                </p>
            </div>

            <div class="rounded-2xl border border-red-200 bg-red-50/60 p-4 shadow-sm dark:border-red-950 dark:bg-red-950/30">
                <p class="text-xs font-bold text-red-700 dark:text-red-400">Total Belum Lunas (Sisa)</p>
                <p class="text-xl font-black text-red-600 dark:text-red-400 font-mono mt-1">
                    {{ formatRupiah(metrics.unpaid_amount) }}
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 shadow-sm dark:border-emerald-950 dark:bg-emerald-950/30">
                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Total Sudah Dibayar</p>
                <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                    {{ formatRupiah(metrics.paid_amount) }}
                </p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 shadow-sm dark:border-amber-950 dark:bg-amber-950/30">
                <p class="text-xs font-bold text-amber-700 dark:text-amber-400">Jatuh Tempo (7 Hari Ke Depan)</p>
                <p class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono mt-1">
                    {{ metrics.due_soon_count }} Tagihan
                </p>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="flex flex-col sm:flex-row items-center gap-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Cari kode invoice supplier atau barang..."
                    class="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-4 text-xs font-semibold focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                />
            </div>

            <select
                v-model="statusFilter"
                @change="applyFilters"
                class="w-full sm:w-44 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Lunas</option>
                <option value="partially_paid">Dicicil / Sebagian</option>
                <option value="paid">Lunas</option>
            </select>

            <select
                v-model="supplierFilter"
                @change="applyFilters"
                class="w-full sm:w-48 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Supplier</option>
                <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.name }}</option>
            </select>
        </div>

        <!-- Data Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Kode Invoice</th>
                        <th class="p-4">Supplier</th>
                        <th class="p-4">Rincian Barang & Kategori</th>
                        <th class="p-4">Total Tagihan</th>
                        <th class="p-4">Jatuh Tempo</th>
                        <th class="p-4">Dibayar & Sisa</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ inv.invoice_number }}
                        </td>
                        <td class="p-4 font-bold text-slate-800 dark:text-slate-200">
                            {{ inv.supplier?.name || '-' }}
                        </td>
                        <td class="p-4 space-y-1">
                            <div v-for="item in inv.items" :key="item.id" class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-slate-900 dark:text-white text-xs">{{ item.product_name }}</span>
                                <span v-if="item.category" class="rounded bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                    {{ item.category.name }}
                                </span>
                                <span class="text-[10px] font-mono text-slate-500">({{ item.quantity }}x @ {{ formatRupiah(item.unit_price) }})</span>
                            </div>
                        </td>
                        <td class="p-4 font-mono font-black text-slate-900 dark:text-white text-xs">
                            {{ formatRupiah(inv.total_amount) }}
                        </td>
                        <td class="p-4 font-mono text-[11px]">
                            <span class="font-bold text-slate-700 dark:text-slate-300 block">{{ formatDate(inv.due_date) }}</span>
                        </td>
                        <td class="p-4 font-mono">
                            <span class="text-emerald-600 font-bold block text-[11px]">Dibayar: {{ formatRupiah(inv.paid_amount) }}</span>
                            <span class="text-red-600 font-black block text-xs">Sisa: {{ formatRupiah(inv.remaining_amount) }}</span>
                        </td>
                        <td class="p-4">
                            <span
                                class="rounded px-2 py-0.5 font-black uppercase text-[9px]"
                                :class="
                                    inv.status === 'paid'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : inv.status === 'partially_paid'
                                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                        : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                "
                            >
                                {{ inv.status === 'paid' ? 'LUNAS' : inv.status === 'partially_paid' ? 'DICICIL' : 'BELUM LUNAS' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button
                                    @click="openDetailModal(inv)"
                                    title="Lihat Rincian Tagihan & Histori Cicilan"
                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-600 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-400"
                                >
                                    <Eye class="h-3.5 w-3.5" /> Detail
                                </button>

                                <button
                                    v-if="inv.status !== 'paid'"
                                    @click="openPayModal(inv)"
                                    title="Bayar Cicilan / Angsuran"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-400"
                                >
                                    <DollarSign class="h-3.5 w-3.5" /> Cicil
                                </button>

                                <button
                                    @click="openEditModal(inv)"
                                    title="Edit Tagihan"
                                    class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-1 text-xs font-bold text-amber-600 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-400"
                                >
                                    <Edit3 class="h-3.5 w-3.5" />
                                </button>

                                <button
                                    @click="openDeleteModal(inv)"
                                    title="Hapus Tagihan"
                                    class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-100 dark:bg-red-950 dark:text-red-400"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="invoices.data.length === 0">
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            Belum ada data tagihan supplier recorded.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MULTI-ITEM FORM MODAL (ADD / EDIT) -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ isEditing ? 'Edit Tagihan Supplier' : 'Tambah Tagihan Supplier Baru (Multi-Item)' }}
                </h3>
                <button @click="showFormModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4 text-xs">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kode Invoice Supplier *</label>
                        <input
                            v-model="form.invoice_number"
                            type="text"
                            required
                            placeholder="Contoh: INV-SUP-2026-001"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-semibold text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Supplier</label>
                        <select
                            v-model="form.supplier_id"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option :value="null">-- Pilih Supplier --</option>
                            <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Jatuh Tempo</label>
                        <input
                            v-model="form.due_date"
                            type="date"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                </div>

                <!-- DYNAMIC ITEMS TABLE -->
                <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/50 dark:bg-slate-800/40 dark:border-slate-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">Daftar Barang & Kategori</h4>
                        <button
                            type="button"
                            @click="addItemRow"
                            class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700"
                        >
                            <Plus class="h-3.5 w-3.5" /> Tambah Barang
                        </button>
                    </div>

                    <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-2 items-end border-b border-slate-200 pb-2 dark:border-slate-700">
                        <div class="col-span-3">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Pilih Produk (Master)</label>
                            <select
                                v-model="item.product_id"
                                @change="onProductSelect(idx)"
                                class="w-full rounded-lg border border-slate-200 p-1.5 text-xs font-semibold dark:bg-slate-800 dark:text-white dark:border-slate-700"
                            >
                                <option :value="null">-- Manual / Bebas --</option>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>

                        <div class="col-span-3">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Nama Produk *</label>
                            <input
                                v-model="item.product_name"
                                type="text"
                                required
                                placeholder="Nama barang..."
                                class="w-full rounded-lg border border-slate-200 p-1.5 text-xs font-semibold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                            />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Kategori Barang</label>
                            <select
                                v-model="item.category_id"
                                class="w-full rounded-lg border border-slate-200 p-1.5 text-xs font-semibold dark:bg-slate-800 dark:text-white dark:border-slate-700"
                            >
                                <option :value="null">-- Kategori --</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Qty *</label>
                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border border-slate-200 p-1.5 text-xs font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                            />
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Harga Satuan (Rp) *</label>
                            <input
                                v-model.number="item.unit_price"
                                type="number"
                                min="0"
                                required
                                class="w-full rounded-lg border border-slate-200 p-1.5 text-xs font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                            />
                        </div>

                        <div class="col-span-1 text-right">
                            <button
                                type="button"
                                @click="removeItemRow(idx)"
                                :disabled="form.items.length <= 1"
                                class="p-1.5 text-red-500 hover:text-red-700 disabled:opacity-30"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TOTAL & INITIAL PAYMENT -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div v-if="!isEditing">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Pembayaran Awal / Cicilan Pertama (Rp)</label>
                        <input
                            v-model.number="form.paid_amount"
                            type="number"
                            min="0"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-emerald-600 dark:bg-slate-800 dark:text-emerald-400 dark:border-slate-700"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Total Tagihan (Semua Barang)</label>
                        <input
                            :value="formatRupiah(calculatedTotal)"
                            readonly
                            disabled
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-black text-lg text-blue-600 bg-slate-100 dark:bg-slate-800 dark:border-slate-700 dark:text-blue-400"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / Keterangan Tagihan</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Contoh: No. PO / Pengiriman via ekspedisi ABC..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-medium text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    ></textarea>
                </div>

                <div class="flex gap-2 pt-3">
                    <button
                        type="button"
                        @click="showFormModal = false"
                        class="w-1/3 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-2/3 rounded-xl bg-blue-600 hover:bg-blue-700 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30"
                    >
                        Simpan Data Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BAYAR CICILAN MODAL -->
    <div v-if="showPayModal && payingInvoice" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <DollarSign class="h-5 w-5 text-emerald-500" /> Bayar Angsuran / Cicilan
                    </h3>
                    <p class="text-xs text-slate-500 font-mono">{{ payingInvoice.invoice_number }}</p>
                </div>
                <button @click="showPayModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60 text-xs space-y-1.5 border border-slate-200 dark:border-slate-700 font-mono">
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Tagihan:</span>
                    <span class="font-bold">{{ formatRupiah(payingInvoice.total_amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Sudah Dibayar:</span>
                    <span class="font-bold text-emerald-600">{{ formatRupiah(payingInvoice.paid_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm font-black border-t border-slate-200 pt-1 dark:border-slate-700">
                    <span>Sisa Tagihan:</span>
                    <span class="text-red-600 font-mono">{{ formatRupiah(payingInvoice.remaining_amount) }}</span>
                </div>
            </div>

            <form @submit.prevent="submitPayForm" class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Pembayaran *</label>
                        <input
                            v-model="payForm.payment_date"
                            type="date"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Metode Bayar *</label>
                        <select
                            v-model="payForm.payment_method"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option value="cash">Tunai / Cash</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jumlah Cicilan / Pembayaran (Rp) *</label>
                    <input
                        v-model.number="payForm.payment_amount"
                        type="number"
                        min="1"
                        step="any"
                        :max="payingInvoice.remaining_amount"
                        required
                        class="w-full rounded-xl border border-slate-200 p-2.5 text-base font-black text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / Bukti Bayar</label>
                    <input
                        v-model="payForm.notes"
                        type="text"
                        placeholder="Contoh: Transfer Bank BCA / Cash di toko..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-medium text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>

                <div class="flex gap-2 pt-2">
                    <button
                        type="button"
                        @click="showPayModal = false"
                        class="w-1/3 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="payForm.processing || payForm.payment_amount <= 0"
                        class="w-2/3 flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-600/30"
                    >
                        <CheckCircle2 class="h-4 w-4" /> Catat Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DETAIL MODAL WITH MULTI-ITEMS & PAYMENT TIMELINE -->
    <div v-if="showDetailModal && selectedInvoice" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Rincian Tagihan Supplier</h3>
                    <p class="text-xs text-slate-500 font-mono">{{ selectedInvoice.invoice_number }}</p>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <div>
                        <span class="text-slate-500 block text-[10px]">Supplier</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedInvoice.supplier?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Tanggal Jatuh Tempo</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">{{ formatDate(selectedInvoice.due_date) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Status</span>
                        <span
                            class="rounded px-2 py-0.5 font-black uppercase text-[9px] inline-block mt-0.5"
                            :class="
                                selectedInvoice.status === 'paid'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                    : selectedInvoice.status === 'partially_paid'
                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                    : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                            "
                        >
                            {{ selectedInvoice.status === 'paid' ? 'LUNAS' : selectedInvoice.status === 'partially_paid' ? 'DICICIL' : 'BELUM LUNAS' }}
                        </span>
                    </div>
                </div>

                <!-- ITEMS TABLE -->
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2">Rincian Barang</h4>
                    <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800 text-[10px] uppercase font-bold">
                                <tr>
                                    <th class="p-2.5">Nama Produk</th>
                                    <th class="p-2.5">Kategori</th>
                                    <th class="p-2.5">Qty</th>
                                    <th class="p-2.5">Harga Satuan</th>
                                    <th class="p-2.5 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="it in selectedInvoice.items" :key="it.id">
                                    <td class="p-2.5 font-bold text-slate-900 dark:text-white">{{ it.product_name }}</td>
                                    <td class="p-2.5">
                                        <span v-if="it.category" class="rounded bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                            {{ it.category.name }}
                                        </span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>
                                    <td class="p-2.5 font-bold font-mono">{{ it.quantity }}</td>
                                    <td class="p-2.5 font-mono">{{ formatRupiah(it.unit_price) }}</td>
                                    <td class="p-2.5 font-mono font-bold text-right">{{ formatRupiah(it.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FINANCIAL BREAKDOWN -->
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-1 font-mono">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Tagihan:</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ formatRupiah(selectedInvoice.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600">
                        <span>Total Sudah Dibayar:</span>
                        <span class="font-bold">{{ formatRupiah(selectedInvoice.paid_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-red-600 font-black text-sm pt-1 border-t border-slate-200 dark:border-slate-700">
                        <span>Sisa Tagihan:</span>
                        <span>{{ formatRupiah(selectedInvoice.remaining_amount) }}</span>
                    </div>
                </div>

                <!-- PAYMENT HISTORY TIMELINE -->
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-2">Histori Cicilan / Angsuran Pembayaran</h4>
                    <div v-if="selectedInvoice.payments && selectedInvoice.payments.length > 0" class="space-y-2">
                        <div
                            v-for="p in selectedInvoice.payments"
                            :key="p.id"
                            class="flex items-center justify-between p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/50 dark:bg-emerald-950/30 dark:border-emerald-900"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold font-mono text-emerald-700 dark:text-emerald-400 text-xs">+{{ formatRupiah(p.payment_amount) }}</span>
                                    <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[9px] font-black uppercase text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">
                                        {{ p.payment_method }}
                                    </span>
                                </div>
                                <p v-if="p.notes" class="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5">{{ p.notes }}</p>
                            </div>
                            <div class="text-right text-[10px] font-mono text-slate-500">
                                {{ formatDate(p.payment_date) }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-4 text-center text-xs text-slate-400 rounded-xl border border-dashed border-slate-200">
                        Belum ada histori angsuran/cicilan pembayaran recorded.
                    </div>
                </div>

                <div v-if="selectedInvoice.notes">
                    <span class="text-slate-500 block text-[10px] mb-1 font-bold">Catatan Invoice:</span>
                    <p class="whitespace-pre-line text-[11px] font-mono text-slate-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900">
                        {{ selectedInvoice.notes }}
                    </p>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button
                    @click="showDetailModal = false"
                    class="w-full rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div v-if="showDeleteModal && deletingInvoice" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center gap-3 rounded-xl bg-red-50 p-3 text-red-900 dark:bg-red-950/80 dark:text-red-200 border border-red-200 dark:border-red-800">
                <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400 shrink-0" />
                <div>
                    <h4 class="font-bold text-xs">Hapus Tagihan {{ deletingInvoice.invoice_number }}?</h4>
                    <p class="text-[11px] text-red-700 dark:text-red-300">Tindakan ini akan menghapus data tagihan supplier dan seluruh histori cicilan dari database.</p>
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
                    @click="confirmDelete"
                    class="w-1/2 rounded-xl bg-red-600 hover:bg-red-700 py-2.5 text-xs font-bold text-white shadow-md shadow-red-600/30"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</template>
