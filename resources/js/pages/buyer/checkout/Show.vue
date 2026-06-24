<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingBag } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CheckoutController from '@/actions/App/Http/Controllers/Web/CheckoutController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { formatIDR } from '@/lib/utils';
import { index as indexAddresses } from '@/routes/buyer/addresses';
import { show as showWallet } from '@/routes/buyer/wallet';
import { index as catalogIndex } from '@/routes/catalog';

type DeliveryMethodKey = 'instant' | 'next_day' | 'regular';

interface AddressData {
    id: number;
    recipient_name: string;
    phone: string;
    full_address: string;
    is_default: boolean;
}

interface Preview {
    subtotal: number;
    discount_total: number;
    taxable_base: number;
    tax_amount: number;
    delivery_fee: number;
    grand_total: number;
    balance: number;
    sufficient_balance: boolean;
}

interface CartItemData {
    id: number;
    quantity: number;
    price_snapshot: number;
    product: { id: number; name: string; image_path: string | null };
}

interface CartData {
    store: { id: number; name: string } | null;
    items: CartItemData[];
}

const props = defineProps<{
    cart: CartData;
    addresses: AddressData[];
    previews: Record<DeliveryMethodKey, Preview>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Checkout', href: CheckoutController.show() }],
    },
});

const { t } = useI18n();

const defaultAddress =
    props.addresses.find((address) => address.is_default) ??
    props.addresses[0] ??
    null;

const addressId = ref<number | null>(defaultAddress?.id ?? null);
const deliveryMethod = ref<DeliveryMethodKey>('regular');
const submitting = ref(false);
const confirmOpen = ref(false);

const preview = computed(() => props.previews[deliveryMethod.value]);

const deliveryOptions = computed(() => [
    {
        value: 'instant' as const,
        label: t('checkout.instant'),
        eta: t('checkout.instantEta'),
        fee: props.previews.instant.delivery_fee,
    },
    {
        value: 'next_day' as const,
        label: t('checkout.nextDay'),
        eta: t('checkout.nextDayEta'),
        fee: props.previews.next_day.delivery_fee,
    },
    {
        value: 'regular' as const,
        label: t('checkout.regular'),
        eta: t('checkout.regularEta'),
        fee: props.previews.regular.delivery_fee,
    },
]);

function confirmCheckout() {
    confirmOpen.value = false;
    submitting.value = true;

    router.post(
        CheckoutController.store.url(),
        {
            address_id: addressId.value,
            delivery_method: deliveryMethod.value,
        },
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="t('checkout.title')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('checkout.title')" />

        <EmptyState
            v-if="cart.items.length === 0"
            :icon="ShoppingBag"
            :title="t('checkout.emptyCartTitle')"
            :description="t('checkout.emptyCartDescription')"
            :action-label="t('cart.browseCatalog')"
            @action="router.visit(catalogIndex.url())"
        />

        <div v-else class="grid gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <h3 class="font-medium">
                            {{ t('checkout.addressTitle') }}
                        </h3>

                        <div
                            v-if="addresses.length === 0"
                            class="rounded-lg border border-dashed border-border p-4 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                {{ t('checkout.noAddress') }}
                            </p>
                            <Button as-child size="sm" class="mt-3">
                                <Link :href="indexAddresses.url()">{{
                                    t('address.add')
                                }}</Link>
                            </Button>
                        </div>

                        <Select v-else v-model="addressId">
                            <SelectTrigger class="w-full">
                                <SelectValue
                                    :placeholder="t('checkout.selectAddress')"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="address in addresses"
                                    :key="address.id"
                                    :value="address.id"
                                >
                                    {{ address.recipient_name }} —
                                    {{ address.full_address }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <h3 class="font-medium">
                            {{ t('checkout.deliveryTitle') }}
                        </h3>

                        <RadioGroup v-model="deliveryMethod">
                            <Label
                                v-for="option in deliveryOptions"
                                :key="option.value"
                                :for="`delivery-${option.value}`"
                                class="flex cursor-pointer items-center justify-between rounded-lg border border-border p-3 has-[[data-state=checked]]:border-primary"
                            >
                                <span class="flex items-center gap-3">
                                    <RadioGroupItem
                                        :id="`delivery-${option.value}`"
                                        :value="option.value"
                                    />
                                    <span>
                                        <span class="block font-medium">{{
                                            option.label
                                        }}</span>
                                        <span
                                            class="block text-xs text-muted-foreground"
                                            >{{ option.eta }}</span
                                        >
                                    </span>
                                </span>
                                <span class="font-medium tabular-nums">{{
                                    formatIDR(option.fee)
                                }}</span>
                            </Label>
                        </RadioGroup>
                    </CardContent>
                </Card>
            </div>

            <Card class="lg:sticky lg:top-6 lg:self-start">
                <CardContent class="space-y-4 pt-6">
                    <h3 class="font-medium">
                        {{ t('checkout.summaryTitle') }}
                    </h3>

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('checkout.subtotal') }}
                            </dt>
                            <dd class="tabular-nums">
                                {{ formatIDR(preview.subtotal) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('checkout.discount') }}
                            </dt>
                            <dd class="tabular-nums">
                                -{{ formatIDR(preview.discount_total) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('checkout.deliveryFee') }}
                            </dt>
                            <dd class="tabular-nums">
                                {{ formatIDR(preview.delivery_fee) }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{ t('checkout.tax') }}
                            </dt>
                            <dd class="tabular-nums">
                                {{ formatIDR(preview.tax_amount) }}
                            </dd>
                        </div>
                    </dl>

                    <Separator />

                    <div class="flex justify-between font-semibold">
                        <span>{{ t('checkout.grandTotal') }}</span>
                        <span class="text-lg tabular-nums">{{
                            formatIDR(preview.grand_total)
                        }}</span>
                    </div>

                    <Button
                        class="w-full"
                        :disabled="
                            !addressId ||
                            !preview.sufficient_balance ||
                            submitting
                        "
                        @click="confirmOpen = true"
                    >
                        {{ t('checkout.payNow') }}
                    </Button>

                    <p
                        v-if="!preview.sufficient_balance"
                        class="text-center text-sm text-destructive"
                    >
                        {{ t('checkout.insufficientBalance') }}
                        <Link
                            :href="showWallet.url()"
                            class="font-medium underline"
                            >{{ t('checkout.topUpLink') }}</Link
                        >
                    </p>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="confirmOpen">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ t('checkout.confirmTitle') }}</DialogTitle>
                    <DialogDescription>
                        {{
                            t('checkout.confirmDescription', {
                                amount: formatIDR(preview.grand_total),
                            })
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-4 gap-2">
                    <Button variant="secondary" @click="confirmOpen = false">{{
                        t('common.cancel')
                    }}</Button>
                    <Button :disabled="submitting" @click="confirmCheckout">{{
                        t('checkout.confirmPay')
                    }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
