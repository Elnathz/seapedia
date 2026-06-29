<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Package, Star } from '@lucide/vue';
import { Button } from '@/components/ui/button';

interface PopularStore {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

defineProps<{ stores: PopularStore[] }>();

const gradients = [
    'from-blue-400 to-blue-600',
    'from-primary to-brand',
    'from-amber-400 to-amber-600',
    'from-rose-400 to-rose-600',
    'from-violet-400 to-violet-600',
    'from-emerald-400 to-emerald-600',
    'from-cyan-400 to-cyan-600',
    'from-orange-400 to-orange-600',
];

function storeGradient(name: string): string {
    let hash = 0;

    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }

    return gradients[Math.abs(hash) % gradients.length];
}

function storeInitials(name: string): string {
    return name
        .split(' ')
        .map((w) => w[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}
</script>

<template>
    <section v-if="stores.length > 0" class="border-b border-border py-16 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Toko Populer
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">Dari penjual aktif dan terpercaya</p>
                </div>
                <Button as-child variant="ghost" size="sm" class="gap-1 text-primary hover:text-primary transition-colors">
                    <Link href="/catalog">
                        Lihat semua
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>

            <div
                class="no-scrollbar -mx-4 flex gap-5 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-0">
                <Link v-for="store in stores" :key="store.id" :href="`/stores/${store.slug}`"
                    class="group flex w-[220px] shrink-0 flex-col overflow-hidden rounded-2xl border border-border/50 bg-white text-center shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-primary/30 hover:shadow-xl lg:w-auto relative">
                    
                    <!-- Banner -->
                    <div class="h-16 w-full bg-gradient-to-r opacity-80 transition-opacity group-hover:opacity-100" :class="storeGradient(store.name)"></div>
                    
                    <!-- Logo Profile -->
                    <div class="relative -mt-8 mx-auto flex size-16 items-center justify-center rounded-2xl border-4 border-white bg-white shadow-sm transition-transform duration-300 group-hover:scale-105">
                        <div class="flex size-full items-center justify-center rounded-xl bg-gradient-to-br text-xl font-bold text-white" :class="storeGradient(store.name)">
                            {{ storeInitials(store.name) }}
                        </div>
                        
                        <!-- Verified Badge -->
                        <div class="absolute -bottom-1 -right-1 flex size-5 items-center justify-center rounded-full border-2 border-white bg-blue-500 text-white shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                    </div>
                    
                    <div class="p-5 pt-3">
                        <h3 class="truncate font-bold text-foreground group-hover:text-primary transition-colors">{{ store.name }}</h3>
                        
                        <div class="mt-3 flex items-center justify-center gap-3 text-xs text-muted-foreground">
                            <div class="flex items-center gap-1.5">
                                <Package class="size-3.5 text-muted-foreground/70" />
                                <span>{{ store.products_count }} produk</span>
                            </div>
                            <div class="size-1 rounded-full bg-border"></div>
                            <div class="flex items-center gap-1">
                                <Star class="size-3.5 fill-amber-400 text-amber-400" />
                                <span class="font-medium text-foreground">4.9</span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
