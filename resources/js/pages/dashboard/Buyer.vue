<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, Wallet } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';
import { formatIDR } from '@/lib/utils';
import { index as catalogIndex } from '@/routes/catalog';

defineProps<{
    balance: number;
    activeOrders: number;
}>();
</script>

<template>
    <Head title="Dashboard Pembeli" />

    <div class="flex flex-col gap-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <StatCard
                label="Saldo Wallet"
                :value="formatIDR(balance)"
                :icon="Wallet"
            />
            <StatCard
                label="Pesanan Aktif"
                :value="activeOrders"
                :icon="ClipboardList"
            />
        </div>

        <EmptyState
            v-if="activeOrders === 0"
            title="Belum ada pesanan"
            description="Kamu belum melakukan pesanan apa pun. Mulai jelajahi katalog kampus."
            action-label="Jelajahi Katalog"
            @action="router.visit(catalogIndex.url())"
        />
    </div>
</template>
