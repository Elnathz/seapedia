<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Inbox } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import SellerOrderController from '@/actions/App/Http/Controllers/Web/SellerOrderController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
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
import { formatDateTime, formatIDR } from '@/lib/utils';

interface OrderRow {
    id: number;
    code: string;
    status: OrderStatusKey;
    grand_total: number;
    created_sim_at: string;
    buyer: { id: number; name: string };
}

interface PaginatedOrders {
    data: OrderRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{ orders: PaginatedOrders }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Masuk', href: SellerOrderController.index() },
        ],
    },
});

const { t, locale } = useI18n();

function goToPage(page: number) {
    router.get(
        SellerOrderController.index.url(),
        { page },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head :title="t('order.incomingOrdersTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('order.incomingOrdersTitle')" />

        <EmptyState
            v-if="props.orders.data.length === 0"
            :icon="Inbox"
            :title="t('order.incomingEmptyTitle')"
            :description="t('order.incomingEmptyDescription')"
        />

        <template v-else>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>{{ t('order.columnCode') }}</TableHead>
                        <TableHead>{{ t('order.columnBuyer') }}</TableHead>
                        <TableHead>{{ t('order.columnStatus') }}</TableHead>
                        <TableHead class="text-right">{{
                            t('order.columnTotal')
                        }}</TableHead>
                        <TableHead class="text-right">{{
                            t('order.columnDate')
                        }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="order in props.orders.data"
                        :key="order.id"
                        class="cursor-pointer hover:bg-muted/50"
                        @click="
                            router.visit(
                                SellerOrderController.show.url(order.id),
                            )
                        "
                    >
                        <TableCell class="font-medium">{{
                            order.code
                        }}</TableCell>
                        <TableCell>{{ order.buyer.name }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="orderStatusBadgeVariant(order.status)"
                            >
                                {{ orderStatusLabel(order.status) }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right tabular-nums">{{
                            formatIDR(order.grand_total)
                        }}</TableCell>
                        <TableCell
                            class="text-right text-sm text-muted-foreground"
                            >{{
                                formatDateTime(order.created_sim_at, locale)
                            }}</TableCell
                        >
                    </TableRow>
                </TableBody>
            </Table>

            <Pagination
                v-if="props.orders.last_page > 1"
                :items-per-page="props.orders.per_page"
                :total="props.orders.total"
                :default-page="props.orders.current_page"
                class="mt-4"
                @update:page="goToPage"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationFirst />
                    <PaginationPrevious />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="
                                item.value === props.orders.current_page
                            "
                        >
                            {{ item.value }}
                        </PaginationItem>
                        <PaginationEllipsis v-else />
                    </template>
                    <PaginationNext />
                    <PaginationLast />
                </PaginationContent>
            </Pagination>
        </template>
    </div>
</template>
