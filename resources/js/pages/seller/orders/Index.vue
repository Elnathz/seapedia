<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Inbox } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import SellerOrderController from '@/actions/App/Http/Controllers/Web/SellerOrderController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
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
            <div class="flex flex-col gap-3">
                <Link
                    v-for="order in props.orders.data"
                    :key="order.id"
                    :href="SellerOrderController.show.url(order.id)"
                >
                    <Card class="transition-colors hover:border-primary">
                        <CardContent
                            class="flex items-center justify-between gap-4 pt-6"
                        >
                            <div>
                                <p class="font-medium">{{ order.code }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ order.buyer.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        formatDateTime(
                                            order.created_sim_at,
                                            locale,
                                        )
                                    }}
                                </p>
                            </div>
                            <div class="text-right">
                                <Badge
                                    :variant="
                                        orderStatusBadgeVariant(order.status)
                                    "
                                >
                                    {{ orderStatusLabel(order.status) }}
                                </Badge>
                                <p class="mt-1 font-medium tabular-nums">
                                    {{ formatIDR(order.grand_total) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

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
