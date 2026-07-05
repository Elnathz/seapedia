<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ShoppingBag, Store } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerOrderController from '@/actions/App/Http/Controllers/Web/BuyerOrderController';
import CheckoutController from '@/actions/App/Http/Controllers/Web/CheckoutController';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';

interface OrderItemData {
    id: number;
    product_name_snapshot: string;
    quantity: number;
    variant?: { name: string } | null;
}

interface OrderData {
    id: number;
    code: string;
    ship_recipient: string;
    ship_address: string;
    delivery_method: string;
    grand_total: number;
    store: { name: string };
    items: OrderItemData[];
}

const props = defineProps<{ order: OrderData }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Checkout', href: CheckoutController.show() }],
    },
});

const { t } = useI18n();

const deliveryMethodLabel = computed(() => {
    const map: Record<string, string> = {
        instant: t('checkout.instant'),
        next_day: t('checkout.nextDay'),
        regular: t('checkout.regular'),
    };

    return map[props.order.delivery_method] ?? props.order.delivery_method;
});
</script>

<template>
    <Head :title="t('checkout.successTitle')" />

    <div class="mx-auto flex w-full max-w-xl flex-col items-center py-6">
        <!-- Celebration hero -->
        <div class="relative mb-6 flex items-center justify-center">
            <span
                class="absolute inline-flex size-24 animate-ping rounded-full bg-primary/20 [animation-duration:2.5s]"
            />
            <span
                class="relative flex size-20 items-center justify-center rounded-full bg-primary/10 text-primary ring-8 ring-primary/5"
            >
                <CheckCircle2 class="size-11" />
            </span>
        </div>

        <h1 class="text-center text-2xl font-bold tracking-tight">
            {{ t('checkout.successTitle') }}
        </h1>
        <p class="mt-2 max-w-md text-center text-sm text-muted-foreground">
            {{ t('checkout.successSubtitle') }}
        </p>

        <!-- Order recap -->
        <Card
            class="mt-6 w-full border-primary/20 bg-gradient-to-b from-primary/5 to-transparent"
        >
            <CardContent class="space-y-4 pt-6">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-muted-foreground">{{
                        t('checkout.successOrderCode')
                    }}</span>
                    <span
                        class="font-mono text-sm font-semibold tracking-wide"
                        >{{ order.code }}</span
                    >
                </div>

                <Separator />

                <div class="flex items-start gap-3">
                    <Store
                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium">
                            {{ order.store.name }}
                        </p>
                        <ul class="mt-1 space-y-0.5">
                            <li
                                v-for="item in order.items"
                                :key="item.id"
                                class="truncate text-sm text-muted-foreground"
                            >
                                {{ item.quantity }}×
                                {{ item.product_name_snapshot
                                }}<span v-if="item.variant">
                                    · {{ item.variant.name }}</span
                                >
                            </li>
                        </ul>
                    </div>
                </div>

                <Separator />

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">
                            {{ t('checkout.successDeliveryMethod') }}
                        </dt>
                        <dd class="text-right font-medium">
                            {{ deliveryMethodLabel }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="shrink-0 text-muted-foreground">
                            {{ t('checkout.successDeliveredTo') }}
                        </dt>
                        <dd class="truncate text-right">
                            {{ order.ship_recipient }}
                        </dd>
                    </div>
                </dl>

                <Separator />

                <div
                    class="flex items-end justify-between rounded-xl bg-primary/10 px-3 py-2.5"
                >
                    <span class="text-sm font-medium">{{
                        t('checkout.successTotalPaid')
                    }}</span>
                    <span class="text-xl font-bold text-primary tabular-nums">{{
                        formatIDR(order.grand_total)
                    }}</span>
                </div>
            </CardContent>
        </Card>

        <div class="mt-6 flex w-full flex-col gap-3 sm:flex-row">
            <Button
                as-child
                class="h-12 w-full text-base font-semibold shadow-md shadow-primary/20 sm:flex-1"
            >
                <Link :href="BuyerOrderController.show(order.id).url">
                    {{ t('checkout.successViewOrder') }}
                </Link>
            </Button>
            <Button
                as-child
                variant="outline"
                class="h-12 w-full text-base font-semibold sm:flex-1"
            >
                <Link :href="catalogIndex.url()">
                    <ShoppingBag class="mr-1.5 size-5" />
                    {{ t('checkout.successShopAgain') }}
                </Link>
            </Button>
        </div>
    </div>
</template>
