<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CalendarClock,
    Clock,
    RotateCcw,
    Wallet,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminOverdueController from '@/actions/App/Http/Controllers/Web/Admin/AdminOverdueController';
import AdminClockController from '@/actions/App/Http/Controllers/Web/Admin/ClockController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

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

const props = defineProps<{
    orders: Paginated;
    eligibleCount: number;
    now: string;
    isSimulated: boolean;
}>();

const nowMs = computed(() => new Date(props.now).getTime());

const dateFmt = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

function formatPrice(price: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(price);
}

function formatDateTime(iso: string | null) {
    return iso ? dateFmt.format(new Date(iso)) : '—';
}

// Whole days between the deadline and the current system clock, rounded up —
// the "Terlambat N hari" label. Uses the server-provided `now` so it matches
// the sweep's own comparison exactly.
function overdueDays(sla: string | null): number {
    if (!sla) {
        return 0;
    }

    return Math.max(
        1,
        Math.ceil((nowMs.value - new Date(sla).getTime()) / 86_400_000),
    );
}

const currentDate = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(props.now)),
);

const isBusy = ref(false);

function post(url: string, data: Record<string, unknown> = {}) {
    isBusy.value = true;
    router.post(url, data, {
        preserveScroll: true,
        onFinish: () => (isBusy.value = false),
    });
}

const advance = (days: number) =>
    post(AdminClockController.advance.url(), { days });
const resetClock = () => post(AdminClockController.reset.url());
const sweepNow = () => post(AdminOverdueController.sweep.url());
</script>

<template>
    <div class="p-4 sm:p-6">
        <!-- Header -->
        <div
            class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex size-11 items-center justify-center rounded-xl bg-destructive/10 text-destructive"
                >
                    <AlertTriangle class="size-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">
                        Pesanan Overdue
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        <span class="font-semibold text-destructive">{{
                            eligibleCount
                        }}</span>
                        pesanan lewat SLA menunggu auto-refund.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- System clock pill -->
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-border/70 bg-muted/40 px-3 py-1.5 text-xs font-medium"
                >
                    <CalendarClock class="size-3.5 text-primary" />
                    {{ currentDate }}
                    <Badge
                        v-if="isSimulated"
                        variant="secondary"
                        class="ml-0.5 text-[10px]"
                        >simulasi</Badge
                    >
                </span>

                <Button
                    v-if="isSimulated"
                    variant="outline"
                    size="sm"
                    :disabled="isBusy"
                    @click="resetClock"
                >
                    <RotateCcw class="mr-1.5 size-4" />
                    Reset ke hari ini
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="isBusy"
                    @click="advance(1)"
                >
                    <Clock class="mr-1.5 size-4" />
                    +1 Hari
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="isBusy"
                    @click="advance(3)"
                >
                    <Clock class="mr-1.5 size-4" />
                    +3 Hari
                </Button>
                <Button
                    size="sm"
                    :disabled="isBusy || eligibleCount === 0"
                    @click="sweepNow"
                >
                    <Wallet class="mr-1.5 size-4" />
                    Proses refund sekarang
                </Button>
            </div>
        </div>

        <p class="mb-4 text-xs text-muted-foreground">
            Menjalankan sweep (via tombol di atas atau saat memajukan hari) akan
            mengembalikan dana pembeli, memulihkan stok, dan menandai pesanan
            sebagai <span class="font-medium">Dikembalikan</span>. Termasuk
            pesanan yang tak kunjung diambil kurir.
        </p>

        <div class="overflow-x-auto rounded-xl border border-border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>ID</TableHead>
                        <TableHead>Pembeli</TableHead>
                        <TableHead>Toko</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Total</TableHead>
                        <TableHead>Batas SLA</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="orders.data.length === 0">
                        <TableCell
                            colspan="6"
                            class="py-10 text-center text-muted-foreground"
                        >
                            Tidak ada pesanan overdue. 🎉
                        </TableCell>
                    </TableRow>
                    <TableRow v-for="order in orders.data" :key="order.id">
                        <TableCell class="font-mono text-xs"
                            >#{{ order.id }}</TableCell
                        >
                        <TableCell class="text-sm font-medium">{{
                            order.buyer?.name ?? '—'
                        }}</TableCell>
                        <TableCell class="text-sm text-muted-foreground">{{
                            order.store?.name ?? '—'
                        }}</TableCell>
                        <TableCell>
                            <Badge variant="secondary" class="capitalize">{{
                                order.status.replace(/_/g, ' ')
                            }}</Badge>
                        </TableCell>
                        <TableCell class="text-right text-sm tabular-nums">{{
                            formatPrice(order.grand_total)
                        }}</TableCell>
                        <TableCell>
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-xs text-muted-foreground tabular-nums"
                                >
                                    {{ formatDateTime(order.sla_due_at) }}
                                </span>
                                <span
                                    class="inline-flex w-fit items-center gap-1 rounded-full bg-destructive/10 px-2 py-0.5 text-[11px] font-medium text-destructive"
                                >
                                    Terlambat
                                    {{ overdueDays(order.sla_due_at) }}
                                    hari
                                </span>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
