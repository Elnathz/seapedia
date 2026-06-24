<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BadgePercent,
    Boxes,
    CalendarClock,
    ShieldCheck,
    ShoppingBag,
    Store,
    Ticket,
    Truck,
    Users,
} from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminClockController from '@/actions/App/Http/Controllers/Web/Admin/ClockController';
import StatCard from '@/components/StatCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    deliveryStatusBadgeVariant,
    deliveryStatusLabel,
} from '@/lib/deliveryStatus';
import type { DeliveryStatusKey } from '@/lib/deliveryStatus';
import { orderStatusBadgeVariant, orderStatusLabel } from '@/lib/orderStatus';
import type { OrderStatusKey } from '@/lib/orderStatus';
import { formatDateTime } from '@/lib/utils';
import { index as indexAdminPromos } from '@/routes/admin/promos';
import { index as indexAdminVouchers } from '@/routes/admin/vouchers';

interface Snapshot {
    simulated_now: string;
    users: {
        total: number;
        admins: number;
        buyers: number;
        sellers: number;
        drivers: number;
    };
    stores_count: number;
    products_count: number;
    orders_by_status: Partial<Record<OrderStatusKey, number>>;
    deliveries_by_status: Partial<Record<DeliveryStatusKey, number>>;
    overdue_eligible_count: number;
    promos: { total: number; active: number; expired: number };
    vouchers: {
        total: number;
        active: number;
        expired: number;
        used_up: number;
    };
}

const props = defineProps<{ snapshot: Snapshot }>();

const { t, locale } = useI18n();

const ALL_ORDER_STATUSES: OrderStatusKey[] = [
    'sedang_dikemas',
    'menunggu_pengirim',
    'sedang_dikirim',
    'pesanan_selesai',
    'dikembalikan',
];

const ALL_DELIVERY_STATUSES: DeliveryStatusKey[] = [
    'available',
    'taken',
    'completed',
];

const confirmOpen = ref(false);
const processing = ref(false);

function confirmAdvance() {
    confirmOpen.value = false;
    processing.value = true;
    router.post(
        AdminClockController.advance.url(),
        {},
        { onFinish: () => (processing.value = false) },
    );
}
</script>

