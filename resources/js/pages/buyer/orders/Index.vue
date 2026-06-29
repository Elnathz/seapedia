<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Package } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import BuyerOrderController from '@/actions/App/Http/Controllers/Web/BuyerOrderController';
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
import { index as catalogIndex } from '@/routes/catalog';

interface OrderRow {
    id: number;
    code: string;
    status: OrderStatusKey;
    grand_total: number;
    created_sim_at: string;
    store: { id: number; name: string; slug: string };
    items: {
        id: number;
        product_name_snapshot: string;
        product?: {
            images?: { image_path: string }[];
        };
    }[];
}

interface PaginatedOrders {
    data: OrderRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{ orders: PaginatedOrders; currentStatus?: string }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Saya', href: BuyerOrderController.index() },
        ],
    },
});

const { t, locale } = useI18n();

function goToPage(page: number) {
    router.get(
        BuyerOrderController.index.url(),
        { page, status: props.currentStatus },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const statuses = [
    { value: '', label: 'Semua' },
    { value: 'menunggu_pembayaran', label: 'Belum Bayar' },
    { value: 'sedang_dikemas', label: 'Dikemas' },
    { value: 'menunggu_pengirim', label: 'Menunggu Kurir' },
    { value: 'sedang_dikirim', label: 'Dikirim' },
    { value: 'pesanan_selesai', label: 'Selesai' },
    { value: 'dibatalkan', label: 'Batal' },
];

function filterStatus(status: string) {
    router.get(
        BuyerOrderController.index.url(),
        { status },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="t('order.myOrdersTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('order.myOrdersTitle')" />

        <!-- Tabs Filter -->
        <div class="flex overflow-x-auto pb-2 scrollbar-hide gap-2 border-b border-border">
            <button
                v-for="s in statuses"
                :key="s.value"
                @click="filterStatus(s.value)"
                class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
                :class="[
                    (props.currentStatus || '') === s.value
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted',
                ]"
            >
                {{ s.label }}
            </button>
        </div>

        <EmptyState
            v-if="props.orders.data.length === 0"
            :icon="Package"
            :title="t('order.emptyTitle')"
            :description="t('order.emptyDescription')"
            :action-label="t('cart.browseCatalog')"
            @action="router.visit(catalogIndex.url())"
        />

        <template v-else>
            <div class="flex flex-col gap-3">
                <Link
                    v-for="order in props.orders.data"
                    :key="order.id"
                    :href="BuyerOrderController.show.url(order.id)"
                >
                    <Card class="group relative overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:shadow-lg hover:border-primary/50 hover:-translate-y-1">
                        <div class="absolute inset-0 bg-gradient-to-r from-primary/0 via-primary/0 to-primary/0 opacity-0 transition-opacity duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:from-primary/5 group-hover:to-transparent group-hover:opacity-100"></div>
                        <CardContent
                            class="flex items-center justify-between gap-4 pt-6 relative z-10"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <Package class="size-4 text-muted-foreground" />
                                    <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ order.code }}</p>
                                </div>
                                <p class="text-base font-semibold text-foreground group-hover:text-primary transition-colors duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]">
                                    {{ order.store.name }}
                                </p>
                                <p class="text-xs text-muted-foreground flex items-center gap-1">
                                    {{
                                        formatDateTime(
                                            order.created_sim_at,
                                            locale,
                                        )
                                    }}
                                </p>
                            </div>
                            <div class="flex-1 min-w-0 px-4 hidden sm:block border-l border-border ml-4">
                                <div v-if="order.items && order.items.length > 0" class="flex items-center gap-3">
                                    <div class="size-10 shrink-0 overflow-hidden rounded bg-muted/50 border border-border flex items-center justify-center">
                                        <img v-if="order.items[0].product?.images?.length" :src="`/storage/${order.items[0].product.images[0].image_path}`" class="size-full object-cover" />
                                        <Package v-else class="size-4 text-muted-foreground/50" />
                                    </div>
                                    <div class="flex flex-col overflow-hidden">
                                        <p class="text-sm font-medium text-foreground truncate">{{ order.items[0].product_name_snapshot }}</p>
                                        <p v-if="order.items.length > 1" class="text-xs text-muted-foreground">+ {{ order.items.length - 1 }} produk lainnya</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-3 shrink-0 ml-auto">
                                <Badge
                                    :variant="
                                        orderStatusBadgeVariant(order.status)
                                    "
                                    class="shadow-sm transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"
                                >
                                    {{ orderStatusLabel(order.status) }}
                                </Badge>
                                <p class="font-bold tabular-nums text-primary text-lg">
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
