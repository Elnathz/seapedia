<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { CalendarClock, Plus, Ticket } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminPromoController from '@/actions/App/Http/Controllers/Web/Admin/PromoController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
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
import {
    discountStatusAccent,
    discountStatusBadgeVariant,
} from '@/lib/discountStatus';
import type { DiscountStatusKey } from '@/lib/discountStatus';
import { formatIDR } from '@/lib/utils';

type DiscountTypeKey = 'percentage' | 'fixed';

interface PromoRow {
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

interface PaginatedPromos {
    data: PromoRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{ promos: PaginatedPromos }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelola Promo', href: AdminPromoController.index() },
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
        AdminPromoController.index.url(),
        { page },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head :title="t('admin.managePromosTitle')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading variant="small" :title="t('admin.managePromosTitle')" />
            <Button @click="formOpen = true">
                <Plus class="size-4" />
                {{ t('admin.addPromo') }}
            </Button>
        </div>

        <EmptyState
            v-if="props.promos.data.length === 0"
            :icon="Ticket"
            :title="t('admin.promosEmptyTitle')"
            :description="t('admin.promosEmptyDescription')"
            :action-label="t('admin.addPromo')"
            @action="formOpen = true"
        />

        <template v-else>
            <div class="grid gap-3 lg:grid-cols-2">
                <div
                    v-for="promo in props.promos.data"
                    :key="promo.id"
                    class="relative flex flex-wrap items-center justify-between gap-4 overflow-hidden rounded-xl border bg-card p-4 pl-5 transition-shadow hover:shadow-sm"
                >
                    <span
                        class="absolute inset-y-0 left-0 w-1"
                        :class="discountStatusAccent(promo.status)"
                        aria-hidden="true"
                    />
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p
                                class="truncate font-mono text-base font-semibold"
                            >
                                {{ promo.code }}
                            </p>
                            <Badge
                                :variant="
                                    discountStatusBadgeVariant(promo.status)
                                "
                            >
                                {{ t(statusLabelKey[promo.status]) }}
                            </Badge>
                        </div>
                        <p
                            class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm text-muted-foreground"
                        >
                            <span
                                class="font-medium text-foreground tabular-nums"
                                >{{ valueLabel(promo) }}</span
                            >
                            <span class="flex items-center gap-1 tabular-nums">
                                <CalendarClock class="size-3.5" />
                                {{ promo.expiry_date.slice(0, 10) }}
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button as-child size="sm" variant="ghost">
                            <Link
                                :href="AdminPromoController.show.url(promo.id)"
                            >
                                {{ t('admin.viewDetail') }}
                            </Link>
                        </Button>
                        <Form
                            v-bind="
                                AdminPromoController.toggleActive.form(promo.id)
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
                                {{
                                    promo.is_active
                                        ? t('admin.deactivateAction')
                                        : t('admin.activateAction')
                                }}
                            </Button>
                        </Form>
                    </div>
                </div>
            </div>

            <Pagination
                v-if="props.promos.last_page > 1"
                :items-per-page="props.promos.per_page"
                :total="props.promos.total"
                :default-page="props.promos.current_page"
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
                            :is-active="
                                item.value === props.promos.current_page
                            "
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
                    v-bind="AdminPromoController.store.form()"
                    :options="{ preserveScroll: true }"
                    @success="formOpen = false"
                    v-slot="{ errors, processing }"
                    class="space-y-4"
                >
                    <DialogHeader>
                        <DialogTitle>{{ t('admin.addPromo') }}</DialogTitle>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="promo_code">{{
                            t('admin.codeLabel')
                        }}</Label>
                        <Input
                            id="promo_code"
                            name="code"
                            required
                            maxlength="32"
                        />
                        <InputError :message="errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="promo_type">{{
                            t('admin.typeLabel')
                        }}</Label>
                        <Select name="type" default-value="percentage" required>
                            <SelectTrigger id="promo_type" class="w-full">
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
                        <Label for="promo_value">{{
                            t('admin.valueLabel')
                        }}</Label>
                        <Input
                            id="promo_value"
                            name="value"
                            type="number"
                            min="1"
                            required
                        />
                        <InputError :message="errors.value" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="promo_max_discount">
                                {{ t('admin.maxDiscountLabel') }}
                            </Label>
                            <Input
                                id="promo_max_discount"
                                name="max_discount"
                                type="number"
                                min="0"
                            />
                            <InputError :message="errors.max_discount" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="promo_min_spend">
                                {{ t('admin.minSpendLabel') }}
                            </Label>
                            <Input
                                id="promo_min_spend"
                                name="min_spend"
                                type="number"
                                min="0"
                            />
                            <InputError :message="errors.min_spend" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="promo_expiry_date">
                            {{ t('admin.expiryDateLabel') }}
                        </Label>
                        <Input
                            id="promo_expiry_date"
                            name="expiry_date"
                            type="date"
                            required
                        />
                        <InputError :message="errors.expiry_date" />
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
