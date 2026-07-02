<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ClipboardList } from '@lucide/vue';
import { ref, watch } from 'vue';
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
import AppLayout from '@/layouts/AppLayout.vue';

interface Order {
    id: number;
    status: string;
    grand_total: number;
    buyer: { id: number; name: string } | null;
    store: { id: number; name: string } | null;
    created_at: string;
}

interface Paginated {
    data: Order[];
    total: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    orders: Paginated;
    filters: { q: string; status: string };
    statuses: string[];
}>();

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');

function applyFilter() {
    const status = statusFilter.value === 'all' ? '' : statusFilter.value;
    router.get(
        '/admin/orders',
        { q: search.value, status },
        { preserveState: true, replace: true },
    );
}

watch(search, applyFilter);
watch(statusFilter, applyFilter);

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(price);
}

const statusVariant = (
    s: string,
): 'default' | 'secondary' | 'destructive' | 'outline' => {
    if (s === 'pesanan_selesai') {
        return 'default';
    }

    if (s === 'dikembalikan') {
        return 'destructive';
    }

    if (s === 'sedang_dikirim') {
        return 'outline';
    }

    return 'secondary';
};
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <ClipboardList class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold">Pesanan</h1>
                    <p class="text-sm text-muted-foreground">
                        Total {{ orders.total }} pesanan
                    </p>
                </div>
            </div>

            <div class="mb-4 flex gap-3">
                <Input
                    v-model="search"
                    placeholder="Cari ID pesanan..."
                    class="max-w-xs"
                />
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-48">
                        <SelectValue placeholder="Semua status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua status</SelectItem>
                        <SelectItem v-for="s in statuses" :key="s" :value="s">{{
                            s.replace(/_/g, ' ')
                        }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead>Pembeli</TableHead>
                            <TableHead>Toko</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Total</TableHead>
                            <TableHead>Tanggal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="orders.data.length === 0">
                            <TableCell
                                colspan="6"
                                class="py-8 text-center text-muted-foreground"
                                >Tidak ada pesanan ditemukan.</TableCell
                            >
                        </TableRow>
                        <TableRow v-for="order in orders.data" :key="order.id">
                            <TableCell class="font-mono text-xs"
                                >#{{ order.id }}</TableCell
                            >
                            <TableCell class="text-sm">{{
                                order.buyer?.name ?? '-'
                            }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{
                                order.store?.name ?? '-'
                            }}</TableCell>
                            <TableCell>
                                <Badge :variant="statusVariant(order.status)">{{
                                    order.status.replace(/_/g, ' ')
                                }}</Badge>
                            </TableCell>
                            <TableCell class="text-sm">{{
                                formatPrice(order.grand_total)
                            }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{
                                order.created_at?.slice(0, 10)
                            }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="orders.last_page > 1" class="mt-4 flex gap-1">
                <template v-for="link in orders.links" :key="link.label">
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
    </AppLayout>
</template>
