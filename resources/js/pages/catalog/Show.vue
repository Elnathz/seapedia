<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ShoppingCart,
    Share2,
    CheckCircle,
    XCircle,
} from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { store as storeCartItem } from '@/actions/App/Http/Controllers/Web/BuyerCartController';
import InputError from '@/components/InputError.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { login } from '@/routes';
import { index as cartIndex } from '@/routes/buyer/cart';
import { index as catalogIndex } from '@/routes/catalog';
import { show as storeShow } from '@/routes/stores';
import { useAuthStore } from '@/stores/auth';

interface ProductImage {
    id: number;
    image_path: string;
    product_variant_id: number | null;
}

interface ProductVariant {
    id: number;
    variant_type: string | null;
    name: string;
    price: number;
    stock: number;
}

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
    variants?: ProductVariant[];
    images?: ProductImage[];
}

const props = defineProps<{
    product: Product;
    relatedProducts?: Product[];
}>();

const { getInitials } = useInitials();
const { t } = useI18n();
const auth = useAuthStore();

// ─── Gallery State ───
const generalImages = computed(() => {
    const imgs: { src: string; type: string }[] = [];

    if (props.product.image_path) {
        imgs.push({
            src: `/storage/${props.product.image_path}`,
            type: 'general',
        });
    }

    if (props.product.images) {
        props.product.images
            .filter(
                (img) =>
                    !img.product_variant_id &&
                    img.image_path !== props.product.image_path,
            )
            .forEach((img) =>
                imgs.push({
                    src: `/storage/${img.image_path}`,
                    type: 'general',
                }),
            );
    }

    return imgs;
});

const getVariantImages = (variant: ProductVariant | null) => {
    if (!variant || !props.product.images) {
        return [];
    }

    return props.product.images
        .filter((img) => img.product_variant_id === variant.id)
        .map((img) => ({
            src: `/storage/${img.image_path}`,
            type: 'variant',
            variantId: variant.id,
        }));
};

// ─── Variant Selection ───
const selectedVariant = ref<ProductVariant | null>(
    props.product.variants && props.product.variants.length > 0
        ? props.product.variants[0]
        : null,
);

const selectVariant = (variant: ProductVariant) => {
    if (variant.stock <= 0) {
        return;
    }

    selectedVariant.value = variant;
};

const displayImages = computed(() => {
    const imgs = [...generalImages.value];

    if (selectedVariant.value) {
        imgs.push(...getVariantImages(selectedVariant.value));
    }

    return imgs.length > 0 ? imgs : [];
});

const activeImageIndex = ref(0);
const activeImage = computed(
    () => displayImages.value[activeImageIndex.value]?.src || '',
);

watch(selectedVariant, () => {
    const firstVariantIdx = generalImages.value.length;

    if (displayImages.value.length > firstVariantIdx) {
        activeImageIndex.value = firstVariantIdx;
    } else {
        activeImageIndex.value = 0;
    }

    quantity.value = 1; // Reset quantity on variant change
});

// ─── Price & Stock ───
const currentPrice = computed(() =>
    selectedVariant.value ? selectedVariant.value.price : props.product.price,
);

const currentStock = computed(() =>
    selectedVariant.value ? selectedVariant.value.stock : props.product.stock,
);

const variantGroups = computed(() => {
    const groups: Record<string, ProductVariant[]> = {};

    if (!props.product.variants) {
        return groups;
    }

    props.product.variants.forEach((v) => {
        const type = v.variant_type || 'Pilihan';

        if (!groups[type]) {
            groups[type] = [];
        }

        groups[type].push(v);
    });

    return groups;
});

// ─── Cart Logic ───
const quantity = ref(1);
const adding = ref(false);
const quantityError = ref<string | null>(null);
const conflictOpen = ref(false);
const conflictMessage = ref('');
const buyingNow = ref(false);

function addToCart(replace = false, redirect = false) {
    if (redirect) {
        buyingNow.value = true;
    } else {
        adding.value = true;
    }

    quantityError.value = null;

    router.post(
        storeCartItem.url(),
        {
            product_id: props.product.id,
            product_variant_id: selectedVariant.value?.id,
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

                if (redirect) {
                    router.visit(cartIndex.url());
                }
            },
            onFinish: () => {
                adding.value = false;
                buyingNow.value = false;
            },
        },
    );
}

function confirmClearAndAdd() {
    conflictOpen.value = false;
    addToCart(true, buyingNow.value);
}
</script>

