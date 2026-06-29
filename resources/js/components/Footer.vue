<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Mail, MessageSquare } from '@lucide/vue';
import Logo from '@/components/brand/Logo.vue';
import { Button } from '@/components/ui/button';
import { index as reviewsIndex } from '@/routes/reviews';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-card pt-16 border-t border-border">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-4 pb-12 border-b border-border">
                
                <!-- Brand -->
                <div class="flex flex-col gap-6">
                    <Logo class="h-10 w-auto" />
                    <p class="text-sm text-muted-foreground leading-relaxed">
                        SEAPEDIA adalah ekosistem marketplace modern yang menyatukan pembeli, penjual, dan kurir dalam satu platform cerdas terintegrasi.
                    </p>
                    <div class="flex flex-col gap-2 text-sm text-muted-foreground mt-2">
                        <div class="flex items-center gap-2"><Mail class="size-4" /> cs@seapedia.com</div>
                    </div>
                </div>

                <!-- Navigasi -->
                <div>
                    <h3 class="font-semibold text-foreground mb-4">Navigasi</h3>
                    <ul class="flex flex-col gap-3 text-sm text-muted-foreground">
                        <li><a href="/#how-it-works" class="hover:text-primary transition-colors">Cara Kerja</a></li>
                        <li><a href="/#why-choose-us" class="hover:text-primary transition-colors">Keunggulan</a></li>
                        <li><a href="/catalog" class="hover:text-primary transition-colors">Katalog Produk</a></li>
                        <li><Link :href="reviewsIndex.url()" class="hover:text-primary transition-colors">Semua Ulasan</Link></li>
                    </ul>
                </div>

                <!-- Bantuan -->
                <div>
                    <h3 class="font-semibold text-foreground mb-4">Bantuan</h3>
                    <ul class="flex flex-col gap-3 text-sm text-muted-foreground">
                        <li><a href="#" class="hover:text-primary transition-colors">Pusat Bantuan</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Panduan Penjual</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Panduan Kurir</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Ulasan Aplikasi -->
                <div>
                    <h3 class="font-semibold text-foreground mb-4">Ulasan Aplikasi</h3>
                    <p class="text-sm text-muted-foreground mb-4 leading-relaxed">
                        Bantu kami berkembang dengan membagikan pengalaman Anda menggunakan SEAPEDIA.
                    </p>
                    <Button as-child variant="outline" class="w-full justify-center gap-2 text-primary border-primary/20 hover:bg-primary/5 hover:text-primary transition-colors">
                        <Link :href="reviewsIndex.url()">
                            <MessageSquare class="size-4" />
                            Tulis Ulasan
                        </Link>
                    </Button>
                </div>

            </div>

            <!-- Copyright -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-8 text-xs text-muted-foreground">
                <p>&copy; {{ year }} SEAPEDIA. Hak cipta dilindungi undang-undang.</p>
                <div class="flex gap-4">
                    <span>Versi 1.0</span>
                    <span>Indonesia</span>
                </div>
            </div>
            
        </div>
    </footer>
</template>
