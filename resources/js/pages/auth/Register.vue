<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import {
    ShoppingBag,
    Store,
    Truck,
    ArrowRight,
    ArrowLeft,
    Check,
    Info,
    X,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

const props = defineProps<{
    passwordRules: string;
    roles: string[];
}>();

defineOptions({
    layout: {
        title: 'Buat akun baru',
        description: 'Isi data di bawah untuk bergabung ke SEAPEDIA',
    },
});

const roleOrder = ['buyer', 'seller', 'driver'] as const;

type RoleKey = (typeof roleOrder)[number];

const roleConfig: Record<
    RoleKey,
    {
        icon: typeof ShoppingBag;
        label: string;
        tagline: string;
        gradient: string;
        lightBg: string;
        iconColor: string;
        borderSelected: string;
        features: string[];
        highlights: string[];
    }
> = {
    buyer: {
        icon: ShoppingBag,
        label: 'Pembeli',
        tagline: 'Belanja produk dari toko',
        gradient: 'from-blue-500 to-blue-600',
        lightBg: 'bg-blue-500/10',
        iconColor: 'text-blue-500',
        borderSelected: 'border-blue-500/50 ring-blue-500/20',
        highlights: ['Wallet & checkout aman', 'Lacak pesanan real-time'],
        features: [
            'Top-up saldo wallet untuk belanja',
            'Kelola alamat pengiriman default',
            'Keranjang belanja (satu toko per checkout)',
            'Checkout dengan PPN 12%, ongkir & diskon',
            'Lacak status pesanan sampai selesai',
        ],
    },
    seller: {
        icon: Store,
        label: 'Penjual',
        tagline: 'Buka toko dan jual produkmu',
        gradient: 'from-primary to-brand',
        lightBg: 'bg-primary/10',
        iconColor: 'text-primary',
        borderSelected: 'border-primary ring-primary/20',
        highlights: ['Toko & katalog sendiri', 'Proses pesanan masuk'],
        features: [
            'Buat toko dengan nama unik',
            'Kelola katalog produk, harga & stok',
            'Terima pesanan masuk dari pembeli',
            'Proses order hingga siap diantar kurir',
            'Pantau laporan pemasukan penjualan',
        ],
    },
    driver: {
        icon: Truck,
        label: 'Kurir',
        tagline: 'Antar pesanan, dapat penghasilan',
        gradient: 'from-amber-500 to-amber-600',
        lightBg: 'bg-amber-500/10',
        iconColor: 'text-amber-500',
        borderSelected: 'border-amber-500/50 ring-amber-500/20',
        highlights: ['Ambil job siap antar', 'Pendapatan dari ongkir'],
        features: [
            'Lihat job pengiriman yang siap diambil',
            'Ambil & antar pesanan ke pembeli',
            'Konfirmasi selesai setelah terkirim',
            'Pendapatan 80% ongkir langsung ke wallet',
            'Riwayat pengiriman & ringkasan earning',
        ],
    },
};

const step = ref(1);
const formRef = ref<InstanceType<typeof Form> | null>(null);
const selectedRoles = ref<string[]>([]);
const flippedCard = ref<string | null>(null);
const reducedMotion = ref(false);
const isValidatingStep1 = ref(false);

const displayRoles = computed(() =>
    roleOrder.filter((role) => props.roles.includes(role)),
);

onMounted(() => {
    reducedMotion.value = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;
});

function nextStep() {
    const el = formRef.value?.$el;
    const nativeForm =
        el instanceof HTMLFormElement
            ? el
            : el instanceof HTMLElement
              ? el.querySelector('form')
              : document.querySelector('form');

    if (nativeForm) {
        const passwordInput = nativeForm.querySelector(
            'input[name="password"]',
        ) as HTMLInputElement;
        const confirmInput = nativeForm.querySelector(
            'input[name="password_confirmation"]',
        ) as HTMLInputElement;

        if (passwordInput && confirmInput) {
            if (passwordInput.value !== confirmInput.value) {
                confirmInput.setCustomValidity(
                    'Kata sandi dan konfirmasi kata sandi tidak cocok.',
                );
            } else {
                confirmInput.setCustomValidity('');
            }
        }

        if (!nativeForm.reportValidity()) {
            return;
        }

        isValidatingStep1.value = true;

        const formData = new FormData(nativeForm);
        formData.delete('roles[]');

        router.post(store.url(), Object.fromEntries(formData), {
            preserveScroll: true,
            preserveState: true,
            onError: (errors: Record<string, string>) => {
                const step1Fields = [
                    'name',
                    'username',
                    'email',
                    'phone',
                    'password',
                    'password_confirmation',
                ];
                const hasStep1Error = Object.keys(errors).some((field) =>
                    step1Fields.includes(field),
                );

                // If there are no errors in step 1 fields, it means the only error is 'roles' (because it's empty)
                // We can safely proceed to step 2.
                if (!hasStep1Error) {
                    step.value = 2;
                    flippedCard.value = null;
                }
            },
            onFinish: () => {
                isValidatingStep1.value = false;
            },
        });
    } else {
        step.value = 2;
        flippedCard.value = null;
    }
}

function prevStep() {
    step.value = 1;
    flippedCard.value = null;
}

function isSelected(role: string) {
    return selectedRoles.value.includes(role);
}

function showDetail(role: string, event: Event) {
    event.preventDefault();
    event.stopPropagation();
    flippedCard.value = role;
}

function hideDetail(event: Event) {
    event.preventDefault();
    event.stopPropagation();
    flippedCard.value = null;
}

function selectRoleFromBack(role: string, event: Event) {
    event.preventDefault();
    event.stopPropagation();

    if (!selectedRoles.value.includes(role)) {
        selectedRoles.value = [...selectedRoles.value, role];
    }

    flippedCard.value = null;
}

function isFlipped(role: string) {
    return flippedCard.value === role;
}

function handleError(errors: Record<string, string>) {
    // If we are currently just validating step 1, suppress the generic toasts
    if (isValidatingStep1.value) {
        return;
    }

    const step1Fields = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'password_confirmation',
    ];
    const hasStep1Error = Object.keys(errors).some((field) =>
        step1Fields.includes(field),
    );

    if (hasStep1Error && step.value === 2) {
        step.value = 1;
        toast.error('Gagal membuat akun', {
            description: 'Periksa kembali data Anda pada langkah 1.',
        });
    } else if (Object.keys(errors).length > 0) {
        toast.error('Gagal membuat akun', {
            description: 'Pastikan Anda telah memilih peran dengan benar.',
        });
    }
}
</script>

