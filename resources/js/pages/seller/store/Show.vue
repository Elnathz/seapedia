<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    Check,
    ExternalLink,
    Info,
    MapPin,
    Navigation,
    Store as StoreIcon,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SellerStoreController from '@/actions/App/Http/Controllers/Web/SellerStoreController';
import Heading from '@/components/Heading.vue';
import ImageCropUpload from '@/components/ImageCropUpload.vue';
import InputError from '@/components/InputError.vue';
import MapPicker from '@/components/MapPicker.vue';
import RegionCascader from '@/components/RegionCascader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useInitials } from '@/composables/useInitials';
import { show as showSellerStore } from '@/routes/seller/store';

interface StoreData {
    id: number;
    name: string;
    slug: string;
    logo_path: string | null;
    description: string | null;
    full_address: string | null;
    province: string | null;
    city: string | null;
    district: string | null;
    village: string | null;
    postal_code: string | null;
    origin_latitude: number | null;
    origin_longitude: number | null;
    is_active: boolean;
}

const props = defineProps<{
    store: StoreData | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Toko Saya', href: showSellerStore() }],
    },
});

const { t } = useI18n();
const { getInitials, getGradientClass } = useInitials();

const formBinding = computed(() =>
    props.store
        ? SellerStoreController.update.form(props.store.id)
        : SellerStoreController.store.form(),
);

const formProvince = ref(props.store?.province ?? '');
const formCity = ref(props.store?.city ?? '');
const formDistrict = ref(props.store?.district ?? '');
const formVillage = ref(props.store?.village ?? '');
const formPostal = ref(props.store?.postal_code ?? '');
const originLat = ref<number | null>(props.store?.origin_latitude ?? null);
const originLng = ref<number | null>(props.store?.origin_longitude ?? null);
const regionRef = ref<InstanceType<typeof RegionCascader> | null>(null);

// Map pin moved → auto-fill postal + resolve the region cascade (best-effort).
function onGeo(geo: {
    province?: string;
    city?: string;
    district?: string;
    village?: string;
    postal_code?: string;
}) {
    if (geo.postal_code) {
        formPostal.value = geo.postal_code;
    }

    regionRef.value?.applyGeo(geo);
}

const logoUrl = computed(() =>
    props.store?.logo_path ? `/storage/${props.store.logo_path}` : null,
);

const regionLine = computed(() =>
    [
        props.store?.village,
        props.store?.district,
        props.store?.city,
        props.store?.province,
    ]
        .filter(Boolean)
        .join(', '),
);

const hasAddress = computed(
    () => !!(props.store?.full_address && props.store?.city),
);
const hasLocation = computed(
    () =>
        props.store?.origin_latitude !== null &&
        props.store?.origin_latitude !== undefined,
);

// Readiness checklist drives the completeness meter — the page's signature.
const checklist = computed(() => [
    { label: 'Nama toko', done: !!props.store?.name },
    { label: 'Deskripsi toko', done: !!props.store?.description },
    { label: 'Alamat toko', done: hasAddress.value },
    { label: 'Titik lokasi pengiriman', done: hasLocation.value },
]);
const doneCount = computed(() => checklist.value.filter((c) => c.done).length);
const completePct = computed(() =>
    Math.round((doneCount.value / checklist.value.length) * 100),
);
const isComplete = computed(() => completePct.value === 100);

const justSaved = ref(false);
function onSaved() {
    justSaved.value = true;
    setTimeout(() => (justSaved.value = false), 2500);
}
</script>