<template>
    <Head :title="t('admin.dashboardTitle')" />

    <div class="flex flex-col gap-6">
        <Card>
            <CardContent
                class="flex flex-wrap items-center justify-between gap-4 pt-6"
            >
                <div class="flex items-center gap-3">
                    <CalendarClock class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-sm text-muted-foreground">
                            {{ t('admin.simulatedDateLabel') }}
                        </p>
                        <p class="font-medium tabular-nums">
                            {{
                                formatDateTime(
                                    props.snapshot.simulated_now,
                                    locale,
                                )
                            }}
                        </p>
                    </div>
                </div>
                <Button :disabled="processing" @click="confirmOpen = true">
                    {{ t('admin.advanceDay') }}
                </Button>
            </CardContent>
        </Card>

        <div
            v-if="props.snapshot.overdue_eligible_count > 0"
            class="flex items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-800 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-300"
        >
            <AlertTriangle class="size-5 shrink-0" />
            <p class="text-sm">
                {{
                    t('admin.overdueEligibleDescription', {
                        count: props.snapshot.overdue_eligible_count,
                    })
                }}
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                :label="t('admin.totalUsers')"
                :value="props.snapshot.users.total"
                :icon="Users"
            />
            <StatCard
                :label="t('admin.totalAdmins')"
                :value="props.snapshot.users.admins"
                :icon="ShieldCheck"
            />
            <StatCard
                :label="t('admin.totalSellers')"
                :value="props.snapshot.users.sellers"
                :icon="Store"
            />
            <StatCard
                :label="t('admin.totalBuyers')"
                :value="props.snapshot.users.buyers"
                :icon="ShoppingBag"
            />
            <StatCard
                :label="t('admin.totalDrivers')"
                :value="props.snapshot.users.drivers"
                :icon="Truck"
            />
            <StatCard
                :label="t('admin.totalStores')"
                :value="props.snapshot.stores_count"
                :icon="Store"
            />
            <StatCard
                :label="t('admin.totalProducts')"
                :value="props.snapshot.products_count"
                :icon="Boxes"
            />
            <StatCard
                :label="t('admin.overdueEligible')"
                :value="props.snapshot.overdue_eligible_count"
                :icon="AlertTriangle"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ t('admin.ordersByStatusTitle') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col">
                    <template
                        v-for="(status, index) in ALL_ORDER_STATUSES"
                        :key="status"
                    >
                        <Separator v-if="index > 0" />
                        <div class="flex items-center justify-between py-2">
                            <Badge :variant="orderStatusBadgeVariant(status)">
                                {{ orderStatusLabel(status) }}
                            </Badge>
                            <span class="text-sm font-semibold tabular-nums">
                                {{
                                    props.snapshot.orders_by_status[status] ?? 0
                                }}
                            </span>
                        </div>
                    </template>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        {{ t('admin.deliveriesByStatusTitle') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col">
                    <template
                        v-for="(status, index) in ALL_DELIVERY_STATUSES"
                        :key="status"
                    >
                        <Separator v-if="index > 0" />
                        <div class="flex items-center justify-between py-2">
                            <Badge
                                :variant="deliveryStatusBadgeVariant(status)"
                            >
                                {{ deliveryStatusLabel(status) }}
                            </Badge>
                            <span class="text-sm font-semibold tabular-nums">
                                {{
                                    props.snapshot.deliveries_by_status[
                                        status
                                    ] ?? 0
                                }}
                            </span>
                        </div>
                    </template>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ t('admin.discountSummaryTitle') }}
                </CardTitle>
            </CardHeader>
            <CardContent class="grid gap-4 sm:grid-cols-2">
                <Link
                    :href="indexAdminPromos.url()"
                    class="flex items-start gap-3 rounded-lg p-2 transition-colors hover:bg-accent"
                >
                    <Ticket class="mt-1 size-5 text-muted-foreground" />
                    <div class="flex-1">
                        <p class="font-medium">{{ t('admin.promosLabel') }}</p>
                        <div
                            class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted-foreground"
                        >
                            <span
                                >{{ t('admin.totalCount') }}:
                                {{ props.snapshot.promos.total }}</span
                            >
                            <span
                                >{{ t('admin.activeCount') }}:
                                {{ props.snapshot.promos.active }}</span
                            >
                            <span
                                >{{ t('admin.expiredCount') }}:
                                {{ props.snapshot.promos.expired }}</span
                            >
                        </div>
                    </div>
                </Link>
                <Link
                    :href="indexAdminVouchers.url()"
                    class="flex items-start gap-3 rounded-lg p-2 transition-colors hover:bg-accent"
                >
                    <BadgePercent class="mt-1 size-5 text-muted-foreground" />
                    <div class="flex-1">
                        <p class="font-medium">
                            {{ t('admin.vouchersLabel') }}
                        </p>
                        <div
                            class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted-foreground"
                        >
                            <span
                                >{{ t('admin.totalCount') }}:
                                {{ props.snapshot.vouchers.total }}</span
                            >
                            <span
                                >{{ t('admin.activeCount') }}:
                                {{ props.snapshot.vouchers.active }}</span
                            >
                            <span
                                >{{ t('admin.expiredCount') }}:
                                {{ props.snapshot.vouchers.expired }}</span
                            >
                            <span
                                >{{ t('admin.usedUpCount') }}:
                                {{ props.snapshot.vouchers.used_up }}</span
                            >
                        </div>
                    </div>
                </Link>
            </CardContent>
        </Card>
    </div>

    <Dialog v-model:open="confirmOpen">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>{{
                    t('admin.advanceDayConfirmTitle')
                }}</DialogTitle>
                <DialogDescription>
                    {{ t('admin.advanceDayConfirmDescription') }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="mt-4 gap-2">
                <Button variant="secondary" @click="confirmOpen = false">
                    {{ t('common.cancel') }}
                </Button>
                <Button :disabled="processing" @click="confirmAdvance">
                    {{ t('admin.advanceDayConfirm') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
