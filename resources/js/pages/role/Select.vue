<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    ShoppingBag,
    Store,
    Truck,
    ArrowRight,
    Loader2,
    UserCircle,
} from '@lucide/vue';
import { ref } from 'vue';
import { store } from '@/routes/role';

const props = defineProps<{
    roles: string[];
}>();

const form = useForm({ role: '' });
const selectingRole = ref<string | null>(null);

function selectRole(role: string) {
    if (form.processing) {
        return;
    }

    selectingRole.value = role;
    form.role = role;
    form.post(store().url, {
        onFinish: () => {
            selectingRole.value = null;
        },
    });
}

const roleConfig: Record<string, any> = {
    buyer: {
        icon: ShoppingBag,
        label: 'Pembeli',
        tagline: 'Mulai belanja produk kampus',
        color: 'text-blue-500',
        bg: 'bg-blue-50',
        hoverBorder: 'group-hover:border-blue-400',
        gradient: 'from-blue-500/10 to-transparent',
    },
    seller: {
        icon: Store,
        label: 'Penjual',
        tagline: 'Kelola toko & jual produkmu',
        color: 'text-primary',
        bg: 'bg-primary/10',
        hoverBorder: 'group-hover:border-primary/50',
        gradient: 'from-primary/10 to-transparent',
    },
    driver: {
        icon: Truck,
        label: 'Kurir',
        tagline: 'Antar pesanan & dapatkan saldo',
        color: 'text-amber-500',
        bg: 'bg-amber-50',
        hoverBorder: 'group-hover:border-amber-400',
        gradient: 'from-amber-500/10 to-transparent',
    },
};
</script>

<template>
    <Head title="Pilih peran | SEAPEDIA" />

    <div class="flex min-h-svh items-center justify-center bg-muted/20 p-4">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div
                class="mb-8 animate-in text-center duration-700 fade-in slide-in-from-bottom-4"
            >
                <div
                    class="mx-auto mb-5 flex size-16 items-center justify-center rounded-2xl border border-primary/10 bg-gradient-to-br from-primary/20 to-primary/5 shadow-inner"
                >
                    <UserCircle class="size-8 text-primary" />
                </div>
                <h1
                    class="text-2xl font-extrabold tracking-tight text-foreground sm:text-3xl"
                >
                    Masuk sebagai...
                </h1>
                <p class="mt-2 px-4 text-sm text-muted-foreground">
                    Pilih peran yang ingin kamu gunakan untuk sesi ini.
                </p>
            </div>

            <!-- Role Cards -->
            <div
                class="flex animate-in flex-col gap-3 delay-150 duration-700 fill-mode-both fade-in slide-in-from-bottom-6"
            >
                <button
                    v-for="role in props.roles"
                    :key="role"
                    type="button"
                    :disabled="form.processing"
                    @click="selectRole(role)"
                    class="group relative flex w-full items-center gap-4 overflow-hidden rounded-2xl border-2 bg-card p-4 text-left transition-all duration-300 focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:outline-none"
                    :class="[
                        selectingRole === role
                            ? 'scale-[0.98] border-primary bg-primary/5 ring-2 ring-primary/20'
                            : `border-border/60 hover:-translate-y-1 hover:shadow-lg ${roleConfig[role]?.hoverBorder}`,
                        form.processing && selectingRole !== role
                            ? 'cursor-not-allowed opacity-50 grayscale'
                            : '',
                    ]"
                >
                    <!-- Background Gradient (Hover) -->
                    <div
                        class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                        :class="roleConfig[role]?.gradient"
                    ></div>

                    <!-- Icon -->
                    <div
                        class="relative z-10 flex size-[3.25rem] shrink-0 items-center justify-center rounded-xl transition-transform duration-500 group-hover:scale-110"
                        :class="roleConfig[role]?.bg ?? 'bg-muted'"
                    >
                        <component
                            :is="roleConfig[role]?.icon ?? ShoppingBag"
                            class="size-6"
                            :class="
                                roleConfig[role]?.color ?? 'text-foreground'
                            "
                        />
                    </div>

                    <!-- Text -->
                    <div class="relative z-10 min-w-0 flex-1">
                        <span
                            class="block text-base font-bold text-foreground capitalize transition-colors duration-300 group-hover:text-primary sm:text-lg"
                        >
                            {{ roleConfig[role]?.label ?? role }}
                        </span>
                        <span
                            class="mt-0.5 line-clamp-1 block text-xs text-muted-foreground transition-colors duration-300 group-hover:text-foreground/80 sm:text-sm"
                        >
                            {{ roleConfig[role]?.tagline ?? '' }}
                        </span>
                    </div>

                    <!-- Arrow or Loader -->
                    <div
                        class="relative z-10 ml-2 flex size-10 shrink-0 items-center justify-center rounded-full border border-border bg-background shadow-sm transition-all duration-300 group-hover:border-primary group-hover:bg-primary group-hover:text-primary-foreground"
                    >
                        <Loader2
                            v-if="selectingRole === role"
                            class="size-5 animate-spin"
                        />
                        <ArrowRight
                            v-else
                            class="size-4 text-muted-foreground transition-colors group-hover:text-primary-foreground sm:size-5"
                        />
                    </div>
                </button>
            </div>

            <div
                class="mt-8 animate-in text-center delay-300 duration-700 fill-mode-both fade-in"
            >
                <p class="text-[11px] text-muted-foreground sm:text-xs">
                    Kamu bisa berganti peran kapan saja melalui menu Profil.
                </p>
            </div>
        </div>
    </div>
</template>
