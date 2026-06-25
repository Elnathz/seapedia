<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, ReceiptText, XCircle } from '@lucide/vue';
import { onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime, formatIDR } from '@/lib/utils';
import { show as showBuyerWallet } from '@/routes/buyer/wallet';

type TopupStatusKey = 'pending' | 'paid' | 'failed' | 'expired';

interface TopupData {
    id: number;
    amount: number;
    status: TopupStatusKey;
    gateway_reference: string;
    created_at: string;
}

const props = defineProps<{ topup: TopupData }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dompet', href: showBuyerWallet() },
            { title: 'Top up', href: '#' },
        ],
    },
});

const { t, locale } = useI18n();

const POLL_MS = 1500;
let pollTimer: ReturnType<typeof setInterval> | undefined;

function stopPolling() {
    if (pollTimer !== undefined) {
        clearInterval(pollTimer);
        pollTimer = undefined;
    }
}

function startPolling() {
    stopPolling();
    pollTimer = setInterval(() => {
        router.reload({ only: ['topup'] });
    }, POLL_MS);
}

watch(
    () => props.topup.status,
    (status) => (status === 'pending' ? startPolling() : stopPolling()),
);

onMounted(() => {
    if (props.topup.status === 'pending') {
        startPolling();
    }
});

onUnmounted(stopPolling);

const statusText: Record<TopupStatusKey, string> = {
    pending: t('topup.statusPending'),
    paid: t('topup.statusPaid'),
    failed: t('topup.statusFailed'),
    expired: t('topup.statusFailed'),
};
</script>

<template>
    <Head :title="t('topup.title')" />

    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <Heading :title="t('topup.title')" />

        <Card class="overflow-hidden">
            <CardContent
                class="flex flex-col items-center gap-5 py-10 text-center"
            >
                <div
                    class="flex size-14 items-center justify-center rounded-full"
                    :class="{
                        'bg-muted text-muted-foreground':
                            props.topup.status === 'pending',
                        'bg-green-100 text-green-600 dark:bg-green-950 dark:text-green-400':
                            props.topup.status === 'paid',
                        'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400':
                            props.topup.status === 'failed' ||
                            props.topup.status === 'expired',
                    }"
                >
                    <Spinner
                        v-if="props.topup.status === 'pending'"
                        class="size-6"
                    />
                    <CheckCircle2
                        v-else-if="props.topup.status === 'paid'"
                        class="size-6"
                    />
                    <XCircle v-else class="size-6" />
                </div>

                <div>
                    <p class="text-2xl font-semibold tabular-nums">
                        {{ formatIDR(props.topup.amount) }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ statusText[props.topup.status] }}
                    </p>
                </div>

                <Separator />

                <div class="w-full space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span
                            class="inline-flex items-center gap-1.5 text-muted-foreground"
                        >
                            <ReceiptText class="size-4" />
                            {{ t('topup.referenceLabel') }}
                        </span>
                        <code class="font-mono text-xs">{{
                            props.topup.gateway_reference
                        }}</code>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">{{
                            t('topup.dateLabel')
                        }}</span>
                        <span>{{
                            formatDateTime(props.topup.created_at, locale)
                        }}</span>
                    </div>
                </div>

                <Badge
                    v-if="props.topup.status === 'pending'"
                    variant="secondary"
                >
                    {{ t('topup.badgePending') }}
                </Badge>

                <Button as-child class="mt-2 w-full">
                    <Link :href="showBuyerWallet.url()">{{
                        t('topup.backToWallet')
                    }}</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
