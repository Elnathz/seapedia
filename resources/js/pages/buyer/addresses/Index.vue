<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { MapPin, Pencil, Plus, Star, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerAddressController from '@/actions/App/Http/Controllers/Web/BuyerAddressController';
import AddressFormDialog from '@/components/AddressFormDialog.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
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
    DialogTrigger,
} from '@/components/ui/dialog';
import { index as indexAddresses } from '@/routes/buyer/addresses';

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

defineProps<{
    addresses: AddressData[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Alamat Pengiriman', href: indexAddresses() }],
    },
});

const { t } = useI18n();

const formOpen = ref(false);
const editing = ref<AddressData | null>(null);

function openCreate() {
    editing.value = null;
    formOpen.value = true;
}

function openEdit(address: AddressData) {
    editing.value = address;
    formOpen.value = true;
}
</script>

<template>
    <Head :title="t('address.title')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading variant="small" :title="t('address.title')" />
            <Button @click="openCreate">
                <Plus class="size-4" />
                {{ t('address.add') }}
            </Button>
        </div>

        <EmptyState
            v-if="addresses.length === 0"
            :icon="MapPin"
            :title="t('address.emptyTitle')"
            :description="t('address.emptyDescription')"
            :action-label="t('address.add')"
            @action="openCreate"
        />

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <Card v-for="address in addresses" :key="address.id">
                <CardContent class="flex flex-col gap-2 pt-6">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium">{{ address.recipient_name }}</p>
                        <Badge v-if="address.is_default" variant="secondary">
                            {{ t('address.defaultBadge') }}
                        </Badge>
                    </div>
                    <div
                        class="mt-1 flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span class="font-semibold">{{ address.phone }}</span>
                    </div>
                    <div class="mt-1 space-y-1 text-sm text-muted-foreground">
                        <p class="leading-relaxed">
                            {{ address.full_address }}
                        </p>
                        <p v-if="address.province" class="text-xs">
                            {{
                                [
                                    address.village,
                                    address.district,
                                    address.city,
                                    address.province,
                                ]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                            <span
                                v-if="address.postal_code"
                                class="font-medium"
                            >
                                - {{ address.postal_code }}</span
                            >
                        </p>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <Form
                            v-if="!address.is_default"
                            v-bind="
                                BuyerAddressController.setDefault.form(
                                    address.id,
                                )
                            "
                            :options="{ preserveScroll: true }"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                size="sm"
                                variant="outline"
                                :disabled="processing"
                            >
                                <Star class="size-4" />
                                {{ t('address.setDefault') }}
                            </Button>
                        </Form>

                        <Button
                            size="sm"
                            variant="outline"
                            @click="openEdit(address)"
                        >
                            <Pencil class="size-4" />
                            <span class="sr-only">{{
                                t('address.edit', {
                                    name: address.recipient_name,
                                })
                            }}</span>
                        </Button>

                        <Dialog>
                            <DialogTrigger as-child>
                                <Button size="sm" variant="destructive">
                                    <Trash2 class="size-4" />
                                    <span class="sr-only">{{
                                        t('address.delete', {
                                            name: address.recipient_name,
                                        })
                                    }}</span>
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="
                                        BuyerAddressController.destroy.form(
                                            address.id,
                                        )
                                    "
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ processing }"
                                >
                                    <DialogHeader class="space-y-3">
                                        <DialogTitle>{{
                                            t('address.deleteConfirmTitle')
                                        }}</DialogTitle>
                                        <DialogDescription>
                                            {{
                                                t(
                                                    'address.deleteConfirmDescription',
                                                )
                                            }}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="mt-4 gap-2">
                                        <DialogClose as-child>
                                            <Button variant="secondary">{{
                                                t('common.cancel')
                                            }}</Button>
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                        >
                                            {{ t('address.deleteConfirm') }}
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </div>

        <AddressFormDialog v-model:open="formOpen" :editing="editing" />
    </div>
</template>
