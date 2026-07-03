<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    Check,
    CircleAlert,
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
import { Card } from '@/components/ui/card';
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
const originLat = ref<number | null>(props.store?.origin_latitude ?? null);
const originLng = ref<number | null>(props.store?.origin_longitude ?? null);

const logoUrl = computed(() =>
    props.store?.logo_path ? `/storage/${props.store.logo_path}` : null,
);

// The summary card reads the SAVED store, so after a successful save it
// re-renders with the new data — the clearest "it worked" signal there is.
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
const isComplete = computed(
    () => hasAddress.value && hasLocation.value && !!props.store?.description,
);

const justSaved = ref(false);
function onSaved() {
    justSaved.value = true;
    setTimeout(() => (justSaved.value = false), 2500);
}
</script>

<template>
    <Head :title="t('store.myStore')" />

    <div class="mx-auto flex max-w-3xl flex-col gap-6 pb-10">
        <Heading
            variant="small"
            :title="store ? t('store.myStore') : t('store.createTitle')"
            :description="
                store
                    ? t('store.editDescription')
                    : t('store.createDescription')
            "
        />

        <!-- Saved-state summary: identity + assembled address + readiness -->
        <Card
            v-if="store"
            class="overflow-hidden border-0 shadow-md"
        >
            <div
                class="bg-gradient-to-r from-primary/10 via-brand/10 to-primary/10 p-6"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <div
                        class="size-20 shrink-0 overflow-hidden rounded-2xl shadow-lg ring-1 ring-black/5"
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

                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2
                                class="text-xl font-bold text-foreground"
                            >
                                {{ store.name }}
                            </h2>
                            <Badge v-if="store.is_active" variant="secondary">
                                {{ t('store.active') }}
                            </Badge>
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="
                                    isComplete
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-amber-100 text-amber-700'
                                "
                            >
                                <Check v-if="isComplete" class="size-3.5" />
                                <CircleAlert v-else class="size-3.5" />
                                {{
                                    isComplete
                                        ? 'Profil lengkap'
                                        : 'Lengkapi profil'
                                }}
                            </span>
                        </div>

                        <p
                            v-if="store.description"
                            class="text-sm text-muted-foreground"
                        >
                            {{ store.description }}
                        </p>

                        <div class="flex items-start gap-2 text-sm">
                            <MapPin
                                class="mt-0.5 size-4 shrink-0 text-primary"
                            />
                            <div v-if="hasAddress" class="min-w-0 space-y-0.5">
                                <p class="font-medium text-foreground">
                                    {{ store.full_address }}
                                </p>
                                <p class="text-muted-foreground">
                                    {{ regionLine }}
                                </p>
                                <p
                                    v-if="store.postal_code"
                                    class="text-muted-foreground"
                                >
                                    Kode Pos {{ store.postal_code }}
                                </p>
                            </div>
                            <p v-else class="text-muted-foreground">
                                Alamat toko belum diisi.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-2 text-sm"
                            :class="
                                hasLocation
                                    ? 'text-emerald-600'
                                    : 'text-amber-600'
                            "
                        >
                            <Navigation class="size-4 shrink-0" />
                            <span>{{
                                hasLocation
                                    ? 'Titik lokasi pengiriman sudah ditandai'
                                    : 'Titik lokasi pengiriman belum ditandai'
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </Card>

        <div
            v-else
            class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border px-6 py-10 text-center"
        >
            <div
                class="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary"
            >
                <StoreIcon class="size-7" />
            </div>
            <div class="space-y-1">
                <p class="font-medium text-foreground">
                    {{ t('store.emptyTitle') }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ t('store.emptyDescription') }}
                </p>
            </div>
        </div>

        <Form
            v-bind="formBinding"
            class="space-y-8"
            @success="onSaved"
            v-slot="{ errors, processing }"
        >
            <!-- Identity -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <StoreIcon class="size-5 text-primary" />
                    <h3 class="text-lg font-semibold">Identitas Toko</h3>
                </div>
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
            </section>

            <!-- Address & pickup location -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <MapPin class="size-5 text-primary" />
                    <h3 class="text-lg font-semibold">Alamat Toko</h3>
                </div>

                <div
                    class="flex items-start gap-2 rounded-lg bg-muted/50 p-3 text-xs text-muted-foreground"
                >
                    <Info class="mt-0.5 size-4 shrink-0" />
                    <span>
                        Ini alamat toko sebagai titik asal pengiriman (bukan
                        alamat pengiriman pembeli). Pin di peta menandai lokasi
                        toko, dan jaraknya ke alamat pembeli dipakai menghitung
                        ongkir. Isi jalan, RT/RW, wilayah, lalu tandai titik yang
                        sama di peta.
                    </span>
                </div>

                <div class="grid gap-2">
                    <Label for="full_address">Alamat (Jalan, RT/RW)</Label>
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

                <RegionCascader
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
                        :default-value="store?.postal_code ?? ''"
                        maxlength="20"
                        placeholder="mis. 50275"
                    />
                    <InputError :message="errors.postal_code" />
                </div>

                <div class="grid gap-2">
                    <Label>Tandai Lokasi Toko di Peta</Label>
                    <MapPicker
                        v-model:latitude="originLat"
                        v-model:longitude="originLng"
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
            </section>

            <!-- Save bar -->
            <div
                class="sticky bottom-0 -mx-4 flex items-center justify-end gap-3 border-t border-border bg-background/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:px-4"
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
                        <Check class="size-4" />
                        Tersimpan
                    </span>
                </transition>
                <Button
                    :disabled="processing"
                    type="submit"
                    class="min-w-[10rem]"
                >
                    <template v-if="processing">Menyimpan…</template>
                    <template v-else>{{
                        store ? 'Simpan Perubahan' : t('store.create')
                    }}</template>
                </Button>
            </div>
        </Form>
    </div>
</template>
