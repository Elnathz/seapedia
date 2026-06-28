<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, Home, LayoutGrid, Package, Search, ShoppingCart, Tag, Truck, User } from '@lucide/vue';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard, home, login } from '@/routes';
import { index as adminPromos } from '@/routes/admin/promos';
import { index as cartIndex } from '@/routes/buyer/cart';
import { index as catalogIndex } from '@/routes/catalog';
import { index as driverJobs } from '@/routes/driver/jobs';
import { edit as editProfile } from '@/routes/profile';
import { index as sellerOrders } from '@/routes/seller/orders';
import { index as sellerProducts } from '@/routes/seller/products';
import { useAuthStore } from '@/stores/auth';
import type { NavItem } from '@/types/navigation';

const auth = useAuthStore();
const { isCurrentOrParentUrl } = useCurrentUrl();

const items = computed<NavItem[]>(() => {
    if (!auth.isAuthenticated) {
        return [
            { title: 'Beranda', href: home(), icon: Home },
            { title: 'Katalog', href: catalogIndex(), icon: Search },
            { title: 'Masuk', href: login(), icon: User },
        ];
    }

    const role = auth.activeRole;

    if (role === 'buyer') {
        return [
            { title: 'Beranda', href: home(), icon: Home },
            { title: 'Katalog', href: catalogIndex(), icon: Search },
            { title: 'Keranjang', href: cartIndex(), icon: ShoppingCart },
            { title: 'Profil', href: editProfile(), icon: User },
        ];
    }

    if (role === 'seller') {
        return [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            { title: 'Produk', href: sellerProducts(), icon: Package },
            { title: 'Pesanan', href: sellerOrders(), icon: ClipboardList },
            { title: 'Profil', href: editProfile(), icon: User },
        ];
    }

    if (role === 'driver') {
        return [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            { title: 'Pengiriman', href: driverJobs(), icon: Truck },
            { title: 'Profil', href: editProfile(), icon: User },
        ];
    }

    if (auth.user?.is_admin) {
        return [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            { title: 'Pesanan', href: '/admin/orders', icon: ClipboardList },
            { title: 'Diskon', href: adminPromos(), icon: Tag },
            { title: 'Profil', href: editProfile(), icon: User },
        ];
    }

    return [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Profil', href: editProfile(), icon: User },
    ];
});
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 flex h-16 items-center justify-around border-t border-border bg-white/95 backdrop-blur-sm min-[769px]:hidden"
        aria-label="Navigasi utama"
    >
        <Link
            v-for="item in items"
            :key="item.title"
            :href="item.href"
            class="relative flex min-h-11 min-w-11 flex-1 flex-col items-center justify-center gap-1 text-xs transition-colors"
            :class="isCurrentOrParentUrl(item.href) ? 'font-medium text-primary' : 'text-muted-foreground/70'"
        >
            <component :is="item.icon" class="size-5" />
            <span>{{ item.title }}</span>
        </Link>
    </nav>
</template>
