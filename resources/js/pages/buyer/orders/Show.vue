<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BuyerOrderController from '@/actions/App/Http/Controllers/Web/BuyerOrderController';
import Heading from '@/components/Heading.vue';
import StatusTimeline from '@/components/StatusTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    deliveryStatusBadgeVariant,
    deliveryStatusLabel,
} from '@/lib/deliveryStatus';
import type { DeliveryStatusKey } from '@/lib/deliveryStatus';
import { orderStatusBadgeVariant, orderStatusLabel } from '@/lib/orderStatus';
import type { OrderStatusKey } from '@/lib/orderStatus';
import { formatDateTime, formatIDR } from '@/lib/utils';

interface OrderItemData {
    id: number;
    product_name_snapshot: string;
    price_snapshot: number;
    quantity: number;
    line_subtotal: number;
    product?: { slug: string; images?: { image_path: string }[] };
    variant?: { name: string };
}

interface HistoryEntry {
    id: number;
    status: OrderStatusKey;
    note: string | null;
    created_at: string;
}

interface DeliveryData {
    id: number;
    status: DeliveryStatusKey;
    driver: { id: number; name: string } | null;
}

interface OrderData {
    id: number;
    code: string;
    status: OrderStatusKey;
    ship_recipient: string;
    ship_phone: string;
    ship_address: string;
    delivery_method: string;
    subtotal: number;
    discount_total: number;
    delivery_fee: number;
    tax_amount: number;
    grand_total: number;
    created_sim_at: string;
    store: { id: number; name: string; slug: string };
    items: OrderItemData[];
    status_histories: HistoryEntry[];
    delivery: DeliveryData | null;
}

defineProps<{ order: OrderData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Saya', href: BuyerOrderController.index() },
            { title: 'Detail Pesanan', href: '#' },
        ],
    },
});

const { t, locale } = useI18n();
</script>

<template>
    <Head :title="order.code" />

    <div class="flex flex-col gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <Heading
                variant="small"
                :title="order.code"
                :description="formatDateTime(order.created_sim_at, locale)"
            />
            <div class="shrink-0">
                <Badge :variant="orderStatusBadgeVariant(order.status)" class="text-sm px-3 py-1">
                    {{ orderStatusLabel(order.status) }}
                </Badge>
            </div>
        </div>

        <div class="grid min-w-0 gap-6 lg:grid-cols-3">
            <div class="flex min-w-0 flex-col gap-6 lg:col-span-2">
                <Card class="min-w-0">
                    <CardContent class="space-y-2 pt-6">
                        <h3 class="font-medium">
                            {{ t('order.shippingTitle') }}
                        </h3>
                        <p class="text-sm mb-2">
                            <span class="text-muted-foreground">Toko:</span>
                            <Link :href="`/stores/${order.store.slug}`" class="font-medium text-primary hover:underline ml-1">{{ order.store.name }}</Link>
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.recipientLabel') }}:</span
                            >
                            {{ order.ship_recipient }}
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.phoneLabel') }}:</span
                            >
                            {{ order.ship_phone }}
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.addressLabel') }}:</span
                            >
                            {{ order.ship_address }}
                        </p>
                        <template v-if="order.delivery">
                            <p class="text-sm">
                                <span class="text-muted-foreground"
                                    >{{
                                        t('driver.assignedDriverLabel')
                                    }}:</span
                                >
                                {{
                                    order.delivery.driver?.name ??
                                    t('driver.noDriverYet')
                                }}
                            </p>
                            <p class="text-sm">
                                <span class="text-muted-foreground"
                                    >{{
                                        t('driver.deliveryStatusLabel')
                                    }}:</span
                                >
                                <Badge
                                    :variant="
                                        deliveryStatusBadgeVariant(
                                            order.delivery.status,
                                        )
                                    "
                                >
                                    {{
                                        deliveryStatusLabel(
                                            order.delivery.status,
                                        )
                                    }}
                                </Badge>
                            </p>
                        </template>
                    </CardContent>
                </Card>

                <Card class="min-w-0">
                    <CardContent class="pt-6">
                        <h3 class="mb-4 font-medium">
                            {{ t('order.itemsTitle') }}
                        </h3>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{
                                        t('order.columnProduct')
                                    }}</TableHead>
                                    <TableHead class="text-right">{{
                                        t('order.columnPrice')
                                    }}</TableHead>
                                    <TableHead class="text-right">{{
                                        t('order.columnQty')
                                    }}</TableHead>
                                    <TableHead class="text-right">{{
                                        t('order.columnSubtotal')
                                    }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="item in order.items"
                                    :key="item.id"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <Link v-if="item.product" :href="`/catalog/${item.product.slug}`" class="shrink-0 group">
                                                <div class="size-12 overflow-hidden rounded border border-border">
                                                    <img v-if="item.product.images?.length" :src="`/storage/${item.product.images[0].image_path}`" class="size-full object-cover transition-transform duration-300 group-hover:scale-110" />
                                                    <div v-else class="size-full bg-muted flex items-center justify-center text-xs text-muted-foreground">No img</div>
                                                </div>
                                            </Link>
                                            <div v-else class="shrink-0 size-12 overflow-hidden rounded border border-border bg-muted flex items-center justify-center text-xs text-muted-foreground">
                                                No img
                                            </div>
                                            <div class="flex flex-col">
                                                <Link v-if="item.product" :href="`/catalog/${item.product.slug}`" class="font-medium hover:text-primary transition-colors">{{ item.product_name_snapshot }}</Link>
                                                <span v-else class="font-medium">{{ item.product_name_snapshot }}</span>
                                                <span v-if="item.variant" class="text-xs text-muted-foreground">{{ item.variant.name }}</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell
                                        class="text-right tabular-nums"
                                        >{{
                                            formatIDR(item.price_snapshot)
                                        }}</TableCell
                                    >
                                    <TableCell
                                        class="text-right tabular-nums"
                                        >{{ item.quantity }}</TableCell
                                    >
                                    <TableCell
                                        class="text-right font-medium tabular-nums"
                                        >{{
                                            formatIDR(item.line_subtotal)
                                        }}</TableCell
                                    >
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-6">
                        <h3 class="mb-4 font-medium">
                            {{ t('order.timelineTitle') }}
                        </h3>
                        <StatusTimeline :histories="order.status_histories" />
                    </CardContent>
                </Card>
            </div>

            <Card class="min-w-0 lg:sticky lg:top-6 lg:self-start">
                <CardContent class="space-y-2 pt-6">
                    <h3 class="font-medium">
                        {{ t('order.summaryTitle') }}
                    </h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('order.subtotal') }}
                            </dt>
                            <dd class="tabular-nums">
                                {{ formatIDR(order.subtotal) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('order.discount') }}
                            </dt>
                            <dd class="tabular-nums">
                                -{{ formatIDR(order.discount_total) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('order.deliveryFee') }}
                            </dt>
                            <dd class="tabular-nums">
                                {{ formatIDR(order.delivery_fee) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('order.tax') }}
                            </dt>
                            <dd class="tabular-nums">
                                {{ formatIDR(order.tax_amount) }}
                            </dd>
                        </div>
                    </dl>
                    <Separator />
                    <div class="flex justify-between font-semibold">
                        <span>{{ t('order.grandTotal') }}</span>
                        <span class="text-lg tabular-nums">{{
                            formatIDR(order.grand_total)
                        }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
