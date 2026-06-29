<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ShieldCheck, ReceiptText, Truck } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import Logo from '@/components/brand/Logo.vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
import { index as catalogIndex } from '@/routes/catalog';

const parallaxEl = ref<HTMLElement | null>(null);
let rafId: number | null = null;
let cleanupFn: (() => void) | null = null;

function applyParallax(dx: number, dy: number) {
    if (rafId) {
        cancelAnimationFrame(rafId);
    }

    rafId = requestAnimationFrame(() => {
        if (parallaxEl.value) {
            parallaxEl.value.style.transform = `translate(${dx}px, ${dy}px)`;
        }
    });
}

function onMouseMove(e: MouseEvent) {
    const x = (e.clientX / window.innerWidth - 0.5) * 2;
    const y = (e.clientY / window.innerHeight - 0.5) * 2;
    applyParallax(x * 14, y * 9);
}

function onScroll() {
    const dy = Math.min(window.scrollY * 0.06, 20);
    applyParallax(0, dy);
}

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    if (window.innerWidth >= 1024) {
        window.addEventListener('mousemove', onMouseMove, { passive: true });
        cleanupFn = () => window.removeEventListener('mousemove', onMouseMove);
    } else {
        window.addEventListener('scroll', onScroll, { passive: true });
        cleanupFn = () => window.removeEventListener('scroll', onScroll);
    }
});

onUnmounted(() => {
    cleanupFn?.();

    if (rafId) {
        cancelAnimationFrame(rafId);
    }
});
</script>

<template>
    <section id="hero" class="relative overflow-hidden bg-gradient-to-b from-[#0A7180] to-[#085b66]">
        <!-- Hero content -->
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-8">
            <div class="grid min-h-[calc(100svh-7rem)] grid-cols-1 items-center gap-8 py-16 lg:grid-cols-2 lg:gap-12 lg:py-20">
                <!-- Left: copy + CTAs -->
                <div class="hero-copy flex flex-col gap-6 lg:gap-8">
                    <!-- Headline -->
                    <div class="space-y-4">
                        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl md:text-6xl lg:text-7xl leading-[1.1]">
                            Satu akun.<br />
                            <span class="text-teal-300">
                                Tiga peran.
                            </span>
                        </h1>
                        <p class="max-w-lg text-lg text-white/90 sm:text-xl leading-relaxed font-medium">
                            Beli, jual, dan antar pesanan cukup dengan satu akun terintegrasi. Tanpa ribet ganti aplikasi.
                        </p>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-wrap gap-4 mt-2">
                        <Button as-child size="lg" class="bg-white text-[#0A7180] font-bold shadow-lg hover:bg-teal-50 hover:shadow-xl transition-all duration-300 hover:scale-105 hover:-translate-y-0.5 px-8">
                            <Link :href="register()">
                                Daftar Gratis
                            </Link>
                        </Button>
                        <Button as-child size="lg" variant="outline"
                            class="gap-2 border-white/20 bg-transparent text-white hover:bg-white/10 hover:text-white transition-all duration-300 hover:scale-105 hover:-translate-y-0.5 px-6">
                            <Link href="/catalog">
                                Jelajahi Katalog
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </div>

                    <!-- Micro-trust / Social Proof -->
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mt-4 text-sm font-medium text-white/70">
                        <span class="flex items-center gap-1.5 transition-colors hover:text-white">
                            <span class="text-yellow-400 tracking-widest text-xs">★★★★★</span>
                            <span class="ml-1">Trusted Marketplace</span>
                        </span>
                        <span class="hidden text-white/20 sm:block">|</span>
                        <span class="flex items-center gap-1.5 transition-colors hover:text-white">
                            <span class="font-bold text-white">1000+</span>
                            Products
                        </span>
                        <span class="hidden text-white/20 sm:block">|</span>
                        <span class="flex items-center gap-1.5 transition-colors hover:text-white">
                            <span class="font-bold text-white">500+</span>
                            Verified Stores
                        </span>
                    </div>
                </div>

                <!-- Right: single hero illustration with parallax -->
                <div class="relative hidden lg:block hero-img-container">
                    <div ref="parallaxEl" class="transition-none">
                        <img src="/hero.svg" alt="SEAPEDIA Marketplace" class="h-auto w-full object-contain filter drop-shadow-2xl"
                            loading="eager" onerror="this.parentElement.classList.add('hero-img-placeholder')" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave divider — gracefully transitions hero to white body without horizontal overflow -->
        <!-- Added -mb-px to remove the sub-pixel 1px gap rendering issue causing the thin line beneath the wave -->
        <div class="absolute bottom-0 -mb-px w-full overflow-hidden leading-none z-20 pointer-events-none" aria-hidden="true">
            <svg viewBox="0 0 1440 56" fill="none" preserveAspectRatio="none" class="w-full h-10 sm:h-12 lg:h-16">
                <path d="M0 28 C240 56 480 0 720 28 C960 56 1200 0 1440 28 L1440 56 L0 56 Z" fill="white" />
            </svg>
        </div>
    </section>
</template>

<style scoped>
/* Entrance fade-up for hero copy children (Premium Dramatic Reveal) */
@keyframes fade-up {
    from {
        opacity: 0;
        transform: translateY(40px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Applying MD3 Emphasized easing for a premium feel */
.hero-copy>*:nth-child(1) {
    animation: fade-up 0.8s cubic-bezier(0.05, 0.7, 0.1, 1) both 0s;
}

.hero-copy>*:nth-child(2) {
    animation: fade-up 0.8s cubic-bezier(0.05, 0.7, 0.1, 1) both 0.1s;
}

.hero-copy>*:nth-child(3) {
    animation: fade-up 0.8s cubic-bezier(0.05, 0.7, 0.1, 1) both 0.2s;
}

.hero-copy>*:nth-child(4) {
    animation: fade-up 0.8s cubic-bezier(0.05, 0.7, 0.1, 1) both 0.3s;
}

/* Hero image entrance (counter-motion, elegant float up) */
@keyframes image-reveal {
    from {
        opacity: 0;
        transform: translateY(60px) scale(0.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.hero-img-container {
    animation: image-reveal 1.2s cubic-bezier(0.05, 0.7, 0.1, 1) both 0.2s;
}

/* Placeholder for missing hero image */
:deep(.hero-img-placeholder) {
    min-height: 14rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Respect prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
    .hero-copy>* {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
}
</style>
