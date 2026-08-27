<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit3, Plus, ShieldCheck, Trash2, UserCog, X } from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Role {
    id: number;
    name: string;
}

interface UserStaff {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

const props = defineProps<{
    users: UserStaff[];
    roles: Role[];
}>();

const showModal = ref(false);
const isEditing = ref(false);
const editingUser = ref<UserStaff | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'Karyawan',
});

const openAddModal = () => {
    isEditing.value = false;
    editingUser.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (u: UserStaff) => {
    isEditing.value = true;
    editingUser.value = u;
    form.name = u.name;
    form.email = u.email;
    form.password = '';
    form.role = u.role;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && editingUser.value) {
        form.put(`/users/${editingUser.value.id}`, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        form.post('/users', {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const deleteUser = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus akun staff ini?')) {
        router.delete(`/users/${id}`);
    }
};
</script>

<template>
    <Head title="Pengguna Staff - Toko Ade Fitri" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Manajemen Pengguna Staff</h1>
                <p class="text-xs text-slate-500">Kelola akun karyawan, pemilik (owner), dan administrator kasir</p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-blue-600/25 transition-all"
            >
                <Plus class="h-4 w-4" /> Tambah Staff Baru
            </button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800/50 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Nama Lengkap</th>
                        <th class="p-4">Alamat Email</th>
                        <th class="p-4">Hak Akses / Role</th>
                        <th class="p-4">Tanggal Terdaftar</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30">
                        <td class="p-4 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <UserCog class="h-4 w-4 text-blue-600" /> {{ u.name }}
                        </td>
                        <td class="p-4 font-mono text-slate-600 dark:text-slate-400">
                            {{ u.email }}
                        </td>
                        <td class="p-4">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 font-extrabold text-[10px]"
                                :class="u.role === 'Admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : (u.role === 'Owner' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300')"
                            >
                                <ShieldCheck class="h-3 w-3" /> {{ u.role }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-500 font-mono text-[11px]">
                            {{ u.created_at }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="openEditModal(u)" class="rounded-lg bg-blue-50 p-2 text-blue-600 hover:bg-blue-100">
                                    <Edit3 class="h-3.5 w-3.5" />
                                </button>
                                <button @click="deleteUser(u.id)" class="rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
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
                    {{ isEditing ? 'Edit Akun Staff' : 'Tambah Staff Baru' }}
                </h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Contoh: Siti Kasir"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Email *</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="kasir@tokoadefitri.com"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Password {{ isEditing ? '(Kosongkan jika tidak diubah)' : '*' }}
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        :required="!isEditing"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-900 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    />
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Role / Hak Akses *</label>
                    <select
                        v-model="form.role"
                        required
                        class="w-full rounded-xl border border-slate-200 p-2.5 font-bold text-slate-800 dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    >
                        <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                    </select>
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
                        Simpan Akun Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
