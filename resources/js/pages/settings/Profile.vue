<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Camera,
    Check,
    CreditCard,
    Loader2,
    Plus,
    ShieldCheck,
    ShoppingBag,
    Store,
    Trash2,
    Truck,
    User,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import AddressList from '@/components/profile/AddressList.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import SettingsNav from '@/components/settings/SettingsNav.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import { formatIDR } from '@/lib/utils';
import { edit } from '@/routes/profile';
import { useAuthStore } from '@/stores/auth';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Profil', href: edit() }],
    },
});

interface RoleMeta {
    label: string;
    icon: typeof User;
    blurb: string;
}

const ROLE_META: Record<string, RoleMeta> = {
    buyer: {
        label: 'Pembeli',
        icon: ShoppingBag,
        blurb: 'Belanja, wallet & pesanan',
    },
    seller: { label: 'Penjual', icon: Store, blurb: 'Toko & produk' },
    driver: {
        label: 'Kurir',
        icon: Truck,
        blurb: 'Antar pesanan & penghasilan',
    },
    admin: {
        label: 'Admin',
        icon: ShieldCheck,
        blurb: 'Pengawasan marketplace',
    },
};

const NON_ADMIN_ROLES = ['buyer', 'seller', 'driver'] as const;

const page = usePage();
const auth = useAuthStore();
const { getInitials, getGradientClass } = useInitials();

const user = computed(() => page.props.auth.user!);
const ownedRoles = computed(
    () => (page.props.auth.roles as string[] | undefined) ?? [],
);
const activeRole = computed(() => auth.activeRole);

const avatarUrl = computed(() =>
    user.value.avatar_path ? `/storage/${user.value.avatar_path}` : null,
);
const avatarError = computed(
    () => (page.props.errors as Record<string, string> | undefined)?.avatar,
);
const roleError = computed(
    () => (page.props.errors as Record<string, string> | undefined)?.role,
);

// Admin lives on `is_admin`, not the role pivot — surface it first, then the
// owned non-admin roles.
const displayedRoles = computed(() => {
    const list: string[] = [];

    if (user.value.is_admin) {
list.push('admin');
}

    ownedRoles.value.forEach((r) => !list.includes(r) && list.push(r));

    return list;
});

const addableRoles = computed(() =>
    NON_ADMIN_ROLES.filter((r) => !ownedRoles.value.includes(r)),
);

const isLastNonAdminRole = computed(() => ownedRoles.value.length <= 1);

const wallet = computed(() => page.props.wallet as { balance: number } | null);
const store = computed(
    () => page.props.store as { name: string; products_count: number } | null,
);
const driverStats = computed(
    () =>
        page.props.driver_stats as {
            completed_jobs: number;
            total_earnings: number;
        } | null,
);
const addresses = computed(() => page.props.addresses as unknown[] | undefined);

// ── Avatar upload ───────────────────────────────────────────────────────────
const fileInput = ref<HTMLInputElement | null>(null);
const uploadingAvatar = ref(false);

function onAvatarPicked(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
return;
}

    uploadingAvatar.value = true;
    router.post(
        '/settings/profile/avatar',
        { avatar: file },
        {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => {
                uploadingAvatar.value = false;

                if (fileInput.value) {
fileInput.value.value = '';
}
            },
        },
    );
}

function removeAvatar() {
    router.delete('/settings/profile/avatar', { preserveScroll: true });
}

// ── Roles ───────────────────────────────────────────────────────────────────
const resignTarget = ref<string | null>(null);

function addRole(role: string) {
    router.post('/role/add', { role }, { preserveScroll: true });
}

function confirmResign() {
    if (!resignTarget.value) {
return;
}

    router.delete(`/role/${resignTarget.value}`, {
        preserveScroll: true,
        onFinish: () => (resignTarget.value = null),
    });
}
</script>

