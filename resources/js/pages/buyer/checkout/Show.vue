<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ReceiptText, ShoppingBag, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import CheckoutController from '@/actions/App/Http/Controllers/Web/CheckoutController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
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
import { Input } from '@/components/ui/input';
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
    province?: string;
    city?: string;
    district?: string;
    village?: string;
    postal_code?: string;
}

interface AppliedDiscount {
    code: string;
    amount: number;
}

interface Preview {
    subtotal: number;
    discount_total: number;
    promo: AppliedDiscount | null;
    promo_error: string | null;
    voucher: AppliedDiscount | null;
    voucher_error: string | null;
    taxable_base: number;
    tax_amount: number;
    delivery_base_fee: number;
    delivery_distance_km: number;
    delivery_distance_fee: number;
    delivery_weight_grams: number;
    delivery_weight_fee: number;
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
    selectedAddressId: number | null;
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

const promoInput = ref('');
const voucherInput = ref('');
const applyingPromo = ref(false);
const applyingVoucher = ref(false);

function reloadPreviews(promoCode: string, voucherCode: string) {
    return {
        promo_code: promoCode,
        voucher_code: voucherCode,
        address_id: addressId.value ?? '',
    };
}

// The delivery surcharge depends on the ships-to address, so re-preview
// server-side whenever it changes — keeps the quote equal to what commit
// charges. Skip the redundant round trip when it already matches the server.
watch(addressId, (value) => {
    if (value === props.selectedAddressId) {
        return;
    }

    router.get(
        CheckoutController.show.url(),
        reloadPreviews(
            preview.value.promo?.code ?? '',
            preview.value.voucher?.code ?? '',
        ),
        { preserveState: true, preserveScroll: true, only: ['previews', 'selectedAddressId'] },
    );
});

function applyPromo() {
    if (!promoInput.value) {
        return;
    }

    applyingPromo.value = true;
    router.get(
        CheckoutController.show.url(),
        reloadPreviews(promoInput.value, preview.value.voucher?.code ?? ''),
        {
            preserveState: true,
            preserveScroll: true,
            only: ['previews'],
            onFinish: () => {
                applyingPromo.value = false;
            },
        },
    );
}

function removePromo() {
    router.get(
        CheckoutController.show.url(),
        reloadPreviews('', preview.value.voucher?.code ?? ''),
        { preserveState: true, preserveScroll: true, only: ['previews'] },
    );
}

function applyVoucher() {
    if (!voucherInput.value) {
        return;
    }

    applyingVoucher.value = true;
    router.get(
        CheckoutController.show.url(),
        reloadPreviews(preview.value.promo?.code ?? '', voucherInput.value),
        {
            preserveState: true,
            preserveScroll: true,
            only: ['previews'],
            onFinish: () => {
                applyingVoucher.value = false;
            },
        },
    );
}

function removeVoucher() {
    router.get(
        CheckoutController.show.url(),
        reloadPreviews(preview.value.promo?.code ?? '', ''),
        { preserveState: true, preserveScroll: true, only: ['previews'] },
    );
}

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
            promo_code: preview.value.promo?.code ?? '',
            voucher_code: preview.value.voucher?.code ?? '',
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
                                    {{ address.recipient_name }},
                                    {{
                                        [
                                            address.district,
                                            address.city,
                                            address.province,
                                        ]
                                            .filter(Boolean)
                                            .join(', ')
                                    }},
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

                <Card>
                    <CardContent class="space-y-4 pt-6">
                        <h3 class="font-medium">
                            {{ t('checkout.discountTitle') }}
                        </h3>

