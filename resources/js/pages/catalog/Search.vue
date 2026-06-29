<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Filter, X, ChevronRight, SlidersHorizontal } from '@lucide/vue';
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
    links: { url: string | null; label: string; active: boolean }[];
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    products: PaginatedProducts;
    filters: any;
    categories: Category[];
}>();

const { t } = useI18n();

// ─── Filter State ───
const searchQuery = ref(props.filters?.q || '');
const inStock = ref(props.filters?.in_stock === '1' || props.filters?.in_stock === true);
const priceMin = ref(props.filters?.price_min || '');
const priceMax = ref(props.filters?.price_max || '');
const selectedCategories = ref<string[]>(props.filters?.categories ? (Array.isArray(props.filters.categories) ? props.filters.categories : [props.filters.categories]) : []);
const sortBy = ref(props.filters?.sort || 'best_match');
const showMobileFilter = ref(false);

watch(() => props.filters, (newFilters) => {
    searchQuery.value = newFilters?.q || '';
    inStock.value = newFilters?.in_stock === '1' || newFilters?.in_stock === true;
    priceMin.value = newFilters?.price_min || '';
    priceMax.value = newFilters?.price_max || '';
    selectedCategories.value = newFilters?.categories ? (Array.isArray(newFilters.categories) ? [...newFilters.categories] : [newFilters.categories]) : [];

    if (newFilters?.sort) {
sortBy.value = newFilters.sort;
}
}, { deep: true });

// ─── Apply Filters ───
const applyFilters = () => {
    const params: any = {};

    if (searchQuery.value) {
params.q = searchQuery.value;
}

    if (inStock.value) {
params.in_stock = '1';
}

    if (priceMin.value) {
params.price_min = priceMin.value;
}

    if (priceMax.value) {
params.price_max = priceMax.value;
}

    if (selectedCategories.value.length) {
params.categories = selectedCategories.value;
}

    if (sortBy.value !== 'best_match') {
params.sort = sortBy.value;
}

    router.get(catalogIndex.url(), params, { preserveState: true, preserveScroll: true });
};

watch(sortBy, () => applyFilters());

const toggleCategory = (id: string | number) => {
    const strId = String(id);
    const idx = selectedCategories.value.indexOf(strId);

    if (idx > -1) {
        selectedCategories.value.splice(idx, 1);
    } else {
        selectedCategories.value.push(strId);
    }

    applyFilters();
};

const clearAllFilters = () => {
    searchQuery.value = props.filters?.q || '';
    inStock.value = false;
    priceMin.value = '';
    priceMax.value = '';
    selectedCategories.value = [];
    sortBy.value = 'best_match';
    applyFilters();
};
</script>

<template>
    <Head :title="searchQuery ? `Hasil Pencarian: ${searchQuery}` : 'Semua Produk'" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        
        <!-- Search Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4 border-b border-border pb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">
                    <template v-if="searchQuery">
                        Menampilkan hasil untuk "<span class="text-primary">{{ searchQuery }}</span>"
                    </template>
                    <template v-else>
                        Semua Produk
                    </template>
                </h1>
                <p class="text-sm text-muted-foreground mt-1">{{ products.total }} produk ditemukan</p>
            </div>

            <div class="flex items-center gap-3">
                <Button variant="outline" class="md:hidden gap-2" @click="showMobileFilter = true">
                    <Filter class="size-4" />
                    Filter
                </Button>

                <Select v-model="sortBy">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Urutkan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="best_match">Paling Sesuai</SelectItem>
                        <SelectItem value="newest">Terbaru</SelectItem>
                        <SelectItem value="price_asc">Harga Terendah</SelectItem>
                        <SelectItem value="price_desc">Harga Tertinggi</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="flex gap-8 items-start">
            
            <!-- Sidebar Desktop -->
            <aside class="hidden md:block w-64 shrink-0 sticky top-24 space-y-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            <SlidersHorizontal class="size-5 text-primary" />
                            Filter
                        </h3>
                        <button @click="clearAllFilters" class="text-xs font-semibold text-primary hover:underline">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Ketersediaan -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-foreground">Ketersediaan</h4>
                    <div class="flex items-center space-x-2">
                        <Checkbox id="in_stock" :checked="inStock" @update:checked="(v) => { inStock = !!v; applyFilters(); }" />
                        <label for="in_stock" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                            Hanya stok tersedia
                        </label>
                    </div>
                </div>

                <!-- Rentang Harga -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-foreground">Rentang Harga (Rp)</h4>
                    <div class="flex items-center gap-2">
                        <Input v-model="priceMin" type="number" placeholder="Min" class="h-9" />
                        <span class="text-muted-foreground">-</span>
                        <Input v-model="priceMax" type="number" placeholder="Max" class="h-9" />
                    </div>
                    <Button class="w-full h-9" @click="applyFilters">Terapkan Harga</Button>
                </div>

                <!-- Kategori -->
                <div v-if="categories && categories.length > 0" class="space-y-4">
                    <h4 class="text-sm font-bold text-foreground">Kategori</h4>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                        <div v-for="cat in categories" :key="cat.id" class="flex items-center space-x-2">
                            <Checkbox :id="'cat_' + cat.id" :checked="selectedCategories.includes(String(cat.id))" @update:checked="toggleCategory(cat.id)" />
                            <label :for="'cat_' + cat.id" class="text-sm font-medium leading-none cursor-pointer">
                                {{ cat.name }}
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="flex-1">
                <div v-if="products.data.length > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <Link
                        v-for="product in products.data"
                        :key="product.id"
                        :href="catalogShow.url(product.slug)"
                        class="group block h-full"
                    >
                        <Card class="h-full cursor-pointer overflow-hidden transition-all duration-200 hover:-translate-y-0.5 hover:border-primary hover:shadow-md p-0 gap-0 flex flex-col">
                            <div class="relative aspect-square w-full overflow-hidden border-b border-border bg-muted/30">
                                <img
                                    v-if="product.image_path"
                                    :src="`/storage/${product.image_path}`"
                                    :alt="product.name"
                                    loading="lazy"
                                    class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <PlaceholderPattern v-else />
                                <Badge v-if="product.stock === 0" variant="destructive" class="absolute top-2 right-2 text-[10px] px-1.5 py-0 h-5">
                                    Habis
                                </Badge>
                            </div>
                            <CardContent class="flex flex-col gap-1 p-3 flex-1">
                                <h2 class="line-clamp-2 leading-tight text-[13px] font-medium">{{ product.name }}</h2>
                                <p class="mt-0.5 font-bold text-foreground text-sm tabular-nums">{{ formatIDR(product.price) }}</p>
                                <div class="mt-auto pt-1.5 flex items-center gap-1.5">
                                    <span class="text-[11px] text-muted-foreground line-clamp-1">{{ product.store.name }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <EmptyState
                    v-else
                    icon="search"
                    title="Tidak ada produk ditemukan"
                    description="Coba ubah filter atau kata kunci pencarian kamu."
                    class="py-20 border border-dashed rounded-2xl bg-muted/30"
                />


            </div>
        </div>
    </div>
</template>
