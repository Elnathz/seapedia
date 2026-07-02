<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    LogOut,
    Settings,
    LayoutDashboard,
    ShoppingBag,
    Repeat2,
    Store,
    Truck,
} from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout, dashboard } from '@/routes';
import { index as buyerOrdersIndex } from '@/routes/buyer/orders';
import { index as driverJobsIndex } from '@/routes/driver/jobs';
import { edit } from '@/routes/profile';
import { select as selectRole } from '@/routes/role';
import { show as sellerStoreShow } from '@/routes/seller/store';
import { useAuthStore } from '@/stores/auth';
import type { User } from '@/types';

type Props = {
    user: User;
};

const auth = useAuthStore();

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="dashboard()"
                prefetch
            >
                <LayoutDashboard class="mr-2 h-4 w-4" />
                Dashboard
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="auth.activeRole === 'buyer'" :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="buyerOrdersIndex()"
                prefetch
            >
                <ShoppingBag class="mr-2 h-4 w-4" />
                Pesanan Saya
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="auth.activeRole === 'seller'" :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="sellerStoreShow()"
                prefetch
            >
                <Store class="mr-2 h-4 w-4" />
                Toko Saya
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="auth.activeRole === 'driver'" :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="driverJobsIndex()"
                prefetch
            >
                <Truck class="mr-2 h-4 w-4" />
                Layanan Kurir
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 inline h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup v-if="auth.roles.length > 1">
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="selectRole.url()">
                <Repeat2 class="mr-2 inline h-4 w-4" />
                Ganti Peran
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator v-if="auth.roles.length > 1" />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
