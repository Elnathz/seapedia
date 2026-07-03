<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Check,
    ImagePlus,
    MapPin,
    Package,
    Store,
} from '@lucide/vue';
import { computed } from 'vue';
import type { FunctionalComponent } from 'vue';
import SellerProductController from '@/actions/App/Http/Controllers/Web/SellerProductController';
import SellerStoreController from '@/actions/App/Http/Controllers/Web/SellerStoreController';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';

interface OnboardingState {
    has_store: boolean;
    has_address: boolean;
    has_logo: boolean;
    has_product: boolean;
}

interface Step {
    done: boolean;
    label: string;
    description: string;
    href: string;
    icon: FunctionalComponent;
}

const props = defineProps<{ onboarding: OnboardingState }>();

const storeHref = SellerStoreController.show().url;
const productHref = SellerProductController.create().url;

const steps = computed<Step[]>(() => [
    {
        done: props.onboarding.has_store,
        label: 'Buat profil toko',
        description: 'Nama dan deskripsi toko Anda.',
        href: storeHref,
        icon: Store,
    },
    {
        done: props.onboarding.has_address,
        label: 'Lengkapi alamat & titik peta',
        description: 'Alamat asal pengiriman untuk hitung ongkir.',
        href: storeHref,
        icon: MapPin,
    },
    {
        done: props.onboarding.has_logo,
        label: 'Unggah logo toko',
        description: 'Rasio 1:1 agar tampil rapi di etalase.',
        href: storeHref,
        icon: ImagePlus,
    },
    {
        done: props.onboarding.has_product,
        label: 'Tambah produk pertama',
        description: 'Mulai berjualan dengan satu produk.',
        href: productHref,
        icon: Package,
    },
]);

const doneCount = computed(
    () => steps.value.filter((step) => step.done).length,
);
const percent = computed(() =>
    Math.round((doneCount.value / steps.value.length) * 100),
);
const nextStep = computed(() => steps.value.find((step) => !step.done) ?? null);
</script>

<template>
    <Card
        class="overflow-hidden border-primary/20 bg-gradient-to-br from-primary/5 to-transparent"
    >
        <CardContent class="space-y-5 pt-6">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold tracking-tight">
                        Lengkapi toko Anda
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Selesaikan langkah berikut agar toko siap berjualan.
                    </p>
                </div>
                <span
                    class="shrink-0 rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary tabular-nums"
                >
                    {{ doneCount }}/{{ steps.length }}
                </span>
            </div>

            <Progress :model-value="percent" />

            <ul class="space-y-2">
                <li
                    v-for="step in steps"
                    :key="step.label"
                    class="flex items-center gap-3 rounded-xl border p-3 transition-colors"
                    :class="
                        step.done
                            ? 'border-primary/30 bg-primary/5'
                            : 'border-border'
                    "
                >
                    <span
                        class="flex size-9 shrink-0 items-center justify-center rounded-full"
                        :class="
                            step.done
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground'
                        "
                    >
                        <Check v-if="step.done" class="size-4" />
                        <component :is="step.icon" v-else class="size-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-medium"
                            :class="{
                                'text-muted-foreground line-through': step.done,
                            }"
                        >
                            {{ step.label }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ step.description }}
                        </p>
                    </div>
                    <Link
                        v-if="!step.done"
                        :href="step.href"
                        class="shrink-0 text-muted-foreground transition-colors hover:text-primary"
                        :aria-label="step.label"
                    >
                        <ArrowRight class="size-4" />
                    </Link>
                </li>
            </ul>

            <Button v-if="nextStep" as-child class="w-full sm:w-auto">
                <Link :href="nextStep.href">
                    {{ nextStep.label }}
                    <ArrowRight class="size-4" />
                </Link>
            </Button>
        </CardContent>
    </Card>
</template>
