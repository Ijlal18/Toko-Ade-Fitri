<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, Edit3, Mail, Phone, Plus, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Supplier {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    notes: string | null;
    products_count: number;
}

const props = defineProps<{
    suppliers: Supplier[];
}>();

const showModal = ref(false);
const isEditing = ref(false);
const editingSupplier = ref<Supplier | null>(null);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    notes: '',
});

const openAddModal = () => {
    isEditing.value = false;
    editingSupplier.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (sup: Supplier) => {
    isEditing.value = true;
    editingSupplier.value = sup;
    form.name = sup.name;
    form.phone = sup.phone || '';
    form.email = sup.email || '';
    form.address = sup.address || '';
    form.notes = sup.notes || '';
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && editingSupplier.value) {
        form.put(`/suppliers/${editingSupplier.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/suppliers', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const deleteSupplier = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus supplier ini?')) {
        router.delete(`/suppliers/${id}`);
    }
};
</script>

<template>
    <Head title="Supplier Pemasok - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Daftar Supplier / Pemasok</h1>
                <p class="text-xs text-slate-500">Kelola informasi pemasok bahan kue dan plastik toko</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Tambah Supplier
            </button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="sup in suppliers"
                :key="sup.id"
                class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
            >
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-extrabold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                            <Building2 class="h-3.5 w-3.5" /> {{ sup.products_count }} Produk Suplai
                        </span>
                        <div class="flex items-center gap-1">
                            <button @click="openEditModal(sup)" class="p-1.5 text-slate-400 hover:text-blue-600">
                                <Edit3 class="h-3.5 w-3.5" />
                            </button>
                            <button @click="deleteSupplier(sup.id)" class="p-1.5 text-slate-400 hover:text-red-600">
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ sup.name }}</h3>

                    <div class="mt-3 space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                        <p v-if="sup.phone" class="flex items-center gap-2">
                            <Phone class="h-3.5 w-3.5 text-blue-600" /> {{ sup.phone }}
                        </p>
                        <p v-if="sup.email" class="flex items-center gap-2">
                            <Mail class="h-3.5 w-3.5 text-blue-600" /> {{ sup.email }}
                        </p>
                        <p v-if="sup.address" class="text-[11px] text-slate-500">
                            {{ sup.address }}
                        </p>
                    </div>
                </div>

                <div v-if="sup.notes" class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 italic dark:border-slate-800">
                    "{{ sup.notes }}"
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ isEditing ? 'Edit Supplier' : 'Tambah Supplier Baru' }}
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Supplier *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: PT Bogasari"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / HP</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="0812..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="sales@..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                        />
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Pemasok</label>
                    <textarea
                        v-model="form.address"
                        rows="2"
                        placeholder="Alamat gudang / kantor supplier..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-semibold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    ></textarea>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                    <input
                        v-model="form.notes"
                        type="text"
                        placeholder="Catatan barang suplai..."
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-semibold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
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
                        Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
