<script setup lang="ts">
import {
    ReceiptText,
    ShieldCheck,
    Truck,
    UserCircle,
    Wallet,
    RefreshCw,
} from '@lucide/vue';

const items = [
    {
        icon: UserCircle,
        title: 'One Account',
        desc: 'Tidak perlu membuat akun baru untuk setiap peran yang berbeda.',
    },
    {
        icon: Wallet,
        title: 'Integrated Wallet',
        desc: 'Satu saldo untuk membayar pesanan atau menerima penghasilan.',
    },
    {
        icon: RefreshCw,
        title: 'Switch Role',
        desc: 'Ganti peran sebagai pembeli, penjual, atau kurir dalam hitungan detik.',
    },
    {
        icon: ShieldCheck,
        title: 'Escrow System',
        desc: 'Dana ditahan aman oleh sistem sampai pesanan selesai dengan baik.',
    },
    {
        icon: ReceiptText,
        title: 'Tax Transparency',
        desc: 'Pajak dihitung otomatis sebelum checkout tanpa biaya tersembunyi.',
    },
    {
        icon: Truck,
        title: 'Internal Delivery',
        desc: 'Pengiriman dilakukan oleh sesama pengguna dalam ekosistem SEAPEDIA.',
    },
] as const;
</script>

<template>
    <section
        id="why-choose-us"
        class="relative overflow-hidden border-y border-border/50 bg-gradient-to-b from-blue-50/50 to-teal-50/30 py-20 sm:py-24"
    >
        <!-- Top Wave Divider -->
        <div
            class="absolute top-0 w-full rotate-180 overflow-hidden leading-none"
        >
            <svg
                class="relative block h-[30px] w-[calc(100%+1.3px)]"
                data-name="Layer 1"
                preserveAspectRatio="none"
                viewBox="0 0 1200 120"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    class="fill-white"
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                ></path>
            </svg>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <h2
                    class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                >
                    Kenapa SEAPEDIA Berbeda?
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-muted-foreground">
                    Kami tidak sekadar menghubungkan penjual dan pembeli,
                    melainkan membangun ekosistem yang utuh.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="item in items"
                    :key="item.title"
                    class="group flex flex-col items-center rounded-2xl border border-border/50 bg-white p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:border-primary/40 hover:shadow-lg"
                >
                    <div
                        class="mb-5 flex size-14 items-center justify-center rounded-xl bg-gradient-to-br from-primary/10 to-brand/10 text-primary transition-transform duration-300 group-hover:scale-110"
                    >
                        <component :is="item.icon" class="size-7" />
                    </div>
                    <h3 class="mb-2 text-lg font-bold text-foreground">
                        {{ item.title }}
                    </h3>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ item.desc }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Bottom Wave Divider -->
        <div class="absolute bottom-0 w-full overflow-hidden leading-none">
            <svg
                class="relative block h-[30px] w-[calc(100%+1.3px)]"
                data-name="Layer 1"
                preserveAspectRatio="none"
                viewBox="0 0 1200 120"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    class="fill-white"
                    d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"
                ></path>
            </svg>
        </div>
    </section>
</template>
