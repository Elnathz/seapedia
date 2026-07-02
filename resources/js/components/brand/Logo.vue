<script setup lang="ts">
interface Props {
    variant?: 'full' | 'mark';
}

withDefaults(defineProps<Props>(), { variant: 'full' });

defineOptions({ inheritAttrs: false });
</script>

<template>
    <!-- Full lockup: triskelion mark + two-tone wordmark -->
    <svg
        v-if="variant === 'full'"
        v-bind="$attrs"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 458 120"
        role="img"
        aria-label="SEAPEDIA"
    >
        <g transform="rotate(55 65 56)" fill="#13B5C4">
            <path d="M61 43 C49 25 60 16 85 17 C57 18 64 30 61 41 Z" />
        </g>
        <g transform="rotate(190 64 60)" fill="#0E9AA6">
            <path d="M61 43 C49 25 60 16 85 17 C57 18 64 30 61 41 Z" />
        </g>
        <g transform="rotate(315 64 74)" fill="#0A7180">
            <path d="M61 43 C49 25 60 16 85 17 C57 18 64 30 61 41 Z" />
        </g>
        <path
            d="M55 50 C62 50 70 55 69 61 C69 67 63 70 57 69 C52 68 49 63 50 58 C51 55 54 53 59 55"
            fill="none"
            stroke="#0A7180"
            stroke-width="2.2"
            stroke-linecap="round"
        />
        <circle cx="60" cy="60" r="1.7" fill="#0A7180" />
        <text
            x="120"
            y="76"
            font-family="'Plus Jakarta Sans','Segoe UI',sans-serif"
            font-weight="800"
            font-size="52"
            letter-spacing="-1.5"
        >
            <tspan fill="#0A7180">SEA</tspan>
            <tspan fill="#13B5C4">PEDIA</tspan>
        </text>
    </svg>

    <!-- Mark only: triskelion for favicon / compact contexts -->
    <svg
        v-else
        v-bind="$attrs"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="20 6 104 104"
        role="img"
        aria-label="SEAPEDIA"
    >
        <g transform="rotate(55 65 56)" fill="#13B5C4">
            <path d="M61 43 C49 25 60 16 85 17 C57 18 64 30 61 41 Z" />
        </g>
        <g transform="rotate(190 64 60)" fill="#0E9AA6">
            <path d="M61 43 C49 25 60 16 85 17 C57 18 64 30 61 41 Z" />
        </g>
        <g transform="rotate(315 64 74)" fill="#0A7180">
            <path d="M61 43 C49 25 60 16 85 17 C57 18 64 30 61 41 Z" />
        </g>
        <path
            d="M55 50 C62 50 70 55 69 61 C69 67 63 70 57 69 C52 68 49 63 50 58 C51 55 54 53 59 55"
            fill="none"
            stroke="#0A7180"
            stroke-width="2.2"
            stroke-linecap="round"
        />
        <circle cx="60" cy="60" r="1.7" fill="#0A7180" />
    </svg>
</template>
