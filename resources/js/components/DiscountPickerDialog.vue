<script setup lang="ts">
import { CheckCircle2, TicketPercent } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Progress } from '@/components/ui/progress';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatIDR } from '@/lib/utils';

interface DiscountOption {
    code: string;
    type: 'percentage' | 'fixed';
    value: number;
    min_spend: number | null;
    max_discount: number | null;
    amount: number;
    eligible: boolean;
}

interface VoucherOption extends DiscountOption {
    usage_limit: number;
    used_count: number;
    remaining: number;
}

defineProps<{
    open: boolean;
    promos: DiscountOption[];
    vouchers: VoucherOption[];
    activePromo: string | null;
    activeVoucher: string | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    apply: [kind: 'promo' | 'voucher', code: string];
}>();

const { t } = useI18n();

function offerLabel(option: DiscountOption): string {
    const base =
        option.type === 'percentage'
            ? t('checkout.discountPercentOff', { value: option.value })
            : t('checkout.discountAmountOff', {
                  amount: formatIDR(option.value),
              });

    if (option.type === 'percentage' && option.max_discount) {
        return `${base} · ${t('checkout.discountMaxCap', { amount: formatIDR(option.max_discount) })}`;
    }

    return base;
}

function usedPercent(voucher: VoucherOption): number {
    if (voucher.usage_limit <= 0) {
        return 100;
    }

    return Math.min(
        100,
        Math.round((voucher.used_count / voucher.usage_limit) * 100),
    );
}

function choose(kind: 'promo' | 'voucher', option: DiscountOption) {
    if (!option.eligible) {
        return;
    }

    emit('apply', kind, option.code);
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <TicketPercent class="size-5 text-primary" />
                    {{ t('checkout.browseDiscounts') }}
                </DialogTitle>
                <DialogDescription>
                    {{ t('checkout.discountTitle') }}
                </DialogDescription>
            </DialogHeader>

            <Tabs default-value="promos" class="w-full">
                <TabsList class="grid w-full grid-cols-2">
                    <TabsTrigger value="promos">
                        {{ t('checkout.promosTab') }} ({{ promos.length }})
                    </TabsTrigger>
                    <TabsTrigger value="vouchers">
                        {{ t('checkout.vouchersTab') }} ({{ vouchers.length }})
                    </TabsTrigger>
                </TabsList>

                <!-- Promos -->
                <TabsContent value="promos" class="mt-3 space-y-2.5">
                    <p
                        v-if="promos.length === 0"
                        class="rounded-lg border border-dashed py-8 text-center text-sm text-muted-foreground"
                    >
                        {{ t('checkout.noPromos') }}
                    </p>

                    <button
                        v-for="promo in promos"
                        :key="promo.code"
                        type="button"
                        :disabled="!promo.eligible"
                        class="w-full rounded-xl border border-border p-3 text-left transition-colors enabled:cursor-pointer enabled:hover:border-primary/60 enabled:hover:bg-primary/5 disabled:opacity-60 aria-[current=true]:border-primary aria-[current=true]:bg-primary/5"
                        :aria-current="activePromo === promo.code"
                        @click="choose('promo', promo)"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2">
                                <span
                                    class="font-mono text-sm font-semibold tracking-wide text-primary"
                                    >{{ promo.code }}</span
                                >
                                <CheckCircle2
                                    v-if="activePromo === promo.code"
                                    class="size-4 text-primary"
                                />
                            </span>
                            <Badge
                                v-if="promo.eligible && promo.amount > 0"
                                variant="secondary"
                                class="shrink-0"
                            >
                                {{
                                    t('checkout.discountSave', {
                                        amount: formatIDR(promo.amount),
                                    })
                                }}
                            </Badge>
                        </div>
                        <p class="mt-1 text-sm text-foreground/80">
                            {{ offerLabel(promo) }}
                        </p>
                        <p
                            v-if="promo.min_spend"
                            class="mt-0.5 text-xs"
                            :class="
                                promo.eligible
                                    ? 'text-muted-foreground'
                                    : 'text-amber-600 dark:text-amber-500'
                            "
                        >
                            {{
                                promo.eligible
                                    ? t('checkout.discountMin', {
                                          amount: formatIDR(promo.min_spend),
                                      })
                                    : t('checkout.discountMinUnmet')
                            }}
                        </p>
                    </button>
                </TabsContent>

                <!-- Vouchers -->
                <TabsContent value="vouchers" class="mt-3 space-y-2.5">
                    <p
                        v-if="vouchers.length === 0"
                        class="rounded-lg border border-dashed py-8 text-center text-sm text-muted-foreground"
                    >
                        {{ t('checkout.noVouchers') }}
                    </p>

                    <button
                        v-for="voucher in vouchers"
                        :key="voucher.code"
                        type="button"
                        :disabled="!voucher.eligible"
                        class="w-full rounded-xl border border-border p-3 text-left transition-colors enabled:cursor-pointer enabled:hover:border-primary/60 enabled:hover:bg-primary/5 disabled:opacity-60 aria-[current=true]:border-primary aria-[current=true]:bg-primary/5"
                        :aria-current="activeVoucher === voucher.code"
                        @click="choose('voucher', voucher)"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2">
                                <span
                                    class="font-mono text-sm font-semibold tracking-wide text-primary"
                                    >{{ voucher.code }}</span
                                >
                                <CheckCircle2
                                    v-if="activeVoucher === voucher.code"
                                    class="size-4 text-primary"
                                />
                            </span>
                            <Badge
                                v-if="voucher.eligible && voucher.amount > 0"
                                variant="secondary"
                                class="shrink-0"
                            >
                                {{
                                    t('checkout.discountSave', {
                                        amount: formatIDR(voucher.amount),
                                    })
                                }}
                            </Badge>
                        </div>
                        <p class="mt-1 text-sm text-foreground/80">
                            {{ offerLabel(voucher) }}
                        </p>
                        <p
                            v-if="voucher.min_spend && !voucher.eligible"
                            class="mt-0.5 text-xs text-amber-600 dark:text-amber-500"
                        >
                            {{ t('checkout.discountMinUnmet') }}
                        </p>
                        <p
                            v-else-if="voucher.min_spend"
                            class="mt-0.5 text-xs text-muted-foreground"
                        >
                            {{
                                t('checkout.discountMin', {
                                    amount: formatIDR(voucher.min_spend),
                                })
                            }}
                        </p>

                        <div class="mt-2 space-y-1">
                            <Progress :model-value="usedPercent(voucher)" />
                            <p class="text-xs text-muted-foreground">
                                {{
                                    t('checkout.voucherLeft', {
                                        remaining: voucher.remaining,
                                        total: voucher.usage_limit,
                                    })
                                }}
                            </p>
                        </div>
                    </button>
                </TabsContent>
            </Tabs>
        </DialogContent>
    </Dialog>
</template>
