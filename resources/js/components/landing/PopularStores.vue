<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Store as StoreIcon } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { index as catalogIndex } from '@/routes/catalog';

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
    <section v-if="stores.length > 0" class="border-b border-border py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <StoreIcon class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                            Toko Populer
                        </h2>
                        <p class="text-sm text-muted-foreground">Dari penjual aktif di kampus</p>
                    </div>
                </div>
                <Button as-child variant="ghost" size="sm" class="gap-1 text-primary hover:text-primary">
                    <Link :href="catalogIndex.url()">
                        Lihat semua
                        <ArrowRight class="size-4" />
                    </Link>
                </Button>
            </div>

            <div class="no-scrollbar -mx-4 flex gap-4 overflow-x-auto px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-0">
                <Link
                    v-for="store in stores"
                    :key="store.id"
                    :href="`/stores/${store.slug}`"
                    class="group flex w-40 shrink-0 flex-col items-center gap-3 rounded-xl border border-border bg-card p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md lg:w-auto"
                >
                    <div
                        class="flex size-14 items-center justify-center rounded-xl bg-gradient-to-br text-lg font-bold text-white shadow-sm transition-transform duration-200 group-hover:scale-105"
                        :class="storeGradient(store.name)"
                    >
                        {{ storeInitials(store.name) }}
                    </div>
                    <div>
                        <p class="line-clamp-1 font-medium text-foreground">{{ store.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ store.products_count }} produk</p>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>
