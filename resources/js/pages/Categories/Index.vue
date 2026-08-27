<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit3, Plus, Tags, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    products_count: number;
}

const props = defineProps<{
    categories: Category[];
}>();

const showModal = ref(false);
const isEditing = ref(false);
const editingCategory = ref<Category | null>(null);

const form = useForm({
    name: '',
    description: '',
});

const openAddModal = () => {
    isEditing.value = false;
    editingCategory.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (cat: Category) => {
    isEditing.value = true;
    editingCategory.value = cat;
    form.name = cat.name;
    form.description = cat.description || '';
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && editingCategory.value) {
        form.put(`/categories/${editingCategory.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/categories', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        router.delete(`/categories/${id}`);
    }
};
</script>

<template>
    <Head title="Kategori Produk - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Kategori Produk</h1>
                <p class="text-xs text-slate-500">Kelola kelompok barang (Bahan Kue, Plastik & Kemasan, Sembako, dll)</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Tambah Kategori
            </button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="cat in categories"
                :key="cat.id"
                class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
            >
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-extrabold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                            <Tags class="h-3.5 w-3.5" /> {{ cat.products_count }} Produk
                        </span>
                        <div class="flex items-center gap-1">
                            <button @click="openEditModal(cat)" class="p-1.5 text-slate-400 hover:text-blue-600">
                                <Edit3 class="h-3.5 w-3.5" />
                            </button>
                            <button @click="deleteCategory(cat.id)" class="p-1.5 text-slate-400 hover:text-red-600">
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ cat.name }}</h3>
                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ cat.description || 'Tidak ada deskripsi' }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[10px] text-slate-400 font-mono dark:border-slate-800">
                    Slug: {{ cat.slug }}
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Kategori *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Plastik & Kemasan"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Keterangan singkat kategori..."
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
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
