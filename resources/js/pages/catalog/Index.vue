<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronRight, Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Spinner } from '@/components/ui/spinner';
import { useCategories } from '@/composables/useCategories';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex, show as catalogShow } from '@/routes/catalog';

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
    activeCategory: ActiveCategory | null;
}>();

const query = ref(props.search ?? '');
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

const subCategories = computed(
    () =>
        categories.value.find((root) => root.slug === activeParentSlug.value)
            ?.children ?? [],
);

function applySearch() {
    visit(
        buildParams({ category: props.activeCategory?.slug, q: query.value }),
    );
}

function selectCategory(slug: string | null) {
    visit(buildParams({ category: slug ?? undefined, q: props.search }));
}

function goToPage(page: number) {
    visit(
        buildParams({
            category: props.activeCategory?.slug,
            q: props.search,
            page,
        }),
    );
}

function buildParams(input: {
    category?: string;
    q?: string | null;
    page?: number;
}): Record<string, string | number> {
    const params: Record<string, string | number> = {};

    if (input.q) {
        params.q = input.q;
    }

    if (input.category) {
        params.category = input.category;
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

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div
            class="overflow-hidden rounded-2xl bg-gradient-to-br from-primary/15 via-secondary/40 to-background px-6 py-10 sm:px-10 sm:py-14"
        >
            <h1 class="text-2xl font-semibold sm:text-3xl">
                {{ t('catalog.heroTitle') }}
            </h1>
            <p class="mt-2 max-w-md text-sm text-muted-foreground sm:text-base">
                {{ t('catalog.heroSubtitle') }}
            </p>
            <div class="mt-6 flex max-w-md items-center gap-2">
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="query"
                        :placeholder="t('catalog.searchPlaceholder')"
                        class="pl-9"
                        @keyup.enter="applySearch"
                        @blur="applySearch"
                    />
                </div>
                <Spinner v-if="loading" class="size-4" />
            </div>
        </div>

        <!-- Category filter chips -->
        <div class="-mx-4 mt-6 overflow-x-auto px-4 sm:mx-0 sm:px-0">
            <div class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                <button
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                    :class="
                        !activeCategory
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground'
                    "
                    @click="selectCategory(null)"
                >
                    {{ t('catalog.allCategories') }}
                </button>
                <button
                    v-for="root in categories"
                    :key="root.id"
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                    :class="
                        root.slug === activeParentSlug
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground'
                    "
                    @click="selectCategory(root.slug)"
                >
                    {{ root.name }}
                </button>
            </div>
        </div>

        <!-- Subcategory chips for the active parent -->
        <div
            v-if="subCategories.length"
            class="-mx-4 mt-3 overflow-x-auto px-4 sm:mx-0 sm:px-0"
        >
            <div class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                <button
                    v-for="child in subCategories"
                    :key="child.id"
                    type="button"
                    class="rounded-full border px-3.5 py-1.5 text-xs font-medium transition-colors"
                    :class="
                        child.slug === activeCategory?.slug
                            ? 'border-primary/60 bg-primary/10 text-primary'
                            : 'border-border bg-card text-muted-foreground hover:text-foreground'
                    "
                    @click="selectCategory(child.slug)"
                >
                    {{ child.name }}
                </button>
            </div>
        </div>

        <!-- Active category breadcrumb -->
        <div
            v-if="activeCategory"
            class="mt-6 flex items-center gap-1.5 text-sm text-muted-foreground"
        >
            <span>{{ t('catalog.title') }}</span>
            <ChevronRight class="size-3.5" />
            <template v-if="activeCategory.parent">
                <button
                    type="button"
                    class="hover:text-foreground"
                    @click="selectCategory(activeCategory.parent.slug)"
                >
                    {{ activeCategory.parent.name }}
                </button>
                <ChevronRight class="size-3.5" />
            </template>
            <span class="font-medium text-foreground">
                {{ activeCategory.name }}
            </span>
        </div>

        <EmptyState
            v-if="products.data.length === 0"
            :title="t('catalog.emptyTitle')"
            :description="t('catalog.emptyDescription')"
            class="mt-10"
        />

        <template v-else>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="product in products.data"
                    :key="product.id"
                    :href="catalogShow.url(product.slug)"
                    class="group"
                >
                    <Card
                        class="h-full cursor-pointer overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md"
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
                            <Badge
                                v-if="product.stock === 0"
                                variant="destructive"
                                class="absolute top-2 right-2"
                            >
                                Stok habis
                            </Badge>
                        </div>
                        <CardContent class="flex flex-col gap-1 pt-4">
                            <Badge variant="secondary" class="w-fit text-xs">
                                {{ product.store.name }}
                            </Badge>
                            <h2
                                class="mt-1 line-clamp-2 leading-snug font-medium"
                            >
                                {{ product.name }}
                            </h2>
                            <p
                                class="mt-1.5 font-semibold text-primary tabular-nums"
                            >
                                {{ formatIDR(product.price) }}
                            </p>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <Pagination
                v-if="products.last_page > 1"
                :items-per-page="products.per_page"
                :total="products.total"
                :default-page="products.current_page"
                class="mt-10"
                @update:page="goToPage"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationFirst />
                    <PaginationPrevious />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="item.value === products.current_page"
                        >
                            {{ item.value }}
                        </PaginationItem>
                        <PaginationEllipsis v-else />
                    </template>
                    <PaginationNext />
                    <PaginationLast />
                </PaginationContent>
            </Pagination>
        </template>
    </div>
</template>
