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
    <section
        id="hero"
        class="relative overflow-hidden bg-gradient-to-br from-[#13B5C4] to-[#0A7180]"
    >
        <!-- Rotating triskelion mark — ambient background texture -->
        <div
            class="hero-mark pointer-events-none absolute -right-24 -top-24 hidden h-[560px] w-[560px] opacity-[0.09] lg:block"
            aria-hidden="true"
        >
            <Logo variant="mark" class="h-full w-full" />
        </div>
        <!-- Smaller mobile mark (top-right, less prominent) -->
        <div
            class="hero-mark pointer-events-none absolute -right-12 -top-12 h-[260px] w-[260px] opacity-[0.07] lg:hidden"
            aria-hidden="true"
        >
            <Logo variant="mark" class="h-full w-full" />
        </div>

        <!-- Hero content -->
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="grid min-h-[calc(100svh-7rem)] grid-cols-1 items-center gap-8 py-16 lg:grid-cols-2 lg:gap-12 lg:py-20"
            >
                <!-- Left: copy + CTAs -->
                <div class="hero-copy flex flex-col gap-6 lg:gap-8">
                    <!-- Eyebrow badge -->
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-medium text-white backdrop-blur-sm"
                    >
                        <span class="size-1.5 rounded-full bg-white/80"></span>
                        Platform Marketplace Kampus
                    </div>

                    <!-- Headline -->
                    <div class="space-y-3">
                        <h1
                            class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl xl:text-7xl"
                        >
                            Satu akun.<br />
                            <span class="text-white/90 underline decoration-white/30 underline-offset-4">
                                Tiga peran.
                            </span>
                        </h1>
                        <p class="max-w-md text-lg text-white/80 sm:text-xl">
                            Belanja, jualan, antar — satu saldo untuk semuanya. Ganti peran kapan saja tanpa keluar akun.
                        </p>
                    </div>

                    <!-- CTAs -->
                    <div class="flex flex-wrap gap-3">
                        <Button
                            as-child
                            size="lg"
                            class="gap-2 bg-white text-primary hover:bg-white/90"
                        >
                            <Link :href="catalogIndex.url()">
                                Jelajahi Katalog
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                        <Button
                            as-child
                            size="lg"
                            variant="outline"
                            class="border-white/40 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20 hover:text-white"
                        >
                            <Link :href="register()">Daftar Gratis</Link>
                        </Button>
                    </div>

                    <!-- Micro-trust -->
                    <div
                        class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/70"
                    >
                        <span class="flex items-center gap-1.5">
                            <ReceiptText class="size-4 text-white/80" />
                            PPN 12% transparan
                        </span>
                        <span class="hidden text-white/30 sm:block">·</span>
                        <span class="flex items-center gap-1.5">
                            <ShieldCheck class="size-4 text-white/80" />
                            Escrow aman
                        </span>
                        <span class="hidden text-white/30 sm:block">·</span>
                        <span class="flex items-center gap-1.5">
                            <Truck class="size-4 text-white/80" />
                            Kurir kampus
                        </span>
                    </div>
                </div>

                <!-- Right: single hero illustration with parallax -->
                <div class="relative hidden lg:block">
                    <div ref="parallaxEl" class="transition-none">
                        <img
                            src="/hero.svg"
                            alt="SEAPEDIA Marketplace"
                            class="h-auto w-full object-contain"
                            loading="eager"
                            onerror="this.parentElement.classList.add('hero-img-placeholder')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave divider — transitions hero to white body -->
        <div class="absolute bottom-0 left-0 right-0 -mb-px overflow-hidden" aria-hidden="true">
            <svg
                viewBox="0 0 1440 56"
                fill="none"
                preserveAspectRatio="none"
                class="hero-wave w-full"
                style="height: 48px"
            >
                <path
                    class="hero-wave-path"
                    d="M0 28 C240 56 480 0 720 28 C960 56 1200 0 1440 28 L1440 56 L0 56 Z"
                    fill="white"
                />
            </svg>
        </div>
    </section>
</template>

<style scoped>
/* Rotating mark */
@keyframes seapedia-spin {
    to {
        transform: rotate(360deg);
    }
}

.hero-mark {
    animation: seapedia-spin 36s linear infinite;
    transform-origin: center center;
}

/* Entrance fade-up for hero copy children */
@keyframes fade-up {
    from {
        opacity: 0;
        transform: translateY(22px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-copy > *:nth-child(1) {
    animation: fade-up 0.65s ease-out both 0s;
}
.hero-copy > *:nth-child(2) {
    animation: fade-up 0.65s ease-out both 0.13s;
}
.hero-copy > *:nth-child(3) {
    animation: fade-up 0.65s ease-out both 0.26s;
}
.hero-copy > *:nth-child(4) {
    animation: fade-up 0.65s ease-out both 0.38s;
}

/* Gentle wave motion */
@keyframes wave-shift {
    0%,
    100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(-30px);
    }
}

.hero-wave-path {
    animation: wave-shift 9s ease-in-out infinite;
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
    .hero-mark,
    .hero-wave-path {
        animation: none !important;
    }

    .hero-copy > * {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
}
</style>
