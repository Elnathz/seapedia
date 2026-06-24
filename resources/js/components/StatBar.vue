<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        value: number;
        total: number;
        /** Tailwind bg-* class for the filled track. */
        fillClass?: string;
        emphasize?: boolean;
    }>(),
    { fillClass: 'bg-primary', emphasize: false },
);

const pct = computed(() => {
    if (props.total <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((props.value / props.total) * 100));
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <div class="flex items-baseline justify-between gap-3">
            <span
                class="truncate text-sm"
                :class="
                    emphasize
                        ? 'font-semibold text-foreground'
                        : 'text-muted-foreground'
                "
            >
                {{ label }}
            </span>
            <span class="text-sm font-semibold tabular-nums">{{ value }}</span>
        </div>
        <div class="h-1.5 overflow-hidden rounded-full bg-muted">
            <div
                class="h-full rounded-full transition-[width] duration-500 motion-reduce:transition-none"
                :class="fillClass"
                :style="{ width: `${pct}%` }"
            />
        </div>
    </div>
</template>
