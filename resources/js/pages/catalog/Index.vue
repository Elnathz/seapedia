<script setup lang="ts">
import { Head, Link, router, useRemember } from '@inertiajs/vue3';
import { ChevronRight, Search } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import BannerCarousel from '@/components/storefront/BannerCarousel.vue';
import BannerImage from '@/components/storefront/BannerImage.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { useCategories } from '@/composables/useCategories';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex, show as catalogShow } from '@/routes/catalog';
import type { BannerNode } from '@/types/banner';

interface Store {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    slug: string;
    name: string;
    description: string;
    price: number;
    stock: number;
    image_path: string | null;
    store: Store;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface ActiveCategory {
    id: number;
    name: string;
    slug: string;
    parent: { name: string; slug: string } | null;
}

const props = defineProps<{
    products: PaginatedProducts;
    search: string | null;
    sort: string | null;
    activeCategory: ActiveCategory | null;
    banners: { main: BannerNode[]; side_top: BannerNode[]; side_bottom: BannerNode[] };
    personalizedProducts: Product[];
    popularCategories: ActiveCategory[];
}>();

const query = ref(props.search ?? '');
const sortValue = ref(props.sort ?? 'random');
const loading = ref(false);
const { t } = useI18n();
const { categories } = useCategories();

// The parent whose subcategory row should be shown: the active category's
// parent when a child is selected, or the active category itself when it is a
// parent. Null means no category filter (the "Semua" chip is active).
const activeParentSlug = computed(
    () =>
        props.activeCategory?.parent?.slug ??
        props.activeCategory?.slug ??
        null,
);

const allProducts = useRemember<Product[]>([...props.products.data], 'catalog-products');
let isLoadMore = false;

watch(() => props.products.data, (newData) => {
    if (isLoadMore) {
        allProducts.value.push(...newData);
        isLoadMore = false;
    } else {
        allProducts.value = [...newData];
    }
});

const subCategories = computed(
    () =>
        categories.value.find((root) => root.slug === activeParentSlug.value)
            ?.children ?? [],
);

function selectCategory(slug: string | null) {
    visit(buildParams({ category: slug ?? undefined, q: props.search, sort: sortValue.value }));
}

function applySort(val: string) {
    sortValue.value = val;
    visit(buildParams({ category: props.activeCategory?.slug, q: props.search, sort: val }));
}

function goToPage(page: number) {
    visit(
        buildParams({
            category: props.activeCategory?.slug,
            q: props.search,
            sort: sortValue.value,
            page,
        }),
    );
}

function loadMore() {
    if (props.products.current_page < props.products.last_page) {
        isLoadMore = true;
        goToPage(props.products.current_page + 1);
    }
}

function resetToPageOne() {
    goToPage(1);
}

function buildParams(input: {
    category?: string;
    q?: string | null;
    sort?: string;
    page?: number;
}): Record<string, string | number> {
    const params: Record<string, string | number> = {};

    if (input.q) {
        params.q = input.q;
    }

    if (input.category) {
        params.category = input.category;
    }

    if (input.sort && input.sort !== 'random') {
        params.sort = input.sort;
    }

    if (input.page) {
        params.page = input.page;
    }

    return params;
}

function visit(params: Record<string, string | number>) {
    router.get(catalogIndex.url(), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
}
</script>

<template>
    <Head :title="t('catalog.title')" />

    <!-- Banner block -->
    <div class="w-full">
        <div v-if="banners.main.length || banners.side_top.length || banners.side_bottom.length" class="mx-auto max-w-7xl">
            <!-- Desktop: carousel (2/3) | side banners (1/3) -->
            <div class="hidden lg:block px-4 py-6 sm:px-6 w-full">
                <div class="grid grid-cols-3 gap-3 w-full" style="aspect-ratio: 3.75/1;">
                    <!-- kiri -->
                    <div class="col-span-2 h-full min-h-0 min-w-0">
                        <BannerCarousel
                            v-if="banners.main.length"
                            :slides="banners.main"
                            class="h-full w-full"
                        />
                    </div>
                    <!-- kanan -->
                    <div class="grid grid-rows-2 gap-3 h-full min-h-0 min-w-0">
                        <!-- row pertama -->
                        <div class="grid grid-cols-2 gap-3 min-h-0 min-w-0">
                            <BannerImage
                                v-for="b in banners.side_top.slice(0, 2)"
                                :key="b.id"
                                :banner="b"
                                class="h-full min-h-0"
                            />
                        </div>
                        <!-- row kedua -->
                        <BannerImage
                            v-if="banners.side_bottom[0]"
                            :banner="banners.side_bottom[0]"
                            class="h-full min-h-0"
                        />
                    </div>
                </div>
            </div>
            <!-- Mobile/tablet -->
            <div class="space-y-3 lg:hidden px-4 py-4 w-full">
                <div v-if="banners.main.length" class="w-full aspect-[5/2]">
                    <BannerCarousel :slides="banners.main" />
                </div>
                <div v-if="banners.side_top.length > 0" class="grid grid-cols-2 gap-3">
                    <div v-for="b in banners.side_top.slice(0, 2)" :key="b.id" class="w-full aspect-[5/4]">
                        <BannerImage :banner="b" />
                    </div>
                </div>
                <div v-if="banners.side_bottom.length > 0" class="w-full aspect-[5/2]">
                    <BannerImage :banner="banners.side_bottom[0]" />
                </div>
            </div>
        </div>
        <!-- Fallback hero when no banners -->
        <div
            v-else
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6"
        >
            <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-primary/15 via-secondary/40 to-background px-6 py-10 sm:px-10 sm:py-14">
                <h1 class="text-2xl font-semibold sm:text-3xl">
                    {{ t('catalog.heroTitle') }}
                </h1>
                <p class="mt-2 max-w-md text-sm text-muted-foreground sm:text-base">
                    {{ t('catalog.heroSubtitle') }}
                </p>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
        <div v-if="popularCategories && popularCategories.length > 0 && !activeCategory && !query" class="mb-10">
            <h2 class="text-xl font-bold mb-4">Kategori Populer</h2>
            <div class="flex gap-3 overflow-x-auto pb-2">
                <button
                    v-for="cat in popularCategories"
                    :key="cat.id"
                    type="button"
                    class="shrink-0 rounded-xl border border-border bg-card px-5 py-3 text-sm font-medium transition-colors hover:border-primary/40 hover:text-primary"
                    @click="selectCategory(cat.slug)"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <!-- Pilihan Untukmu -->
        <div v-if="personalizedProducts && personalizedProducts.length > 0 && !activeCategory && !query" class="mb-10">
            <h2 class="text-xl font-bold mb-4">Pilihan Untukmu</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                <Link
                    v-for="product in personalizedProducts"
                    :key="product.id"
                    :href="catalogShow.url(product.slug)"
                    class="group block h-full"
                >
                    <Card class="h-full cursor-pointer overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md p-0 gap-0 flex flex-col">
                        <div class="relative aspect-square w-full overflow-hidden border-b border-border bg-muted/30">
                            <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name" loading="lazy" class="size-full object-cover transition-transform duration-300 group-hover:scale-105" />
                            <PlaceholderPattern v-else />
                            <Badge v-if="product.stock === 0" variant="destructive" class="absolute top-2 right-2 text-[10px] px-1.5 py-0 h-5">Habis</Badge>
                        </div>
                        <CardContent class="flex flex-col gap-1 p-3 flex-1">
                            <h2 class="line-clamp-2 leading-tight text-[13px] font-medium">{{ product.name }}</h2>
                            <p class="mt-0.5 font-bold text-foreground text-sm tabular-nums">{{ formatIDR(product.price) }}</p>
                            <div class="mt-auto pt-1.5 flex items-center gap-1.5">
                                <span class="text-[11px] text-muted-foreground line-clamp-1">{{ product.store.name }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>
        </div>

        <!-- Section Title for All Products -->
        <h2 v-if="!activeCategory && !query" class="text-xl font-bold mb-4">Jelajahi Produk</h2>

        <!-- Active category breadcrumb -->
        <div v-if="activeCategory" class="mb-6 flex items-center gap-1.5 text-sm text-muted-foreground">
            <span>{{ t('catalog.title') }}</span>
            <ChevronRight class="size-3.5" />
            <template v-if="activeCategory.parent">
                <button type="button" class="hover:text-foreground" @click="selectCategory(activeCategory.parent.slug)">
                    {{ activeCategory.parent.name }}
                </button>
                <ChevronRight class="size-3.5" />
            </template>
            <span class="font-medium text-foreground">
                {{ activeCategory.name }}
            </span>
        </div>

        <!-- Sort & Filter Bar -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-border pb-4">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Category filter chips -->
                <button
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                    :class="!activeCategory ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground'"
                    @click="selectCategory(null)"
                >
                    Semua
                </button>
                <button
                    v-for="root in categories"
                    :key="root.id"
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                    :class="root.slug === activeParentSlug ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground'"
                    @click="selectCategory(root.slug)"
                >
                    {{ root.name }}
                </button>
            </div>

            <div class="flex items-center gap-3">
                <Spinner v-if="loading" class="size-4" />
                <p class="text-sm text-muted-foreground">
                    Menampilkan {{ allProducts.length }} dari {{ products.total }} produk
                </p>
                <Select :model-value="sortValue" @update:model-value="applySort">
                    <SelectTrigger class="w-44">
                        <SelectValue placeholder="Urutkan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="random">Acak</SelectItem>
                        <SelectItem value="newest">Terbaru</SelectItem>
                        <SelectItem value="price_asc">Harga Terendah</SelectItem>
                        <SelectItem value="price_desc">Harga Tertinggi</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Subcategory chips for the active parent -->
        <div v-if="subCategories.length" class="mb-6 flex w-max gap-2 sm:w-auto sm:flex-wrap overflow-x-auto pb-2">
            <button
                v-for="child in subCategories"
                :key="child.id"
                type="button"
                class="shrink-0 rounded-full border px-3.5 py-1.5 text-xs font-medium transition-colors"
                :class="child.slug === activeCategory?.slug ? 'border-primary/60 bg-primary/10 text-primary' : 'border-border bg-card text-muted-foreground hover:text-foreground'"
                @click="selectCategory(child.slug)"
            >
                {{ child.name }}
            </button>
        </div>

        <EmptyState
            v-if="allProducts.length === 0"
            :title="t('catalog.emptyTitle')"
            :description="t('catalog.emptyDescription')"
            class="mt-10"
        />

        <template v-else>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                <Link
                    v-for="product in allProducts"
                    :key="product.id"
                    :href="catalogShow.url(product.slug)"
                    class="group block h-full"
                >
                    <Card class="h-full cursor-pointer overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md p-0 gap-0 flex flex-col">
                        <div class="relative aspect-square w-full overflow-hidden border-b border-border bg-muted/30">
                            <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name" loading="lazy" class="size-full object-cover transition-transform duration-300 group-hover:scale-105" />
                            <PlaceholderPattern v-else />
                            <Badge v-if="product.stock === 0" variant="destructive" class="absolute top-2 right-2 text-[10px] px-1.5 py-0 h-5">Habis</Badge>
                        </div>
                        <CardContent class="flex flex-col gap-1 p-3 flex-1">
                            <h2 class="line-clamp-2 leading-tight text-[13px] font-medium">{{ product.name }}</h2>
                            <p class="mt-0.5 font-bold text-foreground text-sm tabular-nums">{{ formatIDR(product.price) }}</p>
                            <div class="mt-auto pt-1.5 flex items-center gap-1.5">
                                <span class="text-[11px] text-muted-foreground line-clamp-1">{{ product.store.name }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <!-- Tampilkan Lebih Banyak -->
            <div v-if="products.current_page < products.last_page" class="mt-10 flex justify-center">
                <Button variant="outline" size="lg" :disabled="loading" @click="loadMore" class="w-full max-w-sm rounded-full">
                    <Spinner v-if="loading" class="mr-2 size-4" />
                    Tampilkan Lebih Banyak
                </Button>
            </div>
            <div v-else-if="allProducts.length < products.total" class="mt-10 flex justify-center">
                <Button variant="outline" size="lg" :disabled="loading" @click="resetToPageOne" class="w-full max-w-sm rounded-full">
                    <Spinner v-if="loading" class="mr-2 size-4" />
                    Muat Ulang Semua Produk
                </Button>
            </div>
            <div v-else class="mt-10 text-center text-sm text-muted-foreground">
                <p>Anda telah melihat semua produk.</p>
            </div>
        </template>
    </div>
</template>
