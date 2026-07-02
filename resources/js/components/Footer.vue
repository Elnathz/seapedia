<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Mail, MessageSquare } from '@lucide/vue';
import Logo from '@/components/brand/Logo.vue';
import { Button } from '@/components/ui/button';
import { index as reviewsIndex } from '@/routes/reviews';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-border bg-card pt-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Main Footer Content -->
            <div
                class="grid grid-cols-1 gap-12 border-b border-border pb-12 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Brand -->
                <div class="flex flex-col gap-6">
                    <Logo class="h-10 w-auto" />
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        SEAPEDIA adalah ekosistem marketplace modern yang
                        menyatukan pembeli, penjual, dan kurir dalam satu
                        platform cerdas terintegrasi.
                    </p>
                    <div
                        class="mt-2 flex flex-col gap-2 text-sm text-muted-foreground"
                    >
                        <div class="flex items-center gap-2">
                            <Mail class="size-4" /> cs@seapedia.com
                        </div>
                    </div>
                </div>

                <!-- Navigasi -->
                <div>
                    <h3 class="mb-4 font-semibold text-foreground">Navigasi</h3>
                    <ul
                        class="flex flex-col gap-3 text-sm text-muted-foreground"
                    >
                        <li>
                            <a
                                href="/#how-it-works"
                                class="transition-colors hover:text-primary"
                                >Cara Kerja</a
                            >
                        </li>
                        <li>
                            <a
                                href="/#why-choose-us"
                                class="transition-colors hover:text-primary"
                                >Keunggulan</a
                            >
                        </li>
                        <li>
                            <a
                                href="/catalog"
                                class="transition-colors hover:text-primary"
                                >Katalog Produk</a
                            >
                        </li>
                        <li>
                            <Link
                                :href="reviewsIndex.url()"
                                class="transition-colors hover:text-primary"
                                >Semua Ulasan</Link
                            >
                        </li>
                    </ul>
                </div>

                <!-- Bantuan -->
                <div>
                    <h3 class="mb-4 font-semibold text-foreground">Bantuan</h3>
                    <ul
                        class="flex flex-col gap-3 text-sm text-muted-foreground"
                    >
                        <li>
                            <a
                                href="#"
                                class="transition-colors hover:text-primary"
                                >Pusat Bantuan</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors hover:text-primary"
                                >Panduan Penjual</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors hover:text-primary"
                                >Panduan Kurir</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="transition-colors hover:text-primary"
                                >Hubungi Kami</a
                            >
                        </li>
                    </ul>
                </div>

                <!-- Ulasan Aplikasi -->
                <div>
                    <h3 class="mb-4 font-semibold text-foreground">
                        Ulasan Aplikasi
                    </h3>
                    <p
                        class="mb-4 text-sm leading-relaxed text-muted-foreground"
                    >
                        Bantu kami berkembang dengan membagikan pengalaman Anda
                        menggunakan SEAPEDIA.
                    </p>
                    <Button
                        as-child
                        variant="outline"
                        class="w-full justify-center gap-2 border-primary/20 text-primary transition-colors hover:bg-primary/5 hover:text-primary"
                    >
                        <Link :href="reviewsIndex.url()">
                            <MessageSquare class="size-4" />
                            Tulis Ulasan
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Copyright -->
            <div
                class="flex flex-col items-center justify-between gap-4 py-8 text-xs text-muted-foreground sm:flex-row"
            >
                <p>
                    &copy; {{ year }} SEAPEDIA. Hak cipta dilindungi
                    undang-undang.
                </p>
                <div class="flex gap-4">
                    <span>Versi 1.0</span>
                    <span>Indonesia</span>
                </div>
            </div>
        </div>
    </footer>
</template>
