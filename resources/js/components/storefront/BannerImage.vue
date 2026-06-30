<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { bannerSrc  } from '@/types/banner';
import type {BannerNode} from '@/types/banner';

defineProps<{ banner: BannerNode }>();
</script>

<template>
    <Link
        :href="banner.cta_url ?? '/catalog'"
        class="group relative block overflow-hidden rounded-xl border border-border h-full"
    >
        <img
            :src="bannerSrc(banner.image_path)"
            :alt="banner.title"
            loading="lazy"
            class="h-full w-full object-cover lg:aspect-auto aspect-[3/2] transition-transform duration-300 group-hover:scale-105"
        />
    </Link>
</template>
