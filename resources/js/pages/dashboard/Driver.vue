<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Truck, Wallet } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DriverJobController from '@/actions/App/Http/Controllers/Web/DriverJobController';
import EmptyState from '@/components/EmptyState.vue';
import StatTile from '@/components/StatTile.vue';
import { Badge } from '@/components/ui/badge';
import {
    deliveryStatusBadgeVariant,
    deliveryStatusLabel,
} from '@/lib/deliveryStatus';
import type { DeliveryStatusKey } from '@/lib/deliveryStatus';
import { isAtRisk, slaUrgencyLabel, slaUrgencyTone } from '@/lib/slaUrgency';
import type { SlaUrgencyKey } from '@/lib/slaUrgency';
import { formatDate, formatDateTime, formatIDR } from '@/lib/utils';

interface ActiveJob {
    id: number;
    status: DeliveryStatusKey;
    sla_urgency: SlaUrgencyKey;
    sla_ticks_remaining: number;
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

const props = defineProps<{
    activeJobs: ActiveJob[];
    maxActiveJobs: number;
    history: PaginatedHistory;
    totalEarnings: number;
}>();

const { t, locale } = useI18n();

// Group completed jobs under their delivery day so the history reads by date.
const historyByDate = computed(() => {
    const groups: { key: string; entries: HistoryEntry[] }[] = [];

    for (const entry of props.history.data) {
        const key = formatDate(entry.completed_at, locale.value);
        const last = groups.at(-1);

        if (last?.key === key) {
            last.entries.push(entry);
        } else {
            groups.push({ key, entries: [entry] });
        }
    }

    return groups;
});
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
            <h3
                class="mb-4 flex items-center justify-between gap-2 text-sm font-semibold"
            >
                <span class="flex items-center gap-2">
                    <Truck class="size-4 text-muted-foreground" />
                    {{ t('driver.activeJobTitle') }}
                </span>
                <Badge
                    v-if="activeJobs.length > 0"
                    variant="secondary"
                    class="tabular-nums"
                >
                    {{ activeJobs.length }}/{{ maxActiveJobs }}
                </Badge>
            </h3>

            <EmptyState
                v-if="activeJobs.length === 0"
                :icon="Truck"
                :title="t('driver.noActiveJobTitle')"
                :description="t('driver.noActiveJobDescription')"
                :action-label="t('driver.findJobs')"
                @action="router.visit(DriverJobController.index.url())"
            />

            <div v-else class="space-y-3">
                <Link
                    v-for="job in activeJobs"
                    :key="job.id"
                    :href="DriverJobController.show.url(job.id)"
                    class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-xl border p-4 pl-5 transition-colors"
                    :class="
                        isAtRisk(job.sla_urgency)
                            ? 'border-amber-300 bg-amber-50/60 hover:border-amber-400 dark:border-amber-800/70 dark:bg-amber-950/30'
                            : 'border-sky-300 bg-sky-50/60 hover:border-sky-400 dark:border-sky-800/70 dark:bg-sky-950/30'
                    "
                >
                    <span
                        class="absolute inset-y-0 left-0 w-1"
                        :class="
                            isAtRisk(job.sla_urgency)
                                ? slaUrgencyTone(job.sla_urgency).rail
                                : 'bg-sky-500'
                        "
                        aria-hidden="true"
                    />
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-mono font-semibold">
                                {{ job.order.code }}
                            </p>
                            <span
                                v-if="isAtRisk(job.sla_urgency)"
                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                :class="slaUrgencyTone(job.sla_urgency).badge"
                            >
                                <AlertTriangle class="size-3" />
                                {{
                                    slaUrgencyLabel(
                                        job.sla_urgency,
                                        job.sla_ticks_remaining,
                                    )
                                }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ job.order.store.name }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ job.order.ship_address }}
                        </p>
                    </div>
                    <Badge :variant="deliveryStatusBadgeVariant(job.status)">
                        {{ deliveryStatusLabel(job.status) }}
                    </Badge>
                </Link>
            </div>
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
                <div
                    v-for="group in historyByDate"
                    :key="group.key"
                    class="mb-4 last:mb-0"
                >
                    <p
                        class="mb-1 flex items-center gap-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        {{ group.key }}
                        <span class="h-px flex-1 bg-border" />
                    </p>
                    <div class="divide-y divide-border">
                        <div
                            v-for="entry in group.entries"
                            :key="entry.id"
                            class="flex items-center justify-between py-2.5"
                        >
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-medium">
                                    {{ entry.order.code }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ entry.order.store.name }} ·
                                    {{
                                        formatDateTime(
                                            entry.completed_at,
                                            locale,
                                        )
                                    }}
                                </p>
                            </div>
                            <p class="font-semibold text-primary tabular-nums">
                                +{{ formatIDR(entry.earning_amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </section>
    </div>
</template>
