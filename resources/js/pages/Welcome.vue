<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import FeaturedStrip from '@/components/landing/FeaturedStrip.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import ReviewsSection from '@/components/landing/ReviewsSection.vue';
import RoleCards from '@/components/landing/RoleCards.vue';
import TrustBand from '@/components/landing/TrustBand.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';

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

defineProps<{
    featured: Product[];
    reviews: Review[];
}>();
</script>

<template>
    <GuestLayout>
        <Head title="SEAPEDIA — Marketplace Kampus" />
        <HeroSection />
        <TrustBand />
        <FeaturedStrip :products="featured" />
        <RoleCards />
        <ReviewsSection :reviews="reviews" />
    </GuestLayout>
</template>
