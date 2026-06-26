<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ShoppingBag, Store, Truck } from '@lucide/vue';
import { store } from '@/routes/role';

const props = defineProps<{
    roles: string[];
}>();

const form = useForm({ role: '' });

function selectRole(role: string) {
    form.role = role;
    form.post(store().url);
}

const roleConfig: Record<string, { icon: typeof ShoppingBag; label: string; tagline: string; color: string; bg: string }> = {
    buyer: {
        icon: ShoppingBag,
        label: 'Pembeli',
        tagline: 'Belanja produk dari toko kampus',
        color: 'text-blue-500',
        bg: 'bg-blue-500/10',
    },
    seller: {
        icon: Store,
        label: 'Penjual',
        tagline: 'Kelola toko dan jual produkmu',
        color: 'text-primary',
        bg: 'bg-primary/10',
    },
    driver: {
        icon: Truck,
        label: 'Kurir',
        tagline: 'Antar pesanan, dapat penghasilan',
        color: 'text-amber-500',
        bg: 'bg-amber-500/10',
    },
};
</script>

<template>
    <Head title="Pilih peran — SEAPEDIA" />

    <div class="flex min-h-svh items-center justify-center bg-background p-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Pilih cara lanjut</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Akunmu punya beberapa peran. Pilih satu untuk sekarang — bisa ganti kapan saja.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <button
                    v-for="role in props.roles"
                    :key="role"
                    type="button"
                    class="group flex w-full items-center gap-4 rounded-2xl border border-border bg-card p-5 text-left transition-all duration-200 hover:border-primary/40 hover:shadow-md disabled:opacity-50"
                    :disabled="form.processing"
                    @click="selectRole(role)"
                >
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110"
                        :class="roleConfig[role]?.bg ?? 'bg-muted'"
                    >
                        <component
                            :is="roleConfig[role]?.icon ?? ShoppingBag"
                            class="size-6"
                            :class="roleConfig[role]?.color ?? 'text-foreground'"
                        />
                    </div>
                    <div>
                        <p class="font-semibold capitalize text-foreground">
                            {{ roleConfig[role]?.label ?? role }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ roleConfig[role]?.tagline ?? '' }}
                        </p>
                    </div>
                    <svg class="ml-auto size-5 shrink-0 text-muted-foreground transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
