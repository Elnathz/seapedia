<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { bannerSrc  } from '@/types/banner';
import type {BannerNode} from '@/types/banner';

const props = defineProps<{ slides: BannerNode[] }>();
const active = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

function go(i: number) {
    active.value = (i + props.slides.length) % props.slides.length;
}

function pause() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

function resume() {
    if (props.slides.length > 1 && !timer) {
        timer = setInterval(() => go(active.value + 1), 5000);
    }
}

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    resume();
});

onUnmounted(() => {
    pause();
});
</script>

<template>
    <div
        class="relative overflow-hidden rounded-xl border border-border h-full min-h-0 min-w-0"
        @mouseenter="pause"
        @mouseleave="resume"
    >
        <div
            class="flex transition-transform duration-500 ease-out h-full min-h-0 min-w-0 motion-reduce:transition-none"
            :style="{ transform: `translateX(-${active * 100}%)` }"
        >
            <Link
                v-for="slide in slides"
                :key="slide.id"
                :href="slide.cta_url ?? '/catalog'"
                class="block w-full h-full shrink-0 relative"
            >
                <img
                    :src="bannerSrc(slide.image_path)"
                    :alt="slide.title"
                    class="h-full w-full object-cover lg:aspect-auto aspect-[5/2]"
                />
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                    <p class="text-xl font-bold text-white">{{ slide.title }}</p>
                    <p v-if="slide.cta_label" class="mt-1 text-sm text-white/80">{{ slide.cta_label }}</p>
                </div>
            </Link>
        </div>

        <template v-if="slides.length > 1">
            <button
                type="button"
                class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/80 p-1.5 shadow hover:bg-white"
                aria-label="Sebelumnya"
                @click="go(active - 1)"
            >
                <ChevronLeft class="size-4" />
            </button>
            <button
                type="button"
                class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/80 p-1.5 shadow hover:bg-white"
                aria-label="Berikutnya"
                @click="go(active + 1)"
            >
                <ChevronRight class="size-4" />
            </button>
            <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-1.5">
                <button
                    v-for="(s, i) in slides"
                    :key="s.id"
                    type="button"
                    class="size-2 rounded-full transition-colors"
                    :class="i === active ? 'bg-white' : 'bg-white/50'"
                    :aria-label="`Slide ${i + 1}`"
                    @click="go(i)"
                />
            </div>
        </template>
    </div>
</template>
