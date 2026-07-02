<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Store as StoreIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SellerStoreController from '@/actions/App/Http/Controllers/Web/SellerStoreController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import MapPicker from '@/components/MapPicker.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { show as showSellerStore } from '@/routes/seller/store';

interface StoreData {
    id: number;
    name: string;
    slug: string;
    description: string | null;
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

const formBinding = computed(() =>
    props.store
        ? SellerStoreController.update.form(props.store.id)
        : SellerStoreController.store.form(),
);

const originLat = ref<number | null>(props.store?.origin_latitude ?? null);
const originLng = ref<number | null>(props.store?.origin_longitude ?? null);

const { t } = useI18n();
</script>

<template>
    <Head :title="t('store.myStore')" />

    <div class="flex flex-col gap-6">
        <Heading
            variant="small"
            :title="store ? store.name : t('store.createTitle')"
            :description="
                store
                    ? t('store.editDescription')
                    : t('store.createDescription')
            "
        />

        <div
            v-if="!store"
            class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border px-6 py-10 text-center"
        >
            <StoreIcon class="size-10 text-muted-foreground" />
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
            class="max-w-lg space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name"
                    >{{ t('store.nameLabel') }}
                    <span class="text-destructive">*</span></Label
                >
                <Input
                    id="name"
                    name="name"
                    :default-value="store?.name"
                    required
                    maxlength="255"
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
                    maxlength="2000"
                    rows="4"
                    :placeholder="t('store.descriptionPlaceholder')"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2">
                <Label>Titik Asal Pengiriman</Label>
                <p class="text-xs text-muted-foreground">
                    Tandai lokasi toko/gudang asal pengiriman. Dipakai untuk
                    menghitung ongkir berdasarkan jarak ke alamat pembeli.
                </p>
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

            <div class="flex items-center gap-3">
                <Button :disabled="processing" type="submit">
                    {{ store ? t('common.save') : t('store.create') }}
                </Button>
                <Badge v-if="store?.is_active" variant="secondary">{{
                    t('store.active')
                }}</Badge>
            </div>
        </Form>
    </div>
</template>
