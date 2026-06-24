<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    MapPin,
    Package,
    ShoppingCart,
    Store,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import RoleBadge from '@/components/RoleBadge.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as indexBuyerAddresses } from '@/routes/buyer/addresses';
import { index as indexBuyerCart } from '@/routes/buyer/cart';
import { show as showBuyerWallet } from '@/routes/buyer/wallet';
import { index as indexSellerProducts } from '@/routes/seller/products';
import { show as showSellerStore } from '@/routes/seller/store';
import { useAuthStore } from '@/stores/auth';
import type { NavItem } from '@/types';

const auth = useAuthStore();
const { t } = useI18n();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: t('nav.dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (auth.activeRole === 'seller') {
        items.push(
            {
                title: t('nav.myStore'),
                href: showSellerStore(),
                icon: Store,
            },
            {
                title: t('nav.products'),
                href: indexSellerProducts(),
                icon: Package,
            },
        );
    }

    if (auth.activeRole === 'buyer') {
        items.push(
            {
                title: t('nav.wallet'),
                href: showBuyerWallet(),
                icon: Wallet,
            },
            {
                title: t('nav.addresses'),
                href: indexBuyerAddresses(),
                icon: MapPin,
            },
            {
                title: t('nav.cart'),
                href: indexBuyerCart(),
                icon: ShoppingCart,
            },
        );
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <RoleBadge class="px-2 group-data-[collapsible=icon]:hidden" />
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
