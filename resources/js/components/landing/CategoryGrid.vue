<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CupSoda,
    Package,
    Shirt,
    ShoppingBasket,
    Smartphone,
    UtensilsCrossed,
} from '@lucide/vue';
import type { Component } from 'vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { useCategories } from '@/composables/useCategories';


const { categories } = useCategories();

// Map the seeded Lucide icon names to their components; unknown names fall
// back to a neutral package icon so the grid never renders an empty slot.
const iconMap: Record<string, Component> = {
    UtensilsCrossed,
    CupSoda,
    Smartphone,
    Shirt,
    ShoppingBasket,
    Package,
};

function iconFor(name: string | null): Component {
    return (name && iconMap[name]) || Package;
}

function categoryUrl(slug: string) {
    return `/catalog?category=${slug}`;
}

const sectionRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);
let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        isVisible.value = true;

        return;
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                observer?.disconnect();
            }
        },
        { threshold: 0.15 },
    );

    if (sectionRef.value) {
        observer.observe(sectionRef.value);
    }
});

onUnmounted(() => observer?.disconnect());
</script>

<template>
    <section
        v-if="categories.length"
        ref="sectionRef"
        class="border-b border-border bg-gradient-to-b from-background to-secondary/20 py-14 sm:py-16"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <h2
                        class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Belanja per Kategori
                    </h2>
                    <p class="mt-1.5 text-sm text-muted-foreground sm:text-base">
                        Temukan berbagai produk sesuai kebutuhanmu.
                    </p>
                </div>
                <Link
                    href="/catalog"
                    class="hidden shrink-0 items-center gap-1 text-sm font-medium text-primary hover:underline sm:inline-flex"
                >
                    Lihat semua
                    <ArrowRight class="size-4" />
                </Link>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="(root, index) in categories"
                    :key="root.id"
                    :href="categoryUrl(root.slug)"
                    class="category-card group flex flex-col items-center gap-3 rounded-2xl border border-border bg-card p-5 text-center transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-md"
                    :class="{ visible: isVisible }"
                    :style="{ animationDelay: `${index * 40}ms` }"
                >
                    <span
                        class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary/10 to-brand/15 text-primary transition-transform duration-300 group-hover:scale-105"
                    >
                        <component :is="iconFor(root.icon)" class="size-6" />
                    </span>
                    <span class="min-w-0">
                        <span
                            class="block truncate text-sm font-semibold text-foreground"
                        >
                            {{ root.name }}
                        </span>
                        <span class="mt-0.5 block text-xs text-muted-foreground">
                            {{ root.products_total ?? 0 }} produk
                        </span>
                    </span>
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes category-rise {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.category-card {
    opacity: 0;
    transform: translateY(24px);
}

.category-card.visible {
    animation: category-rise 0.45s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@media (prefers-reduced-motion: reduce) {
    .category-card {
        opacity: 1;
        transform: none;
        animation: none !important;
    }
}
</style>
