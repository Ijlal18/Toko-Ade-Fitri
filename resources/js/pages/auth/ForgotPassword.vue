<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Lupa Password',
        description: 'Masukkan email Anda untuk menerima tautan reset password',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Lupa Password - Toko Ade Fitri" />

    <div
        v-if="status"
        class="mb-4 rounded-lg bg-emerald-50 p-3 text-center text-xs font-semibold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200"
    >
        {{ status }}
    </div>

    <div class="space-y-6">
        <Form v-bind="email.form()" v-slot="{ errors, processing }">
            <div class="grid gap-2">
                <Label for="email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">Alamat Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    autofocus
                    placeholder="admin@tokoadefitri.com"
                    class="h-10 border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="my-6 flex items-center justify-start">
                <Button
                    class="h-11 w-full font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/25 transition-all"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" class="mr-2 h-4 w-4" />
                    Kirim Link Reset Password
                </Button>
            </div>
        </Form>

        <div class="space-x-1 text-center text-xs text-slate-600 dark:text-slate-400">
            <span>Atau, kembali ke</span>
            <TextLink :href="login()" class="font-bold text-blue-600 hover:underline dark:text-blue-400">halaman login</TextLink>
        </div>
    </div>
</template>
