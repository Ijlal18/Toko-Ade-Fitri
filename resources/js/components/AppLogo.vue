<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center gap-2.5">
        <div
            class="flex aspect-square size-9 items-center justify-center rounded-lg bg-blue-600 text-white shadow-md shadow-blue-500/20"
        >
            <AppLogoIcon class="size-6 text-white" />
        </div>
        <div class="flex flex-col text-left">
            <span class="text-sm font-extrabold tracking-tight text-blue-600 dark:text-blue-400 leading-none">
                Toko Bahan Kue
            </span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-tight">
                Dan Plastik
            </span>
            <span class="mt-0.5 inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black tracking-wider uppercase bg-emerald-600 text-white w-fit">
                Grosir & Eceran
            </span>
        </div>
    </div>
</template>
