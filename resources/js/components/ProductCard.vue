<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { formatIDR } from '@/lib/utils';
import { show as catalogShow } from '@/routes/catalog';

interface Store {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    slug: string;
    name: string;
    price: number;
    stock: number;
    image_path: string | null;
    store: Store;
}

defineProps<{ product: Product }>();
</script>

<template>
    <Link :href="catalogShow.url(product.slug)" class="group block h-full">
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
                    <span class="text-[11px] text-muted-foreground line-clamp-1">{{ product.store?.name }}</span>
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
