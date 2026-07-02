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
        tagline: 'Belanja harian lebih praktis',
        gradient: 'from-blue-500 to-blue-600',
        lightBg: 'bg-blue-50',
        iconColor: 'text-blue-500',
        features: [
            'Jelajahi jutaan produk pilihan',
            'Sistem checkout cepat & aman',
            'Lacak pesanan secara real-time',
            'Beragam pilihan pembayaran',
            'Nikmati promo & diskon eksklusif',
        ],
        cta: 'Mulai Belanja',
    },
    {
        key: 'seller',
        icon: Store,
        title: 'Penjual',
        tagline: 'Buka toko dalam hitungan menit',
        gradient: 'from-primary to-brand',
        lightBg: 'bg-primary/5',
        iconColor: 'text-primary',
        features: [
            'Kelola stok dan etalase toko',
            'Terima notifikasi pesanan instan',
            'Laporan penjualan yang detail',
            'Dukungan promosi dari platform',
            'Tarik saldo pendapatan kapan saja',
        ],
        cta: 'Buka Toko',
    },
    {
        key: 'driver',
        icon: Truck,
        title: 'Kurir',
        tagline: 'Antar pesanan, tambah penghasilan',
        gradient: 'from-amber-500 to-amber-600',
        lightBg: 'bg-amber-50',
        iconColor: 'text-amber-500',
        features: [
            'Bebas pilih pesanan terdekat',
            'Panduan rute yang akurat',
            'Waktu dan jadwal kerja fleksibel',
            'Dapatkan bonus performa harian',
            'Pendapatan langsung bisa dicairkan',
        ],
        cta: 'Jadi Kurir',
    },
];
</script>

<template>
    <section
        ref="sectionRef"
        id="jadi-mitra"
        class="relative z-0 overflow-hidden bg-gradient-to-b from-white to-blue-50/30 py-20 sm:py-24"
    >
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative z-20 mb-16 text-center">
                <h2
                    class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                >
                    Bagaimana Cara Kerjanya
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-muted-foreground">
                    Satu platform, tiga peran. Tanpa registrasi ulang.
                </p>
            </div>

            <!-- Central Hub -->
            <div class="relative z-20 mb-12 flex flex-col items-center">
                <div
                    class="relative z-20 flex flex-col items-center justify-center rounded-full bg-white p-3.5 shadow-[0_8px_30px_rgb(0,0,0,0.12)] ring-1 ring-border/50"
                >
                    <img src="/favicon.svg" alt="SEAPEDIA" class="size-11" />
                </div>
                <div
                    class="relative z-20 mt-3 rounded-full bg-white/90 px-4 py-1.5 text-[13px] font-bold tracking-widest text-primary shadow-sm ring-1 ring-primary/20 backdrop-blur-sm"
                >
                    ONE ACCOUNT
                </div>

                <!-- Wave Connectors -->
                <div
                    class="absolute top-10 left-1/2 z-10 hidden h-[120px] w-full max-w-5xl -translate-x-1/2 md:block"
                >
                    <svg
                        viewBox="0 0 1000 120"
                        class="h-full w-full overflow-visible"
                        preserveAspectRatio="none"
                    >
                        <!-- Left Line (Buyer) -->
                        <path
                            d="M500 0 C500 70, 166 50, 166 120"
                            fill="none"
                            stroke="#0E9AA6"
                            stroke-width="2.5"
                            stroke-dasharray="6,6"
                            class="opacity-40"
                        />
                        <!-- Center Line (Seller) -->
                        <path
                            d="M500 0 L500 120"
                            fill="none"
                            stroke="#0E9AA6"
                            stroke-width="2.5"
                            stroke-dasharray="6,6"
                            class="opacity-40"
                        />
                        <!-- Right Line (Courier) -->
                        <path
                            d="M500 0 C500 70, 833 50, 833 120"
                            fill="none"
                            stroke="#0E9AA6"
                            stroke-width="2.5"
                            stroke-dasharray="6,6"
                            class="opacity-40"
                        />
                    </svg>
                </div>
            </div>

            <div
                class="relative z-20 mx-auto grid max-w-5xl gap-6 md:grid-cols-3"
            >
                <div
                    v-for="(role, index) in roles"
                    :key="role.key"
                    class="role-card group flex flex-col rounded-2xl border border-border/50 bg-white/80 p-8 backdrop-blur-sm transition-all duration-300 hover:-translate-y-2 hover:border-primary/20 hover:shadow-xl"
                    :class="{ visible: isVisible }"
                    :style="{ animationDelay: `${index * 100}ms` }"
                >
                    <div class="mb-6 flex justify-center">
                        <div
                            class="flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br shadow-md transition-transform duration-300 group-hover:scale-110"
                            :class="role.gradient"
                        >
                            <component
                                :is="role.icon"
                                class="size-8 text-white"
                            />
                        </div>
                    </div>

                    <div class="mb-5 text-center">
                        <h3 class="text-xl font-bold text-foreground">
                            {{ role.title }}
                        </h3>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            {{ role.tagline }}
                        </p>
                    </div>

                    <ul class="mb-8 flex-1 space-y-3">
                        <li
                            v-for="(feat, fi) in role.features"
                            :key="feat"
                            class="role-feature flex items-start gap-3 text-sm leading-relaxed text-muted-foreground"
                            :class="{ visible: isVisible }"
                            :style="{
                                animationDelay: `${index * 100 + fi * 40 + 200}ms`,
                            }"
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

                    <Button
                        as-child
                        variant="outline"
                        class="w-full gap-2 border-primary/20 text-primary transition-colors hover:bg-primary hover:text-white"
                    >
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