                        <div class="space-y-2">
                            <Label for="promo-code">{{
                                t('checkout.promoLabel')
                            }}</Label>
                            <div
                                v-if="preview.promo"
                                class="flex items-center gap-2"
                            >
                                <Badge variant="secondary" class="gap-1.5">
                                    {{ preview.promo.code }} (-{{
                                        formatIDR(preview.promo.amount)
                                    }})
                                    <button
                                        type="button"
                                        :aria-label="
                                            t('checkout.removeCode', {
                                                code: preview.promo.code,
                                            })
                                        "
                                        class="cursor-pointer"
                                        @click="removePromo"
                                    >
                                        <X class="size-3" />
                                    </button>
                                </Badge>
                            </div>
                            <div v-else class="flex gap-2">
                                <Input
                                    id="promo-code"
                                    v-model="promoInput"
                                    :placeholder="
                                        t('checkout.promoPlaceholder')
                                    "
                                    @keyup.enter="applyPromo"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    :disabled="!promoInput || applyingPromo"
                                    @click="applyPromo"
                                >
                                    {{ t('checkout.applyCode') }}
                                </Button>
                            </div>
                            <p
                                v-if="preview.promo_error"
                                class="text-sm text-destructive"
                            >
                                {{ preview.promo_error }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="voucher-code">{{
                                t('checkout.voucherLabel')
                            }}</Label>
                            <div
                                v-if="preview.voucher"
                                class="flex items-center gap-2"
                            >
                                <Badge variant="secondary" class="gap-1.5">
                                    {{ preview.voucher.code }} (-{{
                                        formatIDR(preview.voucher.amount)
                                    }})
                                    <button
                                        type="button"
                                        :aria-label="
                                            t('checkout.removeCode', {
                                                code: preview.voucher.code,
                                            })
                                        "
                                        class="cursor-pointer"
                                        @click="removeVoucher"
                                    >
                                        <X class="size-3" />
                                    </button>
                                </Badge>
                            </div>
                            <div v-else class="flex gap-2">
                                <Input
                                    id="voucher-code"
                                    v-model="voucherInput"
                                    :placeholder="
                                        t('checkout.voucherPlaceholder')
                                    "
                                    @keyup.enter="applyVoucher"
                                />
                                <Button
                                    type="button"
                                    variant="secondary"
                                    :disabled="!voucherInput || applyingVoucher"
                                    @click="applyVoucher"
                                >
                                    {{ t('checkout.applyCode') }}
                                </Button>
                            </div>
                            <p
                                v-if="preview.voucher_error"
                                class="text-sm text-destructive"
                            >
                                {{ preview.voucher_error }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card
                class="border-primary/20 bg-gradient-to-b from-primary/5 to-transparent shadow-[0_8px_30px_-12px_rgba(13,148,136,0.25)] lg:sticky lg:top-6 lg:self-start"
            >
                <CardContent class="space-y-4 pt-6">
                    <h3 class="flex items-center gap-2 font-semibold">
                        <ReceiptText class="size-4 text-primary" />
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
                        <div v-if="preview.promo" class="flex justify-between">
                            <dt class="text-muted-foreground">
                                {{
                                    t('checkout.discountPromo', {
                                        code: preview.promo.code,
                                    })
                                }}
                            </dt>
                            <dd class="tabular-nums">
                                -{{ formatIDR(preview.promo.amount) }}
                            </dd>
                        </div>
                        <div
                            v-if="preview.voucher"
                            class="flex justify-between"
                        >
                            <dt class="text-muted-foreground">
                                {{
                                    t('checkout.discountVoucher', {
                                        code: preview.voucher.code,
                                    })
                                }}
                            </dt>
                            <dd class="tabular-nums">
                                -{{ formatIDR(preview.voucher.amount) }}
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
                        <div
                            v-if="preview.delivery_distance_fee > 0"
                            class="flex justify-between text-xs text-muted-foreground"
                        >
                            <dt class="pl-3">
                                Jarak
                                {{ preview.delivery_distance_km.toFixed(1) }} km
                            </dt>
                            <dd class="tabular-nums">
                                +{{ formatIDR(preview.delivery_distance_fee) }}
                            </dd>
                        </div>
                        <div
                            v-if="preview.delivery_weight_fee > 0"
                            class="flex justify-between text-xs text-muted-foreground"
                        >
                            <dt class="pl-3">
                                Berat
                                {{
                                    (preview.delivery_weight_grams / 1000).toFixed(
                                        1,
                                    )
                                }}
                                kg
                            </dt>
                            <dd class="tabular-nums">
                                +{{ formatIDR(preview.delivery_weight_fee) }}
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

                    <div
                        class="flex items-end justify-between rounded-xl bg-primary/10 px-3 py-2.5"
                    >
                        <span class="text-sm font-medium text-foreground">{{
                            t('checkout.grandTotal')
                        }}</span>
                        <span
                            class="text-xl font-bold text-primary tabular-nums"
                            >{{ formatIDR(preview.grand_total) }}</span
                        >
                    </div>

                    <Button
                        size="lg"
                        class="w-full shadow-md shadow-primary/20"
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
