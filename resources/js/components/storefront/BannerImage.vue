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
        <span
            v-if="banner.badge_label"
            class="absolute top-2 left-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground"
        >
            {{ banner.badge_label }}
        </span>
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/50 to-transparent p-3">
            <p class="text-sm font-medium text-white">{{ banner.title }}</p>
        </div>
    </Link>
</template>
