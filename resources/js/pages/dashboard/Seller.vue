<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Package, Wallet } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';
import { formatIDR } from '@/lib/utils';

defineProps<{
    balance: number;
    activeProducts: number;
}>();
</script>

<template>
    <Head title="Dashboard Penjual" />

    <div class="flex flex-col gap-6">
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

        <EmptyState
            v-if="activeProducts === 0"
            title="Belum ada produk"
            description="Fitur kelola toko dan produk akan tersedia pada update berikutnya."
        />
    </div>
</template>
