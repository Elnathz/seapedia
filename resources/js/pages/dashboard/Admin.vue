<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    BadgePercent,
    Boxes,
    CalendarClock,
    CheckCircle2,
    PackageCheck,
    ShoppingBag,
    Store,
    Ticket,
    Truck,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminClockController from '@/actions/App/Http/Controllers/Web/Admin/ClockController';
import StatBar from '@/components/StatBar.vue';
import StatTile from '@/components/StatTile.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { deliveryStatusFill, deliveryStatusLabel } from '@/lib/deliveryStatus';
import type { DeliveryStatusKey } from '@/lib/deliveryStatus';
import { orderStatusFill, orderStatusLabel } from '@/lib/orderStatus';
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
    'cancelled',
];

const orderCount = (s: OrderStatusKey) =>
    props.snapshot.orders_by_status[s] ?? 0;
const deliveryCount = (s: DeliveryStatusKey) =>
    props.snapshot.deliveries_by_status[s] ?? 0;

const ordersTotal = computed(() =>
    ALL_ORDER_STATUSES.reduce((sum, s) => sum + orderCount(s), 0),
);
const deliveriesTotal = computed(() =>
    ALL_DELIVERY_STATUSES.reduce((sum, s) => sum + deliveryCount(s), 0),
);
const ordersInFlight = computed(
    () =>
        orderCount('sedang_dikemas') +
        orderCount('menunggu_pengirim') +
        orderCount('sedang_dikirim'),
);

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
        <!-- Signature: "time machine" command bar -->
        <section
            class="relative overflow-hidden rounded-2xl border bg-gradient-to-br from-primary/10 via-card to-card p-5 sm:p-6"
        >
            <div
                class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/15 text-primary"
                    >
                        <CalendarClock class="size-6" />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-[0.7rem] font-semibold tracking-wider text-primary uppercase"
                        >
                            {{ t('admin.opsEyebrow') }}
                        </p>
                        <p
                            class="mt-0.5 truncate text-xl font-semibold tabular-nums sm:text-2xl"
                        >
                            {{
                                formatDateTime(
                                    props.snapshot.simulated_now,
                                    locale,
                                )
                            }}
                        </p>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            {{ t('admin.opsSubtitle') }}
                        </p>
                    </div>
                </div>
                <Button
                    size="lg"
                    class="shrink-0"
                    :disabled="processing"
                    @click="confirmOpen = true"
                >
                    {{ t('admin.advanceDay') }}
                    <ArrowRight class="size-4" />
                </Button>
            </div>

            <div
                v-if="props.snapshot.overdue_eligible_count > 0"
                class="mt-5 flex items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-3 text-amber-800 dark:border-amber-700/60 dark:bg-amber-950/50 dark:text-amber-300"
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
        </section>

        <!-- Operational KPI band -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatTile
                :label="t('admin.ordersInFlight')"
                :value="ordersInFlight"
                :hint="t('admin.ordersInFlightHint')"
                :icon="ShoppingBag"
                accent="primary"
            />
            <StatTile
                :label="t('admin.overdueEligible')"
                :value="props.snapshot.overdue_eligible_count"
                :icon="AlertTriangle"
                :accent="
                    props.snapshot.overdue_eligible_count > 0
                        ? 'amber'
                        : 'default'
                "
            />
            <StatTile
                :label="t('admin.jobsAvailable')"
                :value="deliveryCount('available')"
                :icon="Truck"
                accent="sky"
            />
            <StatTile
                :label="t('admin.completedOrders')"
                :value="orderCount('pesanan_selesai')"
                :icon="CheckCircle2"
                accent="default"
            />
        </div>

        <!-- Proportion breakdowns: structure encodes the distribution -->
        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-xl border bg-card p-5">
                <div class="mb-4 flex items-center gap-2">
                    <PackageCheck class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold">
                        {{ t('admin.ordersByStatusTitle') }}
                    </h3>
                    <span
                        class="ml-auto text-xs text-muted-foreground tabular-nums"
                    >
                        {{ ordersTotal }}
                    </span>
                </div>
                <p
                    v-if="ordersTotal === 0"
                    class="py-2 text-sm text-muted-foreground"
                >
                    {{ t('admin.breakdownEmpty') }}
                </p>
                <div v-else class="flex flex-col gap-3">
                    <StatBar
                        v-for="status in ALL_ORDER_STATUSES"
                        :key="status"
                        :label="orderStatusLabel(status)"
                        :value="orderCount(status)"
                        :total="ordersTotal"
                        :fill-class="orderStatusFill(status)"
                    />
                </div>
            </section>

            <section class="rounded-xl border bg-card p-5">
                <div class="mb-4 flex items-center gap-2">
                    <Truck class="size-4 text-muted-foreground" />
                    <h3 class="text-sm font-semibold">
                        {{ t('admin.deliveriesByStatusTitle') }}
                    </h3>
                    <span
                        class="ml-auto text-xs text-muted-foreground tabular-nums"
                    >
                        {{ deliveriesTotal }}
                    </span>
                </div>
                <p
                    v-if="deliveriesTotal === 0"
                    class="py-2 text-sm text-muted-foreground"
                >
                    {{ t('admin.breakdownEmpty') }}
                </p>
                <div v-else class="flex flex-col gap-3">
                    <StatBar
                        v-for="status in ALL_DELIVERY_STATUSES"
                        :key="status"
                        :label="deliveryStatusLabel(status)"
                        :value="deliveryCount(status)"
                        :total="deliveriesTotal"
                        :fill-class="deliveryStatusFill(status)"
                    />
                </div>
            </section>
        </div>

        <!-- Discount management entry points -->
        <div class="grid gap-3 sm:grid-cols-2">
            <Link
                :href="indexAdminPromos.url()"
                class="group flex items-center gap-4 rounded-xl border bg-card p-4 transition-colors hover:border-primary/40 hover:bg-accent/40"
            >
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <Ticket class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-medium">{{ t('admin.promosLabel') }}</p>
                    <p class="text-sm text-muted-foreground tabular-nums">
                        {{ props.snapshot.promos.active }}
                        {{ t('admin.activeCount').toLowerCase() }} ·
                        {{ props.snapshot.promos.total }}
                        {{ t('admin.totalCount').toLowerCase() }}
                    </p>
                </div>
                <ArrowRight
                    class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5"
                />
            </Link>
            <Link
                :href="indexAdminVouchers.url()"
                class="group flex items-center gap-4 rounded-xl border bg-card p-4 transition-colors hover:border-primary/40 hover:bg-accent/40"
            >
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <BadgePercent class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-medium">{{ t('admin.vouchersLabel') }}</p>
                    <p class="text-sm text-muted-foreground tabular-nums">
                        {{ props.snapshot.vouchers.active }}
                        {{ t('admin.activeCount').toLowerCase() }} ·
                        {{ props.snapshot.vouchers.used_up }}
                        {{ t('admin.usedUpCount').toLowerCase() }}
                    </p>
                </div>
                <ArrowRight
                    class="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5"
                />
            </Link>
        </div>

        <!-- Platform inventory: secondary, lower density -->
        <section class="rounded-xl border bg-card p-5">
            <h3 class="mb-4 text-sm font-semibold">
                {{ t('admin.platformSectionTitle') }}
            </h3>
            <div
                class="grid grid-cols-2 gap-x-4 gap-y-5 sm:grid-cols-3 lg:grid-cols-6"
            >
                <div class="flex items-center gap-3">
                    <Users class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-lg font-semibold tabular-nums">
                            {{ props.snapshot.users.total }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('admin.totalUsers') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <ShoppingBag class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-lg font-semibold tabular-nums">
                            {{ props.snapshot.users.buyers }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('admin.totalBuyers') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Store class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-lg font-semibold tabular-nums">
                            {{ props.snapshot.users.sellers }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('admin.totalSellers') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Truck class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-lg font-semibold tabular-nums">
                            {{ props.snapshot.users.drivers }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('admin.totalDrivers') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Store class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-lg font-semibold tabular-nums">
                            {{ props.snapshot.stores_count }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('admin.totalStores') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Boxes class="size-5 text-muted-foreground" />
                    <div>
                        <p class="text-lg font-semibold tabular-nums">
                            {{ props.snapshot.products_count }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ t('admin.totalProducts') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
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
