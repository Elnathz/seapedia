<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';

type Accent = 'default' | 'primary' | 'amber' | 'sky' | 'destructive';

withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        icon?: LucideIcon;
        accent?: Accent;
        hint?: string;
    }>(),
    { accent: 'default' },
);

const accentBar: Record<Accent, string> = {
    default: 'bg-border',
    primary: 'bg-primary',
    amber: 'bg-amber-500',
    sky: 'bg-sky-500',
    destructive: 'bg-destructive',
};

const accentIcon: Record<Accent, string> = {
    default: 'text-muted-foreground',
    primary: 'text-primary',
    amber: 'text-amber-600',
    sky: 'text-sky-600',
    destructive: 'text-destructive',
};
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl border bg-card p-4 transition-shadow hover:shadow-sm"
    >
        <span
            class="absolute inset-y-0 left-0 w-1"
            :class="accentBar[accent]"
            aria-hidden="true"
        />
        <div class="flex items-start justify-between gap-3 pl-2">
            <div class="min-w-0">
                <p
                    class="text-[0.7rem] font-medium tracking-wider text-muted-foreground uppercase"
                >
                    {{ label }}
                </p>
                <p class="mt-1.5 text-2xl font-semibold tabular-nums">
                    {{ value }}
                </p>
                <p v-if="hint" class="mt-1 text-xs text-muted-foreground">
                    {{ hint }}
                </p>
            </div>
            <component
                :is="icon"
                v-if="icon"
                class="size-5 shrink-0"
                :class="accentIcon[accent]"
            />
        </div>
    </div>
</template>
