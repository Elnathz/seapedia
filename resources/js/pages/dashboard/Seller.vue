<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Clock,
    Coins,
    Package,
    PackageCheck,
    Truck,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import SellerOrderController from '@/actions/App/Http/Controllers/Web/SellerOrderController';
import SellerOnboardingCard from '@/components/SellerOnboardingCard.vue';
import StatCard from '@/components/StatCard.vue';
import { Card, CardContent } from '@/components/ui/card';
import { formatIDR } from '@/lib/utils';

interface OnboardingState {
    has_store: boolean;
    has_address: boolean;
    has_logo: boolean;
    has_product: boolean;
}

interface SellerStats {
    awaiting_process: number;
    awaiting_pickup: number;
    in_delivery: number;
    completed: number;
    revenue: number;
}

const props = defineProps<{
    balance: number;
    activeProducts: number;
    onboarding: OnboardingState;
    stats: SellerStats;
}>();

const onboardingComplete = computed(() =>
    Object.values(props.onboarding).every(Boolean),
);

const ordersHref = SellerOrderController.index().url;

const pipeline = computed(() => [
    {
        key: 'sedang_dikemas',
        label: 'Perlu Diproses',
        count: props.stats.awaiting_process,
        icon: PackageCheck,
        highlight: props.stats.awaiting_process > 0,
    },
    {
        key: 'menunggu_pengirim',
        label: 'Menunggu Pengirim',
        count: props.stats.awaiting_pickup,
        icon: Clock,
        highlight: false,
    },
    {
        key: 'sedang_dikirim',
        label: 'Sedang Dikirim',
        count: props.stats.in_delivery,
        icon: Truck,
        highlight: false,
    },
    {
        key: 'pesanan_selesai',
        label: 'Selesai',
        count: props.stats.completed,
        icon: CheckCircle2,
        highlight: false,
    },
]);
</script>

<template>
    <Head title="Dashboard Penjual" />

    <div class="flex flex-col gap-6">
        <SellerOnboardingCard
            v-if="!onboardingComplete"
            :onboarding="onboarding"
        />

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard
                label="Saldo Wallet"
                :value="formatIDR(balance)"
                :icon="Wallet"
            />
            <StatCard
                label="Pendapatan"
                :value="formatIDR(stats.revenue)"
                :icon="Coins"
            />
            <StatCard
                label="Produk Aktif"
                :value="activeProducts"
                :icon="Package"
            />
        </div>

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold tracking-tight">
                    Alur Pesanan
                </h2>
                <Link
                    :href="ordersHref"
                    class="inline-flex items-center gap-1 text-sm text-primary transition-colors hover:text-primary/80"
                >
                    Semua pesanan
                    <ArrowRight class="size-4" />
                </Link>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <component
                    :is="stage.highlight ? Link : 'div'"
                    v-for="stage in pipeline"
                    :key="stage.key"
                    :href="
                        stage.highlight
                            ? `${ordersHref}?status=${stage.key}`
                            : undefined
                    "
                    class="block"
                >
                    <Card
                        class="h-full transition-colors"
                        :class="
                            stage.highlight
                                ? 'border-primary/40 bg-gradient-to-br from-primary/10 to-transparent hover:border-primary'
                                : ''
                        "
                    >
                        <CardContent class="flex flex-col gap-2 pt-6">
                            <div class="flex items-center justify-between">
                                <span
                                    class="flex size-9 items-center justify-center rounded-full"
                                    :class="
                                        stage.highlight
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    <component
                                        :is="stage.icon"
                                        class="size-4"
                                    />
                                </span>
                                <span
                                    v-if="stage.highlight && stage.count > 0"
                                    class="relative flex size-2.5"
                                >
                                    <span
                                        class="absolute inline-flex size-full animate-ping rounded-full bg-primary/60"
                                    />
                                    <span
                                        class="relative inline-flex size-2.5 rounded-full bg-primary"
                                    />
                                </span>
                            </div>
                            <p
                                class="text-2xl font-bold tabular-nums"
                                :class="{ 'text-primary': stage.highlight }"
                            >
                                {{ stage.count }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ stage.label }}
                            </p>
                        </CardContent>
                    </Card>
                </component>
            </div>
        </section>
    </div>
</template>
