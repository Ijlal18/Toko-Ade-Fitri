<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Award, Edit3, Phone, Plus, Search, Trash2, Users, X } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Customer {
    id: number;
    name: string;
    phone: string | null;
    address: string | null;
    type: 'retail' | 'wholesale';
    points: number;
}

const props = defineProps<{
    customers: {
        data: Customer[];
        links: any[];
        total: number;
    };
    filters: {
        search?: string;
        type?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

const applyFilters = () => {
    router.get(
        '/customers',
        {
            search: searchQuery.value,
            type: typeFilter.value,
        },
        { preserveState: true }
    );
};

const showModal = ref(false);
const isEditing = ref(false);
const editingCustomer = ref<Customer | null>(null);

const form = useForm({
    name: '',
    phone: '',
    address: '',
    type: 'retail' as 'retail' | 'wholesale',
});

const openAddModal = () => {
    isEditing.value = false;
    editingCustomer.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (cust: Customer) => {
    isEditing.value = true;
    editingCustomer.value = cust;
    form.name = cust.name;
    form.phone = cust.phone || '';
    form.address = cust.address || '';
    form.type = cust.type;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && editingCustomer.value) {
        form.put(`/customers/${editingCustomer.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/customers', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const deleteCustomer = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus data pelanggan ini?')) {
        router.delete(`/customers/${id}`);
    }
};
</script>

<template>
    <Head title="Manajemen Pelanggan - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Daftar Pelanggan</h1>
                <p class="text-xs text-slate-500">Kelola pelanggan eceran & grosir toko beserta poin reward</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Tambah Pelanggan Baru
            </button>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Cari nama pelanggan, HP..."
                    class="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-4 text-xs font-semibold focus:border-blue-500 focus:outline-none dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                />
            </div>

            <select
                v-model="typeFilter"
                @change="applyFilters"
                class="w-full sm:w-48 rounded-xl border border-slate-200 py-2 px-3 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
            >
                <option value="">Semua Tipe Pelanggan</option>
                <option value="retail">Pelanggan Eceran</option>
                <option value="wholesale">Pelanggan Grosir</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Nama Pelanggan</th>
                        <th class="p-4">No. Telepon / HP</th>
                        <th class="p-4">Tipe Harga</th>
                        <th class="p-4">Poin Reward</th>
                        <th class="p-4">Alamat</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="c in customers.data" :key="c.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Users class="h-4 w-4 text-blue-600" /> {{ c.name }}
                        </td>
                        <td class="p-4 font-mono text-slate-600 dark:text-slate-400">
                            {{ c.phone || '-' }}
                        </td>
                        <td class="p-4">
                            <span
                                class="rounded px-2 py-0.5 font-black text-[10px] uppercase"
                                :class="c.type === 'wholesale' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'"
                            >
                                {{ c.type === 'wholesale' ? 'Grosir' : 'Eceran' }}
                            </span>
                        </td>
                        <td class="p-4 font-bold text-amber-600 flex items-center gap-1">
                            <Award class="h-3.5 w-3.5" /> {{ c.points }} Poin
                        </td>
                        <td class="p-4 text-slate-500 text-[11px]">
                            {{ c.address || '-' }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="openEditModal(c)" class="rounded-lg bg-blue-50 p-2 text-blue-600 hover:bg-blue-100">
                                    <Edit3 class="h-3.5 w-3.5" />
                                </button>
                                <button @click="deleteCustomer(c.id)" class="rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td colspan="6" class="p-12 text-center text-slate-400">
                            Belum ada pelanggan terdaftar.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ isEditing ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' }}
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Pelanggan / Toko *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Budi Santoso (Bakery Rose)"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tipe Penetapan Harga *</label>
                    <select
                        v-model="form.type"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    >
                        <option value="retail">Pelanggan Eceran (Harga Normal)</option>
                        <option value="wholesale">Pelanggan Grosir (Harga Spesial Grosir)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">No. WhatsApp / HP</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        placeholder="0812..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea
                        v-model="form.address"
                        rows="2"
                        placeholder="Alamat rumah / toko pelanggan..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-semibold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    ></textarea>
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
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
