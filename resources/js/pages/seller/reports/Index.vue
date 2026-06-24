<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BarChart3 } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import SellerReportController from '@/actions/App/Http/Controllers/Web/SellerReportController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { orderStatusBadgeVariant, orderStatusLabel } from '@/lib/orderStatus';
import type { OrderStatusKey } from '@/lib/orderStatus';
import { formatIDR } from '@/lib/utils';

interface BreakdownRow {
    status: OrderStatusKey;
    count: number;
    total: number;
}

interface SellerReport {
    total_income: number;
    order_count: number;
    incoming_count: number;
    processed_count: number;
    breakdown: BreakdownRow[];
}

const props = defineProps<{ report: SellerReport }>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laporan Pendapatan',
                href: SellerReportController.index(),
            },
        ],
    },
});

const { t } = useI18n();
</script>

<template>
    <Head :title="t('report.sellerTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('report.sellerTitle')" />

        <EmptyState
            v-if="props.report.order_count === 0"
            :icon="BarChart3"
            :title="t('report.emptyTitle')"
            :description="t('report.sellerEmptyDescription')"
        />

        <template v-else>
            <div class="grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent class="pt-6">
                        <p class="text-sm text-muted-foreground">
                            {{ t('report.totalIncome') }}
                        </p>
                        <p
                            class="mt-1 text-2xl font-semibold text-emerald-600 tabular-nums dark:text-emerald-400"
                        >
                            {{ formatIDR(props.report.total_income) }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <p class="text-sm text-muted-foreground">
                            {{ t('report.incomingCount') }}
                        </p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ props.report.incoming_count }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <p class="text-sm text-muted-foreground">
                            {{ t('report.processedCount') }}
                        </p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ props.report.processed_count }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <h3 class="mb-4 font-medium">
                        {{ t('report.breakdownTitle') }}
                    </h3>
                    <div class="flex flex-col">
                        <template
                            v-for="(row, index) in props.report.breakdown"
                            :key="row.status"
                        >
                            <Separator v-if="index > 0" />
                            <div
                                class="flex items-center justify-between gap-3 py-3"
                            >
                                <Badge
                                    :variant="
                                        orderStatusBadgeVariant(row.status)
                                    "
                                    class="shrink-0"
                                >
                                    {{ orderStatusLabel(row.status) }}
                                </Badge>
                                <div
                                    class="flex items-baseline gap-3 text-right"
                                >
                                    <span
                                        class="text-sm text-muted-foreground tabular-nums"
                                        >{{ row.count }}x</span
                                    >
                                    <span class="font-medium tabular-nums">{{
                                        formatIDR(row.total)
                                    }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </CardContent>
            </Card>
        </template>
    </div>
</template>
