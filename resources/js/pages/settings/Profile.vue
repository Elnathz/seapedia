<script setup lang="ts">
import { Form, Head, usePage, router } from '@inertiajs/vue3';
import { Shield, User, CreditCard, Store, Truck, AlertTriangle, Check } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import AddressList from '@/components/profile/AddressList.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import { formatIDR } from '@/lib/utils';
import { edit } from '@/routes/profile';
import { useAuthStore } from '@/stores/auth';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const auth = useAuthStore();
const { t } = useI18n();
const { getInitials, getGradientClass } = useInitials();

const user = computed(() => page.props.auth.user!);
const wallet = computed(() => page.props.wallet as { balance: number } | null);
const store = computed(() => page.props.store as { name: string; products_count: number } | null);
const driverStats = computed(() => page.props.driver_stats as { completed_jobs: number; total_earnings: number } | null);
const addresses = computed(() => page.props.addresses as any[] | undefined);

const roles = computed(() => {
    const r: string[] = [];

    if (user.value.is_admin) {
r.push('admin');
}

    if (page.props.auth.roles) {
        (page.props.auth.roles as string[]).forEach((role: string) => {
            if (!r.includes(role)) {
r.push(role);
}
        });
    }

    return r;
});
</script>

<template>
    <Head title="Profil" />

    <div class="mx-auto max-w-3xl space-y-6 px-4 pb-10 sm:px-6">
        <!-- Profile Header -->
        <Card class="overflow-hidden border-0 shadow-md">
            <div class="bg-gradient-to-r from-primary/10 via-brand/10 to-primary/10 p-6">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:gap-6">
                    <!-- Avatar -->
                    <div
                        class="flex size-20 shrink-0 items-center justify-center rounded-full text-2xl font-bold text-white shadow-lg sm:size-24"
                        :class="getGradientClass(user.name)"
                    >
                        {{ getInitials(user.name) }}
                    </div>

                    <!-- Info -->
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-2xl font-bold text-foreground">{{ user.name }}</h1>
                        <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                        <div class="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
                            <Badge v-for="role in roles" :key="role" variant="secondary" class="capitalize">
                                {{ role }}
                            </Badge>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
                                <Check class="size-3.5" />
                                Aktif: {{ auth.activeRole }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Card>

        <!-- Section 1: Informasi Profil -->
        <Card>
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-lg">
                    <User class="size-5 text-primary" />
                    Informasi Profil
                </CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">
                                Nama Lengkap <RequiredMark />
                            </Label>
                            <Input
                                id="name"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Nama lengkap"
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="email">
                                Email <RequiredMark />
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="email@contoh.com"
                            />
                            <InputError :message="errors.email" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="phone">Nomor Telepon</Label>
                        <Input
                            id="phone"
                            type="tel"
                            name="phone"
                            :default-value="(user as any).phone ?? ''"
                            autocomplete="tel"
                            placeholder="08xxxxxxxxxx"
                        />
                        <InputError :message="errors.phone" />
                    </div>
                    <Button :disabled="processing" type="submit" class="w-full sm:w-auto">
                        Simpan Perubahan
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <!-- Section 2: Keamanan -->
        <Card>
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Shield class="size-5 text-primary" />
                    Keamanan
                </CardTitle>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="SecurityController.update.form()"
                    class="space-y-4"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-2">
                        <Label for="current_password">Password Lama</Label>
                        <Input
                            id="current_password"
                            type="password"
                            name="current_password"
                            autocomplete="current-password"
                            placeholder="Masukkan password lama"
                        />
                        <InputError :message="errors.current_password" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="password">Password Baru</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter"
                            />
                            <InputError :message="errors.password" />
                        </div>
                        <div class="space-y-2">
                            <Label for="password_confirmation">Konfirmasi Password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                                placeholder="Ulangi password baru"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>
                    </div>
                    <Button :disabled="processing" type="submit" class="w-full sm:w-auto">
                        Update Password
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <!-- Section 3: Role-Specific Stats -->
        <Card v-if="auth.activeRole === 'buyer' && wallet">
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-lg">
                    <CreditCard class="size-5 text-primary" />
                    Dompet Digital
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-primary/10 to-brand/10 p-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Saldo Tersedia</p>
                        <p class="text-3xl font-bold text-primary tabular-nums">{{ formatIDR(wallet.balance) }}</p>
                    </div>
                    <Button as-child variant="outline" size="sm">
                        <a href="/buyer/wallet">Top Up</a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Address List for Buyer -->
        <AddressList v-if="auth.activeRole === 'buyer' && addresses !== undefined" :addresses="addresses" />

        <Card v-if="auth.activeRole === 'seller' && store">
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Store class="size-5 text-primary" />
                    Toko Saya
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex items-center justify-between rounded-xl bg-gradient-to-r from-primary/10 to-brand/10 p-4">
                    <div>
                        <p class="text-lg font-semibold text-foreground">{{ store.name }}</p>
                        <p class="text-sm text-muted-foreground">{{ store.products_count }} produk</p>
                    </div>
                    <Button as-child variant="outline" size="sm">
                        <a href="/seller/store">Kelola Toko</a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card v-if="auth.activeRole === 'driver' && driverStats">
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Truck class="size-5 text-primary" />
                    Statistik Kurir
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-gradient-to-r from-amber-50 to-amber-100 p-4">
                        <p class="text-sm text-amber-700">Penghasilan Total</p>
                        <p class="text-2xl font-bold text-amber-600 tabular-nums">{{ formatIDR(driverStats.total_earnings) }}</p>
                    </div>
                    <div class="rounded-xl bg-gradient-to-r from-emerald-50 to-emerald-100 p-4">
                        <p class="text-sm text-emerald-700">Pengiriman Selesai</p>
                        <p class="text-2xl font-bold text-emerald-600 tabular-nums">{{ driverStats.completed_jobs }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Section 4: Hapus Akun (Destructive) -->
        <Card v-if="!user.is_admin" class="border-destructive/30">
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-lg text-destructive">
                    <AlertTriangle class="size-5" />
                    Zona Berbahaya
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p class="mb-4 text-sm text-muted-foreground">
                    Menghapus akun akan permanently menghapus semua data Anda termasuk riwayat pesanan, saldo wallet, dan data toko. Tindakan ini tidak dapat dibatalkan.
                </p>
                <Button variant="destructive" as-child>
                    <a href="/profile/delete">Hapus Akun Saya</a>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
