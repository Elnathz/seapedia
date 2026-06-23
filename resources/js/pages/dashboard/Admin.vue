<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Package, ShoppingBag, Truck, Users } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';

defineProps<{
    stats: {
        totalUsers: number;
        totalSellers: number;
        totalBuyers: number;
        totalDrivers: number;
    };
}>();
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="flex flex-col gap-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                label="Total Pengguna"
                :value="stats.totalUsers"
                :icon="Users"
            />
            <StatCard
                label="Penjual"
                :value="stats.totalSellers"
                :icon="Package"
            />
            <StatCard
                label="Pembeli"
                :value="stats.totalBuyers"
                :icon="ShoppingBag"
            />
            <StatCard label="Kurir" :value="stats.totalDrivers" :icon="Truck" />
        </div>

        <EmptyState
            title="Alat kelola admin segera hadir"
            description="Kelola pengguna, sweep pesanan overdue, dan laporan akan tersedia pada Sprint 5."
        />
    </div>
</template>
