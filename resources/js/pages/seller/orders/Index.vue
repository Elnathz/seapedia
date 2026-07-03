<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle, Inbox } from '@lucide/vue';
import { computed, ref } from 'vue';
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
import { isAtRisk, slaUrgencyLabel, slaUrgencyTone } from '@/lib/slaUrgency';
import type { SlaUrgencyKey } from '@/lib/slaUrgency';
import { formatDate, formatDateTime, formatIDR } from '@/lib/utils';

interface OrderRow {
    id: number;
    code: string;
    status: OrderStatusKey;
    grand_total: number;
    created_sim_at: string;
    sla_urgency: SlaUrgencyKey;
    sla_ticks_remaining: number;
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

// The server sorts risk-first, so at-risk orders already lead the page. Here we
// lift them into a dedicated "needs attention" band, then group the calm
// remainder by day so the list still reads chronologically ("by date").
const atRisk = computed(() =>
    props.orders.data.filter((o) => isAtRisk(o.sla_urgency)),
);

const restByDate = computed(() => {
    const groups: { key: string; orders: OrderRow[] }[] = [];

    for (const order of props.orders.data) {
        if (isAtRisk(order.sla_urgency)) {
            continue;
        }

        const key = formatDate(order.created_sim_at, locale.value);
        const last = groups.at(-1);

        if (last?.key === key) {
            last.orders.push(order);
        } else {
            groups.push({ key, orders: [order] });
        }
    }

    return groups;
});

// Flatten into a single render stream (section headers interleaved with cards)
// so the order card markup is defined exactly once below.
type RenderItem =
    | { kind: 'header'; id: string; label: string; tone: 'risk' | 'date' }
    | { kind: 'order'; id: string; order: OrderRow };

const renderItems = computed<RenderItem[]>(() => {
    const items: RenderItem[] = [];

    if (atRisk.value.length > 0) {
        items.push({
            kind: 'header',
            id: 'risk',
            label: t('order.needsAttention'),
            tone: 'risk',
        });

        for (const order of atRisk.value) {
            items.push({ kind: 'order', id: `o-${order.id}`, order });
        }
    }

    for (const group of restByDate.value) {
        items.push({
            kind: 'header',
            id: `d-${group.key}`,
            label: group.key,
            tone: 'date',
        });

        for (const order of group.orders) {
            items.push({ kind: 'order', id: `o-${order.id}`, order });
        }
    }

    return items;
});
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
                <template v-for="item in renderItems" :key="item.id">
                    <!-- Section header: the at-risk band, or a date divider -->
                    <div
                        v-if="item.kind === 'header'"
                        class="flex items-center gap-2 pt-2 first:pt-0"
                        :class="
                            item.tone === 'risk'
                                ? 'text-amber-600 dark:text-amber-400'
                                : 'text-muted-foreground'
                        "
                    >
                        <AlertTriangle
                            v-if="item.tone === 'risk'"
                            class="size-4"
                        />
                        <span
                            class="text-xs font-semibold tracking-wide uppercase"
                        >
                            {{ item.label }}
                        </span>
                        <span class="h-px flex-1 bg-border" />
                    </div>

                    <!-- Order card -->
                    <Card
                        v-else
                        class="group relative overflow-hidden transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg"
                    >
                        <span
                            v-if="isAtRisk(item.order.sla_urgency)"
                            class="absolute inset-y-0 left-0 z-10 w-1"
                            :class="slaUrgencyTone(item.order.sla_urgency).rail"
                            aria-hidden="true"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-primary/0 via-primary/0 to-primary/0 opacity-0 transition-opacity duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:from-primary/5 group-hover:to-transparent group-hover:opacity-100"
                        ></div>
                        <CardContent
                            class="relative z-10 flex flex-col justify-between gap-4 pt-6 sm:flex-row sm:items-center"
                        >
                            <Link
                                :href="
                                    SellerOrderController.show.url(
                                        item.order.id,
                                    )
                                "
                                class="grid min-w-0 flex-1 gap-1"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <p
                                        class="text-base font-bold text-foreground transition-colors duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:text-primary"
                                    >
                                        {{ item.order.code }}
                                    </p>
                                    <span
                                        v-if="isAtRisk(item.order.sla_urgency)"
                                        class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                        :class="
                                            slaUrgencyTone(
                                                item.order.sla_urgency,
                                            ).badge
                                        "
                                    >
                                        {{
                                            slaUrgencyLabel(
                                                item.order.sla_urgency,
                                                item.order.sla_ticks_remaining,
                                            )
                                        }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ item.order.buyer.name }} •
                                    <span class="text-xs">{{
                                        formatDateTime(
                                            item.order.created_sim_at,
                                            locale,
                                        )
                                    }}</span>
                                </p>
                            </Link>
                            <div
                                class="flex w-full items-center justify-between gap-2 sm:w-auto sm:flex-col sm:items-end sm:justify-center"
                            >
                                <Badge
                                    :variant="
                                        orderStatusBadgeVariant(
                                            item.order.status,
                                        )
                                    "
                                    class="shadow-sm transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105"
                                >
                                    {{ orderStatusLabel(item.order.status) }}
                                </Badge>
                                <p
                                    class="text-base font-bold text-primary tabular-nums"
                                >
                                    {{ formatIDR(item.order.grand_total) }}
                                </p>
                                <Button
                                    v-if="
                                        item.order.status === 'sedang_dikemas'
                                    "
                                    size="sm"
                                    class="gap-1.5"
                                    :disabled="processing === item.order.id"
                                    @click.prevent="processOrder(item.order.id)"
                                >
                                    <CheckCircle class="size-3.5" />
                                    Proses
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </template>
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
