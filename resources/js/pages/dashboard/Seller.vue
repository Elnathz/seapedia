<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Package, Wallet } from '@lucide/vue';
import { computed } from 'vue';
import SellerOnboardingCard from '@/components/SellerOnboardingCard.vue';
import StatCard from '@/components/StatCard.vue';
import { formatIDR } from '@/lib/utils';

interface OnboardingState {
    has_store: boolean;
    has_address: boolean;
    has_logo: boolean;
    has_product: boolean;
}

const props = defineProps<{
    balance: number;
    activeProducts: number;
    onboarding: OnboardingState;
}>();

const onboardingComplete = computed(() =>
    Object.values(props.onboarding).every(Boolean),
);
</script>

<template>
    <Head title="Dashboard Penjual" />

    <div class="flex flex-col gap-6">
        <SellerOnboardingCard
            v-if="!onboardingComplete"
            :onboarding="onboarding"
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <StatCard
                label="Saldo Wallet"
                :value="formatIDR(balance)"
                :icon="Wallet"
            />
            <StatCard
                label="Produk Aktif"
                :value="activeProducts"
                :icon="Package"
            />
        </div>
    </div>
</template>
