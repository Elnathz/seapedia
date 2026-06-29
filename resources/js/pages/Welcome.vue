<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoryGrid from '@/components/landing/CategoryGrid.vue';
import FeaturedStrip from '@/components/landing/FeaturedStrip.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import PopularStores from '@/components/landing/PopularStores.vue';
import ReviewsSection from '@/components/landing/ReviewsSection.vue';
import RoleCards from '@/components/landing/RoleCards.vue';
import WhyChooseSeapedia from '@/components/landing/WhyChooseSeapedia.vue';
import WhatIsSeapedia from '@/components/landing/WhatIsSeapedia.vue';
import { ArrowRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { register } from '@/routes';

interface Store {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price: number;
    image_path: string | null;
    store: Store;
}

interface Review {
    id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    created_at: string;
}

interface PopularStore {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

defineProps<{
    featured: Product[];
    reviews: Review[];
    popularStores: PopularStore[];
}>();
</script>

<template>
    <Head title="SEAPEDIA Marketplace" />
    <HeroSection />
    <WhatIsSeapedia />
    <WhyChooseSeapedia />
    <RoleCards id="roles" />
    <PopularStores id="stores" :stores="popularStores" />
    <CategoryGrid id="categories" />
    <FeaturedStrip id="featured" :products="featured" />
    <ReviewsSection id="reviews" :reviews="reviews" />
    
    <!-- Final CTA -->
    <div class="bg-card px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-brand px-6 py-12 sm:px-12 sm:py-16 text-center text-white relative shadow-xl">
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
        </div>
    </div>
</template>
