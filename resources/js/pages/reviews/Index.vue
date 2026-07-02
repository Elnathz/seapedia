<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Star,
    MessageSquare,
    ChevronLeft,
    ChevronRight,
    Send,
    Search,
} from '@lucide/vue';
import { ref, watch } from 'vue';
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
import { store } from '@/routes/reviews';

interface ReviewItem {
    id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    role?: string;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedReviews {
    data: ReviewItem[];
    prev_page_url: string | null;
    next_page_url: string | null;
    current_page: number;
    last_page: number;
    links: PaginationLink[];
    total: number;
}

const props = defineProps<{
    reviews: PaginatedReviews;
    filters: {
        role?: string;
        rating?: string;
    };
}>();

const hovered = ref(0);
const submitting = ref(false);
const submitted = ref(false);
const roles = ['Pembeli', 'Penjual', 'Kurir', 'Multi-role'];

const selectedReview = ref<ReviewItem | null>(null);
const isModalOpen = ref(false);

const openReview = (review: ReviewItem) => {
    selectedReview.value = review;
    isModalOpen.value = true;
};

// Filter State
const selectedRole = ref(props.filters.role || '');
const selectedRating = ref(props.filters.rating || '');

// Form State (using store.form() for Inertia, but managing it simpler like in ReviewsSection is fine too,
// wait, the template uses v-bind="store.form()". Let's stick to simple router.post to match the Landing Page for consistency and easier reset.)
const form = ref({ reviewer_name: '', rating: 0, comment: '', role: '' });
const errors = ref<Record<string, string>>({});

const submit = () => {
    if (form.value.rating === 0) {
        errors.value.rating = 'Pilih rating bintang terlebih dahulu.';

        return;
    }

    submitting.value = true;
    errors.value = {};
    router.post(store().url, form.value, {
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
        month: 'long',
        year: 'numeric',
    });
};

// Filter actions
const applyFilters = () => {
    router.get(
        window.location.pathname,
        {
            role: selectedRole.value,
            rating: selectedRating.value,
        },
        { preserveState: true, preserveScroll: true },
    );
};

watch([selectedRole, selectedRating], () => {
    applyFilters();
});

const resetFilters = () => {
    selectedRole.value = '';
    selectedRating.value = '';
};
</script>

<template>
    <Head title="Ulasan Pengguna" />

    <div class="min-h-screen bg-[#F8FAFC]">
        <!-- Hero Header -->
        <div
            class="relative overflow-hidden border-b border-border bg-card py-16 sm:py-24"
        >
            <div
                class="bg-grid-slate-100/[0.04] absolute inset-0 bg-[size:20px_20px]"
            ></div>
            <div
                class="relative z-10 mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8"
            >
                <span
                    class="mb-6 inline-block rounded-full bg-primary/10 px-4 py-1.5 text-sm font-semibold tracking-wider text-primary"
                >
                    KOMUNITAS SEAPEDIA
                </span>
                <h1
                    class="mx-auto max-w-3xl text-4xl leading-tight font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl"
                >
                    Platform Pilihan untuk Berbagai Peran
                </h1>
                <p
                    class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-muted-foreground"
                >
                    Ribuan pengguna telah membuktikan kemudahan bertransaksi,
                    berjualan, dan mengantar pesanan dalam satu ekosistem
                    terpadu.
                </p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-12">
                <!-- Sidebar: Form & Filters -->
                <div class="space-y-8 lg:sticky lg:top-24 lg:col-span-4">
                    <!-- Submit Review Card -->
                    <div
                        class="rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-8"
                    >
                        <h3 class="mb-6 text-xl font-semibold text-foreground">
                            Tinggalkan Ulasan
                        </h3>

                        <div
                            v-if="submitted"
                            class="mb-6 rounded-xl bg-primary/10 px-5 py-4 text-sm font-medium text-primary"
                        >
                            Ulasan berhasil dikirim. Terima kasih!
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
                                    placeholder="Bagikan pengalamanmu..."
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
                                class="mt-2 h-11 w-full gap-2 rounded-xl"
                                :disabled="submitting"
                            >
                                <Send class="size-4" />
                                {{
                                    submitting ? 'Mengirim...' : 'Kirim Ulasan'
                                }}
                            </Button>
                        </form>
                    </div>

                    <!-- Filters -->
                    <div
                        class="rounded-3xl border border-border bg-card p-6 shadow-sm"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h3
                                class="flex items-center gap-2 text-base font-semibold text-foreground"
                            >
                                <Search class="size-4" />
                                Filter Ulasan
                            </h3>
                            <button
                                v-if="selectedRole || selectedRating"
                                @click="resetFilters"
                                class="text-xs font-medium text-primary hover:underline"
                            >
                                Reset
                            </button>
                        </div>

