<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Star, MessageSquare, Send } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { store as reviewsStore } from '@/routes/reviews';

interface Review {
    id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    created_at: string;
}

defineProps<{ reviews: Review[] }>();

const form = ref({ reviewer_name: '', rating: 0, comment: '' });
const hovered = ref(0);
const submitting = ref(false);
const submitted = ref(false);

const errors = ref<Record<string, string>>({});

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
            form.value = { reviewer_name: '', rating: 0, comment: '' };
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
    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                    Apa kata mereka
                </h2>
                <p class="mt-2 text-muted-foreground">Ulasan dari pengguna SEAPEDIA</p>
            </div>

            <div class="grid gap-8 lg:grid-cols-5">
                <!-- Review list: 3 cols -->
                <div class="lg:col-span-3">
                    <!-- Empty state -->
                    <div v-if="reviews.length === 0" class="flex flex-col items-center gap-3 py-12 text-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                            <MessageSquare class="size-6 text-muted-foreground" />
                        </div>
                        <p class="text-sm text-muted-foreground">Belum ada ulasan. Jadilah yang pertama!</p>
                    </div>

                    <div v-else class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="review in reviews"
                            :key="review.id"
                            class="rounded-xl border border-border bg-card p-5"
                        >
                            <div class="flex items-center gap-1 mb-3">
                                <Star
                                    v-for="n in 5"
                                    :key="n"
                                    class="size-4"
                                    :class="n <= review.rating ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'"
                                />
                            </div>
                            <!-- Comment rendered as plain text — no v-html -->
                            <p class="text-sm text-foreground line-clamp-3">{{ review.comment }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-sm font-medium text-foreground">{{ review.reviewer_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(review.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit form: 2 cols -->
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-border bg-card p-6">
                        <h3 class="mb-4 font-semibold text-foreground">Tinggalkan ulasan</h3>

                        <!-- Success flash -->
                        <div
                            v-if="submitted"
                            class="mb-4 rounded-lg bg-primary/10 px-4 py-3 text-sm text-primary"
                        >
                            Ulasan berhasil dikirim, terima kasih!
                        </div>

                        <form class="space-y-4" @submit.prevent="submit">
                            <div class="space-y-1.5">
                                <Label for="reviewer_name">Nama</Label>
                                <Input
                                    id="reviewer_name"
                                    v-model="form.reviewer_name"
                                    placeholder="Nama kamu"
                                    autocomplete="name"
                                    :disabled="submitting"
                                />
                                <p v-if="errors.reviewer_name" class="text-xs text-destructive">{{ errors.reviewer_name }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label>Rating</Label>
                                <div class="flex gap-1">
                                    <button
                                        v-for="n in 5"
                                        :key="n"
                                        type="button"
                                        :aria-label="`${n} bintang`"
                                        class="transition-transform hover:scale-110"
                                        @mouseenter="hovered = n"
                                        @mouseleave="hovered = 0"
                                        @click="form.rating = n"
                                    >
                                        <Star
                                            class="size-7"
                                            :class="n <= (hovered || form.rating) ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'"
                                        />
                                    </button>
                                </div>
                                <p v-if="errors.rating" class="text-xs text-destructive">{{ errors.rating }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="comment">Komentar</Label>
                                <Textarea
                                    id="comment"
                                    v-model="form.comment"
                                    placeholder="Bagikan pengalamanmu..."
                                    rows="4"
                                    :disabled="submitting"
                                />
                                <p v-if="errors.comment" class="text-xs text-destructive">{{ errors.comment }}</p>
                            </div>

                            <Button type="submit" class="w-full gap-2" :disabled="submitting">
                                <Send class="size-4" />
                                {{ submitting ? 'Mengirim...' : 'Kirim Ulasan' }}
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
