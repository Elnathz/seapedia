<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Package } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Card, CardContent } from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import { formatIDR } from '@/lib/utils';

interface Product {
    id: number;
    slug: string;
    name: string;
    price: number;
    stock: number;
    image_path: string | null;
    category: { name: string } | null;
}

interface Store {
    id: number;
    name: string;
    slug: string;
    logo_path: string | null;
    description: string | null;
    is_active: boolean;
    created_at: string;
    products: Product[];
    products_count: number;
}

defineProps<{ store: Store }>();

const { getInitials, getGradientClass } = useInitials();
const { t } = useI18n();

function formatDate(dateStr: string): string {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(dateStr));
}
</script>

<template>
    <Head :title="store.name" />

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <Link
            href="/catalog"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            {{ t('catalog.backToCatalog') }}
        </Link>

        <!-- Store header -->
        <div class="mt-6 flex items-start gap-4">
            <!-- Gradient avatar with initials / logo -->
            <div class="shrink-0">
                <img
                    v-if="store.logo_path"
                    :src="`/storage/${store.logo_path}`"
                    :alt="store.name"
                    class="size-16 rounded-full object-cover shadow-sm sm:size-20 border border-border bg-background"
                />
                <div
                    v-else
                    class="flex size-16 shrink-0 items-center justify-center rounded-full text-xl font-bold text-white shadow-sm sm:size-20"
                    :class="getGradientClass(store.name)"
                >
                    {{ getInitials(store.name) }}
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-semibold sm:text-2xl">
                        {{ store.name }}
                    </h1>
                    <span
                        v-if="store.is_active"
                        class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700"
                    >
                        {{ t('catalog.storeActive') }}
                    </span>
                </div>
                <p
                    v-if="store.description"
                    class="mt-1 text-sm text-muted-foreground"
                >
                    {{ store.description }}
                </p>
                <!-- Meta: product count + join date -->
                <div
                    class="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
                >
                    <span class="inline-flex items-center gap-1">
                        <Package class="size-3.5" />
                        {{ store.products_count }}
                        {{ store.products_count === 1 ? 'produk' : 'produk' }}
                    </span>
                    <span>Bergabung {{ formatDate(store.created_at) }}</span>
                </div>
            </div>
        </div>

        <!-- Product grid — 4 columns like catalog -->
        <EmptyState
            v-if="store.products.length === 0"
            :title="t('store.noProductsTitle')"
            :description="t('store.noProductsDescription')"
            class="mt-10"
        />

        <div
            v-else
            class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4"
        >
            <Link
                v-for="product in store.products"
                :key="product.id"
                :href="`/catalog/${product.slug}`"
            >
                <Card
                    class="group h-full cursor-pointer overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md"
                >
                    <div
                        class="relative aspect-square overflow-hidden rounded-t-xl border-b border-border bg-muted"
                    >
                        <img
                            v-if="product.image_path"
                            :src="`/storage/${product.image_path}`"
                            :alt="product.name"
                            loading="lazy"
                            class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <PlaceholderPattern v-else />
                        <span
                            v-if="product.stock === 0"
                            class="absolute top-2 right-2 rounded-full bg-destructive px-2 py-0.5 text-xs font-medium text-white"
                        >
                            Habis
                        </span>
                    </div>
                    <CardContent class="flex flex-col gap-1 pt-3 sm:pt-4">
                        <span
                            v-if="product.category"
                            class="w-fit rounded-full bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground"
                        >
                            {{ product.category.name }}
                        </span>
                        <h2
                            class="line-clamp-2 text-sm leading-tight font-medium"
                        >
                            {{ product.name }}
                        </h2>
                        <p
                            class="mt-0.5 font-semibold text-primary tabular-nums sm:text-base"
                        >
                            {{ formatIDR(product.price) }}
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
