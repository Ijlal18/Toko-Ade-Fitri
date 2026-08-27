<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 200 200"
        :class="className"
        v-bind="$attrs"
    >
        <!-- AF Monogram Logo -->
        <g fill="currentColor">
            <!-- A & F Interlocking Structure -->
            <path d="M 85 20 L 125 125 H 98 L 88 95 H 58 L 45 125 H 20 L 70 20 Z M 73 50 L 64 78 H 82 Z" />
            <path d="M 95 20 H 175 V 45 H 122 V 70 H 165 V 93 H 122 V 125 H 95 Z" />
            <!-- Artistic Swirl Flourish -->
            <path d="M 60 125 C 15 125 5 150 22 175 C 38 198 78 192 78 165 C 78 148 60 142 48 154 C 40 162 48 174 58 168 C 63 165 60 155 53 155 C 42 155 42 180 62 178 C 76 176 82 142 50 138 H 130 V 125 Z" />
        </g>
    </svg>
</template>
