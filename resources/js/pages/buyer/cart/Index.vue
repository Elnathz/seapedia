<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Minus, Plus, ShoppingBag, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerCartController from '@/actions/App/Http/Controllers/Web/BuyerCartController';
import CheckoutController from '@/actions/App/Http/Controllers/Web/CheckoutController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';

interface CartProduct {
    id: number;
    name: string;
    slug: string;
    image_path: string | null;
    price: number;
    stock: number;
}

interface CartVariant {
    id: number;
    name: string;
    price: number;
    stock: number;
    image_path: string | null;
}

interface CartItemData {
    id: number;
    quantity: number;
    price_snapshot: number;
    product: CartProduct;
    variant?: CartVariant | null;
}

interface CartData {
    id: number;
    store: { id: number; name: string; slug: string } | null;
    items: CartItemData[];
}

const props = defineProps<{ cart: CartData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Keranjang', href: BuyerCartController.index() },
        ],
    },
});

const { t } = useI18n();

const subtotal = computed(() =>
    props.cart.items.reduce(
        (sum, item) => sum + item.price_snapshot * item.quantity,
        0,
    ),
);

function stockOf(item: CartItemData): number {
    return item.variant?.stock ?? item.product.stock;
}

function changeQty(item: CartItemData, delta: number) {
    // Clamp into [1, stock] so the buyer can never push the cart past what the
    // seller has; the checkout also re-validates stock under a lock.
    const next = Math.min(Math.max(item.quantity + delta, 1), stockOf(item));

    if (next === item.quantity) {
        return;
    }

    router.put(
        BuyerCartController.update.url(item.id),
        { quantity: next },
        { preserveScroll: true, preserveState: true },
    );
}

function removeItem(item: CartItemData) {
    router.delete(BuyerCartController.destroy.url(item.id), {
        preserveScroll: true,
    });
}

function clearAll() {
    router.delete(BuyerCartController.clear.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="t('cart.title')" />

    <div class="flex flex-col gap-6">
        <div>
            <Heading variant="small" :title="t('cart.title')" />
            <p class="text-sm text-muted-foreground">
                {{ t('cart.singleStoreNote') }}
            </p>
        </div>

        <EmptyState
            v-if="cart.items.length === 0"
            :icon="ShoppingBag"
            :title="t('cart.emptyTitle')"
            :description="t('cart.emptyDescription')"
            :action-label="t('cart.browseCatalog')"
            @action="router.visit(catalogIndex.url())"
        />

        <template v-else>
            <p v-if="cart.store" class="text-sm font-medium">
                {{ t('cart.storeLabel') }}: {{ cart.store.name }}
            </p>

            <div class="flex flex-col gap-3">
                <Card
                    v-for="item in cart.items"
                    :key="item.id"
                    class="overflow-hidden transition-shadow duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] hover:shadow-[0_10px_30px_-14px_rgba(33,200,185,0.35)]"
                >
                    <CardContent class="flex gap-3 pt-6 sm:gap-4">
                        <img
                            v-if="
                                item.variant?.image_path ||
                                item.product.image_path
                            "
                            :src="`/storage/${item.variant?.image_path || item.product.image_path}`"
                            :alt="item.product.name"
                            class="size-16 shrink-0 rounded-lg border border-border object-cover sm:size-20"
                        />
                        <div
                            v-else
                            class="flex size-16 shrink-0 items-center justify-center rounded-lg border border-dashed border-border text-muted-foreground sm:size-20"
                        >
                            <ShoppingBag class="size-5" />
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col gap-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p
                                        class="line-clamp-2 leading-snug font-medium"
                                    >
                                        {{ item.product.name }}
                                    </p>
                                    <span
                                        v-if="item.variant"
                                        class="mt-1 inline-block w-fit rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary"
                                    >
                                        {{ item.variant.name }}
                                    </span>
                                    <p
                                        class="mt-0.5 text-sm text-muted-foreground tabular-nums"
                                    >
                                        {{ formatIDR(item.price_snapshot) }}
                                    </p>
                                    <p
                                        v-if="
                                            item.quantity >
                                            (item.variant?.stock ??
                                                item.product.stock)
                                        "
                                        class="mt-1 text-xs text-amber-600"
                                    >
                                        {{
                                            t('cart.stockWarning', {
                                                stock:
                                                    item.variant?.stock ??
                                                    item.product.stock,
                                            })
                                        }}
                                    </p>
                                </div>

                                <Button
                                    size="icon"
                                    variant="ghost"
                                    class="-mt-1 -mr-2 size-8 shrink-0 text-muted-foreground transition-colors hover:text-destructive"
                                    @click="removeItem(item)"
                                >
                                    <Trash2 class="size-4" />
                                    <span class="sr-only">{{
                                        t('cart.remove', {
                                            name: item.product.name,
                                        })
                                    }}</span>
                                </Button>
                            </div>

                            <div
                                class="mt-auto flex items-center justify-between gap-2"
                            >
                                <div
                                    class="inline-flex items-center rounded-full border border-border bg-muted/40 p-0.5"
                                >
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="size-7 rounded-full active:scale-95"
                                        :disabled="item.quantity <= 1"
                                        @click="changeQty(item, -1)"
                                    >
                                        <Minus class="size-3.5" />
                                    </Button>
                                    <span
                                        class="w-8 text-center text-sm font-medium tabular-nums"
                                        >{{ item.quantity }}</span
                                    >
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="size-7 rounded-full active:scale-95"
                                        :disabled="
                                            item.quantity >= stockOf(item)
                                        "
                                        @click="changeQty(item, 1)"
                                    >
                                        <Plus class="size-3.5" />
                                    </Button>
                                </div>

                                <p
                                    class="font-semibold text-foreground tabular-nums"
                                >
                                    {{
                                        formatIDR(
                                            item.price_snapshot * item.quantity,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="flex justify-end">
                <!-- Sea-glass summary: a glass plate seated in a teal tray (double-bezel). -->
                <div
                    class="w-full rounded-[1.75rem] bg-primary/5 p-1.5 ring-1 ring-primary/10 sm:w-80"
                >
                    <div
                        class="rounded-[calc(1.75rem-0.375rem)] border border-white/60 bg-card p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.65)]"
                    >
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="text-sm text-muted-foreground">
                                {{ t('cart.subtotal') }}
                            </span>
                            <span
                                class="text-xl font-bold text-primary tabular-nums"
                            >
                                {{ formatIDR(subtotal) }}
                            </span>
                        </div>

                        <Button
                            as-child
                            size="lg"
                            class="group mt-4 h-12 w-full justify-between rounded-full pr-2 pl-5"
                        >
                            <Link :href="CheckoutController.show.url()">
                                <span class="font-semibold">{{
                                    t('cart.goToCheckout')
                                }}</span>
                                <span
                                    class="flex size-8 items-center justify-center rounded-full bg-primary-foreground/20 transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:translate-x-1"
                                >
                                    <ArrowRight class="size-4" />
                                </span>
                            </Link>
                        </Button>

                        <Button
                            variant="ghost"
                            class="mt-2 w-full text-muted-foreground hover:text-destructive"
                            @click="clearAll"
                        >
                            {{ t('cart.clearCart') }}
                        </Button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
