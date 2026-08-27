<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Verifikasi Email Staff',
        description:
            'Tautan verifikasi telah dikirimkan ke email Anda. Silakan periksa kotak masuk atau spam email Anda.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Verifikasi Email - Toko Ade Fitri" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 rounded-lg bg-emerald-50 p-3 text-center text-xs font-semibold text-emerald-700 border border-emerald-200"
    >
        Tautan verifikasi baru telah dikirim ke alamat email yang Anda daftarkan saat pendaftaran.
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="secondary" class="w-full font-bold">
            <Spinner v-if="processing" class="mr-2 h-4 w-4" />
            Kirim Ulang Email Verifikasi
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm font-semibold text-slate-600 hover:text-slate-900">
            Keluar / Logout
        </TextLink>
    </Form>
</template>