                        <div class="space-y-5">
                            <div class="space-y-2">
                                <Label
                                    class="text-xs tracking-wider text-muted-foreground uppercase"
                                    >Berdasarkan Peran</Label
                                >
                                <select
                                    v-model="selectedRole"
                                    class="flex h-10 w-full items-center justify-between rounded-xl border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Semua Peran</option>
                                    <option
                                        v-for="r in roles"
                                        :key="r"
                                        :value="r"
                                    >
                                        {{ r }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    class="text-xs tracking-wider text-muted-foreground uppercase"
                                    >Berdasarkan Rating</Label
                                >
                                <select
                                    v-model="selectedRating"
                                    class="flex h-10 w-full items-center justify-between rounded-xl border border-input bg-transparent px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Semua Rating</option>
                                    <option value="5">
                                        5 Bintang (Sangat Baik)
                                    </option>
                                    <option value="4">4 Bintang (Baik)</option>
                                    <option value="3">3 Bintang (Cukup)</option>
                                    <option value="2">2 Bintang (Buruk)</option>
                                    <option value="1">
                                        1 Bintang (Sangat Buruk)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content: Reviews List -->
                <div class="lg:col-span-8">
                    <!-- Empty State -->
                    <div
                        v-if="reviews.data.length === 0"
                        class="flex h-[400px] flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-border/60 bg-transparent p-8 text-center"
                    >
                        <div
                            class="flex size-20 items-center justify-center rounded-full border border-border bg-card shadow-sm"
                        >
                            <MessageSquare
                                class="size-10 text-muted-foreground/30"
                            />
                        </div>
                        <div>
                            <p class="text-lg font-medium text-foreground">
                                Tidak ada ulasan ditemukan
                            </p>
                            <p
                                class="mx-auto mt-2 max-w-sm text-sm text-muted-foreground"
                            >
                                Coba ubah filter atau jadilah yang pertama
                                memberikan ulasan untuk kategori ini.
                            </p>
                        </div>
                        <Button
                            v-if="selectedRole || selectedRating"
                            variant="outline"
                            @click="resetFilters"
                            class="mt-4 rounded-full"
                        >
                            Hapus Filter
                        </Button>
                    </div>

                    <!-- Reviews Grid -->
                    <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div
                            v-for="review in reviews.data"
                            :key="review.id"
                            class="group flex h-full flex-col rounded-3xl border border-border/50 bg-card p-6 shadow-sm transition-all hover:border-border hover:shadow-md sm:p-8"
                        >
                            <div class="mb-6 flex items-center justify-between">
                                <div class="flex items-center gap-1">
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
                                    class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-semibold tracking-wider text-primary uppercase"
                                >
                                    {{ review.role }}
                                </span>
                            </div>

                            <p
                                class="mb-6 line-clamp-5 text-base leading-relaxed font-medium break-words text-foreground"
                            >
                                "{{ review.comment }}"
                            </p>

                            <button
                                v-if="review.comment.length > 150"
                                @click="openReview(review)"
                                class="-mt-3 mb-6 self-start text-xs font-semibold text-primary hover:underline"
                            >
                                Baca selengkapnya
                            </button>

                            <div
                                class="mt-auto flex items-center justify-between border-t border-border/60 pt-5"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-full border border-primary/10 bg-primary/5 text-xs font-bold text-primary uppercase"
                                    >
                                        {{ review.reviewer_name.charAt(0) }}
                                    </div>
                                    <p
                                        class="text-sm font-semibold text-foreground"
                                    >
                                        {{ review.reviewer_name }}
                                    </p>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDate(review.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Pagination -->
                    <div
                        v-if="reviews.last_page > 1"
                        class="mt-12 flex items-center justify-center gap-2"
                    >
                        <!-- Prev -->
                        <Button
                            as-child
                            variant="outline"
                            size="icon"
                            class="size-10 rounded-full"
                            :class="
                                !reviews.prev_page_url &&
                                'pointer-events-none opacity-50'
                            "
                        >
                            <Link
                                :href="reviews.prev_page_url || '#'"
                                preserve-scroll
                            >
                                <ChevronLeft class="size-4" />
                            </Link>
                        </Button>

                        <!-- Page Numbers (Simple approach using links from paginator) -->
                        <template
                            v-for="link in reviews.links"
                            :key="link.label"
                        >
                            <Button
                                v-if="
                                    !link.label.includes('Previous') &&
                                    !link.label.includes('Next')
                                "
                                as-child
                                :variant="link.active ? 'default' : 'outline'"
                                class="size-10 rounded-full"
                                :class="
                                    !link.url &&
                                    'pointer-events-none opacity-50'
                                "
                            >
                                <Link :href="link.url || '#'" preserve-scroll>
                                    <span v-html="link.label"></span>
                                </Link>
                            </Button>
                        </template>

                        <!-- Next -->
                        <Button
                            as-child
                            variant="outline"
                            size="icon"
                            class="size-10 rounded-full"
                            :class="
                                !reviews.next_page_url &&
                                'pointer-events-none opacity-50'
                            "
                        >
                            <Link
                                :href="reviews.next_page_url || '#'"
                                preserve-scroll
                            >
                                <ChevronRight class="size-4" />
                            </Link>
                        </Button>
                    </div>

                    <p
                        v-if="reviews.total > 0"
                        class="mt-6 text-center text-sm text-muted-foreground"
                    >
                        Menampilkan {{ reviews.data.length }} dari
                        {{ reviews.total }} ulasan
                    </p>
                </div>
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
                                        ? formatDate(selectedReview.created_at)
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
</template>
