<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle, Inbox } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SellerOrderController from '@/actions/App/Http/Controllers/Web/SellerOrderController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
const processing = ref<number | null>(null);

function processOrder(orderId: number) {
    processing.value = orderId;
    router.post(
        SellerOrderController.process.url(orderId),
        {},
        { onFinish: () => (processing.value = null) },
    );
}

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
                <Card
                    v-for="order in props.orders.data"
                    :key="order.id"
                    class="transition-all duration-200 hover:border-primary hover:shadow-sm"
                >
                    <CardContent class="flex items-center gap-4 pt-6">
                        <Link
                            :href="SellerOrderController.show.url(order.id)"
                            class="min-w-0 flex-1"
                        >
                            <p class="font-medium">{{ order.code }}</p>
                            <p class="text-sm text-muted-foreground">{{ order.buyer.name }}</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ formatDateTime(order.created_sim_at, locale) }}
                            </p>
                        </Link>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <Badge :variant="orderStatusBadgeVariant(order.status)">
                                {{ orderStatusLabel(order.status) }}
                            </Badge>
                            <p class="text-sm font-medium tabular-nums">
                                {{ formatIDR(order.grand_total) }}
                            </p>
                            <Button
                                v-if="order.status === 'sedang_dikemas'"
                                size="sm"
                                class="gap-1.5"
                                :disabled="processing === order.id"
                                @click.prevent="processOrder(order.id)"
                            >
                                <CheckCircle class="size-3.5" />
                                Proses
                            </Button>
                        </div>
                    </CardContent>
                </Card>
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
