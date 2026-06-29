<script setup lang="ts">
import { AlertTriangle, Clock } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import AdminClockController from '@/actions/App/Http/Controllers/Web/Admin/ClockController';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

interface Order {
    id: number;
    status: string;
    grand_total: number;
    sla_due_at: string | null;
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

defineProps<{
    orders: Paginated;
    eligibleCount: number;
}>();

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price);
}

const isAdvancing = ref(false);

function advanceTime(days: number) {
    isAdvancing.value = true;
    router.post(
        AdminClockController.advance.url(),
        { days },
        {
            preserveScroll: true,
            onFinish: () => (isAdvancing.value = false),
        }
    );
}
</script>

<template>
    <AppLayout>
        <div class="p-6">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-destructive/10 text-destructive">
                        <AlertTriangle class="size-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Overdue</h1>
                        <p class="text-sm text-muted-foreground">
                            <span class="font-medium text-destructive">{{ eligibleCount }}</span> pesanan menunggu auto-refund
                        </p>
                    </div>
                </div>
                
                <!-- Time Machine / Advance Clock -->
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="advanceTime(1)" :disabled="isAdvancing">
                        <Clock class="size-4 mr-2" />
                        +1 Hari
                    </Button>
                    <Button variant="outline" size="sm" @click="advanceTime(3)" :disabled="isAdvancing">
                        <Clock class="size-4 mr-2" />
                        +3 Hari
                    </Button>
                </div>
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
                            <TableHead>SLA Due</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="orders.data.length === 0">
                            <TableCell colspan="6" class="py-8 text-center text-muted-foreground">Tidak ada pesanan overdue.</TableCell>
                        </TableRow>
                        <TableRow v-for="order in orders.data" :key="order.id">
                            <TableCell class="font-mono text-xs">#{{ order.id }}</TableCell>
                            <TableCell class="text-sm">{{ order.buyer?.name ?? '-' }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ order.store?.name ?? '-' }}</TableCell>
                            <TableCell>
                                <Badge variant="destructive">{{ order.status.replace(/_/g, ' ') }}</Badge>
                            </TableCell>
                            <TableCell class="text-sm">{{ formatPrice(order.grand_total) }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ order.sla_due_at?.slice(0, 16) ?? '-' }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
