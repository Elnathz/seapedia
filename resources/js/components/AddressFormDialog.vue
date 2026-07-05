<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerAddressController from '@/actions/App/Http/Controllers/Web/BuyerAddressController';
import InputError from '@/components/InputError.vue';
import MapPicker from '@/components/MapPicker.vue';
import RegionCascader from '@/components/RegionCascader.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface AddressData {
    id: number;
    recipient_name: string;
    phone: string;
    full_address: string;
    is_default: boolean;
    province?: string;
    city?: string;
    district?: string;
    village?: string;
    postal_code?: string;
    latitude?: number | null;
    longitude?: number | null;
}

const props = defineProps<{
    open: boolean;
    editing?: AddressData | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    success: [];
}>();

const { t } = useI18n();

const formProvince = ref('');
const formCity = ref('');
const formDistrict = ref('');
const formVillage = ref('');
const formPostal = ref('');
const formLat = ref<number | null>(null);
const formLng = ref<number | null>(null);
const regionRef = ref<InstanceType<typeof RegionCascader> | null>(null);

// Map pin moved → auto-fill postal code + resolve the region cascade from the
// reverse-geocoded parts (best-effort; the user can still correct any field).
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

// A create form posts to store; an edit form to update the given record.
const formBinding = computed(() =>
    props.editing
        ? BuyerAddressController.update.form(props.editing.id)
        : BuyerAddressController.store.form(),
);

// Seed the region cascader + map pin from the record being edited whenever the
// dialog opens, so both start on the right values (and reset for a fresh create).
watch(
    () => [props.open, props.editing?.id] as const,
    ([isOpen]) => {
        if (!isOpen) {
            return;
        }

        formProvince.value = props.editing?.province ?? '';
        formCity.value = props.editing?.city ?? '';
        formDistrict.value = props.editing?.district ?? '';
        formVillage.value = props.editing?.village ?? '';
        formPostal.value = props.editing?.postal_code ?? '';
        formLat.value = props.editing?.latitude ?? null;
        formLng.value = props.editing?.longitude ?? null;
    },
    { immediate: true },
);

function onSuccess() {
    emit('update:open', false);
    emit('success');
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-h-[90vh] overflow-y-auto">
            <Form
                :key="editing?.id ?? 'create'"
                v-bind="formBinding"
                :options="{ preserveScroll: true }"
                @success="onSuccess"
                v-slot="{ errors, processing }"
                class="space-y-4"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{
                            editing
                                ? t('address.editTitle')
                                : t('address.addTitle')
                        }}
                    </DialogTitle>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="recipient_name">{{
                        t('address.recipientLabel')
                    }}</Label>
                    <Input
                        id="recipient_name"
                        name="recipient_name"
                        :default-value="editing?.recipient_name ?? ''"
                        required
                        maxlength="255"
                    />
                    <InputError :message="errors.recipient_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">{{ t('address.phoneLabel') }}</Label>
                    <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        :default-value="editing?.phone ?? ''"
                        required
                        maxlength="20"
                    />
                    <InputError :message="errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="full_address">{{
                        t('address.fullAddressLabel')
                    }}</Label>
                    <Textarea
                        id="full_address"
                        name="full_address"
                        :default-value="editing?.full_address ?? ''"
                        required
                        maxlength="500"
                        rows="3"
                        :placeholder="t('address.fullAddressPlaceholder')"
                    />
                    <InputError :message="errors.full_address" />
                </div>

                <RegionCascader
                    ref="regionRef"
                    v-model:province="formProvince"
                    v-model:city="formCity"
                    v-model:district="formDistrict"
                    v-model:village="formVillage"
                    :errors="errors"
                />

                <div class="grid gap-2">
                    <Label for="postal_code">Kode Pos</Label>
                    <Input
                        id="postal_code"
                        name="postal_code"
                        v-model="formPostal"
                        required
                        maxlength="20"
                    />
                    <InputError :message="errors.postal_code" />
                </div>

                <div class="grid gap-2">
                    <Label>Titik Lokasi di Peta</Label>
                    <MapPicker
                        v-model:latitude="formLat"
                        v-model:longitude="formLng"
                        @update:geo="onGeo"
                    />
                    <input
                        type="hidden"
                        name="latitude"
                        :value="formLat ?? ''"
                    />
                    <input
                        type="hidden"
                        name="longitude"
                        :value="formLng ?? ''"
                    />
                    <InputError :message="errors.latitude" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" type="button">{{
                            t('common.cancel')
                        }}</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ t('common.save') }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
