<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-slate-50 to-blue-100/50 p-6 md:p-10 dark:from-slate-950 dark:via-slate-900 dark:to-blue-950/40"
    >
        <div class="w-full max-w-md">
            <div class="flex flex-col gap-6 rounded-2xl bg-white/80 p-8 shadow-xl shadow-blue-500/10 backdrop-blur-xl border border-blue-100 dark:bg-slate-900/80 dark:border-slate-800">
                <div class="flex flex-col items-center gap-4 text-center">
                    <Link href="/login" class="flex items-center gap-2 font-medium">
                        <AppLogo />
                    </Link>
                    <div class="space-y-1.5 mt-2">
                        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ title || 'Selamat Datang' }}
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ description || 'Sistem Point of Sale Toko Ade Fitri' }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
