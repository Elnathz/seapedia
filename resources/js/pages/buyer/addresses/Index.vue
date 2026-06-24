<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { MapPin, Pencil, Plus, Star, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerAddressController from '@/actions/App/Http/Controllers/Web/BuyerAddressController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as indexAddresses } from '@/routes/buyer/addresses';

interface AddressData {
    id: number;
    recipient_name: string;
    phone: string;
    full_address: string;
    is_default: boolean;
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

const formBinding = computed(() =>
    editing.value
        ? BuyerAddressController.update.form(editing.value.id)
        : BuyerAddressController.store.form(),
);

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
                    <p class="text-sm text-muted-foreground">
                        {{ address.phone }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ address.full_address }}
                    </p>

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

        <Dialog v-model:open="formOpen">
            <DialogContent>
                <Form
                    :key="editing?.id ?? 'create'"
                    v-bind="formBinding"
                    :options="{ preserveScroll: true }"
                    @success="formOpen = false"
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
    </div>
</template>
