<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CategoryGrid from '@/components/landing/CategoryGrid.vue';
import FeaturedStrip from '@/components/landing/FeaturedStrip.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import PopularStores from '@/components/landing/PopularStores.vue';
import ReviewsSection from '@/components/landing/ReviewsSection.vue';
import RoleCards from '@/components/landing/RoleCards.vue';
import TrustBand from '@/components/landing/TrustBand.vue';

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
    <Head title="Marketplace Kampus" />
    <HeroSection />
    <RoleCards id="roles" />
    <TrustBand />
    <PopularStores id="stores" :stores="popularStores" />
    <CategoryGrid id="categories" />
    <FeaturedStrip id="featured" :products="featured" />
    <ReviewsSection id="reviews" :reviews="reviews" />
</template>
