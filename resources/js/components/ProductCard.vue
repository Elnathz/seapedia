<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
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
        <Card
            class="flex h-full cursor-pointer flex-col gap-0 overflow-hidden p-0 transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md"
        >
            <div
                class="relative aspect-square w-full overflow-hidden border-b border-border bg-muted/30"
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
                    class="absolute top-2 right-2 h-5 px-1.5 py-0 text-[10px]"
                    >Habis</Badge
                >
            </div>
            <CardContent class="flex flex-1 flex-col gap-1 p-3">
                <h2 class="line-clamp-2 text-[13px] leading-tight font-medium">
                    {{ product.name }}
                </h2>
                <p
                    class="mt-0.5 text-sm font-bold text-foreground tabular-nums"
                >
                    {{ formatIDR(product.price) }}
                </p>
                <div class="mt-auto flex items-center gap-1.5 pt-1.5">
                    <span
                        class="line-clamp-1 text-[11px] text-muted-foreground"
                        >{{ product.store?.name }}</span
                    >
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