<template>
    <Head :title="product.name" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <Link
            :href="catalogIndex.url()"
            class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            {{ t('catalog.backToCatalog') }}
        </Link>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:gap-12">
            <!-- LEFT: Image Gallery -->
            <div class="flex flex-col gap-4">
                <div
                    class="group relative flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-border bg-muted md:aspect-auto md:h-[500px]"
                >
                    <img
                        v-if="activeImage"
                        :src="activeImage"
                        :alt="product.name"
                        class="ease-out-expo h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                    />
                    <PlaceholderPattern v-else />

                    <!-- Carousel Controls -->
                    <button
                        v-if="displayImages.length > 1"
                        @click="
                            activeImageIndex =
                                activeImageIndex > 0
                                    ? activeImageIndex - 1
                                    : displayImages.length - 1
                        "
                        class="absolute left-4 flex h-10 w-10 items-center justify-center rounded-full bg-background/80 text-foreground opacity-0 shadow-md backdrop-blur transition-opacity duration-200 group-hover:opacity-100 hover:bg-background"
                    >
                        <ArrowLeft class="size-5" />
                    </button>
                    <button
                        v-if="displayImages.length > 1"
                        @click="
                            activeImageIndex =
                                activeImageIndex < displayImages.length - 1
                                    ? activeImageIndex + 1
                                    : 0
                        "
                        class="absolute right-4 flex h-10 w-10 items-center justify-center rounded-full bg-background/80 text-foreground opacity-0 shadow-md backdrop-blur transition-opacity duration-200 group-hover:opacity-100 hover:bg-background"
                    >
                        <ArrowLeft class="size-5 rotate-180" />
                    </button>
                </div>
                <!-- Thumbnails -->
                <div
                    v-if="displayImages.length > 1"
                    class="scrollbar-hide flex gap-3 overflow-x-auto pb-2"
                >
                    <button
                        v-for="(img, idx) in displayImages"
                        :key="idx"
                        @click="activeImageIndex = idx"
                        :class="[
                            'flex h-20 w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 bg-muted transition-all duration-200',
                            activeImageIndex === idx
                                ? 'scale-95 border-primary ring-2 ring-primary/20'
                                : 'border-transparent hover:border-border',
                        ]"
                    >
                        <img
                            :src="img.src"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                    </button>
                </div>
            </div>

            <!-- RIGHT: Product Info -->
            <div class="flex flex-col">
                <div class="flex items-start justify-between gap-4">
                    <h1
                        class="text-2xl leading-tight font-bold tracking-tight text-foreground md:text-3xl"
                    >
                        {{ product.name }}
                    </h1>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="shrink-0 rounded-full text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                    >
                        <Share2 class="size-5" />
                    </Button>
                </div>

                <div class="mt-6 flex flex-col gap-2">
                    <span
                        class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{
                            props.product.variants?.length
                                ? 'Mulai Dari'
                                : 'Harga'
                        }}
                    </span>
                    <p
                        class="text-3xl font-black tracking-tight text-primary tabular-nums md:text-4xl"
                    >
                        {{ formatIDR(currentPrice) }}
                    </p>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <Badge
                        v-if="currentStock > 0"
                        variant="secondary"
                        class="gap-1.5 border-transparent bg-emerald-500/15 px-3 py-1 text-sm text-emerald-600 hover:bg-emerald-500/25"
                    >
                        <CheckCircle class="size-4" /> Stok Tersedia ({{
                            currentStock
                        }})
                    </Badge>
                    <Badge
                        v-else
                        variant="destructive"
                        class="gap-1.5 px-3 py-1 text-sm"
                    >
                        <XCircle class="size-4" /> Stok Habis
                    </Badge>
                </div>

                <!-- Store Info -->
                <div class="mt-6 mb-2">
                    <Link
                        :href="storeShow.url(product.store.slug)"
                        class="group flex items-center gap-4 rounded-2xl border-2 border-border/50 bg-muted/30 p-4 transition-all duration-300 hover:border-primary/50 hover:bg-muted/50 hover:shadow-sm"
                    >
                        <Avatar
                            class="size-12 border-2 border-background shadow-sm transition-transform group-hover:scale-105"
                        >
                            <AvatarFallback
                                class="bg-primary/10 text-base font-bold text-primary"
                                >{{
                                    getInitials(product.store.name)
                                }}</AvatarFallback
                            >
                        </Avatar>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-bold text-foreground">
                                    {{ product.store.name }}
                                </p>
                                <Badge
                                    v-if="product.store.is_active"
                                    variant="secondary"
                                    class="h-4 border-transparent bg-blue-500/15 px-1 text-[9px] font-bold text-blue-600 uppercase hover:bg-blue-500/25"
                                >
                                    PRO
                                </Badge>
                            </div>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">
                                Kunjungi Toko →
                            </p>
                        </div>
                    </Link>
                </div>

                <hr class="my-6 border-border" />

                <!-- Variant Selector -->
                <div
                    v-for="(variants, typeName) in variantGroups"
                    :key="typeName"
                    class="mb-6"
                >
                    <div class="mb-3 flex items-baseline justify-between">
                        <h3 class="text-sm font-semibold text-foreground">
                            {{ typeName }}
                        </h3>
                        <span
                            v-if="
                                selectedVariant &&
                                selectedVariant.variant_type === typeName
                            "
                            class="text-sm font-medium text-primary"
                        >
                            {{ selectedVariant.name }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        <button
                            v-for="variant in variants"
                            :key="variant.id"
                            @click="selectVariant(variant)"
                            :disabled="variant.stock <= 0"
                            :class="[
                                'ease-out-back rounded-xl border-2 px-5 py-2.5 text-sm font-semibold transition-all duration-200',
                                selectedVariant?.id === variant.id
                                    ? 'scale-[0.98] border-primary bg-primary/10 text-primary shadow-sm'
                                    : variant.stock > 0
                                      ? 'border-border bg-background text-foreground hover:border-primary/50 hover:bg-muted'
                                      : 'cursor-not-allowed border-border/50 bg-muted/50 text-muted-foreground opacity-50',
                            ]"
                        >
                            {{ variant.name }}
                        </button>
                    </div>
                </div>

                <!-- Action Section -->
                <div class="mt-auto pt-6">
                    <div
                        v-if="
                            auth.isAuthenticated && auth.activeRole === 'buyer'
                        "
                        class="flex flex-col gap-4"
                    >
                        <div class="grid gap-2">
                            <label
                                for="quantity"
                                class="text-sm font-semibold text-foreground"
                                >{{ t('cart.quantityLabel') }}</label
                            >
                            <div class="flex items-center gap-4">
                                <Input
                                    id="quantity"
                                    v-model="quantity"
                                    type="number"
                                    inputmode="numeric"
                                    min="1"
                                    :max="currentStock"
                                    class="h-12 w-24 rounded-xl text-center text-lg font-medium"
                                    :disabled="currentStock <= 0"
                                />
                                <span
                                    v-if="
                                        currentStock > 0 && currentStock <= 10
                                    "
                                    class="animate-pulse text-sm font-medium text-orange-500"
                                    >Tersisa {{ currentStock }} buah!</span
                                >
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <Button
                                variant="outline"
                                class="h-11 flex-1 gap-2.5 rounded-xl border-2 border-primary font-bold text-primary transition-transform hover:bg-primary/5 active:scale-[0.98]"
                                :disabled="
                                    adding ||
                                    buyingNow ||
                                    currentStock <= 0 ||
                                    (props.product.variants?.length > 0 &&
                                        !selectedVariant)
                                "
                                @click="addToCart(false, false)"
                            >
                                <ShoppingCart class="size-4" />
                                + Keranjang
                            </Button>
                            <Button
                                class="h-11 flex-1 gap-2.5 rounded-xl font-bold shadow-md shadow-primary/20 transition-all hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98]"
                                :disabled="
                                    adding ||
                                    buyingNow ||
                                    currentStock <= 0 ||
                                    (props.product.variants?.length > 0 &&
                                        !selectedVariant)
                                "
                                @click="addToCart(false, true)"
                            >
                                Beli Langsung
                            </Button>
                        </div>
                        <InputError :message="quantityError ?? undefined" />
                    </div>

                    <div v-else-if="!auth.isAuthenticated" class="mt-4">
                        <Button
                            as-child
                            size="lg"
                            class="h-14 w-full rounded-xl text-base font-bold shadow-lg shadow-primary/25"
                        >
                            <Link :href="login()">Masuk untuk Membeli</Link>
                        </Button>
                    </div>
                    <div
                        v-else
                        class="mt-4 rounded-xl border border-primary/20 bg-primary/10 p-5 text-center"
                    >
                        <p class="text-sm font-medium text-primary">
                            Aktifkan peran Pembeli untuk melakukan transaksi.
                        </p>
                    </div>
                </div>

                <hr class="my-8 border-border" />

                <!-- Description -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-foreground">
                        Deskripsi Produk
                    </h3>
                    <p
                        v-if="product.description"
                        class="text-sm leading-relaxed whitespace-pre-line text-muted-foreground md:text-base"
                    >
                        {{ product.description }}
                    </p>
                    <p v-else class="text-sm text-muted-foreground italic">
                        Belum ada deskripsi.
                    </p>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div
            v-if="relatedProducts && relatedProducts.length > 0"
            class="mx-auto mt-16 max-w-6xl px-4 md:px-8"
        >
            <h2 class="mb-6 text-2xl font-bold text-foreground">
                Produk Terkait
            </h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                <ProductCard
                    v-for="relProduct in relatedProducts"
                    :key="relProduct.id"
                    :product="relProduct"
                />
            </div>
        </div>

        <Dialog v-model:open="conflictOpen">
            <DialogContent class="rounded-2xl sm:max-w-md">
                <DialogHeader class="space-y-3">
                    <DialogTitle class="text-xl">{{
                        t('cart.conflictTitle')
                    }}</DialogTitle>
                    <DialogDescription class="text-base">
                        {{ conflictMessage }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-6 gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl font-medium"
                        @click="conflictOpen = false"
                        >{{ t('common.cancel') }}</Button
                    >
                    <Button
                        class="h-11 rounded-xl font-medium"
                        @click="confirmClearAndAdd"
                        >{{ t('cart.clearAndAdd') }}</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
/* Hidden scrollbar for gallery thumbnails */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Add custom transition timing that respects reduced motion but is playful by default */
.ease-out-back {
    transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.ease-out-expo {
    transition-timing-function: cubic-bezier(0.19, 1, 0.22, 1);
}
</style>
