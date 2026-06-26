<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { useInitials } from '@/composables/useInitials';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex, show as catalogShow } from '@/routes/catalog';

interface Product {
    id: number;
    slug: string;
    name: string;
    price: number;
    image_path: string | null;
}

interface Store {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    products: Product[];
}

defineProps<{ store: Store }>();

const { getInitials } = useInitials();
const { t } = useI18n();
</script>

<template>
    <Head :title="store.name" />

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <Link
            :href="catalogIndex.url()"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            {{ t('catalog.backToCatalog') }}
        </Link>

        <div class="mt-6 flex items-center gap-4">
            <Avatar class="size-14">
                <AvatarFallback class="text-lg">{{
                    getInitials(store.name)
                }}</AvatarFallback>
            </Avatar>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold">{{ store.name }}</h1>
                    <Badge v-if="store.is_active" variant="secondary">{{
                        t('catalog.storeActive')
                    }}</Badge>
                </div>
                <p
                    v-if="store.description"
                    class="mt-1 text-sm text-muted-foreground"
                >
                    {{ store.description }}
                </p>
            </div>
        </div>

        <EmptyState
            v-if="store.products.length === 0"
            :title="t('store.noProductsTitle')"
            :description="t('store.noProductsDescription')"
            class="mt-10"
        />

        <div v-else class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="product in store.products"
                :key="product.id"
                :href="catalogShow.url(product.slug)"
            >
                <Card
                    class="group h-full cursor-pointer overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md"
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
                    </div>
                    <CardContent class="flex flex-col gap-1 pt-4">
                        <h2 class="font-medium">{{ product.name }}</h2>
                        <p class="mt-1 font-semibold tabular-nums text-primary">
                            {{ formatIDR(product.price) }}
                        </p>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
