<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Check, ShoppingBag, Store, Truck } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';

const sectionRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        isVisible.value = true;

        return;
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                observer?.disconnect();
            }
        },
        { threshold: 0.15 },
    );

    if (sectionRef.value) {
observer.observe(sectionRef.value);
}
});

onUnmounted(() => observer?.disconnect());

const roles = [
    {
        key: 'buyer',
        icon: ShoppingBag,
        title: 'Pembeli',
        tagline: 'Belanja produk dari toko kampus',
        gradient: 'from-blue-500 to-blue-600',
        lightBg: 'bg-blue-50',
        iconColor: 'text-blue-500',
        features: [
            'Jelajahi katalog produk aktif',
            'Tambah ke keranjang & checkout',
            'Lacak status pesanan real-time',
            'Top-up wallet & bayar via saldo',
            'Riwayat transaksi lengkap',
        ],
        cta: 'Mulai Belanja',
    },
    {
        key: 'seller',
        icon: Store,
        title: 'Penjual',
        tagline: 'Buka toko dan jual produkmu',
        gradient: 'from-primary to-brand',
        lightBg: 'bg-primary/5',
        iconColor: 'text-primary',
        features: [
            'Buat & kelola toko sendiri',
            'Upload produk dengan foto',
            'Kelola stok & harga',
            'Proses pesanan masuk',
            'Pantau laporan penjualan',
        ],
        cta: 'Buka Toko',
    },
    {
        key: 'driver',
        icon: Truck,
        title: 'Kurir',
        tagline: 'Antar pesanan, dapat penghasilan',
        gradient: 'from-amber-500 to-amber-600',
        lightBg: 'bg-amber-50',
        iconColor: 'text-amber-500',
        features: [
            'Lihat pesanan siap antar',
            'Ambil & selesaikan pengiriman',
            'Penghasilan langsung ke wallet',
            'Riwayat pengiriman & pendapatan',
            'Kerja fleksibel antar kuliah',
        ],
        cta: 'Jadi Kurir',
    },
];
</script>

<template>
    <section
        ref="sectionRef"
        id="jadi-mitra"
        class="bg-gradient-to-b from-background to-secondary/30 py-16 sm:py-20"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl lg:text-4xl">
                    Satu Akun, Tiga Cara Bermain
                </h2>
                <div class="mx-auto mt-3 h-1 w-20 rounded-full bg-gradient-to-r from-primary to-brand" />
                <p class="mx-auto mt-4 max-w-xl text-muted-foreground">
                    Daftar sekali, langsung bisa belanja, buka toko, atau jadi kurir kampus.
                    Ganti peran kapan saja. Saldo tetap satu.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-3">
                <div
                    v-for="(role, index) in roles"
                    :key="role.key"
                    class="role-card group flex flex-col rounded-2xl border border-border bg-card p-8 transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-lg"
                    :class="{ visible: isVisible }"
                    :style="{ animationDelay: `${index * 100}ms` }"
                >
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex size-20 items-center justify-center rounded-2xl bg-gradient-to-br shadow-lg transition-transform duration-300 group-hover:scale-105"
                            :class="role.gradient"
                        >
                            <component :is="role.icon" class="size-10 text-white" />
                        </div>
                    </div>

                    <div class="mb-5 text-center">
                        <h3 class="text-xl font-bold text-foreground">{{ role.title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ role.tagline }}</p>
                    </div>

                    <ul class="mb-6 flex-1 space-y-2.5">
                        <li
                            v-for="(feat, fi) in role.features"
                            :key="feat"
                            class="role-feature flex items-start gap-2.5 text-sm text-muted-foreground"
                            :class="{ visible: isVisible }"
                            :style="{ animationDelay: `${index * 100 + fi * 40 + 200}ms` }"
                        >
                            <span
                                class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full"
                                :class="role.lightBg"
                            >
                                <Check class="size-3" :class="role.iconColor" />
                            </span>
                            {{ feat }}
                        </li>
                    </ul>

                    <Button as-child variant="outline" class="w-full gap-2 border-primary/20 text-primary hover:bg-primary hover:text-white">
                        <Link :href="register()">
                            {{ role.cta }}
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateX(-8px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.role-card {
    opacity: 0;
    transform: translateY(30px);
}

.role-card.visible {
    animation: slide-up 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.role-feature {
    opacity: 0;
    transform: translateX(-8px);
}

.role-feature.visible {
    animation: fade-in 0.35s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@media (prefers-reduced-motion: reduce) {
    .role-card,
    .role-feature {
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
}
</style>
