<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BadgePercent,
    BarChart3,
    Inbox,
    LayoutGrid,
    MapPin,
    Package,
    Receipt,
    ShoppingCart,
    Store,
    Ticket,
    Truck,
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
import { index as indexAdminPromos } from '@/routes/admin/promos';
import { index as indexAdminVouchers } from '@/routes/admin/vouchers';
import { index as indexBuyerAddresses } from '@/routes/buyer/addresses';
import { index as indexBuyerCart } from '@/routes/buyer/cart';
import { index as indexBuyerOrders } from '@/routes/buyer/orders';
import { index as indexBuyerReports } from '@/routes/buyer/reports';
import { show as showBuyerWallet } from '@/routes/buyer/wallet';
import { index as indexDriverJobs } from '@/routes/driver/jobs';
import { index as indexSellerOrders } from '@/routes/seller/orders';
import { index as indexSellerProducts } from '@/routes/seller/products';
import { index as indexSellerReports } from '@/routes/seller/reports';
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
            {
                title: t('nav.incomingOrders'),
                href: indexSellerOrders(),
                icon: Inbox,
            },
            {
                title: t('nav.reports'),
                href: indexSellerReports(),
                icon: BarChart3,
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
            {
                title: t('nav.myOrders'),
                href: indexBuyerOrders(),
                icon: Receipt,
            },
            {
                title: t('nav.reports'),
                href: indexBuyerReports(),
                icon: BarChart3,
            },
        );
    }

    if (auth.activeRole === 'driver') {
        items.push({
            title: t('nav.driverJobs'),
            href: indexDriverJobs(),
            icon: Truck,
        });
    }

    if (auth.user?.is_admin) {
        items.push(
            {
                title: t('admin.managePromosTitle'),
                href: indexAdminPromos(),
                icon: Ticket,
            },
            {
                title: t('admin.manageVouchersTitle'),
                href: indexAdminVouchers(),
                icon: BadgePercent,
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
