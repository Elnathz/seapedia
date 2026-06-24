<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Receipt } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import BuyerReportController from '@/actions/App/Http/Controllers/Web/BuyerReportController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { orderStatusBadgeVariant, orderStatusLabel } from '@/lib/orderStatus';
import type { OrderStatusKey } from '@/lib/orderStatus';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';

interface BreakdownRow {
    status: OrderStatusKey;
    count: number;
    total: number;
}

interface BuyerReport {
    total_spent: number;
    order_count: number;
    breakdown: BreakdownRow[];
}

const props = defineProps<{ report: BuyerReport }>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Laporan Pengeluaran',
                href: BuyerReportController.index(),
            },
        ],
    },
});

const { t } = useI18n();
</script>

<template>
    <Head :title="t('report.buyerTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('report.buyerTitle')" />

        <EmptyState
            v-if="props.report.order_count === 0"
            :icon="Receipt"
            :title="t('report.emptyTitle')"
            :description="t('report.buyerEmptyDescription')"
            :action-label="t('cart.browseCatalog')"
            @action="router.visit(catalogIndex.url())"
        />

        <template v-else>
            <div class="grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardContent class="pt-6">
                        <p class="text-sm text-muted-foreground">
                            {{ t('report.totalSpent') }}
                        </p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ formatIDR(props.report.total_spent) }}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <p class="text-sm text-muted-foreground">
                            {{ t('report.orderCount') }}
                        </p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums">
                            {{ props.report.order_count }}
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
