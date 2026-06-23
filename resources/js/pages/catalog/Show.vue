<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';

interface Product {
    slug: string;
    name: string;
    storeName: string;
    category: string;
    price: number;
    stock: number;
    description: string;
}

defineProps<{ product: Product }>();
</script>

<template>
    <Head :title="product.name" />

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <Link
            :href="catalogIndex.url()"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            Kembali ke katalog
        </Link>

        <Card class="mt-6 overflow-hidden">
            <div class="grid sm:grid-cols-2">
                <div
                    class="relative aspect-square border-b border-border sm:border-r sm:border-b-0"
                >
                    <PlaceholderPattern />
                </div>
                <CardContent class="flex flex-col gap-3 pt-6">
                    <Badge variant="secondary" class="w-fit">
                        {{ product.category }}
                    </Badge>
                    <h1 class="text-xl font-semibold">{{ product.name }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ product.storeName }}
                    </p>
                    <p class="text-2xl font-bold tabular-nums">
                        {{ formatIDR(product.price) }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ product.description }}
                    </p>
                    <p class="text-sm">
                        Stok:
                        <span class="font-medium tabular-nums">{{
                            product.stock
                        }}</span>
                    </p>
                </CardContent>
            </div>
        </Card>
    </div>
</template>
