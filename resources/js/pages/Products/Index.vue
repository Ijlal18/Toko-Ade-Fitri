<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Boxes,
    Edit3,
    Filter,
    Plus,
    PlusCircle,
    Search,
    Trash2,
    X,
} from '@lucide/vue';
import { ref } from 'vue';
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
    category_id: number;
    supplier_id?: number | null;
    supplier_ids?: number[];
    sku: string;
    barcode: string;
    name: string;
    purchase_price: number;
    selling_price_retail: number;
    selling_price_wholesale: number;
    stock: number;
    min_stock: number;
    unit: string;
    notes?: string | null;
    is_active: boolean;
    category?: Category;
    supplier?: Supplier;
    suppliers?: Supplier[];
}

const props = defineProps<{
    products: {
        data: Product[];
        links: any[];
        total: number;
    };
    categories: Category[];
    suppliers: Supplier[];
    filters: {
        search?: string;
        category_id?: string;
        low_stock?: boolean;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category_id || '');
const lowStockFilter = ref(props.filters.low_stock || false);

const applyFilters = () => {
    router.get(
        '/products',
        {
            search: searchQuery.value,
            category_id: categoryFilter.value,
            low_stock: lowStockFilter.value ? 1 : 0,
        },
        { preserveState: true }
    );
};

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const editingProduct = ref<Product | null>(null);

const form = useForm({
    id: null as number | null,
    category_id: null as number | null,
    supplier_id: null as number | null,
    supplier_ids: [] as number[],
    sku: '',
    barcode: '',
    name: '',
    purchase_price: 0,
    selling_price_retail: 0,
    selling_price_wholesale: 0,
    stock: 0,
    min_stock: 5,
    unit: 'pcs',
    notes: '',
    is_active: true,
});

const toggleSupplierSelection = (id: number) => {
    const idx = form.supplier_ids.indexOf(id);
    if (idx > -1) {
        form.supplier_ids.splice(idx, 1);
    } else {
        form.supplier_ids.push(id);
    }
};

const openAddModal = () => {
    isEditing.value = false;
    editingProduct.value = null;
    form.reset();
    form.supplier_ids = [];
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (product: Product) => {
    isEditing.value = true;
    editingProduct.value = product;
    form.clearErrors();
    form.id = product.id;
    form.category_id = product.category_id;
    form.supplier_id = product.supplier_id ?? null;
    form.supplier_ids = product.suppliers && product.suppliers.length > 0 ? product.suppliers.map((s) => s.id) : product.supplier_id ? [product.supplier_id] : [];
    form.sku = product.sku;
    form.barcode = product.barcode || '';
    form.name = product.name;
    form.purchase_price = Number(product.purchase_price);
    form.selling_price_retail = Number(product.selling_price_retail);
    form.selling_price_wholesale = Number(product.selling_price_wholesale);
    form.stock = product.stock;
    form.min_stock = product.min_stock;
    form.unit = product.unit;
    form.notes = product.notes || '';
    form.is_active = product.is_active;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && editingProduct.value) {
        form.put(`/products/${editingProduct.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post('/products', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const deleteProduct = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        router.delete(`/products/${id}`);
    }
};

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};
</script>

<template>
    <Head title="Manajemen Produk - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Daftar Produk Toko</h1>
                <p class="text-xs text-slate-500">Kelola master barang, harga eceran, harga grosir, dan stok minimum</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Tambah Produk Baru
            </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-col sm:flex-row items-center gap-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Cari nama produk, SKU, barcode..."
                    class="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-4 text-xs font-semibold focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                />
            </div>

            <select
                v-model="categoryFilter"
                @change="applyFilters"
                class="w-full sm:w-48 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>

            <label class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                <input
                    v-model="lowStockFilter"
                    @change="applyFilters"
                    type="checkbox"
                    class="rounded text-blue-600 focus:ring-blue-500"
                />
                <span>Hanya Stok Menipis</span>
            </label>

            <button
                @click="applyFilters"
                class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300"
            >
                Filter
            </button>
        </div>

        <!-- Products Data Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">SKU / Barcode</th>
                        <th class="p-4">Nama Produk</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Supplier</th>
                        <th class="p-4">Harga Beli</th>
                        <th class="p-4">Harga Eceran</th>
                        <th class="p-4">Harga Grosir</th>
                        <th class="p-4">Stok</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                        v-for="prod in products.data"
                        :key="prod.id"
                        class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors"
                    >
                        <td class="p-4 font-mono">
                            <span class="font-bold text-blue-600 dark:text-blue-400 block">{{ prod.sku }}</span>
                            <span class="text-[10px] text-slate-400">{{ prod.barcode || '-' }}</span>
                        </td>
                        <td class="p-4 font-bold text-slate-900 dark:text-white max-w-xs">
                            <div>{{ prod.name }}</div>
                            <div v-if="prod.notes" class="text-[10px] font-normal text-amber-600 dark:text-amber-400 mt-1 whitespace-pre-line wrap-break-word leading-relaxed bg-amber-50/80 dark:bg-amber-950/40 p-1.5 rounded-lg border border-amber-200/80 dark:border-amber-900/50">
                                <span class="font-bold">📝 Catatan:</span> {{ prod.notes }}
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="rounded bg-blue-50 px-2 py-0.5 font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                {{ prod.category?.name || '-' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="s in (prod.suppliers && prod.suppliers.length > 0 ? prod.suppliers : (prod.supplier ? [prod.supplier] : []))"
                                    :key="s.id"
                                    class="rounded bg-slate-100 px-1.5 py-0.5 font-semibold text-[10px] text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ s.name }}
                                </span>
                                <span v-if="(!prod.suppliers || prod.suppliers.length === 0) && !prod.supplier" class="text-slate-400">-</span>
                            </div>
                        </td>
                        <td class="p-4 text-slate-500 font-mono">
                            {{ formatRupiah(prod.purchase_price) }}
                        </td>
                        <td class="p-4 font-bold text-blue-600 dark:text-blue-400 font-mono">
                            {{ formatRupiah(prod.selling_price_retail) }}
                        </td>
                        <td class="p-4 font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ formatRupiah(prod.selling_price_wholesale) }}
                        </td>
                        <td class="p-4">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 font-extrabold text-[10px]"
                                :class="prod.stock <= prod.min_stock ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'"
                            >
                                {{ prod.stock }} {{ prod.unit }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openEditModal(prod)"
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-400"
                                >
                                    <Edit3 class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    @click="deleteProduct(prod.id)"
                                    class="rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100 dark:bg-red-950 dark:text-red-400"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="products.data.length === 0">
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            Belum ada produk terdaftar.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL ADD / EDIT PRODUCT -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ isEditing ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Produk *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Tepung Terigu Segitiga Biru 1 KG"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-semibold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                        <select
                            v-model="form.category_id"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option :value="null">-- Pilih Kategori --</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Supplier (Bisa Lebih Dari 1)</label>
                        <div class="flex flex-wrap gap-1.5 p-2 rounded-xl border border-slate-200 dark:border-slate-700 min-h-10.5 max-h-32 overflow-y-auto bg-slate-50/50 dark:bg-slate-800/40">
                            <label
                                v-for="sup in suppliers"
                                :key="sup.id"
                                @click.prevent="toggleSupplierSelection(sup.id)"
                                class="cursor-pointer select-none rounded-lg px-2.5 py-1 text-xs font-bold transition-all border"
                                :class="form.supplier_ids.includes(sup.id)
                                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'"
                            >
                                <span v-if="form.supplier_ids.includes(sup.id)">✓ </span>{{ sup.name }}
                            </label>
                            <span v-if="suppliers.length === 0" class="text-xs text-slate-400 p-1">Belum ada supplier</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">SKU (Kode Barang)</label>
                        <input
                            v-model="form.sku"
                            type="text"
                            placeholder="Otomatis jika kosong"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-mono text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Barcode Scan</label>
                        <input
                            v-model="form.barcode"
                            type="text"
                            placeholder="Scan barcode"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-mono text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Harga Beli (Modal)</label>
                        <input
                            v-model.number="form.purchase_price"
                            type="number"
                            min="0"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-blue-600 mb-1">Harga ECERAN *</label>
                        <input
                            v-model.number="form.selling_price_retail"
                            type="number"
                            min="0"
                            required
                            class="w-full rounded-xl border border-blue-200 p-2.5 font-bold text-blue-700 dark:bg-slate-800 dark:text-blue-300 dark:border-blue-900"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-emerald-600 mb-1">Harga GROSIR *</label>
                        <input
                            v-model.number="form.selling_price_wholesale"
                            type="number"
                            min="0"
                            required
                            class="w-full rounded-xl border border-emerald-200 p-2.5 font-bold text-emerald-700 dark:bg-slate-800 dark:text-emerald-300 dark:border-emerald-900"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Stok Awal</label>
                        <input
                            v-model.number="form.stock"
                            type="number"
                            min="0"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Stok Minimum</label>
                        <input
                            v-model.number="form.min_stock"
                            type="number"
                            min="0"
                            required
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Satuan (Unit)</label>
                        <select
                            v-model="form.unit"
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        >
                            <option value="pcs">pcs</option>
                            <option value="kg">kg</option>
                            <option value="pack">pack</option>
                            <option value="dus">dus</option>
                            <option value="roll">roll</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Produk (Notes)</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Contoh: Khusus eceran, expired date, kondisi simpan dingin..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-medium text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    ></textarea>
                </div>

                <div class="flex gap-2 pt-3">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="w-1/3 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-2/3 rounded-xl bg-blue-600 hover:bg-blue-700 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30"
                    >
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
