<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShoppingBag, Store, Truck, ChevronDown } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

const expanded = ref<string | null>(null);

const toggle = (key: string) => {
    expanded.value = expanded.value === key ? null : key;
};

const roles = [
    {
        key: 'buyer',
        icon: ShoppingBag,
        title: 'Pembeli',
        tagline: 'Belanja produk dari toko kampus',
        color: 'text-blue-500',
        bg: 'bg-blue-500/10',
        features: [
            'Jelajahi katalog produk aktif',
            'Tambah ke keranjang & checkout',
            'Lacak status pesanan real-time',
            'Top-up wallet & bayar via saldo',
            'Riwayat transaksi lengkap',
        ],
    },
    {
        key: 'seller',
        icon: Store,
        title: 'Penjual',
        tagline: 'Buka toko dan jual produkmu',
        color: 'text-emerald-500',
        bg: 'bg-emerald-500/10',
        features: [
            'Buat & kelola toko sendiri',
            'Upload produk dengan foto',
            'Kelola stok & harga',
            'Proses pesanan masuk',
            'Pantau laporan penjualan',
        ],
    },
    {
        key: 'driver',
        icon: Truck,
        title: 'Kurir',
        tagline: 'Antar pesanan, dapat penghasilan',
        color: 'text-amber-500',
        bg: 'bg-amber-500/10',
        features: [
            'Lihat pesanan siap antar',
            'Ambil & selesaikan pengiriman',
            'Penghasilan langsung ke wallet',
            'Riwayat pengiriman & pendapatan',
            'Kerja fleksibel antar kuliah',
        ],
    },
] as const;
</script>

<template>
    <section class="border-b border-border py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                    Satu akun, tiga cara bermain
                </h2>
                <p class="mt-2 text-muted-foreground">
                    Pilih peranmu — bisa ganti kapan saja tanpa daftar ulang.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <button
                    v-for="role in roles"
                    :key="role.key"
                    type="button"
                    class="group cursor-pointer rounded-2xl border border-border bg-card p-6 text-left transition-all duration-200 hover:border-primary/40 hover:shadow-md"
                    :class="{ 'border-primary/40 shadow-md': expanded === role.key }"
                    @click="toggle(role.key)"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110"
                                :class="role.bg"
                            >
                                <component :is="role.icon" class="size-5" :class="role.color" />
                            </div>
                            <div>
                                <p class="font-semibold text-foreground">{{ role.title }}</p>
                                <p class="text-xs text-muted-foreground">{{ role.tagline }}</p>
                            </div>
                        </div>
                        <ChevronDown
                            class="size-4 shrink-0 text-muted-foreground transition-transform duration-200"
                            :class="{ 'rotate-180': expanded === role.key }"
                        />
                    </div>

                    <!-- Expandable feature list -->
                    <div
                        class="overflow-hidden transition-all duration-300"
                        :class="expanded === role.key ? 'mt-4 max-h-48' : 'max-h-0'"
                    >
                        <ul class="space-y-2">
                            <li
                                v-for="feat in role.features"
                                :key="feat"
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <span class="size-1.5 shrink-0 rounded-full" :class="role.bg.replace('/10', '')"></span>
                                {{ feat }}
                            </li>
                        </ul>
                    </div>
                </button>
            </div>

            <div class="mt-8 text-center">
                <Button as-child size="lg">
                    <Link :href="register()">Mulai sekarang — gratis</Link>
                </Button>
            </div>
        </div>
    </section>
</template>
