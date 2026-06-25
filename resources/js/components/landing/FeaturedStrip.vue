<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ShoppingBag } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { formatIDR } from '@/lib/utils';
import { show as catalogShow, index as catalogIndex } from '@/routes/catalog';

interface Store {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    image_path: string | null;
    store: Store;
}

defineProps<{ products: Product[] }>();
</script>

<template>
    <section class="border-b border-border py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Lagi ramai
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">Produk terbaru dari toko-toko aktif</p>
                </div>
                <Button as-child variant="ghost" size="sm" class="gap-1 text-primary hover:text-primary">
                    <Link :href="catalogIndex.url()">
                        Lihat semua
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>

            <!-- Empty state -->
            <div v-if="products.length === 0" class="flex flex-col items-center gap-3 py-12 text-center">
                <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                    <ShoppingBag class="size-6 text-muted-foreground" />
                </div>
                <p class="text-sm text-muted-foreground">Belum ada produk. Jadilah yang pertama berjualan!</p>
            </div>

            <!-- Product cards horizontal scroll -->
            <div
                v-else
                class="no-scrollbar -mx-4 flex gap-4 overflow-x-auto px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid lg:grid-cols-3 lg:overflow-visible lg:px-0 xl:grid-cols-6"
            >
                <Link
                    v-for="product in products"
                    :key="product.id"
                    :href="catalogShow.url({ product: product.slug })"
                    class="group w-48 shrink-0 overflow-hidden rounded-xl border border-border bg-card transition-shadow hover:shadow-md lg:w-auto"
                >
                    <div class="relative aspect-square overflow-hidden bg-muted">
                        <img
                            v-if="product.image_path"
                            :src="`/storage/${product.image_path}`"
                            :alt="product.name"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center">
                            <ShoppingBag class="size-8 text-muted-foreground/40" />
                        </div>
                    </div>
                    <div class="p-3">
                        <p class="truncate text-sm font-medium text-foreground">{{ product.name }}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">{{ product.store.name }}</p>
                        <p class="mt-1.5 text-sm font-semibold text-primary">{{ formatIDR(product.price) }}</p>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
