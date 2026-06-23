<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Truck } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import StatCard from '@/components/StatCard.vue';

defineProps<{
    activeDeliveries: number;
}>();
</script>

<template>
    <Head title="Dashboard Kurir" />

    <div class="flex flex-col gap-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <StatCard
                label="Pengantaran Aktif"
                :value="activeDeliveries"
                :icon="Truck"
            />
        </div>

        <EmptyState
            v-if="activeDeliveries === 0"
            title="Belum ada pengantaran"
            description="Tugas pengantaran akan muncul di sini setelah penjual mulai memproses pesanan."
        />
    </div>
</template>
