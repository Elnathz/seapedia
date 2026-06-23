<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Settings } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

const { t } = useI18n();

const items = computed<NavItem[]>(() => [
    { title: t('nav.dashboard'), href: dashboard(), icon: LayoutGrid },
    { title: t('nav.settings'), href: editProfile(), icon: Settings },
]);

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-center justify-around border-t border-sidebar-border bg-sidebar min-[769px]:hidden"
        aria-label="Primary"
    >
        <Link
            v-for="item in items"
            :key="item.title"
            :href="item.href"
            class="flex min-h-11 min-w-11 flex-1 flex-col items-center justify-center gap-1 text-xs"
            :class="
                isCurrentOrParentUrl(item.href)
                    ? 'font-medium text-sidebar-primary'
                    : 'text-sidebar-foreground/70'
            "
        >
            <component :is="item.icon" class="size-5" />
            <span>{{ item.title }}</span>
        </Link>
    </nav>
</template>
