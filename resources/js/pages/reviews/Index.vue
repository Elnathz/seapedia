<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { store } from '@/routes/reviews';

interface ReviewItem {
    id: number;
    reviewer_name: string;
    rating: number;
    comment: string;
    created_at: string;
}

interface PaginatedReviews {
    data: ReviewItem[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

defineProps<{
    reviews: PaginatedReviews;
}>();
</script>

<template>
    <Head title="Reviews" />

    <div class="p-6 lg:p-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-8">
            <h1 class="text-2xl font-semibold">
                What people say about SEAPEDIA
            </h1>

            <Card>
                <CardHeader>
                    <CardTitle>Leave a review</CardTitle>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['comment']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-4"
                    >
                        <div class="grid gap-2">
                            <Label for="reviewer_name">Your name</Label>
                            <Input
                                id="reviewer_name"
                                name="reviewer_name"
                                type="text"
                                required
                                placeholder="Jane Doe"
                            />
                            <InputError :message="errors.reviewer_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Rating</Label>
                            <div class="flex items-center gap-4">
                                <Label
                                    v-for="star in 5"
                                    :key="star"
                                    :for="`rating-${star}`"
                                    class="flex items-center gap-1 font-normal"
                                >
                                    <input
                                        :id="`rating-${star}`"
                                        type="radio"
                                        name="rating"
                                        :value="star"
                                        required
                                        class="accent-primary"
                                    />
                                    {{ star }}
                                </Label>
                            </div>
                            <InputError :message="errors.rating" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="comment">Comment</Label>
                            <Textarea
                                id="comment"
                                name="comment"
                                required
                                placeholder="Tell us about your experience"
                            />
                            <InputError :message="errors.comment" />
                        </div>

                        <Button
                            type="submit"
                            class="w-fit"
                            :disabled="processing"
                        >
                            <Spinner v-if="processing" />
                            Submit review
                        </Button>
                    </Form>
                </CardContent>
            </Card>

            <div class="flex flex-col gap-4">
                <h2 class="text-lg font-medium">Recent reviews</h2>

                <p
                    v-if="reviews.data.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    No reviews yet. Be the first to share your experience.
                </p>

                <Card v-for="review in reviews.data" :key="review.id">
                    <CardContent class="flex flex-col gap-1 py-4">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">{{
                                review.reviewer_name
                            }}</span>
                            <span class="text-sm text-muted-foreground"
                                >{{ review.rating }} / 5</span
                            >
                        </div>
                        <p class="text-sm">{{ review.comment }}</p>
                    </CardContent>
                </Card>

                <div class="flex justify-center gap-2">
                    <Button
                        v-if="reviews.prev_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="reviews.prev_page_url">Previous</Link>
                    </Button>
                    <Button
                        v-if="reviews.next_page_url"
                        as-child
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="reviews.next_page_url">Next</Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
