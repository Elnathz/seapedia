<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, Truck, Wallet } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import DriverJobController from '@/actions/App/Http/Controllers/Web/DriverJobController';
import EmptyState from '@/components/EmptyState.vue';
import StatTile from '@/components/StatTile.vue';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    deliveryStatusBadgeVariant,
    deliveryStatusLabel,
} from '@/lib/deliveryStatus';
import type { DeliveryStatusKey } from '@/lib/deliveryStatus';
import { formatDateTime, formatIDR } from '@/lib/utils';

interface ActiveJob {
    id: number;
    status: DeliveryStatusKey;
    order: {
        code: string;
        ship_address: string;
        store: { name: string };
    };
}

interface HistoryEntry {
    id: number;
    earning_amount: number;
    completed_at: string;
    order: { code: string; store: { name: string } };
}

interface PaginatedHistory {
    data: HistoryEntry[];
}

defineProps<{
    activeJob: ActiveJob | null;
    history: PaginatedHistory;
    totalEarnings: number;
}>();

const { t, locale } = useI18n();
</script>

<template>
    <Head title="Dashboard Kurir" />

    <div class="flex flex-col gap-6">
        <div class="grid gap-3 sm:grid-cols-2">
            <StatTile
                :label="t('driver.totalEarnings')"
                :value="formatIDR(totalEarnings)"
                :icon="Wallet"
                accent="primary"
            />
            <StatTile
                :label="t('driver.completedCount')"
                :value="history.data.length"
                :icon="CheckCircle2"
                accent="default"
            />
        </div>

        <section class="rounded-xl border bg-card p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold">
                <Truck class="size-4 text-muted-foreground" />
                {{ t('driver.activeJobTitle') }}
            </h3>

            <EmptyState
                v-if="!activeJob"
                :icon="Truck"
                :title="t('driver.noActiveJobTitle')"
                :description="t('driver.noActiveJobDescription')"
                :action-label="t('driver.findJobs')"
                @action="router.visit(DriverJobController.index.url())"
            />

            <Link
                v-else
                :href="DriverJobController.show.url(activeJob.id)"
                class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-xl border border-sky-300 bg-sky-50/60 p-4 pl-5 transition-colors hover:border-sky-400 dark:border-sky-800/70 dark:bg-sky-950/30"
            >
                <span
                    class="absolute inset-y-0 left-0 w-1 bg-sky-500"
                    aria-hidden="true"
                />
                <div class="min-w-0">
                    <p class="font-mono font-semibold">
                        {{ activeJob.order.code }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ activeJob.order.store.name }}
                    </p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ activeJob.order.ship_address }}
                    </p>
                </div>
                <Badge :variant="deliveryStatusBadgeVariant(activeJob.status)">
                    {{ deliveryStatusLabel(activeJob.status) }}
                </Badge>
            </Link>
        </section>

        <section class="rounded-xl border bg-card p-5">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold">
                <CheckCircle2 class="size-4 text-muted-foreground" />
                {{ t('driver.historyTitle') }}
            </h3>

            <EmptyState
                v-if="history.data.length === 0"
                :icon="CheckCircle2"
                :title="t('driver.historyEmptyTitle')"
                :description="t('driver.historyEmptyDescription')"
            />

            <template v-else>
                <div v-for="(entry, index) in history.data" :key="entry.id">
                    <div class="flex items-center justify-between py-2.5">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-medium">
                                {{ entry.order.code }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ entry.order.store.name }} ·
                                {{ formatDateTime(entry.completed_at, locale) }}
                            </p>
                        </div>
                        <p
                            class="font-semibold text-primary tabular-nums"
                        >
                            +{{ formatIDR(entry.earning_amount) }}
                        </p>
                    </div>
                    <Separator v-if="index < history.data.length - 1" />
                </div>
            </template>
        </section>
    </div>
</template>
