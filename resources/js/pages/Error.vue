<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Home, ArrowLeft, AlertOctagon, ShieldAlert, FileQuestion, ServerCrash, RotateCcw } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    status: number;
}>();

const errorDetails = computed(() => {
    switch (props.status) {
        case 403:
            return {
                title: 'Akses Ditolak',
                description: 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Peran aktif Anda saat ini mungkin tidak sesuai untuk aksi ini.',
                icon: ShieldAlert,
                color: 'text-amber-500',
                bg: 'bg-amber-500/10',
                border: 'border-amber-500/20',
                gradient: 'from-amber-500/10 via-amber-500/5 to-transparent'
            };
        case 404:
            return {
                title: 'Halaman Tidak Ditemukan',
                description: 'Halaman yang Anda cari mungkin telah dihapus, diubah namanya, atau tidak pernah ada.',
                icon: FileQuestion,
                color: 'text-blue-500',
                bg: 'bg-blue-500/10',
                border: 'border-blue-500/20',
                gradient: 'from-blue-500/10 via-blue-500/5 to-transparent'
            };
        case 500:
            return {
                title: 'Gangguan Server Internal',
                description: 'Terjadi kesalahan pada sistem kami. Tim teknis kami sedang berusaha memperbaikinya segera.',
                icon: ServerCrash,
                color: 'text-red-500',
                bg: 'bg-red-500/10',
                border: 'border-red-500/20',
                gradient: 'from-red-500/10 via-red-500/5 to-transparent'
            };
        case 502:
            return {
                title: 'Gerbang Buruk (Bad Gateway)',
                description: 'Server kami menerima respons yang tidak valid dari server hulu. Hal ini biasanya terjadi saat server sedang dimulai ulang. Silakan muat ulang halaman ini dalam beberapa saat.',
                icon: ServerCrash,
                color: 'text-purple-500',
                bg: 'bg-purple-500/10',
                border: 'border-purple-500/20',
                gradient: 'from-purple-500/10 via-purple-500/5 to-transparent'
            };
        case 503:
            return {
                title: 'Layanan Tidak Tersedia',
                description: 'Sistem sedang dalam pemeliharaan rutin. Silakan kembali lagi dalam beberapa menit.',
                icon: RotateCcw,
                color: 'text-orange-500',
                bg: 'bg-orange-500/10',
                border: 'border-orange-500/20',
                gradient: 'from-orange-500/10 via-orange-500/5 to-transparent'
            };
        default:
            return {
                title: 'Terjadi Kesalahan',
                description: 'Maaf, terjadi kesalahan yang tidak terduga.',
                icon: AlertOctagon,
                color: 'text-primary',
                bg: 'bg-primary/10',
                border: 'border-primary/20',
                gradient: 'from-primary/10 via-primary/5 to-transparent'
            };
    }
});

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head :title="errorDetails.title" />

    <div class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-background px-6 py-12">
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-1/2 left-1/2 h-[800px] w-[800px] -translate-x-1/2 rounded-full bg-gradient-to-b opacity-50 blur-3xl transition-colors duration-1000" :class="errorDetails.gradient"></div>
        </div>

        <div class="z-10 w-full max-w-md text-center">
            <!-- Icon with pulse effect -->
            <div class="mb-8 flex justify-center animate-in zoom-in duration-700 spring-bounce">
                <div class="relative flex h-32 w-32 items-center justify-center rounded-3xl border shadow-lg backdrop-blur-xl transition-colors duration-500" :class="[errorDetails.bg, errorDetails.border]">
                    <div class="absolute inset-0 rounded-3xl animate-pulse opacity-50" :class="errorDetails.bg"></div>
                    <component :is="errorDetails.icon" class="relative z-10 h-14 w-14 transition-colors duration-500" :class="errorDetails.color" stroke-width="1.5" />
                </div>
            </div>

            <!-- Error Content -->
            <div class="space-y-4 animate-in slide-in-from-bottom-8 fade-in duration-700 delay-150 fill-mode-both">
                <div class="space-y-2">
                    <h1 class="text-6xl font-black tracking-tighter transition-colors duration-500" :class="errorDetails.color">
                        {{ status }}
                    </h1>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        {{ errorDetails.title }}
                    </h2>
                </div>
                
                <p class="mx-auto max-w-sm text-base text-muted-foreground">
                    {{ errorDetails.description }}
                </p>
            </div>

            <!-- Actions -->
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row animate-in slide-in-from-bottom-6 fade-in duration-700 delay-300 fill-mode-both">
                <Button variant="outline" size="lg" class="w-full sm:w-auto rounded-full gap-2 hover:bg-muted" @click="goBack">
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Button>
                <Button size="lg" class="w-full sm:w-auto rounded-full gap-2 shadow-lg transition-transform hover:-translate-y-0.5 active:translate-y-0" as-child>
                    <Link href="/catalog">
                        <Home class="h-4 w-4" />
                        Beranda Katalog
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Watermark / Brand -->
        <div class="absolute bottom-8 z-10 text-center animate-in fade-in duration-1000 delay-500 fill-mode-both">
            <p class="text-sm font-semibold tracking-wider text-muted-foreground/60">SEAPEDIA</p>
        </div>
    </div>
</template>

<style scoped>
/* Spring bounce animation for the icon container */
@keyframes spring-bounce {
    0% { transform: scale(0.8); opacity: 0; }
    40% { transform: scale(1.05); opacity: 1; }
    80% { transform: scale(0.97); }
    100% { transform: scale(1); }
}

.spring-bounce {
    animation-name: spring-bounce;
    animation-timing-function: cubic-bezier(0.28, 0.84, 0.42, 1);
}
</style>