<template>
    <div v-bind="$attrs">
        <Head title="Daftar" />

        <Form
            novalidate
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            :class="['flex flex-col gap-6', step === 2 && 'register-form-wide']"
            ref="formRef"
            @error="handleError"
        >
            <!-- Step 1 -->
            <div v-show="step === 1" class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="name">Nama lengkap</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        minlength="2"
                        maxlength="100"
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Nama lengkap kamu"
                    />
                    <InputError
                        :message="errors.name || $page.props.errors.name"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <Input
                        id="username"
                        type="text"
                        required
                        minlength="3"
                        maxlength="30"
                        pattern="^[A-Za-z0-9_\-]+$"
                        title="Username hanya boleh berisi huruf, angka, strip (-), atau underscore (_)"
                        :tabindex="2"
                        autocomplete="username"
                        name="username"
                        placeholder="username_kamu"
                    />
                    <InputError
                        :message="
                            errors.username || $page.props.errors.username
                        "
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Alamat email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        maxlength="255"
                        :tabindex="3"
                        autocomplete="email"
                        name="email"
                        placeholder="email@gmail.com"
                    />
                    <InputError
                        :message="errors.email || $page.props.errors.email"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">Nomor HP</Label>
                    <Input
                        id="phone"
                        type="tel"
                        pattern="^(08|\+62)[0-9]{8,12}$"
                        title="Nomor HP harus diawali dengan 08 atau +62 dan berisi angka dengan panjang yang sesuai"
                        required
                        :tabindex="4"
                        autocomplete="tel"
                        name="phone"
                        placeholder="08xxxxxxxxxx atau +62xxxxxxxxxx"
                    />
                    <p class="mt-0.5 text-[11px] text-muted-foreground">
                        Format penulisannya: 08... atau +62...
                    </p>
                    <InputError
                        :message="errors.phone || $page.props.errors.phone"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Kata sandi</Label>
                    <PasswordInput
                        id="password"
                        required
                        minlength="8"
                        :tabindex="5"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Min. 8 karakter"
                        :passwordrules="passwordRules"
                    />
                    <p class="mt-0.5 text-[11px] text-muted-foreground">
                        Wajib mengandung minimal 8 karakter, huruf besar-kecil,
                        angka, dan simbol.
                    </p>
                    <InputError
                        :message="
                            errors.password || $page.props.errors.password
                        "
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation"
                        >Konfirmasi kata sandi</Label
                    >
                    <PasswordInput
                        id="password_confirmation"
                        required
                        minlength="8"
                        :tabindex="6"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Ulangi kata sandi"
                        :passwordrules="passwordRules"
                    />
                    <InputError
                        :message="
                            errors.password_confirmation ||
                            $page.props.errors.password_confirmation
                        "
                    />
                </div>

                <Button
                    type="button"
                    class="mt-2 flex w-full items-center justify-center gap-2"
                    tabindex="7"
                    @click="nextStep"
                >
                    Pemilihan role
                    <ArrowRight class="size-4" />
                </Button>

                <div class="mt-2 text-center text-sm text-muted-foreground">
                    Sudah punya akun?
                    <TextLink
                        :href="login()"
                        class="underline underline-offset-4"
                        :tabindex="8"
                        >Masuk</TextLink
                    >
                </div>
            </div>

            <!-- Step 2 — widens on desktop to fit 3 role cards inside the right panel -->
            <div
                v-show="step === 2"
                class="register-step-2 grid w-full min-w-0 gap-5"
            >
                <div class="mb-1 flex items-center gap-3">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 shrink-0 rounded-full hover:bg-muted"
                        aria-label="Kembali ke data akun"
                        @click="prevStep"
                    >
                        <ArrowLeft class="size-4" />
                    </Button>
                    <div>
                        <h3
                            class="text-lg leading-tight font-semibold text-foreground"
                        >
                            Pilih peran
                        </h3>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Geser untuk lihat semua peran. Pilih satu atau
                            lebih, bisa diubah nanti.
                        </p>
                    </div>
                </div>

                <div class="role-rail w-full min-w-0">
                    <div
                        class="role-rail-track -mx-0.5 flex snap-x snap-mandatory [scrollbar-width:thin] items-stretch gap-4 overflow-x-auto overscroll-x-contain px-0.5 pt-1 pb-2 [-ms-overflow-style:none]"
                        role="list"
                        aria-label="Pilihan peran SEAPEDIA"
                    >
                        <div
                            v-for="(role, index) in displayRoles"
                            :key="role"
                            class="role-card-shell h-[420px] w-[14.75rem] shrink-0 snap-start sm:w-[15.25rem]"
                            :class="{ 'role-card-enter': !reducedMotion }"
                            :style="
                                reducedMotion
                                    ? undefined
                                    : { animationDelay: `${index * 80}ms` }
                            "
                            role="listitem"
                        >
                            <div
                                class="relative h-full w-full [perspective:1000px]"
                            >
                                <div
                                    class="role-flip-inner absolute inset-0 transition-transform duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] [transform-style:preserve-3d] motion-reduce:duration-0"
                                    :class="{
                                        'is-flipped':
                                            isFlipped(role) && !reducedMotion,
                                        'is-flipped-reduced':
                                            isFlipped(role) && reducedMotion,
                                    }"
                                >
                                    <!-- Front -->
                                    <div
                                        class="role-face-front absolute inset-0 flex min-w-0 [transform:translate3d(0,0,0)] flex-col rounded-2xl border bg-card shadow-sm [-webkit-backface-visibility:hidden] [-webkit-transform:translate3d(0,0,0)] [backface-visibility:hidden]"
                                        :class="[
                                            isSelected(role)
                                                ? [
                                                      'ring-2',
                                                      roleConfig[
                                                          role as RoleKey
                                                      ]?.borderSelected ??
                                                          'border-primary ring-primary/20',
                                                  ]
                                                : 'border-border hover:border-primary/30 hover:shadow-md',
                                            isFlipped(role)
                                                ? 'pointer-events-none'
                                                : '',
                                        ]"
                                    >
                                        <!-- Ambient glow wrapper -->
                                        <div
                                            class="pointer-events-none absolute inset-0 overflow-hidden rounded-2xl"
                                        >
                                            <div
                                                class="absolute -top-10 -right-10 size-36 rounded-full bg-gradient-to-br opacity-[0.18] blur-2xl"
                                                :class="
                                                    roleConfig[role as RoleKey]
                                                        ?.gradient
                                                "
                                                aria-hidden="true"
                                            />
                                            <div
                                                class="absolute -bottom-12 -left-8 size-28 rounded-full bg-gradient-to-br opacity-10 blur-2xl"
                                                :class="
                                                    roleConfig[role as RoleKey]
                                                        ?.gradient
                                                "
                                                aria-hidden="true"
                                            />
                                        </div>

                                        <!-- Selection indicator -->
                                        <span
                                            class="absolute top-3 right-3 z-10 flex size-6 items-center justify-center rounded-full border transition-colors"
                                            :class="
                                                isSelected(role)
                                                    ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                                    : 'border-border/80 bg-background/90'
                                            "
                                            aria-hidden="true"
                                        >
                                            <Check
                                                v-if="isSelected(role)"
                                                class="size-3.5"
                                            />
                                        </span>

                                        <label
                                            class="relative z-10 flex min-h-0 min-w-0 flex-1 cursor-pointer flex-col p-4 pb-3"
                                            :for="`role-${role}`"
                                        >
                                            <div
                                                class="flex flex-1 flex-col items-center justify-center px-1 text-center"
                                            >
                                                <div
                                                    class="flex size-[4.5rem] items-center justify-center rounded-2xl bg-gradient-to-br shadow-lg ring-4 ring-background"
                                                    :class="
                                                        roleConfig[
                                                            role as RoleKey
                                                        ]?.gradient
                                                    "
                                                >
                                                    <component
                                                        :is="
                                                            roleConfig[
                                                                role as RoleKey
                                                            ]?.icon ??
                                                            ShoppingBag
                                                        "
                                                        class="size-9 text-white"
                                                    />
                                                </div>

                                                <div class="mt-4 min-w-0">
                                                    <span
                                                        class="text-lg font-bold tracking-tight text-foreground"
                                                    >
                                                        {{
                                                            roleConfig[
                                                                role as RoleKey
                                                            ]?.label ?? role
                                                        }}
                                                    </span>
                                                    <p
                                                        class="mt-1.5 text-sm leading-snug text-muted-foreground"
                                                    >
                                                        {{
                                                            roleConfig[
                                                                role as RoleKey
                                                            ]?.tagline ?? ''
                                                        }}
                                                    </p>
                                                </div>

                                                <ul
                                                    class="mt-4 w-full space-y-1.5"
                                                >
                                                    <li
                                                        v-for="highlight in roleConfig[
                                                            role as RoleKey
                                                        ]?.highlights ?? []"
                                                        :key="highlight"
                                                        class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-[11px] leading-snug text-muted-foreground"
                                                        :class="
                                                            roleConfig[
                                                                role as RoleKey
                                                            ]?.lightBg
                                                        "
                                                    >
                                                        <Check
                                                            class="size-3 shrink-0"
                                                            :class="
                                                                roleConfig[
                                                                    role as RoleKey
                                                                ]?.iconColor
                                                            "
                                                        />
                                                        <span>{{
                                                            highlight
                                                        }}</span>
                                                    </li>
                                                </ul>

                                                <p
                                                    v-if="isSelected(role)"
                                                    class="mt-3 text-[11px] font-medium text-primary"
                                                >
                                                    Peran dipilih
                                                </p>
                                            </div>

                                            <input
                                                :id="`role-${role}`"
                                                v-model="selectedRoles"
                                                type="checkbox"
                                                :value="role"
                                                class="sr-only"
                                            />
                                        </label>

                                        <div class="relative z-10 px-4 pb-4">
                                            <button
                                                type="button"
                                                class="flex w-full min-w-0 items-center justify-center gap-1.5 rounded-xl border border-border bg-muted/50 px-2 py-2 text-[11px] leading-snug font-medium text-foreground transition-colors hover:border-primary/30 hover:bg-muted/80 focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:outline-none"
                                                :aria-expanded="isFlipped(role)"
                                                @click="
                                                    showDetail(role, $event)
                                                "
                                            >
                                                <Info
                                                    class="size-3.5 shrink-0 text-muted-foreground"
                                                />
                                                <span class="text-center"
                                                    >Lihat fitur peran</span
                                                >
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Back -->
                                    <div
                                        class="role-face-back absolute inset-0 flex min-w-0 flex-col rounded-2xl border-2 border-primary/30 bg-card p-4 shadow-md [-webkit-backface-visibility:hidden] [backface-visibility:hidden]"
                                        :class="[
                                            reducedMotion
                                                ? ''
                                                : '[transform:rotateY(180deg)_translate3d(0,0,0)] [-webkit-transform:rotateY(180deg)_translate3d(0,0,0)]',
                                            !isFlipped(role)
                                                ? 'pointer-events-none'
                                                : '',
                                        ]"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br"
                                                :class="
                                                    roleConfig[role as RoleKey]
                                                        ?.gradient
                                                "
                                            >
                                                <component
                                                    :is="
                                                        roleConfig[
                                                            role as RoleKey
                                                        ]?.icon ?? ShoppingBag
                                                    "
                                                    class="size-5 text-white"
                                                />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="text-sm font-bold text-foreground"
                                                >
                                                    {{
                                                        roleConfig[
                                                            role as RoleKey
                                                        ]?.label ?? role
                                                    }}
                                                </p>
                                                <p
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    Detail kemampuan peran
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                class="flex size-7 shrink-0 items-center justify-center rounded-full border border-border/80 bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:outline-none"
                                                aria-label="Tutup detail peran"
                                                @click="hideDetail($event)"
                                            >
                                                <X class="size-3.5" />
                                            </button>
                                        </div>

                                        <ul
                                            class="mt-3 min-h-0 flex-1 space-y-2 overflow-y-auto pr-0.5"
                                        >
                                            <li
                                                v-for="feature in roleConfig[
                                                    role as RoleKey
                                                ]?.features ?? []"
                                                :key="feature"
                                                class="flex min-w-0 items-start gap-2 text-[11px] leading-snug text-muted-foreground"
                                            >
                                                <span
                                                    class="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full"
                                                    :class="
                                                        roleConfig[
                                                            role as RoleKey
                                                        ]?.lightBg
                                                    "
                                                >
                                                    <Check
                                                        class="size-2.5"
                                                        :class="
                                                            roleConfig[
                                                                role as RoleKey
                                                            ]?.iconColor
                                                        "
                                                    />
                                                </span>
                                                {{ feature }}
                                            </li>
                                        </ul>

                                        <p
                                            class="mt-2 shrink-0 text-[10px] leading-snug text-muted-foreground"
                                        >
                                            Satu akun bisa punya beberapa peran
                                            & satu saldo wallet.
                                        </p>

                                        <div class="mt-3 shrink-0">
                                            <Button
                                                type="button"
                                                size="sm"
                                                class="h-9 w-full rounded-xl px-2 text-xs"
                                                :variant="
                                                    isSelected(role)
                                                        ? 'secondary'
                                                        : 'default'
                                                "
                                                @click="
                                                    selectRoleFromBack(
                                                        role,
                                                        $event,
                                                    )
                                                "
                                            >
                                                {{
                                                    isSelected(role)
                                                        ? 'Sudah dipilih'
                                                        : 'Pilih peran ini'
                                                }}
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p
                        class="mt-2 mb-4 text-center text-[11px] text-muted-foreground"
                    >
                        Geser ke samping untuk melihat semua peran
                    </p>

                    <input
                        v-for="role in selectedRoles"
                        :key="`selected-role-${role}`"
                        type="hidden"
                        name="roles[]"
                        :value="role"
                    />

                    <InputError :message="errors.roles" class="text-center" />

                    <Button
                        type="submit"
                        class="mt-2 w-full max-w-full"
                        tabindex="7"
                        :disabled="processing || selectedRoles.length === 0"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        Buat akun
                    </Button>
                </div>
            </div>
        </Form>
    </div>
</template>

<style scoped>
.role-card-shell {
    flex: 0 0 auto;
}

.role-flip-inner.is-flipped {
    transform: rotateY(180deg);
}

.role-flip-inner.is-flipped-reduced .role-face-front {
    visibility: hidden;
    opacity: 0;
    pointer-events: none;
}

.role-flip-inner.is-flipped-reduced .role-face-back {
    transform: none;
}

@keyframes role-card-enter {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.role-card-enter {
    animation: role-card-enter 0.45s cubic-bezier(0.4, 0, 0.2, 1) both;
}

@media (prefers-reduced-motion: reduce) {
    .role-card-enter {
        animation: none;
    }

    .role-flip-inner {
        transition: none !important;
    }
}
</style>
