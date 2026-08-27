<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Masuk ke Sistem POS Toko',
        description: 'Masukkan email dan password akun staff untuk melanjutkan',
    },
});

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();
</script>

<template>
    <Head title="Login Kasir & Admin - Toko Ade Fitri" />

    <div
        v-if="status"
        class="mb-4 rounded-lg bg-emerald-50 p-3 text-center text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200"
    >
        {{ status }}
    </div>

    <Form
        action="/login"
        method="post"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Email Staff / Kasir</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="admin@tokoadefitri.com"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Kata Sandi</Label>
                    <Link
                        v-if="canResetPassword"
                        href="/forgot-password"
                        :tabindex="5"
                        class="text-xs text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 font-medium"
                    >
                        Lupa Password?
                    </Link>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <Label for="remember" class="flex items-center space-x-2 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Ingat Saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 h-11 w-full font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/25 transition-all"
                :tabindex="4"
                :disabled="processing"
            >
                <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                Masuk
            </Button>
        </div>

        <div class="text-center text-xs text-slate-600 dark:text-slate-400 pt-1">
            Belum punya akun staff?
            <Link href="/register" class="font-bold text-blue-600 hover:underline dark:text-blue-400">Daftar Akun Baru</Link>
        </div>
    </Form>
</template>
