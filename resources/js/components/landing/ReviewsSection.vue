<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Star, MessageSquare, Send, ArrowRight } from '@lucide/vue';
import { ref } from 'vue';
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
import { store as reviewsStore } from '@/routes/reviews';
import { index as reviewsIndex } from '@/routes/reviews';

interface Review {
    id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    created_at: string;
    role?: string;
}

defineProps<{ reviews: Review[] }>();

const form = ref({ reviewer_name: '', rating: 0, comment: '', role: '' });
const hovered = ref(0);
const submitting = ref(false);
const submitted = ref(false);

const selectedReview = ref<Review | null>(null);
const isModalOpen = ref(false);

const openReview = (review: Review) => {
    selectedReview.value = review;
    isModalOpen.value = true;
};

const errors = ref<Record<string, string>>({});

const roles = ['Pembeli', 'Penjual', 'Kurir', 'Multi-role'];

const submit = () => {
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
        onError: (e) => {
            errors.value = e;
        },
        onFinish: () => {
            submitting.value = false;
        },
        preserveScroll: true,
    });
};

const formatDate = (iso: string) => {
    return new Date(iso).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <section class="bg-card/50 py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="mb-16 flex flex-col items-center justify-between gap-6 sm:flex-row sm:items-end"
            >
                <div>
                    <h2
                        class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                    >
                        Apa kata mereka
                    </h2>
                    <p class="mt-3 max-w-2xl text-lg text-muted-foreground">
                        Pengalaman langsung dari pengguna SEAPEDIA yang telah
                        merasakan kemudahan ekosistem multi-peran.
                    </p>
                </div>
                <Button
                    as-child
                    variant="outline"
                    class="group hidden border-primary/20 text-primary hover:bg-primary/5 sm:flex"
                >
                    <Link :href="reviewsIndex.url()">
                        Lihat Semua Ulasan
                        <ArrowRight
                            class="ml-2 size-4 transition-transform group-hover:translate-x-1"
                        />
                    </Link>
                </Button>
            </div>

            <!-- 3 Column Grid -->
            <div
                class="grid grid-cols-1 items-start gap-8 md:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Col 1: Form -->
                <div class="lg:col-span-1">
                    <div
                        class="rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-8"
                    >
                        <h3 class="mb-6 text-xl font-semibold text-foreground">
                            Bagikan Pengalamanmu
                        </h3>

                        <div
                            v-if="submitted"
                            class="mb-6 rounded-xl bg-primary/10 px-5 py-4 text-sm font-medium text-primary"
                        >
                            Ulasan berhasil dikirim. Terima kasih atas masukan
                            Anda!
                        </div>

                        <form class="space-y-5" @submit.prevent="submit">
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
                                <Label>Peran Pengguna</Label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="r in roles"
                                        :key="r"
                                        type="button"
                                        @click="form.role = r"
                                        class="rounded-xl border px-3 py-2 text-xs font-medium transition-colors"
                                        :class="
                                            form.role === r
                                                ? 'border-primary bg-primary text-white'
                                                : 'border-border bg-transparent text-muted-foreground hover:bg-muted'
                                        "
                                    >
                                        {{ r }}
                                    </button>
                                </div>
                                <p
                                    v-if="errors.role"
                                    class="text-xs text-destructive"
                                >
                                    {{ errors.role }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label>Rating</Label>
                                <div class="flex gap-2">
                                    <button
                                        v-for="n in 5"
                                        :key="n"
                                        type="button"
                                        :aria-label="`${n} bintang`"
                                        class="transition-transform hover:scale-110 focus:outline-none"
                                        @mouseenter="hovered = n"
                                        @mouseleave="hovered = 0"
                                        @click="form.rating = n"
                                    >
                                        <Star
                                            class="size-8"
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
                                {{
                                    submitting ? 'Mengirim...' : 'Kirim Ulasan'
                                }}
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Col 2 & 3: Review Cards -->
                <div class="lg:col-span-2">
                    <div
                        v-if="reviews.length === 0"
                        class="flex h-full min-h-[300px] flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-border/60 bg-transparent p-8 text-center"
                    >
                        <div
                            class="flex size-16 items-center justify-center rounded-2xl bg-muted/50"
                        >
                            <MessageSquare
                                class="size-8 text-muted-foreground/50"
                            />
                        </div>
                        <div>
                            <p class="font-medium text-foreground">
                                Belum ada ulasan
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Jadilah yang pertama membagikan pengalaman Anda.
                            </p>
                        </div>
                    </div>

                    <!-- We use columns for masonry-like feel, or standard grid with items-start to prevent stretching -->
                    <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div
                            v-for="review in reviews"
                            :key="review.id"
                            class="flex h-full flex-col rounded-3xl border border-border/50 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div class="mb-5 flex items-center justify-between">
                                <div class="flex items-center gap-0.5">
                                    <Star
                                        v-for="n in 5"
                                        :key="n"
                                        class="size-4"
                                        :class="
                                            n <= review.rating
                                                ? 'fill-amber-400 text-amber-400'
                                                : 'text-muted-foreground/20'
                                        "
                                    />
                                </div>
                                <span
                                    v-if="review.role"
                                    class="rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-semibold tracking-wider text-primary uppercase"
                                >
                                    {{ review.role }}
                                </span>
                            </div>

                            <p
                                class="mb-6 line-clamp-5 text-base leading-relaxed break-words text-foreground"
                            >
                                {{ review.comment }}
                            </p>

                            <button
                                v-if="review.comment.length > 150"
                                @click="openReview(review)"
                                class="-mt-3 mb-6 self-start text-xs font-semibold text-primary hover:underline"
                            >
                                Baca selengkapnya
                            </button>

                            <div
                                class="mt-auto flex items-center justify-between border-t border-border/50 pt-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-foreground"
                                    >
                                        {{ review.reviewer_name }}
                                    </p>
                                </div>
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    {{ formatDate(review.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile See All Button -->
                <div class="mt-4 sm:hidden">
                    <Button
                        as-child
                        variant="outline"
                        class="w-full border-primary/20 text-primary"
                    >
                        <Link :href="reviewsIndex.url()">
                            Lihat Semua Ulasan
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Full Review Modal -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent class="rounded-3xl p-6 sm:max-w-[500px]">
                <DialogHeader>
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-full border border-primary/20 bg-primary/10 text-base font-bold text-primary uppercase"
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
