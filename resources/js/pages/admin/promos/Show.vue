<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import AdminPromoController from '@/actions/App/Http/Controllers/Web/Admin/PromoController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { discountStatusBadgeVariant } from '@/lib/discountStatus';
import type { DiscountStatusKey } from '@/lib/discountStatus';
import { formatIDR } from '@/lib/utils';

type DiscountTypeKey = 'percentage' | 'fixed';

interface PromoData {
    id: number;
    code: string;
    type: DiscountTypeKey;
    value: number;
    max_discount: number | null;
    min_spend: number | null;
    expiry_date: string;
    is_active: boolean;
    status: DiscountStatusKey;
}

const props = defineProps<{ promo: PromoData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelola Promo', href: AdminPromoController.index() },
            { title: 'Detail Promo', href: '#' },
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
    return props.promo.type === 'percentage'
        ? `${props.promo.value}%`
        : formatIDR(props.promo.value);
}
</script>

<template>
    <Head :title="t('admin.promoDetailTitle')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading variant="small" :title="t('admin.promoDetailTitle')" />
            <Button as-child variant="outline" size="sm">
                <Link :href="AdminPromoController.index.url()">
                    <ArrowLeft class="size-4" />
                    {{ t('admin.backToList') }}
                </Link>
            </Button>
        </div>

        <Card>
            <CardContent class="flex flex-col pt-6">
                <div class="flex items-center justify-between py-2">
                    <span class="font-mono text-lg font-semibold">{{ promo.code }}</span>
                    <Badge :variant="discountStatusBadgeVariant(promo.status)">
                        {{ t(statusLabelKey[promo.status]) }}
                    </Badge>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{ t('admin.typeLabel') }}</span>
                    <span class="text-sm font-medium">
                        {{ promo.type === 'percentage' ? t('admin.typePercentage') : t('admin.typeFixed') }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{ t('admin.valueLabel') }}</span>
                    <span class="text-sm font-medium tabular-nums">{{ valueLabel() }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{ t('admin.maxDiscountLabel') }}</span>
                    <span class="text-sm font-medium tabular-nums">
                        {{ promo.max_discount !== null ? formatIDR(promo.max_discount) : '—' }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{ t('admin.minSpendLabel') }}</span>
                    <span class="text-sm font-medium tabular-nums">
                        {{ promo.min_spend !== null ? formatIDR(promo.min_spend) : '—' }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-muted-foreground">{{ t('admin.expiryDateLabel') }}</span>
                    <span class="text-sm font-medium tabular-nums">{{ promo.expiry_date.slice(0, 10) }}</span>
                </div>
            </CardContent>
        </Card>

        <Form
            v-bind="AdminPromoController.toggleActive.form(promo.id)"
            :options="{ preserveScroll: true }"
            v-slot="{ processing }"
        >
            <Button type="submit" variant="outline" :disabled="processing">
                {{ promo.is_active ? t('admin.deactivateAction') : t('admin.activateAction') }}
            </Button>
        </Form>
    </div>
</template>
