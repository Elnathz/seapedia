<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Logo from '@/components/brand/Logo.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh bg-background">
        <!-- Left brand panel — desktop only -->
        <div
            class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-[#13B5C4] to-[#0A7180] p-10 text-white lg:flex xl:w-5/12"
        >
            <!-- Decorative rotating mark -->
            <div
                class="auth-mark pointer-events-none absolute -right-16 -top-16 h-[420px] w-[420px] opacity-[0.1]"
                aria-hidden="true"
            >
                <Logo variant="mark" class="h-full w-full" />
            </div>
            <div
                class="auth-mark pointer-events-none absolute -bottom-20 -left-20 h-[320px] w-[320px] opacity-[0.07]"
                aria-hidden="true"
                style="animation-delay: -18s"
            >
                <Logo variant="mark" class="h-full w-full" />
            </div>

            <!-- Logo -->
            <Link :href="home()" class="relative z-10">
                <Logo class="h-10 w-auto brightness-0 invert" />
            </Link>

            <!-- Brand copy -->
            <div class="relative z-10 space-y-4">
                <p class="text-4xl font-bold leading-tight xl:text-5xl">
                    Satu akun.<br />Tiga peran.
                </p>
                <p class="text-lg text-white/80">
                    Belanja, jualan, antar dalam satu saldo untuk semuanya.
                </p>
            </div>

            <!-- Footer -->
            <p class="relative z-10 text-sm text-white/60">
                &copy; {{ new Date().getFullYear() }} SEAPEDIA · Marketplace
            </p>
        </div>

        <!-- Right form panel -->
        <div class="flex flex-1 flex-col items-center justify-center gap-6 overflow-x-hidden p-6 md:p-10">
            <div class="auth-form-shell w-full max-w-sm overflow-visible">
                <div class="flex flex-col gap-8">
                    <!-- Mobile logo -->
                    <div class="flex flex-col items-center gap-4 lg:hidden">
                        <Link :href="home()">
                            <Logo class="h-9 w-auto" />
                        </Link>
                    </div>

                    <div class="space-y-1.5">
                        <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                            {{ title }}
                        </h1>
                        <p class="text-sm text-muted-foreground">{{ description }}</p>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes seapedia-spin {
    to {
        transform: rotate(360deg);
    }
}

.auth-mark {
    animation: seapedia-spin 40s linear infinite;
    transform-origin: center center;
}

@media (prefers-reduced-motion: reduce) {
    .auth-mark {
        animation: none !important;
    }
}

/* Register step 2: use most of the right panel width for wider role cards */
@media (min-width: 1024px) {
    .auth-form-shell:has(.register-form-wide) {
        max-width: min(46rem, calc(50vw - 3rem));
    }
}
</style>
