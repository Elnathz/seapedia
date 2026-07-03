<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Repeat2, X } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/components/ui/badge';
import { select as selectRole } from '@/routes/role';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const { t } = useI18n();

const roleLabel = (role: string) =>
    role.charAt(0).toUpperCase() + role.slice(1);

// First-visit coachmark: point multi-role users at the role switcher exactly
// once (persisted in localStorage), since the active-role model is easy to miss.
const HINT_KEY = 'seapedia_role_switch_hint_seen';
const showHint = ref(false);

onMounted(() => {
    if (typeof window === 'undefined') {
return;
}

    if (auth.roles.length > 1 && !window.localStorage.getItem(HINT_KEY)) {
        showHint.value = true;
    }
});

function dismissHint() {
    showHint.value = false;

    if (typeof window !== 'undefined') {
        window.localStorage.setItem(HINT_KEY, '1');
    }
}
</script>

<template>
    <div v-if="auth.activeRole" class="relative flex items-center gap-2">
        <Badge variant="secondary" class="capitalize">
            {{ roleLabel(auth.activeRole) }}
        </Badge>
        <Link
            v-if="auth.roles.length > 1"
            :href="selectRole.url()"
            class="relative flex min-h-11 min-w-11 items-center justify-center gap-1 rounded-md text-xs text-muted-foreground hover:text-foreground"
            :aria-label="t('nav.switchRole')"
            @click="dismissHint"
        >
            <Repeat2 class="size-4" />
            <span
                v-if="showHint"
                class="absolute -top-0.5 -right-0.5 flex size-2.5"
                aria-hidden="true"
            >
                <span
                    class="absolute inline-flex size-full rounded-full bg-primary/70 motion-safe:animate-ping"
                />
                <span
                    class="relative inline-flex size-2.5 rounded-full bg-primary"
                />
            </span>
            <span class="sr-only">{{ t('nav.switchRole') }}</span>
        </Link>

        <!-- First-visit coachmark -->
        <div
            v-if="showHint"
            class="absolute top-full left-0 z-50 mt-3 w-64 rounded-xl border border-border/70 bg-popover p-3 text-popover-foreground shadow-lg motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-top-1"
            role="status"
        >
            <span
                class="absolute -top-1.5 left-6 size-3 rotate-45 border-t border-l border-border/70 bg-popover"
                aria-hidden="true"
            />
            <div class="flex items-start gap-2">
                <div
                    class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                >
                    <Repeat2 class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold">Satu akun, banyak peran</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Ganti antara Pembeli, Penjual, dan Kurir kapan saja
                        lewat tombol ini.
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <Link
                            :href="selectRole.url()"
                            class="text-xs font-semibold text-primary hover:underline"
                            @click="dismissHint"
                        >
                            Ganti peran
                        </Link>
                        <button
                            type="button"
                            class="text-xs text-muted-foreground hover:text-foreground"
                            @click="dismissHint"
                        >
                            Mengerti
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="shrink-0 text-muted-foreground transition-colors hover:text-foreground"
                    aria-label="Tutup"
                    @click="dismissHint"
                >
                    <X class="size-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
