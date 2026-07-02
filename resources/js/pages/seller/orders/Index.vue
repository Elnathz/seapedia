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

const props = defineProps<{
    orders: PaginatedOrders;
    currentStatus?: string;
}>();

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
        { page, status: props.currentStatus },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const statuses = [
    { value: '', label: 'Semua' },
    { value: 'sedang_dikemas', label: 'Perlu Diproses' },
    { value: 'menunggu_pengirim', label: 'Menunggu Kurir' },
    { value: 'sedang_dikirim', label: 'Dikirim' },
    { value: 'pesanan_selesai', label: 'Selesai' },
    { value: 'dikembalikan', label: 'Dikembalikan' },
];

function filterStatus(status: string) {
    router.get(
        SellerOrderController.index.url(),
        { status },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="t('order.incomingOrdersTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('order.incomingOrdersTitle')" />

        <!-- Tabs Filter -->
        <div
            class="scrollbar-hide flex gap-2 overflow-x-auto border-b border-border pb-2"
        >
            <button
                v-for="s in statuses"
                :key="s.value"
                @click="filterStatus(s.value)"
                class="border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                :class="[
                    (props.currentStatus || '') === s.value
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted hover:text-foreground',
                ]"
            >
                {{ s.label }}
            </button>
        </div>

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
                    class="group relative overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-primary/0 via-primary/0 to-primary/0 opacity-0 transition-opacity duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:from-primary/5 group-hover:to-transparent group-hover:opacity-100"
                    ></div>
                    <CardContent
                        class="relative z-10 flex flex-col justify-between gap-4 pt-6 sm:flex-row sm:items-center"
                    >
                        <Link
                            :href="SellerOrderController.show.url(order.id)"
                            class="grid min-w-0 flex-1 gap-1"
                        >
                            <div class="flex items-center gap-2">
                                <p
                                    class="text-base font-bold text-foreground transition-colors duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:text-primary"
                                >
                                    {{ order.code }}
                                </p>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                {{ order.buyer.name }} •
                                <span class="text-xs">{{
                                    formatDateTime(order.created_sim_at, locale)
                                }}</span>
                            </p>
                        </Link>
                        <div
                            class="flex w-full items-center justify-between gap-2 sm:w-auto sm:flex-col sm:items-end sm:justify-center"
                        >
                            <Badge
                                :variant="orderStatusBadgeVariant(order.status)"
                                class="shadow-sm transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"
                            >
                                {{ orderStatusLabel(order.status) }}
                            </Badge>
                            <p
                                class="text-base font-bold text-primary tabular-nums"
                            >
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
