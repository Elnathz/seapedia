<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import AdminVoucherController from '@/actions/App/Http/Controllers/Web/Admin/VoucherController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { discountStatusBadgeVariant } from '@/lib/discountStatus';
import type { DiscountStatusKey } from '@/lib/discountStatus';
import { formatIDR } from '@/lib/utils';

type DiscountTypeKey = 'percentage' | 'fixed';

interface VoucherData {
    id: number;
    code: string;
    type: DiscountTypeKey;
    value: number;
    max_discount: number | null;
    min_spend: number | null;
    expiry_date: string;
    usage_limit: number;
    used_count: number;
    remaining_usage: number;
    is_active: boolean;
    status: DiscountStatusKey;
}

const props = defineProps<{ voucher: VoucherData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelola Voucher', href: AdminVoucherController.index() },
            { title: 'Detail Voucher', href: '#' },
        ],
    },
});

const { t } = useI18n();

const statusLabelKey: Record<DiscountStatusKey, string> = {
    active: 'admin.statusActive',
    inactive: 'admin.statusInactive',
    expired: 'admin.statusExpired',
    used_up: 'admin.statusUsedUp',
};

function valueLabel(): string {
    return props.voucher.type === 'percentage'
        ? `${props.voucher.value}%`
        : formatIDR(props.voucher.value);
}
</script>

<template>
    <Head :title="t('admin.voucherDetailTitle')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading variant="small" :title="t('admin.voucherDetailTitle')" />
            <Button as-child variant="outline" size="sm">
                <Link :href="AdminVoucherController.index.url()">
                    <ArrowLeft class="size-4" />
                    {{ t('admin.backToList') }}
                </Link>
            </Button>
        </div>

        <Card>
            <CardContent class="flex flex-col pt-6">
                <div class="flex items-center justify-between py-2">
                    <span class="font-mono text-lg font-semibold">{{
                        voucher.code
                    }}</span>
                    <Badge
                        :variant="discountStatusBadgeVariant(voucher.status)"
                    >
                        {{ t(statusLabelKey[voucher.status]) }}
                    </Badge>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.typeLabel')
                    }}</span>
                    <span class="text-sm font-medium">
                        {{
                            voucher.type === 'percentage'
                                ? t('admin.typePercentage')
                                : t('admin.typeFixed')
                        }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.valueLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">{{
                        valueLabel()
                    }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.maxDiscountLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">
                        {{
                            voucher.max_discount !== null
                                ? formatIDR(voucher.max_discount)
                                : '—'
                        }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.minSpendLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">
                        {{
                            voucher.min_spend !== null
                                ? formatIDR(voucher.min_spend)
                                : '—'
                        }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.expiryDateLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">{{
                        voucher.expiry_date.slice(0, 10)
                    }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.usageLimitLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">{{
                        voucher.usage_limit
                    }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.usedCountLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">{{
                        voucher.used_count
                    }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{
                        t('admin.remainingUsageLabel')
                    }}</span>
                    <span class="text-sm font-medium tabular-nums">{{
                        voucher.remaining_usage
                    }}</span>
                </div>
            </CardContent>
        </Card>

        <Form
            v-bind="AdminVoucherController.toggleActive.form(voucher.id)"
            :options="{ preserveScroll: true }"
            v-slot="{ processing }"
        >
            <Button type="submit" variant="outline" :disabled="processing">
                {{
                    voucher.is_active
                        ? t('admin.deactivateAction')
                        : t('admin.activateAction')
                }}
            </Button>
        </Form>
    </div>
</template>
