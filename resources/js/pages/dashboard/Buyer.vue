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
    Package
} from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';
import { index as buyerCart } from '@/routes/buyer/cart';
import { index as buyerAddresses } from '@/routes/buyer/addresses';
import { index as buyerOrders, show as buyerOrderShow } from '@/routes/buyer/orders';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const props = defineProps<{
    balance: number;
    activeOrdersCount: number;
    completedOrdersCount: number;
    recentActiveOrders: any[];
}>();

const formatStatus = (status: string) => {
    return status.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'sedang_dikemas': return 'default';
        case 'menunggu_pengirim': return 'secondary';
        case 'sedang_dikirim': return 'destructive';
        default: return 'outline';
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
                class="bg-gradient-to-br from-primary/10 to-transparent border-primary/20"
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
            <div class="md:col-span-4 lg:col-span-8 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold tracking-tight">Pesanan Sedang Berjalan</h2>
                    <Button variant="ghost" size="sm" class="text-muted-foreground hover:text-foreground" @click="router.visit(buyerOrders.url())">
                        Lihat Semua <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                </div>

                <template v-if="activeOrdersCount === 0">
                    <Card class="border-dashed bg-muted/30">
                        <CardContent class="flex flex-col items-center justify-center p-12 text-center">
                            <div class="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center mb-6">
                                <Package class="h-10 w-10 text-primary" />
                            </div>
                            <h3 class="text-lg font-bold mb-2">Belum ada pesanan yang berjalan nih!</h3>
                            <p class="text-sm text-muted-foreground max-w-sm mb-6">
                                Kamu belum memiliki pesanan aktif. Mulai belanja sekarang atau cek riwayat pesananmu yang sudah selesai.
                            </p>
                            <div class="flex flex-wrap gap-3 justify-center">
                                <Button variant="outline" @click="router.visit(buyerOrders.url())">
                                    <History class="mr-2 h-4 w-4" />
                                    Cek Pesanan Saya
                                </Button>
                                <Button @click="router.visit(catalogIndex.url())">
                                    <ShoppingBag class="mr-2 h-4 w-4" />
                                    Mulai Belanja
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </template>

                <template v-else>
                    <div class="grid gap-4">
                        <Card v-for="order in recentActiveOrders" :key="order.id" class="overflow-hidden transition-all hover:shadow-md border-border/50">
                            <CardHeader class="bg-muted/30 pb-4 border-b">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <CardTitle class="text-base font-semibold flex items-center gap-2">
                                            {{ order.store?.name }}
                                        </CardTitle>
                                        <CardDescription class="text-xs mt-1">
                                            Order ID: #{{ order.id }} • {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                                        </CardDescription>
                                    </div>
                                    <Badge :variant="getStatusColor(order.status)" class="capitalize">
                                        {{ formatStatus(order.status) }}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent class="pt-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-16 w-16 bg-muted rounded-md flex items-center justify-center flex-shrink-0">
                                        <Package class="h-8 w-8 text-muted-foreground/50" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium line-clamp-1">
                                            {{ order.items?.[0]?.product?.name || 'Produk' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-1" v-if="order.items?.length > 1">
                                            + {{ order.items.length - 1 }} produk lainnya
                                        </p>
                                        <p class="text-sm font-bold text-primary mt-2">
                                            {{ formatIDR(order.grand_total) }}
                                        </p>
                                    </div>
                                    <div class="ml-auto">
                                        <Button variant="secondary" size="sm" @click="router.visit(buyerOrderShow.url({ order: order.id }))">
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
            <div class="md:col-span-3 lg:col-span-4 flex flex-col gap-4">
                <h2 class="text-xl font-semibold tracking-tight">Aksi Cepat</h2>
                <Card>
                    <CardContent class="p-4 grid grid-cols-2 gap-3">
                        <Link :href="catalogIndex.url()" class="flex flex-col items-center justify-center p-4 rounded-xl border border-border/50 bg-card hover:bg-muted/50 hover:border-primary/50 transition-all text-center group cursor-pointer h-28">
                            <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <ShoppingBag class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Katalog</span>
                        </Link>
                        
                        <Link :href="buyerCart.url()" class="flex flex-col items-center justify-center p-4 rounded-xl border border-border/50 bg-card hover:bg-muted/50 hover:border-primary/50 transition-all text-center group cursor-pointer h-28">
                            <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <ShoppingCart class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Keranjang</span>
                        </Link>

                        <Link :href="buyerOrders.url()" class="flex flex-col items-center justify-center p-4 rounded-xl border border-border/50 bg-card hover:bg-muted/50 hover:border-primary/50 transition-all text-center group cursor-pointer h-28">
                            <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <History class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Riwayat</span>
                        </Link>

                        <Link :href="buyerAddresses.url()" class="flex flex-col items-center justify-center p-4 rounded-xl border border-border/50 bg-card hover:bg-muted/50 hover:border-primary/50 transition-all text-center group cursor-pointer h-28">
                            <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <MapPin class="h-5 w-5" />
                            </div>
                            <span class="text-xs font-semibold">Alamat</span>
                        </Link>
                    </CardContent>
                </Card>
                
                <Card class="bg-primary text-primary-foreground mt-2 overflow-hidden relative">
                    <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-lg">Promo Spesial</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-primary-foreground/80 mb-4">
                            Gunakan voucher gratis ongkir untuk pesanan pertamamu bulan ini!
                        </p>
                        <Button variant="secondary" size="sm" class="w-full text-xs font-bold" @click="router.visit(catalogIndex.url())">
                            Klaim Sekarang
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
