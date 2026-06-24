<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ShoppingCart } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { store as storeCartItem } from '@/actions/App/Http/Controllers/Web/BuyerCartController';
import InputError from '@/components/InputError.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
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
import { Input } from '@/components/ui/input';
import { useInitials } from '@/composables/useInitials';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';
import { show as storeShow } from '@/routes/stores';
import { useAuthStore } from '@/stores/auth';

interface Store {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
}

interface Product {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    price: number;
    stock: number;
    image_path: string | null;
    store: Store;
}

const props = defineProps<{ product: Product }>();

const { getInitials } = useInitials();
const { t } = useI18n();
const auth = useAuthStore();

const quantity = ref(1);
const adding = ref(false);
const quantityError = ref<string | null>(null);
const conflictOpen = ref(false);
const conflictMessage = ref('');

function addToCart(replace = false) {
    adding.value = true;
    quantityError.value = null;

    router.post(
        storeCartItem.url(),
        {
            product_id: props.product.id,
            quantity: quantity.value,
            replace,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                if (errors.store) {
                    conflictMessage.value = errors.store;
                    conflictOpen.value = true;
                } else if (errors.quantity) {
                    quantityError.value = errors.quantity;
                }
            },
            onSuccess: () => {
                conflictOpen.value = false;
            },
            onFinish: () => {
                adding.value = false;
            },
        },
    );
}

function confirmClearAndAdd() {
    conflictOpen.value = false;
    addToCart(true);
}
</script>

<template>
    <Head :title="product.name" />

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <Link
            :href="catalogIndex.url()"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            {{ t('catalog.backToCatalog') }}
        </Link>

        <Card class="mt-6 overflow-hidden">
            <div class="grid sm:grid-cols-2">
                <div
                    class="relative aspect-square border-b border-border sm:border-r sm:border-b-0"
                >
                    <img
                        v-if="product.image_path"
                        :src="`/storage/${product.image_path}`"
                        :alt="product.name"
                        loading="lazy"
                        class="size-full object-cover"
                    />
                    <PlaceholderPattern v-else />
                </div>
                <CardContent class="flex flex-col gap-3 pt-6">
                    <h1 class="text-xl font-semibold">{{ product.name }}</h1>
                    <p class="text-2xl font-bold tabular-nums">
                        {{ formatIDR(product.price) }}
                    </p>
                    <p
                        v-if="product.description"
                        class="text-sm text-muted-foreground"
                    >
                        {{ product.description }}
                    </p>
                    <p class="text-sm">
                        {{ t('catalog.stock') }}:
                        <span class="font-medium tabular-nums">{{
                            product.stock
                        }}</span>
                    </p>

                    <div v-if="auth.activeRole === 'buyer'">
                        <div class="flex items-end gap-3">
                            <div class="grid gap-1.5">
                                <label
                                    for="quantity"
                                    class="text-xs text-muted-foreground"
                                    >{{ t('cart.quantityLabel') }}</label
                                >
                                <Input
                                    id="quantity"
                                    v-model="quantity"
                                    type="number"
                                    inputmode="numeric"
                                    min="1"
                                    :max="product.stock"
                                    class="w-20"
                                    :disabled="product.stock <= 0"
                                />
                            </div>
                            <Button
                                :disabled="adding || product.stock <= 0"
                                @click="addToCart(false)"
                            >
                                <ShoppingCart class="size-4" />
                                {{ t('cart.addToCart') }}
                            </Button>
                        </div>
                        <InputError :message="quantityError ?? undefined" />
                    </div>

                    <Dialog v-model:open="conflictOpen">
                        <DialogContent>
                            <DialogHeader class="space-y-3">
                                <DialogTitle>{{
                                    t('cart.conflictTitle')
                                }}</DialogTitle>
                                <DialogDescription>
                                    {{ conflictMessage }}
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter class="mt-4 gap-2">
                                <Button
                                    variant="secondary"
                                    @click="conflictOpen = false"
                                    >{{ t('common.cancel') }}</Button
                                >
                                <Button @click="confirmClearAndAdd">{{
                                    t('cart.clearAndAdd')
                                }}</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <Link
                        :href="storeShow.url(product.store.slug)"
                        class="mt-2 flex items-center gap-3 rounded-xl border border-border p-3 transition-colors hover:border-primary"
                    >
                        <Avatar class="size-9">
                            <AvatarFallback>{{
                                getInitials(product.store.name)
                            }}</AvatarFallback>
                        </Avatar>
                        <div class="flex-1">
                            <p class="text-sm font-medium">
                                {{ product.store.name }}
                            </p>
                            <Badge
                                v-if="product.store.is_active"
                                variant="secondary"
                                class="mt-0.5"
                            >
                                {{ t('catalog.storeActive') }}
                            </Badge>
                        </div>
                    </Link>
                </CardContent>
            </div>
        </Card>
    </div>
</template>