<template>
    <Head :title="t('store.myStore')" />

    <div class="mx-auto flex max-w-5xl flex-col gap-6 pb-10">
        <Heading
            variant="small"
            :title="store ? t('store.myStore') : t('store.createTitle')"
            :description="
                store
                    ? t('store.editDescription')
                    : t('store.createDescription')
            "
        />

        <!-- Onboarding (no store yet) -->
        <div
            v-if="!store"
            class="flex flex-col items-center gap-3 rounded-3xl border border-dashed border-border bg-muted/20 px-6 py-12 text-center"
        >
            <div
                class="flex size-16 items-center justify-center rounded-2xl bg-primary/10 text-primary"
            >
                <StoreIcon class="size-8" />
            </div>
            <div class="space-y-1">
                <p class="text-lg font-semibold text-foreground">
                    {{ t('store.emptyTitle') }}
                </p>
                <p class="max-w-md text-sm text-muted-foreground">
                    {{ t('store.emptyDescription') }}
                </p>
            </div>
        </div>

        <!-- Saved-state hero: identity + completeness -->
        <section
            v-else
            class="reveal relative overflow-hidden rounded-3xl border border-border/60 shadow-[0_1px_3px_rgba(0,0,0,0.03),0_28px_56px_-32px_rgba(13,148,136,0.32)]"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary/12 via-brand/8 to-transparent"
                aria-hidden="true"
            />
            <div
                class="relative flex flex-col gap-6 p-6 sm:flex-row sm:items-center sm:p-8"
            >
                <div
                    class="size-20 shrink-0 overflow-hidden rounded-2xl shadow-lg ring-1 ring-black/5 sm:size-24"
                >
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="store.name"
                        class="size-full object-cover"
                    />
                    <div
                        v-else
                        class="flex size-full items-center justify-center text-2xl font-bold text-white"
                        :class="getGradientClass(store.name)"
                    >
                        {{ getInitials(store.name) }}
                    </div>
                </div>

                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-foreground">
                            {{ store.name }}
                        </h2>
                        <Badge v-if="store.is_active" variant="secondary">
                            {{ t('store.active') }}
                        </Badge>
                    </div>
                    <p
                        v-if="hasAddress"
                        class="flex items-start gap-1.5 text-sm text-muted-foreground"
                    >
                        <MapPin class="mt-0.5 size-4 shrink-0 text-primary" />
                        <span>{{ store.full_address }} · {{ regionLine }}</span>
                    </p>
                    <p
                        v-else
                        class="flex items-center gap-1.5 text-sm text-amber-600"
                    >
                        <MapPin class="size-4 shrink-0" /> Alamat toko belum
                        lengkap.
                    </p>

                    <!-- Completeness meter (signature) -->
                    <div class="pt-1">
                        <div
                            class="mb-1 flex items-center justify-between text-xs"
                        >
                            <span class="font-medium text-foreground">
                                {{
                                    isComplete
                                        ? 'Profil toko lengkap'
                                        : `Kelengkapan profil ${completePct}%`
                                }}
                            </span>
                            <span class="text-muted-foreground"
                                >{{ doneCount }}/{{ checklist.length }}</span
                            >
                        </div>
                        <div
                            class="h-1.5 w-full overflow-hidden rounded-full bg-border/70"
                        >
                            <div
                                class="h-full rounded-full transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                :class="
                                    isComplete ? 'bg-emerald-500' : 'bg-primary'
                                "
                                :style="{ width: `${completePct}%` }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Preview storefront -->
                <Button
                    as-child
                    variant="outline"
                    size="sm"
                    class="shrink-0 gap-1.5"
                >
                    <a :href="`/stores/${store.slug}`" target="_blank">
                        Lihat Toko
                        <ExternalLink class="size-3.5" />
                    </a>
                </Button>
            </div>
        </section>

        <Form
            v-bind="formBinding"
            class="grid gap-6 lg:grid-cols-3"
            @success="onSaved"
            v-slot="{ errors, processing }"
        >
            <!-- Main column: identity + address -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Identity -->
                <section
                    class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm"
                >
                    <div class="mb-5 flex items-center gap-2.5">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <StoreIcon class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-semibold">Identitas Toko</h3>
                            <p class="text-xs text-muted-foreground">
                                Logo, nama & deskripsi
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label>Foto Profil Toko</Label>
                            <ImageCropUpload
                                name="logo"
                                :current-url="logoUrl"
                                :aspect="1"
                            />
                            <InputError :message="errors.logo" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="name">
                                {{ t('store.nameLabel') }}
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="name"
                                name="name"
                                :default-value="store?.name"
                                required
                                maxlength="100"
                                :placeholder="t('store.namePlaceholder')"
                            />
                            <InputError :message="errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="description">{{
                                t('store.descriptionLabel')
                            }}</Label>
                            <Textarea
                                id="description"
                                name="description"
                                :default-value="store?.description ?? ''"
                                maxlength="500"
                                rows="3"
                                :placeholder="t('store.descriptionPlaceholder')"
                            />
                            <InputError :message="errors.description" />
                        </div>
                    </div>
                </section>

                <!-- Address & pickup location -->
                <section
                    class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm"
                >
                    <div class="mb-5 flex items-center gap-2.5">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <MapPin class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-semibold">Alamat & Titik Kirim</h3>
                            <p class="text-xs text-muted-foreground">
                                Titik asal untuk hitung ongkir
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            class="flex items-start gap-2 rounded-lg bg-muted/50 p-3 text-xs text-muted-foreground"
                        >
                            <Info class="mt-0.5 size-4 shrink-0" />
                            <span>
                                Ini titik asal pengiriman (bukan alamat
                                pembeli). Tandai lokasi di peta — wilayah & kode
                                pos terisi otomatis dan jaraknya ke pembeli
                                dipakai menghitung ongkir.
                            </span>
                        </div>

                        <div class="grid gap-2">
                            <Label for="full_address"
                                >Alamat (Jalan, RT/RW)</Label
                            >
                            <Textarea
                                id="full_address"
                                name="full_address"
                                :default-value="store?.full_address ?? ''"
                                maxlength="500"
                                rows="2"
                                placeholder="mis. Jl. Prof. Soedarto No. 13, RT 02/RW 05"
                            />
                            <InputError :message="errors.full_address" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Tandai Lokasi Toko di Peta</Label>
                            <MapPicker
                                v-model:latitude="originLat"
                                v-model:longitude="originLng"
                                @update:geo="onGeo"
                            />
                            <input
                                type="hidden"
                                name="origin_latitude"
                                :value="originLat ?? ''"
                            />
                            <input
                                type="hidden"
                                name="origin_longitude"
                                :value="originLng ?? ''"
                            />
                            <InputError :message="errors.origin_latitude" />
                        </div>

                        <RegionCascader
                            ref="regionRef"
                            v-model:province="formProvince"
                            v-model:city="formCity"
                            v-model:district="formDistrict"
                            v-model:village="formVillage"
                            :errors="errors"
                        />

                        <div class="grid gap-2 sm:max-w-[12rem]">
                            <Label for="postal_code">Kode Pos</Label>
                            <Input
                                id="postal_code"
                                name="postal_code"
                                v-model="formPostal"
                                maxlength="20"
                                placeholder="mis. 50275"
                            />
                            <InputError :message="errors.postal_code" />
                        </div>
                    </div>
                </section>
            </div>

            <!-- Side rail: readiness checklist -->
            <div class="lg:col-span-1">
                <div
                    class="space-y-4 rounded-2xl border border-border/60 bg-card p-6 shadow-sm lg:sticky lg:top-6"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl"
                            :class="
                                isComplete
                                    ? 'bg-emerald-100 text-emerald-600'
                                    : 'bg-amber-100 text-amber-600'
                            "
                        >
                            <Navigation class="size-5" />
                        </div>
                        <div>
                            <h3 class="font-semibold">Kesiapan Toko</h3>
                            <p class="text-xs text-muted-foreground">
                                {{ doneCount }} dari
                                {{ checklist.length }} beres
                            </p>
                        </div>
                    </div>

                    <ul class="space-y-2">
                        <li
                            v-for="item in checklist"
                            :key="item.label"
                            class="flex items-center gap-2.5 text-sm"
                        >
                            <span
                                class="flex size-5 shrink-0 items-center justify-center rounded-full"
                                :class="
                                    item.done
                                        ? 'bg-emerald-500 text-white'
                                        : 'border border-border bg-background'
                                "
                            >
                                <Check v-if="item.done" class="size-3" />
                            </span>
                            <span
                                :class="
                                    item.done
                                        ? 'text-foreground'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ item.label }}
                            </span>
                        </li>
                    </ul>

                    <p
                        v-if="!isComplete"
                        class="rounded-lg bg-primary/5 px-3 py-2 text-xs text-muted-foreground"
                    >
                        Lengkapi semua poin agar tokomu tampil optimal dan
                        ongkir pembeli terhitung akurat.
                    </p>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-border/60 pt-4"
                    >
                        <transition
                            enter-active-class="transition duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            leave-active-class="transition duration-200"
                            leave-to-class="opacity-0"
                        >
                            <span
                                v-if="justSaved"
                                class="flex items-center gap-1.5 text-sm font-medium text-emerald-600"
                            >
                                <Check class="size-4" /> Tersimpan
                            </span>
                        </transition>
                        <Button
                            :disabled="processing"
                            type="submit"
                            class="transition-transform active:scale-[0.98]"
                        >
                            <template v-if="processing">Menyimpan…</template>
                            <template v-else>{{
                                store ? 'Simpan Perubahan' : t('store.create')
                            }}</template>
                        </Button>
                    </div>
                </div>
            </div>
        </Form>
    </div>
</template>

<style scoped>
@media (prefers-reduced-motion: no-preference) {
    .reveal {
        animation: reveal-up 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
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
