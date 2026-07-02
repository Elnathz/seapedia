<script setup lang="ts">
import { CheckCircle2 } from '@lucide/vue';
</script>

<template>
    <section
        id="about"
        class="relative overflow-hidden bg-white py-24 sm:py-32"
    >
        <!-- Subtle Wave Background Element -->
        <div
            class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-[0.03]"
        >
            <svg
                viewBox="0 0 100 100"
                class="animate-pulse-slow h-[200%] w-full"
            >
                <path
                    d="M0,50 Q25,25 50,50 T100,50 L100,100 L0,100 Z"
                    fill="currentColor"
                    class="text-primary"
                />
            </svg>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <!-- Wave Eyebrow (Seapedia Theme) -->
                <div class="mb-6 flex items-center justify-center gap-4">
                    <!-- Left Wave -->
                    <svg
                        class="h-2 w-8 text-primary/40"
                        viewBox="0 0 100 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M0 10 Q 12.5 0 25 10 T 50 10 T 75 10 T 100 10"
                            stroke="currentColor"
                            stroke-width="5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <span
                        class="text-xl font-extrabold tracking-[0.2em] text-primary uppercase"
                        >Apa Itu Seapedia</span
                    >

                    <!-- Right Wave -->
                    <svg
                        class="h-2 w-8 text-primary/40"
                        viewBox="0 0 100 20"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M0 10 Q 12.5 20 25 10 T 50 10 T 75 10 T 100 10"
                            stroke="currentColor"
                            stroke-width="5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <!-- Section Header -->
                <h2
                    class="text-3xl leading-tight font-extrabold tracking-tight text-foreground sm:text-4xl md:text-5xl"
                >
                    Satu Ekosistem. <br class="hidden sm:block" />
                    <span
                        class="bg-gradient-to-r from-primary to-brand bg-clip-text text-transparent"
                        >Tanpa Batasan Peran.</span
                    >
                </h2>

                <!-- Description (Storytelling) -->
                <div
                    class="mt-8 space-y-6 text-lg leading-relaxed text-muted-foreground"
                >
                    <p>
                        Selama ini, ekosistem e-commerce terpecah. Pembeli
                        mengunduh satu aplikasi, penjual harus membuat akun toko
                        yang rumit, dan kurir beroperasi di sistem yang sama
                        sekali berbeda. Batasan ini membuat transisi antar peran
                        menjadi lambat dan merepotkan.
                    </p>
                    <p>
                        <strong class="font-semibold text-foreground"
                            >SEAPEDIA hadir untuk mendobrak dinding
                            tersebut.</strong
                        >
                        Kami merancang platform tanpa batas di mana Anda
                        memegang kendali penuh. Hari ini Anda berbelanja
                        kebutuhan, besok Anda membuka toko, lalu mengambil rute
                        pengiriman di waktu luang, semuanya terjadi secara
                        instan dalam
                        <span class="font-medium text-foreground"
                            >satu akun, satu dompet, dan satu identitas
                            terintegrasi.</span
                        >
                    </p>
                </div>

                <!-- Divider -->
                <div class="my-10 flex items-center justify-center gap-4">
                    <div
                        class="h-px w-24 bg-gradient-to-r from-transparent to-border"
                    ></div>
                    <div class="size-2 rounded-full bg-primary/20"></div>
                    <div
                        class="h-px w-24 bg-gradient-to-l from-transparent to-border"
                    ></div>
                </div>

                <!-- Core Values -->
                <div
                    class="flex flex-col items-center justify-center gap-6 font-medium text-foreground sm:flex-row sm:gap-12"
                >
                    <div class="group flex cursor-default items-center gap-2">
                        <CheckCircle2
                            class="size-5 text-primary transition-transform group-hover:scale-110 group-hover:text-brand"
                        />
                        <span>One Identity</span>
                    </div>
                    <div class="group flex cursor-default items-center gap-2">
                        <CheckCircle2
                            class="size-5 text-primary transition-transform group-hover:scale-110 group-hover:text-brand"
                        />
                        <span>One Wallet</span>
                    </div>
                    <div class="group flex cursor-default items-center gap-2">
                        <CheckCircle2
                            class="size-5 text-primary transition-transform group-hover:scale-110 group-hover:text-brand"
                        />
                        <span>Switch Anytime</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes pulse-slow {
    0%,
    100% {
        opacity: 0.02;
    }
    50% {
        opacity: 0.05;
    }
}
.animate-pulse-slow {
    animation: pulse-slow 8s ease-in-out infinite;
}
</style>