<template>
    <Head title="Profil" />

    <div class="mx-auto w-full max-w-5xl space-y-8 px-4 pb-16 sm:px-6">
        <div class="flex flex-col gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Akun Saya</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola identitas, role, dan keamanan akunmu.
                </p>
            </div>
            <SettingsNav />
        </div>

        <!-- ── Identity header (signature) ─────────────────────────────── -->
        <section
            class="reveal relative overflow-hidden rounded-3xl border border-border/60 shadow-[0_1px_3px_rgba(0,0,0,0.03),0_24px_48px_-28px_rgba(13,148,136,0.28)]"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary/12 via-brand/8 to-transparent"
                aria-hidden="true"
            />
            <div
                class="relative flex flex-col items-center gap-5 p-6 sm:flex-row sm:gap-7 sm:p-8"
            >
                <!-- Avatar with upload affordance -->
                <div class="group relative shrink-0">
                    <div
                        class="rounded-full bg-background/70 p-1 ring-1 ring-border/70 backdrop-blur-sm"
                    >
                        <img
                            v-if="avatarUrl"
                            :src="avatarUrl"
                            :alt="`Avatar ${user.name}`"
                            class="size-24 rounded-full object-cover sm:size-28"
                        />
                        <div
                            v-else
                            class="flex size-24 items-center justify-center rounded-full text-3xl font-bold text-white sm:size-28"
                            :class="getGradientClass(user.name)"
                        >
                            {{ getInitials(user.name) }}
                        </div>
                    </div>

                    <button
                        type="button"
                        class="absolute right-0 bottom-0 flex size-9 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-md ring-4 ring-background transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] hover:scale-105 active:scale-95 disabled:opacity-70"
                        :disabled="uploadingAvatar"
                        aria-label="Ganti foto profil"
                        @click="fileInput?.click()"
                    >
                        <Loader2
                            v-if="uploadingAvatar"
                            class="size-4 animate-spin"
                        />
                        <Camera v-else class="size-4" />
                    </button>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onAvatarPicked"
                    />
                </div>

                <!-- Identity -->
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <h2 class="truncate text-2xl font-bold text-foreground">
                        {{ user.name }}
                    </h2>
                    <p class="truncate text-sm text-muted-foreground">
                        {{ user.email }}
                    </p>

                    <div
                        class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start"
                    >
                        <span
                            v-if="activeRole"
                            class="inline-flex items-center gap-1.5 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground"
                        >
                            <Check class="size-3.5" />
                            Aktif:
                            {{ ROLE_META[activeRole]?.label ?? activeRole }}
                        </span>
                        <Badge
                            v-for="role in displayedRoles"
                            :key="role"
                            variant="secondary"
                            class="gap-1 font-medium"
                        >
                            <component
                                :is="ROLE_META[role]?.icon ?? User"
                                class="size-3"
                            />
                            {{ ROLE_META[role]?.label ?? role }}
                        </Badge>
                    </div>

                    <div
                        v-if="avatarUrl"
                        class="mt-3 flex justify-center sm:justify-start"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 text-xs font-medium text-muted-foreground transition-colors hover:text-destructive"
                            @click="removeAvatar"
                        >
                            <Trash2 class="size-3" /> Hapus foto
                        </button>
                    </div>
                    <InputError
                        :message="avatarError"
                        class="mt-2 justify-center sm:justify-start"
                    />
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- ── Main column ─────────────────────────────────────────── -->
            <div class="space-y-6 lg:col-span-2">
                <Card
                    class="reveal rounded-2xl border-border/60 shadow-[0_1px_3px_rgba(0,0,0,0.03),0_14px_30px_-22px_rgba(13,148,136,0.2)]"
                >
                    <CardContent class="p-6">
                        <div class="mb-5 flex items-center gap-2.5">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary"
                            >
                                <User class="size-5" />
                            </div>
                            <div>
                                <h3 class="font-semibold">Informasi Profil</h3>
                                <p class="text-xs text-muted-foreground">
                                    Nama, email & kontak
                                </p>
                            </div>
                        </div>

                        <Form
                            v-bind="ProfileController.update.form()"
                            class="space-y-4"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name"
                                        >Nama Lengkap <RequiredMark
                                    /></Label>
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
                                    <Label for="email"
                                        >Email <RequiredMark
                                    /></Label>
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
                                    :default-value="user.phone ?? ''"
                                    autocomplete="tel"
                                    placeholder="08xxxxxxxxxx"
                                />
                                <InputError :message="errors.phone" />
                            </div>
                            <Button
                                :disabled="processing"
                                type="submit"
                                class="transition-transform active:scale-[0.98]"
                            >
                                Simpan Perubahan
                            </Button>
                        </Form>
                    </CardContent>
                </Card>

                <!-- Role-specific summary -->
                <Card
                    v-if="activeRole === 'buyer' && wallet"
                    class="reveal overflow-hidden rounded-2xl border-border/60"
                >
                    <CardContent class="p-6">
                        <div
                            class="flex items-center justify-between rounded-xl bg-gradient-to-r from-primary/10 to-brand/10 p-4"
                        >
                            <div>
                                <p
                                    class="flex items-center gap-1.5 text-sm text-muted-foreground"
                                >
                                    <CreditCard class="size-4" /> Saldo Wallet
                                </p>
                                <p
                                    class="mt-1 text-3xl font-bold text-primary tabular-nums"
                                >
                                    {{ formatIDR(wallet.balance) }}
                                </p>
                            </div>
                            <Button as-child variant="outline" size="sm">
                                <a href="/buyer/wallet">Top Up</a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <AddressList
                    v-if="activeRole === 'buyer' && addresses !== undefined"
                    :addresses="addresses as any[]"
                />

                <Card
                    v-if="activeRole === 'seller' && store"
                    class="reveal rounded-2xl border-border/60"
                >
                    <CardContent class="p-6">
                        <div
                            class="flex items-center justify-between rounded-xl bg-gradient-to-r from-primary/10 to-brand/10 p-4"
                        >
                            <div>
                                <p
                                    class="flex items-center gap-1.5 text-lg font-semibold"
                                >
                                    <Store class="size-4 text-primary" />
                                    {{ store.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ store.products_count }} produk aktif
                                </p>
                            </div>
                            <Button as-child variant="outline" size="sm">
                                <a href="/seller/store">Kelola</a>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    v-if="activeRole === 'driver' && driverStats"
                    class="reveal rounded-2xl border-border/60"
                >
                    <CardContent class="grid gap-4 p-6 sm:grid-cols-2">
                        <div class="rounded-xl bg-primary/5 p-4">
                            <p
                                class="flex items-center gap-1.5 text-sm text-muted-foreground"
                            >
                                <Truck class="size-4 text-primary" />
                                Penghasilan
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold text-primary tabular-nums"
                            >
                                {{ formatIDR(driverStats.total_earnings) }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-muted/60 p-4">
                            <p class="text-sm text-muted-foreground">
                                Pengiriman selesai
                            </p>
                            <p class="mt-1 text-2xl font-bold tabular-nums">
                                {{ driverStats.completed_jobs }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ── Side rail ───────────────────────────────────────────── -->
            <div class="space-y-6">
                <!-- Roles & access -->
                <Card
                    class="reveal rounded-2xl border-border/60 shadow-[0_1px_3px_rgba(0,0,0,0.03),0_14px_30px_-22px_rgba(13,148,136,0.2)]"
                >
                    <CardContent class="p-6">
                        <div class="mb-4 flex items-center gap-2.5">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary"
                            >
                                <ShieldCheck class="size-5" />
                            </div>
                            <div>
                                <h3 class="font-semibold">Role &amp; Akses</h3>
                                <p class="text-xs text-muted-foreground">
                                    Satu akun, banyak peran
                                </p>
                            </div>
                        </div>

                        <ul class="space-y-2">
                            <li
                                v-for="role in ownedRoles"
                                :key="role"
                                class="flex items-center gap-3 rounded-xl border border-border/60 p-3"
                            >
                                <component
                                    :is="ROLE_META[role]?.icon ?? User"
                                    class="size-4 shrink-0 text-primary"
                                />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium">
                                        {{ ROLE_META[role]?.label ?? role }}
                                        <span
                                            v-if="activeRole === role"
                                            class="ml-1 text-xs font-normal text-primary"
                                            >&bull; aktif</span
                                        >
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ ROLE_META[role]?.blurb }}
                                    </p>
                                </div>
                                <button
                                    v-if="!isLastNonAdminRole"
                                    type="button"
                                    class="flex size-7 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                    :aria-label="`Lepas role ${ROLE_META[role]?.label ?? role}`"
                                    @click="resignTarget = role"
                                >
                                    <X class="size-4" />
                                </button>
                            </li>
                        </ul>

                        <div v-if="addableRoles.length" class="mt-4">
                            <p
                                class="mb-2 text-xs font-medium text-muted-foreground"
                            >
                                Tambah peran
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-for="role in addableRoles"
                                    :key="role"
                                    variant="outline"
                                    size="sm"
                                    class="gap-1.5 transition-transform active:scale-[0.97]"
                                    @click="addRole(role)"
                                >
                                    <Plus class="size-3.5" />
                                    {{ ROLE_META[role]?.label ?? role }}
                                </Button>
                            </div>
                        </div>

                        <InputError :message="roleError" class="mt-3" />
                        <p
                            v-if="isLastNonAdminRole"
                            class="mt-3 text-xs text-muted-foreground"
                        >
                            Ini satu-satunya role kamu. Untuk keluar sepenuhnya,
                            hapus akun di bawah.
                        </p>
                    </CardContent>
                </Card>

                <!-- Danger zone -->
                <Card
                    v-if="!user.is_admin"
                    class="reveal rounded-2xl border-destructive/30 bg-destructive/[0.03]"
                >
                    <CardContent class="p-6">
                        <div class="mb-3 flex items-center gap-2.5">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-destructive/10 text-destructive"
                            >
                                <AlertTriangle class="size-5" />
                            </div>
                            <h3 class="font-semibold text-destructive">
                                Zona Berbahaya
                            </h3>
                        </div>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Akun kamu di-nonaktifkan dan datamu dianonimkan.
                            Riwayat pesanan tetap tersimpan untuk pihak lain.
                            Pastikan saldo wallet Rp0 dan tak ada pesanan aktif.
                        </p>

                        <Dialog>
                            <DialogTrigger as-child>
                                <Button
                                    variant="destructive"
                                    class="w-full transition-transform active:scale-[0.98]"
                                >
                                    <Trash2 class="size-4" /> Hapus Akun
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-md">
                                <DialogHeader>
                                    <DialogTitle>Hapus akun ini?</DialogTitle>
                                    <DialogDescription>
                                        Masukkan password untuk mengonfirmasi.
                                        Tindakan ini tidak dapat dibatalkan.
                                    </DialogDescription>
                                </DialogHeader>
                                <Form
                                    v-bind="ProfileController.destroy.form()"
                                    class="space-y-4"
                                    v-slot="{ errors, processing }"
                                >
                                    <div class="space-y-2">
                                        <Label for="delete_password"
                                            >Password</Label
                                        >
                                        <Input
                                            id="delete_password"
                                            type="password"
                                            name="password"
                                            autocomplete="current-password"
                                            placeholder="Password akun"
                                        />
                                        <InputError
                                            :message="errors.password"
                                        />
                                    </div>
                                    <DialogFooter class="gap-2 sm:gap-0">
                                        <DialogClose as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                >Batal</Button
                                            >
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                        >
                                            Ya, hapus akun
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Resign role confirmation -->
        <Dialog
            :open="resignTarget !== null"
            @update:open="(v) => !v && (resignTarget = null)"
        >
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>
                        Lepas role
                        {{
                            resignTarget
                                ? (ROLE_META[resignTarget]?.label ??
                                  resignTarget)
                                : ''
                        }}?
                    </DialogTitle>
                    <DialogDescription>
                        <template v-if="resignTarget === 'seller'">
                            Tokomu disembunyikan dari katalog (tidak dihapus)
                            dan bisa dipulihkan jika kamu menambah role Penjual
                            lagi.
                        </template>
                        <template v-else>
                            Kamu bisa menambahkannya kembali kapan saja. Riwayat
                            terkait tetap tersimpan.
                        </template>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <Button variant="outline" @click="resignTarget = null"
                        >Batal</Button
                    >
                    <Button variant="destructive" @click="confirmResign"
                        >Lepas role</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
@media (prefers-reduced-motion: no-preference) {
    .reveal {
        animation: reveal-up 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .reveal:nth-child(2) {
        animation-delay: 0.05s;
    }
    .reveal:nth-child(3) {
        animation-delay: 0.1s;
    }
}

@keyframes reveal-up {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
