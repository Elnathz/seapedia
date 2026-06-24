<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingBag, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerCartController from '@/actions/App/Http/Controllers/Web/BuyerCartController';
import CheckoutController from '@/actions/App/Http/Controllers/Web/CheckoutController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
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

interface CartItemData {
    id: number;
    quantity: number;
    price_snapshot: number;
    product: CartProduct;
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

function changeQty(item: CartItemData, delta: number) {
    const next = item.quantity + delta;

    if (next < 1) {
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
                <Card v-for="item in cart.items" :key="item.id">
                    <CardContent class="flex items-center gap-4 pt-6">
                        <img
                            v-if="item.product.image_path"
                            :src="`/storage/${item.product.image_path}`"
                            :alt="item.product.name"
                            class="size-16 rounded-md border border-border object-cover"
                        />
                        <div
                            v-else
                            class="flex size-16 items-center justify-center rounded-md border border-dashed border-border text-muted-foreground"
                        >
                            <ShoppingBag class="size-5" />
                        </div>

                        <div class="flex-1">
                            <p class="font-medium">{{ item.product.name }}</p>
                            <p
                                class="text-sm text-muted-foreground tabular-nums"
                            >
                                {{ formatIDR(item.price_snapshot) }}
                            </p>
                            <p
                                v-if="item.quantity > item.product.stock"
                                class="mt-1 text-xs text-amber-600 dark:text-amber-400"
                            >
                                {{
                                    t('cart.stockWarning', {
                                        stock: item.product.stock,
                                    })
                                }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                size="icon"
                                variant="outline"
                                class="size-8"
                                :disabled="item.quantity <= 1"
                                @click="changeQty(item, -1)"
                            >
                                <Minus class="size-3.5" />
                            </Button>
                            <span class="w-8 text-center tabular-nums">{{
                                item.quantity
                            }}</span>
                            <Button
                                size="icon"
                                variant="outline"
                                class="size-8"
                                @click="changeQty(item, 1)"
                            >
                                <Plus class="size-3.5" />
                            </Button>
                        </div>

                        <p class="w-28 text-right font-medium tabular-nums">
                            {{ formatIDR(item.price_snapshot * item.quantity) }}
                        </p>

                        <Button
                            size="icon"
                            variant="ghost"
                            class="text-muted-foreground hover:text-destructive"
                            @click="removeItem(item)"
                        >
                            <Trash2 class="size-4" />
                            <span class="sr-only">{{
                                t('cart.remove', { name: item.product.name })
                            }}</span>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <Separator />

            <div class="flex flex-wrap items-center justify-between gap-4">
                <Button variant="outline" @click="clearAll">
                    {{ t('cart.clearCart') }}
                </Button>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-muted-foreground">
                            {{ t('cart.subtotal') }}
                        </p>
                        <p class="text-xl font-semibold tabular-nums">
                            {{ formatIDR(subtotal) }}
                        </p>
                    </div>
                    <Button as-child>
                        <Link :href="CheckoutController.show.url()">{{
                            t('cart.goToCheckout')
                        }}</Link>
                    </Button>
                </div>
            </div>
        </template>
    </div>
</template>
