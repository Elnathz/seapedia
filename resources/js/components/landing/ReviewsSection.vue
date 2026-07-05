<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowRight, MessageSquare, Quote, Send, Star } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as reviewsIndex, store as reviewsStore } from '@/routes/reviews';

interface Review {
    id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    created_at: string;
    role?: string;
}

const props = defineProps<{ reviews: Review[] }>();

// The "best" review leads the section: highest rating, then the most
// substantial comment as the tie-breaker.
const bestReview = computed<Review | null>(() => {
    if (!props.reviews.length) {
        return null;
    }

    return [...props.reviews].sort(
        (a, b) => b.rating - a.rating || b.comment.length - a.comment.length,
    )[0];
});

// One marquee "set", padded so even a handful of reviews fills the ribbon; the
// template renders it twice for a seamless -50% loop.
const oneSet = computed<Review[]>(() => {
    const list = props.reviews;

    if (!list.length) {
        return [];
    }

    const out: Review[] = [];

    while (out.length < Math.max(8, list.length)) {
        out.push(...list);
    }

    return out;
});

const marqueeCards = computed(() => [...oneSet.value, ...oneSet.value]);

// A sine wave over the card index gives the ribbon its gentle "melengkung" arch
// as it scrolls — an organic curve rather than a flat row.
function cardStyle(index: number) {
    const y = Math.sin(index * 0.7) * 16;
    const rot = Math.cos(index * 0.7) * 2;

    return {
        transform: `translateY(${y.toFixed(2)}px) rotate(${rot.toFixed(2)}deg)`,
    };
}

const form = ref({ reviewer_name: '', rating: 0, comment: '', role: '' });
const hovered = ref(0);
const submitting = ref(false);
const submitted = ref(false);
const errors = ref<Record<string, string>>({});
const roles = ['Pembeli', 'Penjual', 'Kurir', 'Multi-role'];

const selectedReview = ref<Review | null>(null);
const isModalOpen = ref(false);

function openReview(review: Review) {
    selectedReview.value = review;
    isModalOpen.value = true;
}

