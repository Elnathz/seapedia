<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SellerOrderController from '@/actions/App/Http/Controllers/Web/SellerOrderController';
import Heading from '@/components/Heading.vue';
import StatusTimeline from '@/components/StatusTimeline.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
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
import { index as indexSellerOrders } from '@/routes/seller/orders';

interface OrderItemData {
    id: number;
    product_name_snapshot: string;
    price_snapshot: number;
    quantity: number;
    line_subtotal: number;
}

interface HistoryEntry {
    id: number;
    status: OrderStatusKey;
    note: string | null;
    created_at: string;
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
    buyer: { id: number; name: string };
    items: OrderItemData[];
    status_histories: HistoryEntry[];
}

const props = defineProps<{ order: OrderData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Masuk', href: indexSellerOrders() },
            { title: 'Detail Pesanan', href: '#' },
        ],
    },
});

const { t, locale } = useI18n();

const confirmOpen = ref(false);
const processing = ref(false);

function confirmProcess() {
    confirmOpen.value = false;
    processing.value = true;

    router.post(
        SellerOrderController.process.url(props.order.id),
        {},
        {
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="order.code" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="order.code"
                :description="formatDateTime(order.created_sim_at, locale)"
            />
            <Badge :variant="orderStatusBadgeVariant(order.status)">
                {{ orderStatusLabel(order.status) }}
            </Badge>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <Card>
                    <CardContent class="space-y-2 pt-6">
                        <h3 class="font-medium">
                            {{ t('order.shippingTitle') }}
                        </h3>
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
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.columnBuyer') }}:</span
                            >
                            {{ order.buyer.name }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
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
                                    <TableCell>{{
                                        item.product_name_snapshot
                                    }}</TableCell>
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

            <Card class="lg:sticky lg:top-6 lg:self-start">
                <CardContent class="space-y-4 pt-6">
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

                    <Button
                        v-if="order.status === 'sedang_dikemas'"
                        class="w-full"
                        :disabled="processing"
                        @click="confirmOpen = true"
                    >
                        {{ t('order.processAction') }}
                    </Button>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="confirmOpen">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{
                        t('order.processConfirmTitle')
                    }}</DialogTitle>
                    <DialogDescription>
                        {{
                            t('order.processConfirmDescription', {
                                code: order.code,
                            })
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-4 gap-2">
                    <Button variant="secondary" @click="confirmOpen = false">{{
                        t('common.cancel')
                    }}</Button>
                    <Button :disabled="processing" @click="confirmProcess">{{
                        t('order.processConfirm')
                    }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
