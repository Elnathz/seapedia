<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Mail, Phone, MapPin } from '@lucide/vue';
import Logo from '@/components/brand/Logo.vue';
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
</script>

<template>
    <footer class="bg-card pt-20 border-t border-border">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            <!-- Final CTA -->
            <div class="mb-16 overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-brand px-6 py-12 sm:px-12 sm:py-16 text-center text-white relative shadow-xl">
                <!-- Decorative elements -->
                <div class="absolute -top-24 -left-24 size-48 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-24 -right-24 size-48 rounded-full bg-white/10 blur-2xl"></div>
                
                <div class="relative z-10 mx-auto max-w-2xl">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Siap memulai?</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-white/90">
                        Daftar sekarang dan rasakan marketplace multi‑role.
                    </p>
                    <div class="mt-8 flex justify-center">
                        <Button as-child size="lg" class="bg-white text-primary hover:bg-white/90 gap-2 group transition-transform hover:scale-105">
                            <Link :href="register()">
                                Daftar Gratis
                                <ArrowRight class="size-4 transition-transform group-hover:translate-x-1" />
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

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

                <!-- Navigation -->
                <div>
                    <h3 class="font-semibold text-foreground mb-4">Navigasi</h3>
                    <ul class="flex flex-col gap-3 text-sm text-muted-foreground">
                        <li><a href="#how-it-works" class="hover:text-primary transition-colors">Cara Kerja</a></li>
                        <li><a href="#why-choose-us" class="hover:text-primary transition-colors">Keunggulan</a></li>
                        <li><a href="#ecosystem" class="hover:text-primary transition-colors">Toko Populer</a></li>
                        <li><a href="#ecosystem" class="hover:text-primary transition-colors">Produk Pilihan</a></li>
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

                <!-- Legal -->
                <div>
                    <h3 class="font-semibold text-foreground mb-4">Legal</h3>
                    <ul class="flex flex-col gap-3 text-sm text-muted-foreground">
                        <li><a href="#" class="hover:text-primary transition-colors">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Kebijakan Pengembalian</a></li>
                    </ul>
                </div>

            </div>

            <!-- Copyright -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-8 text-xs text-muted-foreground">
                <p>&copy; {{ new Date().getFullYear() }} SEAPEDIA. Hak cipta dilindungi undang-undang.</p>
                <div class="flex gap-4">
                    <span>Versi 1.0</span>
                    <span>Indonesia</span>
                </div>
            </div>
            
        </div>
    </footer>
</template>
