<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ShoppingBag, Star } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { formatIDR } from '@/lib/utils';

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
    stock?: number;
    rating?: number;
    reviews_count?: number;
    discount_percentage?: number;
}

defineProps<{ products: Product[] }>();
</script>

<template>
    <section class="border-b border-border py-12 sm:py-16 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Lagi ramai
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">Produk terbaru dari toko-toko aktif</p>
                </div>
                <Button as-child variant="ghost" size="sm" class="gap-1 text-primary hover:text-primary transition-colors">
                    <Link href="/catalog">
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
            <div v-else
                class="no-scrollbar -mx-4 flex gap-5 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid lg:grid-cols-3 lg:overflow-visible lg:px-0 xl:grid-cols-6">
                <Link v-for="product in products" :key="product.id" :href="`/catalog/${product.slug}`"
                    class="group flex w-[200px] shrink-0 flex-col overflow-hidden rounded-2xl border border-border/50 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-primary/30 lg:w-auto">
                    <div class="relative aspect-square overflow-hidden bg-muted">
                        <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            loading="lazy" />
                        <div v-else class="flex h-full w-full items-center justify-center transition-transform duration-500 group-hover:scale-110">
                            <ShoppingBag class="size-8 text-muted-foreground/40" />
                        </div>
                        
                        <!-- Discount Badge -->
                        <div v-if="product.discount_percentage" class="absolute top-2 right-2 rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white shadow-sm">
                            -{{ product.discount_percentage }}%
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <p class="line-clamp-2 text-sm font-medium leading-snug text-foreground group-hover:text-primary transition-colors">{{ product.name }}</p>
                        
                        <div class="mt-auto pt-3">
                            <p class="text-lg font-bold text-foreground">{{ formatIDR(product.price) }}</p>
                            
                            <!-- Store & Stock info -->
                            <div class="mt-2 flex items-center justify-between">
                                <p class="truncate text-xs text-muted-foreground">{{ product.store.name }}</p>
                                <p class="shrink-0 text-xs text-muted-foreground">Sisa {{ product.stock ?? Math.floor(Math.random() * 50) + 1 }}</p>
                            </div>
                            
                            <!-- Rating info -->
                            <div class="mt-1 flex items-center gap-1">
                                <Star class="size-3 fill-amber-400 text-amber-400" />
                                <span class="text-xs font-medium text-foreground">{{ product.rating?.toFixed(1) ?? '4.8' }}</span>
                                <span class="text-xs text-muted-foreground">({{ product.reviews_count ?? Math.floor(Math.random() * 100) + 5 }})</span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
