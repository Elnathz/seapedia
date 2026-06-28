<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Store as StoreIcon } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

interface Owner {
    id: number;
    name: string;
    email: string;
}

interface Store {
    id: number;
    name: string;
    slug: string;
    owner: Owner | null;
    products_count: number;
    created_at: string;
}

interface Paginated {
    data: Store[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    stores: Paginated;
    filters: { q: string };
}>();

const search = ref(props.filters.q ?? '');

watch(search, (val) => {
    router.get('/admin/stores', { q: val }, { preserveState: true, replace: true });
});
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <StoreIcon class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">Toko</h1>
                    <p class="text-sm text-muted-foreground">Total {{ stores.total }} toko</p>
                </div>
            </div>

            <div class="mb-4">
                <Input v-model="search" placeholder="Cari nama toko..." class="max-w-xs" />
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Nama Toko</TableHead>
                            <TableHead>Pemilik</TableHead>
                            <TableHead>Produk</TableHead>
                            <TableHead>Bergabung</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="stores.data.length === 0">
                            <TableCell colspan="5" class="py-8 text-center text-muted-foreground">Tidak ada toko ditemukan.</TableCell>
                        </TableRow>
                        <TableRow v-for="store in stores.data" :key="store.id">
                            <TableCell class="font-mono text-xs">{{ store.id }}</TableCell>
                            <TableCell class="font-medium">{{ store.name }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ store.owner?.name ?? '-' }}</TableCell>
                            <TableCell>{{ store.products_count }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ store.created_at?.slice(0, 10) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="stores.last_page > 1" class="mt-4 flex gap-1">
                <template v-for="link in stores.links" :key="link.label">
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
