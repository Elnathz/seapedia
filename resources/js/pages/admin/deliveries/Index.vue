<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Truck } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

interface Delivery {
    id: number;
    status: string;
    earning_amount: number | null;
    driver: { id: number; name: string } | null;
    order: { id: number; status: string; store: { name: string } | null } | null;
    created_at: string;
}

interface Paginated {
    data: Delivery[];
    total: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    deliveries: Paginated;
    filters: { status: string };
    statuses: string[];
}>();

const statusFilter = ref(props.filters.status || 'all');

watch(statusFilter, (val) => {
    const status = val === 'all' ? '' : val;
    router.get('/admin/deliveries', { status }, { preserveState: true, replace: true });
});

const statusVariant = (s: string): 'default' | 'secondary' | 'outline' => {
    if (s === 'selesai') {
return 'default';
}

    if (s === 'diambil') {
return 'outline';
}

    return 'secondary';
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <Truck class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">Pengiriman</h1>
                    <p class="text-sm text-muted-foreground">Total {{ deliveries.total }} pengiriman</p>
                </div>
            </div>

            <div class="mb-4">
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-48">
                        <SelectValue placeholder="Semua status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua status</SelectItem>
                        <SelectItem v-for="s in statuses" :key="s" :value="s">{{ s }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Kurir</TableHead>
                            <TableHead>Toko</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Komisi</TableHead>
                            <TableHead>Tanggal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="deliveries.data.length === 0">
                            <TableCell colspan="6" class="py-8 text-center text-muted-foreground">Tidak ada pengiriman ditemukan.</TableCell>
                        </TableRow>
                        <TableRow v-for="d in deliveries.data" :key="d.id">
                            <TableCell class="font-mono text-xs">#{{ d.id }}</TableCell>
                            <TableCell class="text-sm">{{ d.driver?.name ?? 'Belum diambil' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ d.order?.store?.name ?? '-' }}</TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(d.status)">{{ d.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-sm">{{ d.earning_amount ? `Rp ${d.earning_amount.toLocaleString('id-ID')}` : '-' }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ d.created_at?.slice(0, 10) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="deliveries.last_page > 1" class="mt-4 flex gap-1">
                <template v-for="link in deliveries.links" :key="link.label">
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
