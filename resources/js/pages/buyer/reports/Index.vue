<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Receipt } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import BuyerReportController from '@/actions/App/Http/Controllers/Web/BuyerReportController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{
                                    t('order.columnStatus')
                                }}</TableHead>
                                <TableHead class="text-right">{{
                                    t('report.columnOrders')
                                }}</TableHead>
                                <TableHead class="text-right">{{
                                    t('report.columnAmount')
                                }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in props.report.breakdown"
                                :key="row.status"
                            >
                                <TableCell>
                                    <Badge
                                        :variant="
                                            orderStatusBadgeVariant(row.status)
                                        "
                                    >
                                        {{ orderStatusLabel(row.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right tabular-nums">{{
                                    row.count
                                }}</TableCell>
                                <TableCell
                                    class="text-right font-medium tabular-nums"
                                    >{{ formatIDR(row.total) }}</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </template>
    </div>
</template>
