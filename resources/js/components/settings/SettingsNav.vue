<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Palette, Shield, User } from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

const items = [
    { title: 'Profil', href: editProfile(), icon: User },
    { title: 'Keamanan', href: editSecurity(), icon: Shield },
    { title: 'Tampilan', href: editAppearance(), icon: Palette },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <nav
        class="inline-flex items-center gap-1 rounded-full border border-border/60 bg-muted/40 p-1"
        aria-label="Menu pengaturan"
    >
        <Link
            v-for="item in items"
            :key="toUrl(item.href)"
            :href="item.href"
            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1.5 font-medium transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none active:scale-[0.97] text-xs sm:gap-2 sm:px-3.5 sm:text-sm"
            :class="
                isCurrentOrParentUrl(item.href)
                    ? 'bg-background text-foreground shadow-sm ring-1 ring-border/60'
                    : 'text-muted-foreground hover:text-foreground'
            "
        >
            <component :is="item.icon" class="size-3.5 shrink-0 sm:size-4" />
            {{ item.title }}
        </Link>
    </nav>
</template>
