<script setup lang="ts">
import { ReceiptText, ShieldCheck, Truck } from '@lucide/vue';

const items = [
    {
        icon: ReceiptText,
        title: 'PPN 12% Transparan',
        desc: 'Pajak dihitung otomatis dan ditampilkan sebelum checkout — tidak ada biaya tersembunyi.',
    },
    {
        icon: ShieldCheck,
        title: 'Escrow Aman',
        desc: 'Dana pembeli ditahan sampai pesanan selesai. Penjual dibayar setelah kurir mengantar.',
    },
    {
        icon: Truck,
        title: 'Kurir Kampus',
        desc: 'Pengiriman dilakukan oleh mahasiswa kurir terverifikasi di lingkungan kampusmu.',
    },
] as const;
</script>

<template>
    <section class="border-b border-border bg-muted/30">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 divide-y divide-border sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <div
                    v-for="item in items"
                    :key="item.title"
                    class="flex items-start gap-4 px-4 py-6 sm:px-8 sm:py-8"
                >
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <component :is="item.icon" class="size-5" />
                    </div>
                    <div>
                        <p class="font-semibold text-foreground">{{ item.title }}</p>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