function submit() {
    if (form.value.rating === 0) {
        errors.value.rating = 'Pilih rating bintang terlebih dahulu.';

        return;
    }

    submitting.value = true;
    errors.value = {};
    router.post(reviewsStore().url, form.value, {
        onSuccess: () => {
            submitted.value = true;
            form.value = {
                reviewer_name: '',
                rating: 0,
                comment: '',
                role: '',
            };
        },
        onError: (e) => (errors.value = e),
        onFinish: () => (submitting.value = false),
        preserveScroll: true,
    });
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <section class="overflow-hidden bg-card/50 py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-[11px] font-semibold tracking-[0.15em] text-primary uppercase"
                >
                    Ulasan Pengguna
                </span>
                <h2
                    class="mt-4 text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                >
                    Apa kata mereka
                </h2>
                <p class="mt-3 text-lg text-muted-foreground">
                    Pengalaman langsung dari pengguna SEAPEDIA di setiap peran.
                </p>
            </div>

            <!-- Empty state -->
            <div
                v-if="reviews.length === 0"
                class="mx-auto mt-12 flex max-w-md flex-col items-center gap-4 rounded-3xl border border-dashed border-border/60 p-10 text-center"
            >
                <div
                    class="flex size-16 items-center justify-center rounded-2xl bg-muted/50"
                >
                    <MessageSquare class="size-8 text-muted-foreground/50" />
                </div>
                <div>
                    <p class="font-medium text-foreground">Belum ada ulasan</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Jadilah yang pertama membagikan pengalamanmu.
                    </p>
                </div>
            </div>

            <!-- Featured best review -->
            <figure
                v-else-if="bestReview"
                class="reveal-up relative mx-auto mt-12 max-w-3xl rounded-[2rem] border border-primary/15 bg-gradient-to-br from-primary/[0.07] via-card to-brand/[0.05] p-8 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_30px_60px_-40px_rgba(13,148,136,0.4)] sm:p-10"
            >
                <Quote
                    class="absolute top-6 right-8 size-16 text-primary/10"
                    aria-hidden="true"
                />
                <div class="flex items-center gap-1.5">
                    <Star
                        v-for="n in 5"
                        :key="n"
                        class="size-5"
                        :class="
                            n <= bestReview.rating
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-muted-foreground/20'
                        "
                    />
                    <span
                        class="ml-2 rounded-full bg-primary/10 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-primary uppercase"
                    >
                        Ulasan terbaik
                    </span>
                </div>
                <blockquote
                    class="relative mt-5 text-xl leading-relaxed font-medium text-foreground sm:text-2xl"
                >
                    “{{ bestReview.comment }}”
                </blockquote>
                <figcaption
                    class="mt-6 flex items-center gap-3 border-t border-border/50 pt-5"
                >
                    <div
                        class="flex size-11 items-center justify-center rounded-full bg-gradient-to-br from-primary to-brand text-base font-bold text-white uppercase"
                    >
                        {{ bestReview.reviewer_name.charAt(0) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-foreground">
                            {{ bestReview.reviewer_name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            <template v-if="bestReview.role"
                                >{{ bestReview.role }} ·
                            </template>
                            {{ formatDate(bestReview.created_at) }}
                        </p>
                    </div>
                </figcaption>
            </figure>
        </div>

        <!-- Full-bleed marquee: all reviews drift rightward on a gently arched
             ribbon. Continuous ambient motion (a ticker, so linear is right);
             pauses on hover and for reduced-motion users. -->
        <div
            v-if="reviews.length"
            class="marquee group relative mt-14 w-full py-8"
        >
            <div class="marquee-track flex w-max gap-5 pr-5">
                <button
                    v-for="(review, i) in marqueeCards"
                    :key="i"
                    type="button"
                    :style="cardStyle(i)"
                    :aria-hidden="i >= oneSet.length"
                    class="flex w-[280px] shrink-0 flex-col rounded-2xl border border-border/60 bg-card p-5 text-left shadow-sm transition-shadow duration-300 hover:shadow-lg"
                    @click="openReview(review)"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-0.5">
                            <Star
                                v-for="n in 5"
                                :key="n"
                                class="size-3.5"
                                :class="
                                    n <= review.rating
                                        ? 'fill-amber-400 text-amber-400'
                                        : 'text-muted-foreground/20'
                                "
                            />
                        </div>
                        <span
                            v-if="review.role"
                            class="rounded-full bg-primary/10 px-2 py-0.5 text-[9px] font-semibold tracking-wider text-primary uppercase"
                        >
                            {{ review.role }}
                        </span>
                    </div>
                    <p
                        class="mt-3 line-clamp-4 text-sm leading-relaxed text-foreground/90"
                    >
                        {{ review.comment }}
                    </p>
                    <div class="mt-4 flex items-center gap-2 pt-1">
                        <div
                            class="flex size-7 items-center justify-center rounded-full bg-muted text-xs font-bold text-muted-foreground uppercase"
                        >
                            {{ review.reviewer_name.charAt(0) }}
                        </div>
                        <span
                            class="truncate text-xs font-medium text-foreground"
                        >
                            {{ review.reviewer_name }}
                        </span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Submission form + see-all -->
        <div class="mx-auto mt-14 max-w-2xl px-4 sm:px-6 lg:px-8">
            <div
                class="rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-8"
            >
                <div class="mb-6 flex items-center justify-between gap-4">
                    <h3 class="text-xl font-semibold text-foreground">
                        Bagikan pengalamanmu
                    </h3>
                    <Button
                        as-child
                        variant="ghost"
                        size="sm"
                        class="group hidden gap-1 text-primary sm:flex"
                    >
                        <Link :href="reviewsIndex.url()">
                            Semua ulasan
                            <ArrowRight
                                class="size-4 transition-transform group-hover:translate-x-0.5"
                            />
                        </Link>
                    </Button>
                </div>

                <div
                    v-if="submitted"
                    class="mb-6 rounded-xl bg-primary/10 px-5 py-4 text-sm font-medium text-primary"
                >
                    Ulasan berhasil dikirim. Terima kasih atas masukanmu!
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="reviewer_name">Nama Lengkap</Label>
                            <Input
                                id="reviewer_name"
                                v-model="form.reviewer_name"
                                placeholder="John Doe"
                                autocomplete="name"
                                :disabled="submitting"
                                class="rounded-xl bg-muted/50"
                            />
                            <p
                                v-if="errors.reviewer_name"
                                class="text-xs text-destructive"
                            >
                                {{ errors.reviewer_name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label>Rating</Label>
                            <div class="flex gap-1.5">
                                <button
                                    v-for="n in 5"
                                    :key="n"
                                    type="button"
                                    :aria-label="`${n} bintang`"
                                    class="transition-transform duration-150 hover:scale-110 focus:outline-none active:scale-95"
                                    @mouseenter="hovered = n"
                                    @mouseleave="hovered = 0"
                                    @click="form.rating = n"
                                >
                                    <Star
                                        class="size-7"
                                        :class="
                                            n <= (hovered || form.rating)
                                                ? 'fill-amber-400 text-amber-400'
                                                : 'fill-muted-foreground/10 text-muted-foreground/20'
                                        "
                                    />
                                </button>
                            </div>
                            <p
                                v-if="errors.rating"
                                class="text-xs text-destructive"
                            >
                                {{ errors.rating }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Peran Pengguna</Label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="r in roles"
                                :key="r"
                                type="button"
                                class="rounded-xl border px-3.5 py-2 text-xs font-medium transition-colors"
                                :class="
                                    form.role === r
                                        ? 'border-primary bg-primary text-white'
                                        : 'border-border bg-transparent text-muted-foreground hover:bg-muted'
                                "
                                @click="form.role = r"
                            >
                                {{ r }}
                            </button>
                        </div>
                        <p v-if="errors.role" class="text-xs text-destructive">
                            {{ errors.role }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="comment">Komentar</Label>
                        <Textarea
                            id="comment"
                            v-model="form.comment"
                            placeholder="Platform ini sangat membantu karena..."
                            rows="4"
                            :disabled="submitting"
                            class="resize-none rounded-xl bg-muted/50"
                        />
                        <p
                            v-if="errors.comment"
                            class="text-xs text-destructive"
                        >
                            {{ errors.comment }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="h-11 w-full gap-2 rounded-xl"
                        :disabled="submitting"
                    >
                        <Send class="size-4" />
                        {{ submitting ? 'Mengirim...' : 'Kirim Ulasan' }}
                    </Button>
                </form>
            </div>
        </div>

        <!-- Full Review Modal -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent class="rounded-3xl p-6 sm:max-w-[500px]">
                <DialogHeader>
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-full bg-gradient-to-br from-primary to-brand text-base font-bold text-white uppercase"
                            >
                                {{ selectedReview?.reviewer_name?.charAt(0) }}
                            </div>
                            <div>
                                <DialogTitle class="text-base font-semibold">{{
                                    selectedReview?.reviewer_name
                                }}</DialogTitle>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        selectedReview
                                            ? formatDate(
                                                  selectedReview.created_at,
                                              )
                                            : ''
                                    }}
                                </p>
                            </div>
                        </div>
                        <span
                            v-if="selectedReview?.role"
                            class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-semibold tracking-wider text-primary uppercase"
                        >
                            {{ selectedReview.role }}
                        </span>
                    </div>
                </DialogHeader>
                <div class="mb-4 flex items-center gap-1">
                    <Star
                        v-for="n in 5"
                        :key="n"
                        class="size-4"
                        :class="
                            n <= (selectedReview?.rating || 0)
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-muted-foreground/20'
                        "
                    />
                </div>
                <div class="max-h-[60vh] overflow-y-auto pr-2">
                    <p
                        class="text-base leading-relaxed break-words whitespace-pre-wrap text-foreground"
                    >
                        {{ selectedReview?.comment }}
                    </p>
                </div>
            </DialogContent>
        </Dialog>
    </section>
</template>

<style scoped>
.marquee {
    -webkit-mask-image: linear-gradient(
        to right,
        transparent,
        #000 7%,
        #000 93%,
        transparent
    );
    mask-image: linear-gradient(
        to right,
        transparent,
        #000 7%,
        #000 93%,
        transparent
    );
}

.marquee-track {
    /* Start shifted a full set left, drift back to 0 → cards travel rightward.
       The two rendered sets are identical, so the wrap is seamless. */
    animation: marquee-right 48s linear infinite;
    will-change: transform;
}

.marquee:hover .marquee-track {
    animation-play-state: paused;
}

@keyframes marquee-right {
    from {
        transform: translateX(-50%);
    }
    to {
        transform: translateX(0);
    }
}

.reveal-up {
    animation: reveal-up 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes reveal-up {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .marquee-track {
        animation: none;
    }
    .marquee {
        overflow-x: auto;
    }
    .reveal-up {
        animation: none;
    }
}
</style>
