<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DriverJobController from '@/actions/App/Http/Controllers/Web/DriverJobController';
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
import {
    deliveryStatusBadgeVariant,
    deliveryStatusLabel,
} from '@/lib/deliveryStatus';
import type { DeliveryStatusKey } from '@/lib/deliveryStatus';
import type { OrderStatusKey } from '@/lib/orderStatus';
import { formatDateTime, formatIDR } from '@/lib/utils';
import { useAuthStore } from '@/stores/auth';

type DeliveryMethodKey = 'instant' | 'next_day' | 'regular';

interface OrderItemData {
    id: number;
    product_name_snapshot: string;
    quantity: number;
}

interface HistoryEntry {
    id: number;
    status: OrderStatusKey;
    note: string | null;
    created_at: string;
}

interface JobData {
    id: number;
    status: DeliveryStatusKey;
    earning_preview: number;
    earning_amount: number;
    driver: { id: number; name: string } | null;
    order: {
        id: number;
        code: string;
        status: OrderStatusKey;
        ship_recipient: string;
        ship_phone: string;
        ship_address: string;
        delivery_method: DeliveryMethodKey;
        delivery_fee: number;
        created_sim_at: string;
        store: { id: number; name: string };
        items: OrderItemData[];
        status_histories: HistoryEntry[];
    };
}

const props = defineProps<{ job: JobData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Tersedia', href: DriverJobController.index() },
            { title: 'Detail Pengiriman', href: '#' },
        ],
    },
});

const { t, locale } = useI18n();
const auth = useAuthStore();

const confirmOpen = ref(false);
const processing = ref(false);

const isOwnJob = computed(
    () => props.job.driver !== null && props.job.driver.id === auth.user?.id,
);

const methodLabelKey: Record<DeliveryMethodKey, string> = {
    instant: 'checkout.instant',
    next_day: 'checkout.nextDay',
    regular: 'checkout.regular',
};

function confirmAction() {
    confirmOpen.value = false;
    processing.value = true;

    const action =
        props.job.status === 'available'
            ? DriverJobController.take.url(props.job.id)
            : DriverJobController.complete.url(props.job.id);

    router.post(
        action,
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
    <Head :title="job.order.code" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="job.order.code"
                :description="formatDateTime(job.order.created_sim_at, locale)"
            />
            <Badge :variant="deliveryStatusBadgeVariant(job.status)">
                {{ deliveryStatusLabel(job.status) }}
            </Badge>
        </div>

        <div class="grid min-w-0 gap-6 lg:grid-cols-3">
            <div class="flex min-w-0 flex-col gap-6 lg:col-span-2">
                <Card class="min-w-0">
                    <CardContent class="space-y-2 pt-6">
                        <h3 class="font-medium">
                            {{ t('order.shippingTitle') }}
                        </h3>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('driver.columnStore') }}:</span
                            >
                            {{ job.order.store.name }}
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.recipientLabel') }}:</span
                            >
                            {{ job.order.ship_recipient }}
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.phoneLabel') }}:</span
                            >
                            {{ job.order.ship_phone }}
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.addressLabel') }}:</span
                            >
                            {{ job.order.ship_address }}
                        </p>
                        <p class="text-sm">
                            <span class="text-muted-foreground"
                                >{{ t('order.deliveryMethodLabel') }}:</span
                            >
                            {{ t(methodLabelKey[job.order.delivery_method]) }}
                        </p>
                    </CardContent>
                </Card>

                <Card class="min-w-0">
                    <CardContent class="pt-6">
                        <h3 class="mb-4 font-medium">
                            {{ t('order.itemsTitle') }}
                        </h3>
                        <ul class="space-y-2">
                            <li
                                v-for="item in job.order.items"
                                :key="item.id"
                                class="flex items-center justify-between text-sm"
                            >
                                <span>{{ item.product_name_snapshot }}</span>
                                <span class="text-muted-foreground tabular-nums"
                                    >x{{ item.quantity }}</span
                                >
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="pt-6">
                        <h3 class="mb-4 font-medium">
                            {{ t('order.timelineTitle') }}
                        </h3>
                        <StatusTimeline
                            :histories="job.order.status_histories"
                        />
                    </CardContent>
                </Card>
            </div>

            <Card class="min-w-0 lg:sticky lg:top-6 lg:self-start">
                <CardContent class="space-y-4 pt-6">
                    <h3 class="text-sm font-semibold">
                        {{ t('driver.payoutTitle') }}
                    </h3>

                    <div
                        class="flex items-center justify-between text-sm text-muted-foreground"
                    >
                        <span>{{ t('order.deliveryFee') }}</span>
                        <span class="tabular-nums">{{
                            formatIDR(job.order.delivery_fee)
                        }}</span>
                    </div>

                    <!-- Signature: the 80/20 split, made legible -->
                    <div
                        class="rounded-xl border border-primary/20 bg-primary/5 p-4"
                    >
                        <p
                            class="text-xs font-medium text-primary"
                        >
                            {{
                                job.status === 'completed'
                                    ? t('driver.totalEarnings')
                                    : t('driver.courierShare')
                            }}
                        </p>
                        <p
                            class="mt-1 text-2xl font-bold text-primary tabular-nums"
                        >
                            +{{
                                formatIDR(
                                    job.status === 'completed'
                                        ? job.earning_amount
                                        : job.earning_preview,
                                )
                            }}
                        </p>
                        <div
                            class="mt-3 flex h-2 overflow-hidden rounded-full bg-primary/10"
                            aria-hidden="true"
                        >
                            <div class="h-full w-[80%] bg-primary" />
                        </div>
                        <p
                            class="mt-2 text-xs text-primary/70"
                        >
                            {{ t('driver.earningShareNote') }}
                        </p>
                    </div>

                    <Button
                        v-if="job.status === 'available'"
                        class="w-full"
                        :disabled="processing"
                        @click="confirmOpen = true"
                    >
                        {{ t('driver.takeAction') }}
                    </Button>
                    <Button
                        v-else-if="job.status === 'taken' && isOwnJob"
                        class="w-full"
                        :disabled="processing"
                        @click="confirmOpen = true"
                    >
                        {{ t('driver.completeAction') }}
                    </Button>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="confirmOpen">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{
                        job.status === 'available'
                            ? t('driver.takeConfirmTitle')
                            : t('driver.completeConfirmTitle')
                    }}</DialogTitle>
                    <DialogDescription>
                        {{
                            job.status === 'available'
                                ? t('driver.takeConfirmDescription', {
                                      code: job.order.code,
                                  })
                                : t('driver.completeConfirmDescription', {
                                      code: job.order.code,
                                  })
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-4 gap-2">
                    <Button variant="secondary" @click="confirmOpen = false">{{
                        t('common.cancel')
                    }}</Button>
                    <Button :disabled="processing" @click="confirmAction">{{
                        job.status === 'available'
                            ? t('driver.takeConfirm')
                            : t('driver.completeConfirm')
                    }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
