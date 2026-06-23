<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingBag, ShieldCheck, Truck, Wallet } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { register } from '@/routes';
import { index as catalogIndex } from '@/routes/catalog';

const features = [
    {
        icon: ShoppingBag,
        title: 'Belanja dari Sesama Mahasiswa',
        description:
            'Jelajahi produk dan jasa dari toko-toko di lingkungan kampusmu sendiri.',
    },
    {
        icon: Wallet,
        title: 'Satu Wallet untuk Semua Peran',
        description:
            'Saldo yang sama dipakai saat kamu jadi pembeli, penjual, maupun kurir.',
    },
    {
        icon: Truck,
        title: 'Pengiriman oleh Kurir Kampus',
        description:
            'Pesanan diantar langsung oleh kurir mahasiswa yang terdaftar di SEAPEDIA.',
    },
    {
        icon: ShieldCheck,
        title: 'Transaksi Lebih Aman',
        description:
            'Setiap peran diverifikasi dan setiap aksi tercatat — bukan sekadar grup chat.',
    },
];
</script>

<template>
    <Head title="SEAPEDIA — Marketplace Kampus" />

    <section
        class="border-b border-border bg-gradient-to-b from-accent/40 to-background"
    >
        <div
            class="mx-auto flex max-w-6xl flex-col items-center gap-6 px-4 py-16 text-center sm:px-6 lg:py-24"
        >
            <h1
                class="max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl"
            >
                Marketplace Kampus untuk Jual, Beli, dan Antar
            </h1>
            <p class="max-w-xl text-base text-muted-foreground sm:text-lg">
                SEAPEDIA menghubungkan mahasiswa pembeli, penjual, dan kurir
                dalam satu platform — lengkap dengan wallet bersama dan peran
                yang bisa diganti kapan saja.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <Button as-child size="lg">
                    <Link :href="catalogIndex.url()">Jelajahi Katalog</Link>
                </Button>
                <Button as-child size="lg" variant="outline">
                    <Link :href="register()">Daftar Sekarang</Link>
                </Button>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="feature in features" :key="feature.title">
                <CardContent class="flex flex-col gap-3 pt-6">
                    <div
                        class="flex size-10 items-center justify-center rounded-md bg-primary/10 text-primary"
                    >
                        <component :is="feature.icon" class="size-5" />
                    </div>
                    <h2 class="font-medium">{{ feature.title }}</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ feature.description }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </section>
</template>
