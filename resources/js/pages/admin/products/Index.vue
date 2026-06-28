<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Package } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

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
    filters: { q: string };
}>();

const search = ref(props.filters.q ?? '');

watch(search, (val) => {
    router.get('/admin/products', { q: val }, { preserveState: true, replace: true });
});

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price);
}
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <Package class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">Produk</h1>
                    <p class="text-sm text-muted-foreground">Total {{ products.total }} produk</p>
                </div>
            </div>

            <div class="mb-4">
                <Input v-model="search" placeholder="Cari nama produk..." class="max-w-xs" />
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead>Toko</TableHead>
                            <TableHead>Kategori</TableHead>
                            <TableHead>Harga</TableHead>
                            <TableHead>Stok</TableHead>
                            <TableHead>Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="products.data.length === 0">
                            <TableCell colspan="7" class="py-8 text-center text-muted-foreground">Tidak ada produk ditemukan.</TableCell>
                        </TableRow>
                        <TableRow v-for="product in products.data" :key="product.id">
                            <TableCell class="font-mono text-xs">{{ product.id }}</TableCell>
                            <TableCell class="max-w-[180px] truncate font-medium">{{ product.name }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ product.store?.name ?? '-' }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ product.category?.name ?? '-' }}</TableCell>
                            <TableCell class="text-sm">{{ formatPrice(product.price) }}</TableCell>
                            <TableCell>{{ product.stock }}</TableCell>
                            <TableCell>
                                <Badge :variant="product.is_active ? 'default' : 'secondary'">
                                    {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="products.last_page > 1" class="mt-4 flex gap-1">
                <template v-for="link in products.links" :key="link.label">
                    <button
                        v-if="link.url"
                        type="button"
                        class="rounded border px-3 py-1 text-sm"
                        :class="link.active ? 'border-primary bg-primary text-white' : 'border-border hover:bg-muted'"
                        @click="router.get(link.url)"
                        v-html="link.label"
                    />
                    <span v-else class="rounded border border-border px-3 py-1 text-sm opacity-40" v-html="link.label" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
