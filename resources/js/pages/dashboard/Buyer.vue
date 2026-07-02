<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ClipboardList,
    Wallet,
    CheckCircle,
    ShoppingBag,
    ShoppingCart,
    MapPin,
    History,
    ArrowRight,
    Package,
} from '@lucide/vue';
import StatCard from '@/components/StatCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatIDR } from '@/lib/utils';
import { index as buyerAddresses } from '@/routes/buyer/addresses';
import { index as buyerCart } from '@/routes/buyer/cart';
import {
    index as buyerOrders,
    show as buyerOrderShow,
} from '@/routes/buyer/orders';
import { index as catalogIndex } from '@/routes/catalog';

defineProps<{
    balance: number;
    activeOrdersCount: number;
    completedOrdersCount: number;
    recentActiveOrders: any[];
}>();

const formatStatus = (status: string) => {
    return status
        .split('_')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'sedang_dikemas':
            return 'default';
        case 'menunggu_pengirim':
            return 'secondary';
        case 'sedang_dikirim':
            return 'destructive';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head title="Dashboard Pembeli" />

    <div class="flex flex-col gap-8 pb-10">
        <!-- Welcome Header -->
        <div class="space-y-2">
            <h1 class="text-3xl font-bold tracking-tight">
                Halo, {{ $page.props.auth.user?.name }} 👋
            </h1>
            <p class="text-muted-foreground">
                Berikut adalah ringkasan aktivitas belanja kamu di Seapedia.
            </p>
        </div>

        <!-- Overview Cards -->
        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard
                label="Saldo Wallet"
                :value="formatIDR(balance)"
                :icon="Wallet"
                class="border-primary/20 bg-gradient-to-br from-primary/10 to-transparent"
            />
            <StatCard
                label="Pesanan Aktif"
                :value="activeOrdersCount"
                :icon="ClipboardList"
            />
            <StatCard
                label="Pesanan Selesai"
                :value="completedOrdersCount"
                :icon="CheckCircle"
            />
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid gap-6 md:grid-cols-7 lg:grid-cols-12">
            <!-- Left Column: Active Orders -->
            <div class="flex flex-col gap-4 md:col-span-4 lg:col-span-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold tracking-tight">
                        Pesanan Sedang Berjalan
                    </h2>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="text-muted-foreground hover:text-foreground"
                        @click="router.visit(buyerOrders.url())"
                    >
                        Lihat Semua <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </div>

                <template v-if="activeOrdersCount === 0">
                    <Card class="border-dashed bg-muted/30">
                        <CardContent
                            class="flex flex-col items-center justify-center p-12 text-center"
                        >
                            <div
                                class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10"
                            >
                                <Package class="h-10 w-10 text-primary" />
                            </div>
                            <h3 class="mb-2 text-lg font-bold">
                                Belum ada pesanan yang berjalan nih!
                            </h3>
                            <p
                                class="mb-6 max-w-sm text-sm text-muted-foreground"
                            >
                                Kamu belum memiliki pesanan aktif. Mulai belanja
                                sekarang atau cek riwayat pesananmu yang sudah
                                selesai.
                            </p>
                            <div class="flex flex-wrap justify-center gap-3">
                                <Button
                                    variant="outline"
                                    @click="router.visit(buyerOrders.url())"
                                >
                                    <History class="mr-2 h-4 w-4" />
                                    Cek Pesanan Saya
                                </Button>
                                <Button
                                    @click="router.visit(catalogIndex.url())"
                                >
                                    <ShoppingBag class="mr-2 h-4 w-4" />
                                    Mulai Belanja
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </template>

                <template v-else>
                    <div class="grid gap-4">
                        <Card
                            v-for="order in recentActiveOrders"
                            :key="order.id"
                            class="overflow-hidden border-border/50 transition-all hover:shadow-md"
                        >
                            <CardHeader class="border-b bg-muted/30 pb-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <CardTitle
                                            class="flex items-center gap-2 text-base font-semibold"
                                        >
                                            {{ order.store?.name }}
                                        </CardTitle>
                                        <CardDescription class="mt-1 text-xs">
                                            Order ID: #{{ order.id }} •
                                            {{
                                                new Date(
                                                    order.created_at,
                                                ).toLocaleDateString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'long',
                                                    year: 'numeric',
                                                })
                                            }}
                                        </CardDescription>
                                    </div>
                                    <Badge
                                        :variant="getStatusColor(order.status)"
                                        class="capitalize"
                                    >
                                        {{ formatStatus(order.status) }}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-md bg-muted"
                                    >
                                        <Package
                                            class="h-8 w-8 text-muted-foreground/50"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="line-clamp-1 text-sm font-medium"
                                        >
                                            {{
                                                order.items?.[0]?.product
                                                    ?.name || 'Produk'
                                            }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                            v-if="order.items?.length > 1"
                                        >
                                            +
                                            {{ order.items.length - 1 }} produk
                                            lainnya
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-bold text-primary"
                                        >
                                            {{ formatIDR(order.grand_total) }}
                                        </p>
                                    </div>
                                    <div class="ml-auto">
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            @click="
                                                router.visit(
                                                    buyerOrderShow.url({
                                                        order: order.id,
                                                    }),
                                                )
                                            "
                                        >
                                            Lacak
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </template>
            </div>

            <!-- Right Column: Quick Actions -->
            <div class="flex flex-col gap-4 md:col-span-3 lg:col-span-4">
                <h2 class="text-xl font-semibold tracking-tight">Aksi Cepat</h2>
                <Card>
                    <CardContent class="grid grid-cols-2 gap-3 p-4">
                        <Link
                            :href="catalogIndex.url()"
                            class="group flex h-28 cursor-pointer flex-col items-center justify-center rounded-xl border border-border/50 bg-card p-4 text-center transition-all hover:border-primary/50 hover:bg-muted/50"
                        >
                            <div
                                class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition-transform group-hover:scale-110"
                            >
                                <ShoppingBag class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Katalog</span>
                        </Link>

                        <Link
                            :href="buyerCart.url()"
                            class="group flex h-28 cursor-pointer flex-col items-center justify-center rounded-xl border border-border/50 bg-card p-4 text-center transition-all hover:border-primary/50 hover:bg-muted/50"
                        >
                            <div
                                class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition-transform group-hover:scale-110"
                            >
                                <ShoppingCart class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Keranjang</span>
                        </Link>

                        <Link
                            :href="buyerOrders.url()"
                            class="group flex h-28 cursor-pointer flex-col items-center justify-center rounded-xl border border-border/50 bg-card p-4 text-center transition-all hover:border-primary/50 hover:bg-muted/50"
                        >
                            <div
                                class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition-transform group-hover:scale-110"
                            >
                                <History class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Riwayat</span>
                        </Link>

                        <Link
                            :href="buyerAddresses.url()"
                            class="group flex h-28 cursor-pointer flex-col items-center justify-center rounded-xl border border-border/50 bg-card p-4 text-center transition-all hover:border-primary/50 hover:bg-muted/50"
                        >
                            <div
                                class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition-transform group-hover:scale-110"
                            >
                                <MapPin class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Alamat</span>
                        </Link>
                    </CardContent>
                </Card>

                <Card
                    class="relative mt-2 overflow-hidden bg-primary text-primary-foreground"
                >
                    <div
                        class="absolute -top-6 -right-6 h-32 w-32 rounded-full bg-white/10 blur-2xl"
                    ></div>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-lg">Promo Spesial</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="mb-4 text-sm text-primary-foreground/80">
                            Gunakan voucher gratis ongkir untuk pesanan
                            pertamamu bulan ini!
                        </p>
                        <Button
                            variant="secondary"
                            size="sm"
                            class="w-full text-xs font-bold"
                            @click="router.visit(catalogIndex.url())"
                        >
                            Klaim Sekarang
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
