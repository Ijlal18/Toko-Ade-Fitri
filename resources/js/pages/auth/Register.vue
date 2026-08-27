<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineProps<{
    passwordRules?: string;
}>();

defineOptions({
    layout: {
        title: 'Pendaftaran Akun Staff Baru',
        description: 'Lengkapi formulir di bawah ini untuk membuat akun karyawan kasir baru',
    },
});
</script>

<template>
    <Head title="Daftar Akun Baru - Toko Ade Fitri" />

    <Form
        action="/register"
        method="post"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nama Lengkap Staff *</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Siti Kasir"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Alamat Email *</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="staff@tokoadefitri.com"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kata Sandi *</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Minimal 8 karakter"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Konfirmasi Kata Sandi *</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Ulangi kata sandi"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 h-11 w-full font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/25 transition-all"
                :tabindex="5"
                :disabled="processing"
            >
                <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                Daftar Akun Staff
            </Button>
        </div>

        <div class="text-center text-xs text-slate-600 dark:text-slate-400">
            Sudah memiliki akun?
            <Link href="/login" class="font-bold text-blue-600 hover:underline dark:text-blue-400">Masuk di sini</Link>
        </div>
    </Form>
</template>
