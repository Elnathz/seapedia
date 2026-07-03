<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Filter, SlidersHorizontal } from '@lucide/vue';
import { ChevronDown, Search } from '@lucide/vue';
import { ref, watch, computed } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
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

// ─── Filter State ───
const searchQuery = ref(props.filters?.q || '');
const inStock = ref(
    props.filters?.in_stock === '1' || props.filters?.in_stock === true,
);
const priceMin = ref(props.filters?.price_min || '');
const priceMax = ref(props.filters?.price_max || '');
const selectedCategories = ref<string[]>(
    props.filters?.categories
        ? Array.isArray(props.filters.categories)
            ? props.filters.categories
            : [props.filters.categories]
        : [],
);
const sortBy = ref(props.filters?.sort || 'best_match');
const showMobileFilter = ref(false);

const searchCategory = ref('');

const filteredCategories = computed(() => {
    if (!searchCategory.value) {
        return props.categories;
    }

    return props.categories.filter((c) =>
        c.name.toLowerCase().includes(searchCategory.value.toLowerCase()),
    );
});

watch(
    () => props.filters,
    (newFilters) => {
        searchQuery.value = newFilters?.q || '';
        inStock.value =
            newFilters?.in_stock === '1' || newFilters?.in_stock === true;
        priceMin.value = newFilters?.price_min || '';
        priceMax.value = newFilters?.price_max || '';
        selectedCategories.value = newFilters?.categories
            ? Array.isArray(newFilters.categories)
                ? [...newFilters.categories]
                : [newFilters.categories]
            : [];

        if (newFilters?.sort) {
            sortBy.value = newFilters.sort;
        }
    },
    { deep: true },
);

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

    router.get(catalogIndex.url(), params, {
        preserveState: true,
        preserveScroll: true,
    });
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
    <Head
        :title="
            searchQuery ? `Hasil Pencarian: ${searchQuery}` : 'Semua Produk'
        "
    />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Search Header -->
        <div
            class="mb-8 flex flex-col justify-between gap-4 border-b border-border pb-6 md:flex-row md:items-center"
        >
            <div>
                <h1 class="text-2xl font-bold text-foreground">
                    <template v-if="searchQuery">
                        Menampilkan hasil untuk "<span class="text-primary">{{
                            searchQuery
                        }}</span
                        >"
                    </template>
                    <template v-else> Semua Produk </template>
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ products.total }} produk ditemukan
                </p>
            </div>

            <div class="flex items-center gap-3">
                <Button
                    variant="outline"
                    class="gap-2 md:hidden"
                    @click="showMobileFilter = true"
                >
                    <Filter class="size-4" />
                    Filter
                </Button>

                <Select v-model="sortBy">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Urutkan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="best_match"
                            >Paling Sesuai</SelectItem
                        >
                        <SelectItem value="newest">Terbaru</SelectItem>
                        <SelectItem value="price_asc"
                            >Harga Terendah</SelectItem
                        >
                        <SelectItem value="price_desc"
                            >Harga Tertinggi</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="flex items-start gap-8">
            <!-- Sidebar Desktop -->
            <aside
                class="sticky top-24 hidden w-64 shrink-0 space-y-8 md:block"
            >
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-lg font-bold">
                            <SlidersHorizontal class="size-5 text-primary" />
                            Filter
                        </h3>
                        <button
                            @click="clearAllFilters"
                            class="text-xs font-semibold text-primary hover:underline"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Rentang Harga -->
                <div class="space-y-4">
                    <h4 class="text-sm font-bold text-foreground">
                        Rentang Harga (Rp)
                    </h4>
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="priceMin"
                            type="number"
                            placeholder="Min"
                            class="h-9"
                        />
                        <span class="text-muted-foreground">-</span>
                        <Input
                            v-model="priceMax"
                            type="number"
                            placeholder="Max"
                            class="h-9"
                        />
                    </div>
                    <Button class="h-9 w-full" @click="applyFilters"
                        >Terapkan Harga</Button
                    >
                </div>

                <!-- Kategori -->
                <div
                    v-if="categories && categories.length > 0"
                    class="border-t border-border pt-4"
                >
                    <h4 class="mb-3 text-sm font-bold text-foreground">
                        Kategori
                    </h4>
                    <div class="space-y-1">
                        <Collapsible
                            v-for="parent in categories.slice(0, 5)"
                            :key="parent.id"
                            class="group/cat w-full"
                        >
                            <CollapsibleTrigger as-child>
                                <div
                                    class="flex cursor-pointer items-center justify-between rounded-md px-2 py-1.5 hover:bg-muted/50"
                                >
                                    <span class="text-sm font-semibold">{{
                                        parent.name
                                    }}</span>
                                    <ChevronDown
                                        class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-data-[state=open]/cat:rotate-180"
                                    />
                                </div>
                            </CollapsibleTrigger>
                            <CollapsibleContent class="space-y-2 py-1.5 pl-4">
                                <div
                                    v-for="child in parent.children"
                                    :key="child.id"
                                    class="flex items-center space-x-2"
                                >
                                    <Checkbox
                                        :id="'cat_' + child.id"
                                        :model-value="
                                            selectedCategories.includes(
                                                String(child.id),
                                            )
                                        "
                                        @update:model-value="
                                            toggleCategory(child.id)
                                        "
                                    />
                                    <label
                                        :for="'cat_' + child.id"
                                        class="cursor-pointer text-sm leading-none font-medium text-muted-foreground hover:text-foreground"
                                    >
                                        {{ child.name }}
                                    </label>
                                </div>
                                <div
                                    v-if="parent.children.length === 0"
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Tidak ada sub-kategori
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    </div>

                    <!-- Modal Lihat Semua Kategori -->
                    <Dialog v-if="categories.length > 5">
                        <DialogTrigger as-child>
                            <button
                                class="mt-3 w-full px-2 text-left text-sm font-semibold text-primary hover:underline"
                            >
                                Lihat Semua
                            </button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[700px]">
                            <DialogHeader>
                                <DialogTitle>Semua Kategori</DialogTitle>
                            </DialogHeader>
                            <div class="py-4">
                                <div class="relative mb-6">
                                    <Search
                                        class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="searchCategory"
                                        type="text"
                                        placeholder="Cari Kategori..."
                                        class="pl-9"
                                    />
                                </div>

                                <!-- 2-Column Grid for Categories -->
                                <div
                                    class="grid max-h-[500px] grid-cols-2 gap-x-8 gap-y-6 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="parent in filteredCategories"
                                        :key="parent.id"
                                    >
                                        <h5
                                            class="mb-3 text-sm font-bold text-foreground"
                                        >
                                            {{ parent.name }}
                                        </h5>
                                        <div class="space-y-2.5">
                                            <div
                                                v-for="child in parent.children"
                                                :key="child.id"
                                                class="flex items-center space-x-2"
                                            >
                                                <Checkbox
                                                    :id="
                                                        'modal_cat_' + child.id
                                                    "
                                                    :model-value="
                                                        selectedCategories.includes(
                                                            String(child.id),
                                                        )
                                                    "
                                                    @update:model-value="
                                                        toggleCategory(child.id)
                                                    "
                                                />
                                                <label
                                                    :for="
                                                        'modal_cat_' + child.id
                                                    "
                                                    class="cursor-pointer text-sm leading-none font-medium text-muted-foreground hover:text-foreground"
                                                >
                                                    {{ child.name }}
                                                </label>
                                            </div>
                                            <div
                                                v-if="
                                                    parent.children.length === 0
                                                "
                                                class="text-xs text-muted-foreground italic"
                                            >
                                                Tidak ada sub-kategori
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-if="filteredCategories.length === 0"
                                        class="col-span-2 py-8 text-center text-sm text-muted-foreground"
                                    >
                                        Kategori tidak ditemukan.
                                    </div>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="flex-1">
                <div
                    v-if="products.data.length > 0"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
                >
                    <Link
                        v-for="product in products.data"
                        :key="product.id"
                        :href="catalogShow.url(product.slug)"
                        class="group block h-full"
                    >
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
                                    class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <PlaceholderPattern v-else />
                                <Badge
                                    v-if="product.stock === 0"
                                    variant="destructive"
                                    class="absolute top-2 right-2 h-5 px-1.5 py-0 text-[10px]"
                                >
                                    Habis
                                </Badge>
                            </div>
                            <CardContent class="flex flex-1 flex-col gap-1 p-3">
                                <h2
                                    class="line-clamp-2 text-[13px] leading-tight font-medium"
                                >
                                    {{ product.name }}
                                </h2>
                                <p
                                    class="mt-0.5 text-sm font-bold text-foreground tabular-nums"
                                >
                                    {{ formatIDR(product.price) }}
                                </p>
                                <div
                                    class="mt-auto flex items-center gap-1.5 pt-1.5"
                                >
                                    <span
                                        class="line-clamp-1 text-[11px] text-muted-foreground"
                                        >{{ product.store.name }}</span
                                    >
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
                    class="rounded-2xl border border-dashed bg-muted/30 py-20"
                />
            </div>
        </div>

        <!-- Mobile Filter Sheet -->
        <Sheet
            :open="showMobileFilter"
            @update:open="showMobileFilter = $event"
        >
            <SheetContent
                side="bottom"
                class="h-[80vh] overflow-y-auto rounded-t-xl px-4 py-6 sm:max-w-none"
            >
                <SheetHeader class="mb-6">
                    <SheetTitle>Filter Pencarian</SheetTitle>
                </SheetHeader>

                <div class="space-y-8 pb-20">
                    <!-- Rentang Harga -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-foreground">
                            Rentang Harga (Rp)
                        </h4>
                        <div class="flex items-center gap-2">
                            <Input
                                v-model="priceMin"
                                type="number"
                                placeholder="Min"
                                class="h-9"
                            />
                            <span class="text-muted-foreground">-</span>
                            <Input
                                v-model="priceMax"
                                type="number"
                                placeholder="Max"
                                class="h-9"
                            />
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div
                        v-if="categories && categories.length > 0"
                        class="border-t border-border pt-4"
                    >
                        <h4 class="mb-3 text-sm font-bold text-foreground">
                            Kategori
                        </h4>
                        <div class="space-y-1">
                            <Collapsible
                                v-for="parent in categories.slice(0, 5)"
                                :key="'mobile_' + parent.id"
                                class="group/cat w-full"
                            >
                                <CollapsibleTrigger as-child>
                                    <div
                                        class="flex cursor-pointer items-center justify-between rounded-md px-2 py-1.5 hover:bg-muted/50"
                                    >
                                        <span class="text-sm font-semibold">{{
                                            parent.name
                                        }}</span>
                                        <ChevronDown
                                            class="h-4 w-4 text-muted-foreground transition-transform duration-200 group-data-[state=open]/cat:rotate-180"
                                        />
                                    </div>
                                </CollapsibleTrigger>
                                <CollapsibleContent
                                    class="space-y-2 py-1.5 pl-4"
                                >
                                    <div
                                        v-for="child in parent.children"
                                        :key="'mobile_' + child.id"
                                        class="flex items-center space-x-2"
                                    >
                                        <Checkbox
                                            :id="'mobile_cat_' + child.id"
                                            :model-value="
                                                selectedCategories.includes(
                                                    String(child.id),
                                                )
                                            "
                                            @update:model-value="
                                                toggleCategory(child.id)
                                            "
                                        />
                                        <label
                                            :for="'mobile_cat_' + child.id"
                                            class="cursor-pointer text-sm leading-none font-medium text-muted-foreground hover:text-foreground"
                                        >
                                            {{ child.name }}
                                        </label>
                                    </div>
                                    <div
                                        v-if="parent.children.length === 0"
                                        class="text-xs text-muted-foreground italic"
                                    >
                                        Tidak ada sub-kategori
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </div>
                </div>

                <div
                    class="absolute right-0 bottom-0 left-0 z-10 flex gap-3 border-t border-border bg-background p-4"
                >
                    <Button
                        variant="outline"
                        class="w-1/3"
                        @click="
                            clearAllFilters();
                            showMobileFilter = false;
                        "
                        >Reset</Button
                    >
                    <Button
                        class="flex-1"
                        @click="
                            applyFilters();
                            showMobileFilter = false;
                        "
                        >Terapkan Filter</Button
                    >
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
