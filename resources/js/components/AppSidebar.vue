<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BadgePercent,
    BarChart3,
    ExternalLink,
    Images,
    Inbox,
    LayoutGrid,
    MapPin,
    Package,
    Receipt,
    ShoppingCart,
    Settings,
    Store,
    Tags,
    Ticket,
    Truck,
    Users,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import RoleBadge from '@/components/RoleBadge.vue';
import { Button } from '@/components/ui/button';
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
import { index as indexAdminCategories } from '@/routes/admin/categories';
import { index as indexAdminPromos } from '@/routes/admin/promos';
import { index as indexAdminVouchers } from '@/routes/admin/vouchers';
import { index as indexBuyerAddresses } from '@/routes/buyer/addresses';
import { index as indexBuyerCart } from '@/routes/buyer/cart';
import { index as indexBuyerOrders } from '@/routes/buyer/orders';
import { index as indexBuyerReports } from '@/routes/buyer/reports';
import { show as showBuyerWallet } from '@/routes/buyer/wallet';
import { index as catalogIndex } from '@/routes/catalog';
import { index as indexDriverJobs } from '@/routes/driver/jobs';
import { edit as editProfile } from '@/routes/profile';
import { index as indexSellerOrders } from '@/routes/seller/orders';
import { index as indexSellerProducts } from '@/routes/seller/products';
import { index as indexSellerReports } from '@/routes/seller/reports';
import { show as showSellerStore } from '@/routes/seller/store';
import { useAuthStore } from '@/stores/auth';
import type { NavGroup } from '@/types';

const auth = useAuthStore();
const { t } = useI18n();

const navGroups = computed<NavGroup[]>(() => {
    const groups: NavGroup[] = [];

    // 1. Group Main (Aksesible untuk semua role)
    const mainItems = [
        {
            title: t('nav.dashboard'),
            href: dashboard.url(),
            icon: LayoutGrid,
        },
        {
            title: 'Katalog Produk',
            href: catalogIndex.url(),
            icon: Images,
        },
    ];
    groups.push({
        label: 'Main',
        items: mainItems,
    });

    // 2. Group Role-Specific
    if (auth.activeRole === 'buyer') {
        groups.push({
            label: 'Belanja',
            items: [
                {
                    title: 'Keranjang',
                    href: indexBuyerCart.url(),
                    icon: ShoppingCart,
                },
                {
                    title: 'Daftar Alamat',
                    href: indexBuyerAddresses.url(),
                    icon: MapPin,
                },
            ],
        });

        groups.push({
            label: 'Transaksi',
            items: [
                {
                    title: t('nav.wallet'),
                    href: showBuyerWallet.url(),
                    icon: Wallet,
                },
                {
                    title: t('nav.myOrders'),
                    href: indexBuyerOrders.url(),
                    icon: Receipt,
                },
                {
                    title: t('nav.reports'),
                    href: indexBuyerReports.url(),
                    icon: BarChart3,
                },
            ],
        });
    }

    if (auth.activeRole === 'seller') {
        groups.push({
            label: 'Toko Saya',
            items: [
                {
                    title: t('nav.myStore'),
                    href: showSellerStore.url(),
                    icon: Store,
                },
                {
                    title: t('nav.products'),
                    href: indexSellerProducts.url(),
                    icon: Package,
                },
                {
                    title: t('nav.incomingOrders'),
                    href: indexSellerOrders.url(),
                    icon: Inbox,
                },
                {
                    title: t('nav.reports'),
                    href: indexSellerReports.url(),
                    icon: BarChart3,
                },
            ],
        });
    }

    if (auth.activeRole === 'driver') {
        groups.push({
            label: 'Layanan Kurir',
            items: [
                {
                    title: t('nav.driverJobs'),
                    href: indexDriverJobs.url(),
                    icon: Truck,
                },
            ],
        });
    }

    if (auth.user?.is_admin) {
        groups.push({
            label: 'Data Utama (Admin)',
            items: [
                { title: 'Pengguna', href: '/admin/users', icon: Users },
                { title: 'Toko', href: '/admin/stores', icon: Store },
                { title: 'Produk', href: '/admin/products', icon: Package },
                { title: 'Pesanan', href: '/admin/orders', icon: Inbox },
                { title: 'Pengiriman', href: '/admin/deliveries', icon: Truck },
            ],
        });

        groups.push({
            label: 'Manajemen Promo (Admin)',
            items: [
                {
                    title: t('admin.manageCategoriesTitle'),
                    href: indexAdminCategories.url(),
                    icon: Tags,
                },
                {
                    title: t('admin.managePromosTitle'),
                    href: indexAdminPromos.url(),
                    icon: Ticket,
                },
                {
                    title: t('admin.manageVouchersTitle'),
                    href: indexAdminVouchers.url(),
                    icon: BadgePercent,
                },
                { title: 'Banner', href: '/admin/banners', icon: Images },
            ],
        });

        groups.push({
            label: 'Alat Simulasi (Admin)',
            items: [
                {
                    title: 'Overdue (Time Machine)',
                    href: '/admin/overdue',
                    icon: AlertTriangle,
                },
            ],
        });
    }

    // 3. Group Pengaturan
    groups.push({
        label: 'Pengaturan',
        items: [
            {
                title: 'Profil & Settings',
                href: editProfile.url(),
                icon: Settings,
            },
        ],
    });

    return groups;
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
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
