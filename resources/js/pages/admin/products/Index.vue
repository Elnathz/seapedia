<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Package } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    price: number;
    stock: number;
    is_active: boolean;
    store: { id: number; name: string; slug: string } | null;
    category: { id: number; name: string } | null;
    created_at: string;
}

interface Paginated {
    data: Product[];
    total: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    products: Paginated;
    categories: Category[];
    filters: { q: string; category: number | null };
}>();

const search = ref(props.filters.q ?? '');
// reka-ui reserves the empty string, so "all" is the no-filter sentinel.
const category = ref(
    props.filters.category ? String(props.filters.category) : 'all',
);

function applyFilters() {
    router.get(
        '/admin/products',
        {
            q: search.value || undefined,
            category: category.value === 'all' ? undefined : category.value,
        },
        { preserveState: true, replace: true },
    );
}

watch(search, applyFilters);
watch(category, applyFilters);

// Server orders rows by category, so consecutive rows share a category. Insert
// a subheader before each new group to make the table read per category.
type Row =
    | { kind: 'header'; id: string; name: string; count: number }
    | { kind: 'product'; id: string; product: Product };

const rows = computed<Row[]>(() => {
    const items: Row[] = [];
    let currentKey: number | 'none' | null = null;

    for (const product of props.products.data) {
        const key = product.category?.id ?? 'none';

        if (key !== currentKey) {
            currentKey = key;
            const count = props.products.data.filter(
                (p) => (p.category?.id ?? 'none') === key,
            ).length;
            items.push({
                kind: 'header',
                id: `h-${key}`,
                name: product.category?.name ?? 'Tanpa Kategori',
                count,
            });
        }

        items.push({ kind: 'product', id: `p-${product.id}`, product });
    }

    return items;
});

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(price);
}
</script>

<template>
    <div class="p-6">
        <div class="mb-6 flex items-center gap-3">
            <div
                class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary"
            >
                <Package class="size-5" />
            </div>
            <div>
                <h1 class="text-xl font-bold">Produk</h1>
                <p class="text-sm text-muted-foreground">
                    Total {{ products.total }} produk
                </p>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <Input
                v-model="search"
                placeholder="Cari nama produk..."
                class="max-w-xs"
            />
            <Select v-model="category">
                <SelectTrigger class="w-52">
                    <SelectValue placeholder="Semua kategori" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua kategori</SelectItem>
                    <SelectItem
                        v-for="c in categories"
                        :key="c.id"
                        :value="String(c.id)"
                    >
                        {{ c.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="overflow-x-auto rounded-lg border border-border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>ID</TableHead>
                        <TableHead>Nama</TableHead>
                        <TableHead>Toko</TableHead>
                        <TableHead>Harga</TableHead>
                        <TableHead>Stok</TableHead>
                        <TableHead>Status</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="products.data.length === 0">
                        <TableCell
                            colspan="6"
                            class="py-8 text-center text-muted-foreground"
                            >Tidak ada produk ditemukan.</TableCell
                        >
                    </TableRow>
                    <template v-for="row in rows" :key="row.id">
                        <TableRow
                            v-if="row.kind === 'header'"
                            class="bg-muted/50 hover:bg-muted/50"
                        >
                            <TableCell
                                colspan="6"
                                class="py-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                {{ row.name }}
                                <span class="ml-1 tabular-nums opacity-70"
                                    >· {{ row.count }}</span
                                >
                            </TableCell>
                        </TableRow>
                        <TableRow v-else>
                            <TableCell class="font-mono text-xs">{{
                                row.product.id
                            }}</TableCell>
                            <TableCell
                                class="max-w-[220px] truncate font-medium"
                                >{{ row.product.name }}</TableCell
                            >
                            <TableCell class="text-sm text-muted-foreground">{{
                                row.product.store?.name ?? '-'
                            }}</TableCell>
                            <TableCell class="text-sm">{{
                                formatPrice(row.product.price)
                            }}</TableCell>
                            <TableCell class="tabular-nums">{{
                                row.product.stock
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        row.product.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        row.product.is_active
                                            ? 'Aktif'
                                            : 'Nonaktif'
                                    }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div v-if="products.last_page > 1" class="mt-4 flex gap-1">
            <template v-for="link in products.links" :key="link.label">
                <button
                    v-if="link.url"
                    type="button"
                    class="rounded border px-3 py-1 text-sm"
                    :class="
                        link.active
                            ? 'border-primary bg-primary text-white'
                            : 'border-border hover:bg-muted'
                    "
                    @click="router.get(link.url)"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="rounded border border-border px-3 py-1 text-sm opacity-40"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
