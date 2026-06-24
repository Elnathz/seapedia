<script setup lang="ts">
import { CheckCircle2 } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import { orderStatusLabel } from '@/lib/orderStatus';
import type { OrderStatusKey } from '@/lib/orderStatus';
import { formatDateTime } from '@/lib/utils';

interface HistoryEntry {
    id: number;
    status: OrderStatusKey;
    note: string | null;
    created_at: string;
}

defineProps<{
    histories: HistoryEntry[];
}>();

const { locale } = useI18n();
</script>

<template>
    <ol class="space-y-4">
        <li
            v-for="(entry, index) in histories"
            :key="entry.id"
            class="relative flex gap-3 pb-1 pl-1"
        >
            <span class="flex flex-col items-center">
                <CheckCircle2 class="size-5 text-primary" />
                <span
                    v-if="index < histories.length - 1"
                    class="mt-1 w-px flex-1 bg-border"
                />
            </span>
            <div class="flex-1 pb-2">
                <p class="font-medium">{{ orderStatusLabel(entry.status) }}</p>
                <p class="text-xs text-muted-foreground">
                    {{ formatDateTime(entry.created_at, locale) }}
                </p>
                <p v-if="entry.note" class="mt-1 text-sm text-muted-foreground">
                    {{ entry.note }}
                </p>
            </div>
        </li>
    </ol>
</template>
