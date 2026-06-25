<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ShieldCheck, ReceiptText, Truck } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
import { index as catalogIndex } from '@/routes/catalog';
</script>

<template>
    <section class="relative overflow-hidden border-b border-border bg-background">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid min-h-[calc(100svh-4rem)] grid-cols-1 items-center gap-8 py-16 lg:grid-cols-2 lg:gap-12 lg:py-20">
                <!-- Left: copy + CTAs -->
                <div class="flex flex-col gap-6 lg:gap-8">
                    <div class="inline-flex w-fit items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-medium text-primary">
                        <span class="size-1.5 rounded-full bg-primary"></span>
                        Platform Marketplace Kampus
                    </div>

                    <div class="space-y-3">
                        <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-6xl xl:text-7xl">
                            Satu akun.<br />
                            <span class="text-primary">Tiga peran.</span>
                        </h1>
                        <p class="max-w-md text-lg text-muted-foreground sm:text-xl">
                            Belanja, jualan, antar — satu saldo untuk semuanya. Ganti peran kapan saja tanpa keluar akun.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Button as-child size="lg" class="gap-2">
                            <Link :href="catalogIndex.url()">
                                Jelajahi Katalog
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                        <Button as-child size="lg" variant="outline">
                            <Link :href="register()">Daftar Gratis</Link>
                        </Button>
                    </div>

                    <!-- Micro-trust -->
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                        <span class="flex items-center gap-1.5">
                            <ReceiptText class="size-4 text-primary" />
                            PPN 12% transparan
                        </span>
                        <span class="hidden text-border sm:block">·</span>
                        <span class="flex items-center gap-1.5">
                            <ShieldCheck class="size-4 text-primary" />
                            Escrow aman
                        </span>
                        <span class="hidden text-border sm:block">·</span>
                        <span class="flex items-center gap-1.5">
                            <Truck class="size-4 text-primary" />
                            Kurir kampus
                        </span>
                    </div>
                </div>

                <!-- Right: image collage -->
                <div class="relative hidden lg:block">
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Main large image -->
                        <div class="col-span-2 row-span-1 overflow-hidden rounded-2xl bg-muted shadow-xl ring-1 ring-border">
                            <img
                                src="/images/banner/hero/hero.jpeg"
                                alt="SEAPEDIA Marketplace"
                                class="h-56 w-full object-cover xl:h-64"
                                loading="eager"
                            />
                        </div>
                        <!-- Side images row -->
                        <div class="overflow-hidden rounded-xl bg-muted shadow-md ring-1 ring-border">
                            <img
                                src="/images/banner/side/side1.jpeg"
                                alt="Produk kampus"
                                class="h-36 w-full object-cover xl:h-40"
                                loading="lazy"
                            />
                        </div>
                        <div class="overflow-hidden rounded-xl bg-muted shadow-md ring-1 ring-border">
                            <img
                                src="/images/banner/side/side2.jpeg"
                                alt="Produk kampus"
                                class="h-36 w-full object-cover xl:h-40"
                                loading="lazy"
                            />
                        </div>
                        <div class="overflow-hidden rounded-xl bg-muted shadow-md ring-1 ring-border">
                            <img
                                src="/images/banner/side/side3.jpeg"
                                alt="Produk kampus"
                                class="h-28 w-full object-cover xl:h-32"
                                loading="lazy"
                            />
                        </div>
                        <div class="overflow-hidden rounded-xl bg-muted shadow-md ring-1 ring-border">
                            <img
                                src="/images/banner/side/side4.jpeg"
                                alt="Produk kampus"
                                class="h-28 w-full object-cover xl:h-32"
                                loading="lazy"
                            />
                        </div>
                    </div>
                    <!-- decorative teal glow -->
                    <div class="pointer-events-none absolute -right-8 -top-8 size-64 rounded-full bg-primary/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-8 -left-8 size-48 rounded-full bg-primary/8 blur-3xl"></div>
                </div>
            </div>
        </div>
    </section>
</template>
