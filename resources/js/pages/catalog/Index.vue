<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex, show as catalogShow } from '@/routes/catalog';

interface Product {
    slug: string;
    name: string;
    storeName: string;
    category: string;
    price: number;
    stock: number;
    description: string;
}

const props = defineProps<{
    products: Product[];
    search: string | null;
}>();

const query = ref(props.search ?? '');
const loading = ref(false);

function applySearch() {
    router.get(catalogIndex.url(), query.value ? { q: query.value } : {}, {
        preserveState: true,
        replace: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
}
</script>

<template>
    <Head title="Katalog" />

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold">Katalog</h1>
                <p class="text-sm text-muted-foreground">
                    Produk dan jasa dari toko-toko kampus.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Input
                    v-model="query"
                    placeholder="Cari produk..."
                    class="w-full sm:w-64"
                    @keyup.enter="applySearch"
                    @blur="applySearch"
                />
                <Spinner v-if="loading" class="size-4" />
            </div>
        </div>

        <EmptyState
            v-if="products.length === 0"
            title="Produk tidak ditemukan"
            description="Coba kata kunci lain atau lihat semua produk."
            class="mt-10"
        />

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="product in products"
                :key="product.slug"
                :href="catalogShow.url(product.slug)"
            >
                <Card class="h-full transition-colors hover:border-primary">
                    <div
                        class="relative aspect-video overflow-hidden rounded-t-xl border-b border-border"
                    >
                        <PlaceholderPattern />
                    </div>
                    <CardContent class="flex flex-col gap-1 pt-4">
                        <Badge variant="secondary" class="w-fit">
                            {{ product.category }}
                        </Badge>
                        <h2 class="font-medium">{{ product.name }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ product.storeName }}
                        </p>
                        <p class="mt-1 font-semibold tabular-nums">
                            {{ formatIDR(product.price) }}
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
