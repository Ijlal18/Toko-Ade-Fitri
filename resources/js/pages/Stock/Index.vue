<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowDownRight, ArrowLeftRight, ArrowUpRight, Plus, RefreshCw, X } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Product {
    id: number;
    name: string;
    stock: number;
    unit: string;
}

interface User {
    id: number;
    name: string;
}

interface StockMovement {
    id: number;
    product_id: number;
    user_id: number | null;
    type: 'in' | 'out' | 'adjustment';
    quantity: number;
    reason: string | null;
    reference: string | null;
    created_at: string;
    product?: Product;
    user?: User;
}

const props = defineProps<{
    movements: {
        data: StockMovement[];
        links: any[];
        total: number;
    };
    products: Product[];
    filters: {
        product_id?: string;
        type?: string;
    };
}>();

const selectedProductFilter = ref(props.filters.product_id || '');
const selectedTypeFilter = ref(props.filters.type || '');

const applyFilters = () => {
    router.get(
        '/stock',
        {
            product_id: selectedProductFilter.value,
            type: selectedTypeFilter.value,
        },
        { preserveState: true }
    );
};

const showModal = ref(false);

const form = useForm({
    product_id: null as number | null,
    type: 'in' as 'in' | 'out' | 'adjustment',
    quantity: 1,
    reason: '',
    reference: '',
});

const openAddModal = () => {
    form.reset();
    showModal.value = true;
};

const submitForm = () => {
    form.post('/stock', {
        onSuccess: () => (showModal.value = false),
    });
};

const formatDateTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};
</script>

<template>
    <Head title="Manajemen & Penyesuaian Stok - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Manajemen & Pergerakan Stok</h1>
                <p class="text-xs text-slate-500">Catat stok masuk dari supplier, stok keluar, atau penyesuaian stok opname</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Catat Pergerakan Stok
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-col sm:flex-row items-center gap-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <select
                v-model="selectedProductFilter"
                @change="applyFilters"
                class="w-full sm:w-64 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Produk</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (Stok: {{ p.stock }})</option>
            </select>

            <select
                v-model="selectedTypeFilter"
                @change="applyFilters"
                class="w-full sm:w-48 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Jenis Pergerakan</option>
                <option value="in">Stok Masuk (+)</option>
                <option value="out">Stok Keluar (-)</option>
                <option value="adjustment">Penyesuaian Opname</option>
            </select>
        </div>

        <!-- Stock Log Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Tanggal & Waktu</th>
                        <th class="p-4">Nama Produk</th>
                        <th class="p-4">Tipe Pergerakan</th>
                        <th class="p-4">Jumlah (Qty)</th>
                        <th class="p-4">Alasan / Keterangan</th>
                        <th class="p-4">Petugas / User</th>
                        <th class="p-4">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="m in movements.data" :key="m.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-mono text-slate-500 text-[11px]">
                            {{ formatDateTime(m.created_at) }}
                        </td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white">
                            {{ m.product?.name || 'Produk Dihapus' }}
                        </td>
                        <td class="p-4">
                            <span
                                v-if="m.type === 'in'"
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 font-black text-[10px] text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                            >
                                <ArrowDownRight class="h-3 w-3" /> Stok Masuk
                            </span>
                            <span
                                v-else-if="m.type === 'out'"
                                class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 font-black text-[10px] text-red-700 dark:bg-red-950 dark:text-red-300"
                            >
                                <ArrowUpRight class="h-3 w-3" /> Stok Keluar
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 font-black text-[10px] text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                            >
                                <RefreshCw class="h-3 w-3" /> Penyesuaian
                            </span>
                        </td>
                        <td class="p-4 font-bold font-mono">
                            <span :class="m.type === 'in' ? 'text-emerald-600' : (m.type === 'out' ? 'text-red-600' : 'text-amber-600')">
                                {{ m.type === 'in' ? '+' : (m.type === 'out' ? '-' : '') }}{{ m.quantity }} {{ m.product?.unit || 'pcs' }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-400">
                            {{ m.reason || '-' }}
                        </td>
                        <td class="p-4 text-slate-500 font-medium">
                            {{ m.user?.name || 'Sistem' }}
                        </td>
                        <td class="p-4 font-mono text-[11px] text-slate-400">
                            {{ m.reference || '-' }}
                        </td>
                    </tr>
                    <tr v-if="movements.data.length === 0">
                        <td colspan="7" class="p-12 text-center text-slate-400">
                            Belum ada catatan pergerakan stok.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL ADD STOCK MOVEMENT -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Catat Pergerakan / Penyesuaian Stok
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Produk *</label>
                    <select
                        v-model="form.product_id"
                        required
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    >
                        <option :value="null">-- Pilih Produk --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">
                            {{ p.name }} (Stok Saat Ini: {{ p.stock }} {{ p.unit }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tipe Pergerakan *</label>
                        <select
                            v-model="form.type"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option value="in">Stok Masuk (+)</option>
                            <option value="out">Stok Keluar (-)</option>
                            <option value="adjustment">Penyesuaian Target</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Jumlah (Qty) *</label>
                        <input
                            v-model.number="form.quantity"
                            type="number"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Alasan / Keterangan *</label>
                    <input
                        v-model="form.reason"
                        type="text"
                        required
                        placeholder="Contoh: Restok dari supplier / Barang kadaluarsa"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-semibold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">No. Referensi / PO / Faktur</label>
                    <input
                        v-model="form.reference"
                        type="text"
                        placeholder="PO-2026-001"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-mono text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>

                <div class="flex gap-2 pt-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="w-1/3 rounded-xl border border-slate-200 py-2.5 font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-2/3 rounded-xl bg-blue-600 hover:bg-blue-700 py-2.5 font-bold text-white shadow-lg shadow-blue-600/30"
                    >
                        Simpan Pergerakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
