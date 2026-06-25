<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
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

const props = defineProps<{
    products: PaginatedProducts;
    search: string | null;
}>();

const query = ref(props.search ?? '');
const loading = ref(false);
const { t } = useI18n();

function applySearch() {
    visit(query.value ? { q: query.value } : {});
}

function goToPage(page: number) {
    visit({
        ...(props.search ? { q: props.search } : {}),
        page,
    });
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
                            class="relative aspect-video overflow-hidden rounded-t-xl border-b border-border bg-muted"
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
                            <h2 class="mt-1 line-clamp-2 font-medium leading-snug">{{ product.name }}</h2>
                            <p class="mt-1.5 font-semibold tabular-nums text-primary">
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
