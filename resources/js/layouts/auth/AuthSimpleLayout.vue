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
            class="hidden w-1/2 flex-col justify-between bg-primary p-10 text-primary-foreground lg:flex xl:w-5/12"
        >
            <Link :href="home()">
                <Logo class="h-10 w-auto brightness-0 invert" />
            </Link>
            <div class="space-y-4">
                <p class="text-4xl font-bold leading-tight xl:text-5xl">
                    Satu akun.<br />Tiga peran.
                </p>
                <p class="text-primary-foreground/80 text-lg">
                    Belanja, jualan, antar — satu saldo untuk semuanya.
                </p>
            </div>
            <p class="text-sm text-primary-foreground/60">
                &copy; {{ new Date().getFullYear() }} SEAPEDIA · Marketplace Kampus
            </p>
        </div>

        <!-- Right form panel -->
        <div class="flex flex-1 flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="w-full max-w-sm">
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
