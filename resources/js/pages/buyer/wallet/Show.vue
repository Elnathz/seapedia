<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowDownLeft, ArrowUpRight, Wallet as WalletIcon } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import BuyerWalletController from '@/actions/App/Http/Controllers/Web/BuyerWalletController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDateTime, formatIDR } from '@/lib/utils';
import { show as showBuyerWallet } from '@/routes/buyer/wallet';

type TransactionType =
    | 'topup'
    | 'payment'
    | 'refund'
    | 'income'
    | 'earning'
    | 'reversal'
    | 'adjustment';

interface WalletTransactionData {
    id: number;
    type: TransactionType;
    direction: 'credit' | 'debit';
    amount: number;
    balance_after: number;
    description: string | null;
    created_at: string;
}

interface PaginatedTransactions {
    data: WalletTransactionData[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    wallet: { balance: number };
    transactions: PaginatedTransactions;
    minTopupAmount: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dompet', href: showBuyerWallet() }],
    },
});

const { t, locale } = useI18n();

const quickAmounts = [50_000, 100_000, 200_000, 500_000];
const amount = ref<number>(quickAmounts[0]);

const typeLabelKeys: Record<TransactionType, string> = {
    topup: 'wallet.typeTopup',
    payment: 'wallet.typePayment',
    refund: 'wallet.typeRefund',
    income: 'wallet.typeIncome',
    earning: 'wallet.typeEarning',
    reversal: 'wallet.typeReversal',
    adjustment: 'wallet.typeAdjustment',
};

function typeLabel(type: TransactionType) {
    return t(typeLabelKeys[type]);
}

function goToPage(page: number) {
    router.get(
        showBuyerWallet.url(),
        { page },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head :title="t('wallet.title')" />

    <div class="flex flex-col gap-6">
        <Heading :title="t('wallet.title')" />

        <Card
            class="overflow-hidden border-none bg-gradient-to-br from-primary via-primary to-secondary text-primary-foreground shadow-lg"
        >
            <CardContent class="flex flex-col gap-2 py-8">
                <div
                    class="flex items-center gap-2 text-sm text-primary-foreground/80"
                >
                    <WalletIcon class="size-4" />
                    {{ t('wallet.balanceLabel') }}
                </div>
                <p class="text-4xl font-semibold tabular-nums sm:text-5xl">
                    {{ formatIDR(props.wallet.balance) }}
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="space-y-4 pt-6">
                <div>
                    <h3 class="font-medium">{{ t('wallet.topupTitle') }}</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ t('wallet.topupDescription') }}
                    </p>
                </div>

                <Form
                    v-bind="BuyerWalletController.store.form()"
                    class="flex flex-col gap-4 sm:flex-row sm:items-start"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2 sm:max-w-xs">
                        <Label for="amount">{{
                            t('wallet.amountLabel')
                        }}</Label>
                        <Input
                            id="amount"
                            v-model="amount"
                            name="amount"
                            type="number"
                            inputmode="numeric"
                            :min="minTopupAmount"
                            step="1000"
                            :placeholder="t('wallet.amountPlaceholder')"
                        />
                        <InputError :message="errors.amount" />

                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="quick in quickAmounts"
                                :key="quick"
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="amount = quick"
                            >
                                {{ formatIDR(quick) }}
                            </Button>
                        </div>
                    </div>

                    <Button
                        :disabled="processing"
                        type="submit"
                        class="sm:mt-7"
                    >
                        {{ t('wallet.topupSubmit') }}
                    </Button>
                </Form>
            </CardContent>
        </Card>

        <div>
            <h3 class="mb-3 font-medium">{{ t('wallet.historyTitle') }}</h3>

            <EmptyState
                v-if="props.transactions.data.length === 0"
                :title="t('wallet.emptyTitle')"
                :description="t('wallet.emptyDescription')"
            />

            <template v-else>
                <!-- Mobile card list (< lg), desktop table (≥ lg) -->
                <div class="flex flex-col gap-2 lg:hidden">
                    <div
                        v-for="tx in props.transactions.data"
                        :key="tx.id"
                        class="flex items-center gap-3 rounded-xl border border-border bg-card px-4 py-3"
                    >
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full"
                            :class="tx.direction === 'credit' ? 'bg-green-500/10' : 'bg-rose-500/10'"
                        >
                            <component
                                :is="tx.direction === 'credit' ? ArrowDownLeft : ArrowUpRight"
                                class="size-4"
                                :class="tx.direction === 'credit' ? 'text-green-600' : 'text-rose-600'"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <Badge variant="secondary" class="text-xs">{{ typeLabel(tx.type) }}</Badge>
                            </div>
                            <p class="mt-0.5 text-xs text-muted-foreground">{{ formatDateTime(tx.created_at, locale) }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p
                                class="font-semibold tabular-nums"
                                :class="tx.direction === 'credit' ? 'text-green-600' : 'text-rose-600'"
                            >
                                {{ tx.direction === 'credit' ? '+' : '-' }}{{ formatIDR(tx.amount) }}
                            </p>
                            <p class="text-xs text-muted-foreground tabular-nums">{{ formatIDR(tx.balance_after) }}</p>
                        </div>
                    </div>
                </div>

                <div class="hidden overflow-x-auto rounded-lg border border-border lg:block">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('wallet.columnDate') }}</TableHead>
                                <TableHead>{{ t('wallet.columnType') }}</TableHead>
                                <TableHead>{{ t('wallet.columnDirection') }}</TableHead>
                                <TableHead class="text-right">{{ t('wallet.columnAmount') }}</TableHead>
                                <TableHead class="text-right">{{ t('wallet.columnBalance') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="tx in props.transactions.data" :key="tx.id">
                                <TableCell class="whitespace-nowrap text-sm text-muted-foreground">
                                    {{ formatDateTime(tx.created_at, locale) }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{{ typeLabel(tx.type) }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <span
                                        class="inline-flex items-center gap-1 text-sm"
                                        :class="tx.direction === 'credit' ? 'text-green-600' : 'text-rose-600'"
                                    >
                                        <component :is="tx.direction === 'credit' ? ArrowDownLeft : ArrowUpRight" class="size-3.5" />
                                        {{ tx.direction === 'credit' ? t('wallet.directionCredit') : t('wallet.directionDebit') }}
                                    </span>
                                </TableCell>
                                <TableCell
                                    class="text-right font-medium tabular-nums"
                                    :class="tx.direction === 'credit' ? 'text-green-600' : 'text-rose-600'"
                                >
                                    {{ tx.direction === 'credit' ? '+' : '-' }}{{ formatIDR(tx.amount) }}
                                </TableCell>
                                <TableCell class="text-right tabular-nums">
                                    {{ formatIDR(tx.balance_after) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination
                    v-if="props.transactions.last_page > 1"
                    :items-per-page="props.transactions.per_page"
                    :total="props.transactions.total"
                    :default-page="props.transactions.current_page"
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
                                    item.value ===
                                    props.transactions.current_page
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
        </div>
    </div>
</template>
