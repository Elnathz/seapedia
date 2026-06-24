<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { BadgePercent, Plus } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminVoucherController from '@/actions/App/Http/Controllers/Web/Admin/VoucherController';
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
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { discountStatusBadgeVariant } from '@/lib/discountStatus';
import type { DiscountStatusKey } from '@/lib/discountStatus';
import { formatIDR } from '@/lib/utils';

type DiscountTypeKey = 'percentage' | 'fixed';

interface VoucherRow {
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

interface PaginatedVouchers {
    data: VoucherRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{ vouchers: PaginatedVouchers }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelola Voucher', href: AdminVoucherController.index() },
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

const formOpen = ref(false);

function valueLabel(row: { type: DiscountTypeKey; value: number }): string {
    return row.type === 'percentage' ? `${row.value}%` : formatIDR(row.value);
}

function goToPage(page: number) {
    router.get(
        AdminVoucherController.index.url(),
        { page },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head :title="t('admin.manageVouchersTitle')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading variant="small" :title="t('admin.manageVouchersTitle')" />
            <Button @click="formOpen = true">
                <Plus class="size-4" />
                {{ t('admin.addVoucher') }}
            </Button>
        </div>

        <EmptyState
            v-if="props.vouchers.data.length === 0"
            :icon="BadgePercent"
            :title="t('admin.vouchersEmptyTitle')"
            :description="t('admin.vouchersEmptyDescription')"
            :action-label="t('admin.addVoucher')"
            @action="formOpen = true"
        />

        <template v-else>
            <div class="flex flex-col gap-3">
                <Card v-for="voucher in props.vouchers.data" :key="voucher.id">
                    <CardContent
                        class="flex flex-wrap items-center justify-between gap-4 pt-6"
                    >
                        <div class="min-w-0">
                            <p class="font-mono font-medium">
                                {{ voucher.code }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ valueLabel(voucher) }} ·
                                {{ voucher.used_count }}/{{ voucher.usage_limit }} ·
                                {{ t('admin.expiryDateLabel') }}:
                                {{ voucher.expiry_date.slice(0, 10) }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge :variant="discountStatusBadgeVariant(voucher.status)">
                                {{ t(statusLabelKey[voucher.status]) }}
                            </Badge>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="AdminVoucherController.show.url(voucher.id)">
                                    {{ t('admin.viewDetail') }}
                                </Link>
                            </Button>
                            <Form
                                v-bind="AdminVoucherController.toggleActive.form(voucher.id)"
                                :options="{ preserveScroll: true }"
                                v-slot="{ processing }"
                            >
                                <Button
                                    type="submit"
                                    size="sm"
                                    variant="outline"
                                    :disabled="processing"
                                >
                                    {{
                                        voucher.is_active
                                            ? t('admin.deactivateAction')
                                            : t('admin.activateAction')
                                    }}
                                </Button>
                            </Form>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Pagination
                v-if="props.vouchers.last_page > 1"
                :items-per-page="props.vouchers.per_page"
                :total="props.vouchers.total"
                :default-page="props.vouchers.current_page"
                class="mt-4"
                @update:page="goToPage"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationFirst />
                    <PaginationPrevious />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="item.value === props.vouchers.current_page"
                        >
                            {{ item.value }}
                        </PaginationItem>
                        <PaginationEllipsis v-else />
                    </template>
                    <PaginationNext />
                    <PaginationLast />
                </PaginationContent>
            </Pagination>
        </template>

        <Dialog v-model:open="formOpen">
            <DialogContent>
                <Form
                    v-bind="AdminVoucherController.store.form()"
                    :options="{ preserveScroll: true }"
                    @success="formOpen = false"
                    v-slot="{ errors, processing }"
                    class="space-y-4"
                >
                    <DialogHeader>
                        <DialogTitle>{{ t('admin.addVoucher') }}</DialogTitle>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="voucher_code">{{ t('admin.codeLabel') }}</Label>
                        <Input id="voucher_code" name="code" required maxlength="32" />
                        <InputError :message="errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="voucher_type">{{ t('admin.typeLabel') }}</Label>
                        <Select name="type" default-value="percentage" required>
                            <SelectTrigger id="voucher_type" class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="percentage">
                                    {{ t('admin.typePercentage') }}
                                </SelectItem>
                                <SelectItem value="fixed">
                                    {{ t('admin.typeFixed') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="voucher_value">{{ t('admin.valueLabel') }}</Label>
                        <Input id="voucher_value" name="value" type="number" min="1" required />
                        <InputError :message="errors.value" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="voucher_max_discount">
                                {{ t('admin.maxDiscountLabel') }}
                            </Label>
                            <Input id="voucher_max_discount" name="max_discount" type="number" min="0" />
                            <InputError :message="errors.max_discount" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="voucher_min_spend">
                                {{ t('admin.minSpendLabel') }}
                            </Label>
                            <Input id="voucher_min_spend" name="min_spend" type="number" min="0" />
                            <InputError :message="errors.min_spend" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="voucher_expiry_date">
                                {{ t('admin.expiryDateLabel') }}
                            </Label>
                            <Input id="voucher_expiry_date" name="expiry_date" type="date" required />
                            <InputError :message="errors.expiry_date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="voucher_usage_limit">
                                {{ t('admin.usageLimitLabel') }}
                            </Label>
                            <Input id="voucher_usage_limit" name="usage_limit" type="number" min="1" required />
                            <InputError :message="errors.usage_limit" />
                        </div>
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" type="button">
                                {{ t('common.cancel') }}
                            </Button>
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
