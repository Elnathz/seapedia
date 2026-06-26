<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Logo from '@/components/brand/Logo.vue';
import LocaleToggle from '@/components/LocaleToggle.vue';
import RoleBadge from '@/components/RoleBadge.vue';
import { Button } from '@/components/ui/button';
import { dashboard, home, login, register } from '@/routes';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const { t } = useI18n();
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-border bg-white/95 backdrop-blur"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8"
        >
            <Link :href="home()" class="flex shrink-0 items-center">
                <Logo class="h-9 w-auto" />
            </Link>

            <!-- Authenticated: locale + role badge + dashboard CTA -->
            <div v-if="auth.isAuthenticated" class="flex items-center gap-3">
                <LocaleToggle />
                <RoleBadge />
                <Button as-child size="sm">
                    <Link :href="dashboard()">{{ t('nav.dashboard') }}</Link>
                </Button>
            </div>

            <!-- Guest: Masuk + Daftar -->
            <div v-else class="flex items-center gap-2">
                <Button as-child variant="ghost" size="sm">
                    <Link :href="login()">{{ t('nav.login') }}</Link>
                </Button>
                <Button as-child size="sm">
                    <Link :href="register()">{{ t('nav.register') }}</Link>
                </Button>
            </div>
        </div>
    </header>
</template>
