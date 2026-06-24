<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Truck } from '@lucide/vue';
import { useI18n } from 'vue-i18n';
import DriverJobController from '@/actions/App/Http/Controllers/Web/DriverJobController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
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
import { formatIDR } from '@/lib/utils';

type DeliveryMethodKey = 'instant' | 'next_day' | 'regular';

interface JobRow {
    id: number;
    earning_preview: number;
    order: {
        id: number;
        code: string;
        ship_address: string;
        delivery_method: DeliveryMethodKey;
        delivery_fee: number;
        store: { id: number; name: string };
    };
}

interface PaginatedJobs {
    data: JobRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{ jobs: PaginatedJobs }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Tersedia', href: DriverJobController.index() },
        ],
    },
});

const { t } = useI18n();

const methodLabelKey: Record<DeliveryMethodKey, string> = {
    instant: 'checkout.instant',
    next_day: 'checkout.nextDay',
    regular: 'checkout.regular',
};

function goToPage(page: number) {
    router.get(
        DriverJobController.index.url(),
        { page },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head :title="t('driver.jobsTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('driver.jobsTitle')" />

        <EmptyState
            v-if="props.jobs.data.length === 0"
            :icon="Truck"
            :title="t('driver.jobsEmptyTitle')"
            :description="t('driver.jobsEmptyDescription')"
        />

        <template v-else>
            <div class="flex flex-col gap-3">
                <Link
                    v-for="job in props.jobs.data"
                    :key="job.id"
                    :href="DriverJobController.show.url(job.id)"
                >
                    <Card class="transition-colors hover:border-primary">
                        <CardContent
                            class="flex items-center justify-between gap-4 pt-6"
                        >
                            <div class="min-w-0">
                                <p class="font-medium">
                                    {{ job.order.code }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ job.order.store.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ job.order.ship_address }}
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <Badge variant="outline">
                                    {{
                                        t(
                                            methodLabelKey[
                                                job.order.delivery_method
                                            ],
                                        )
                                    }}
                                </Badge>
                                <p
                                    class="mt-1 font-medium text-emerald-600 tabular-nums dark:text-emerald-400"
                                >
                                    +{{ formatIDR(job.earning_preview) }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('driver.earningPreview') }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <Pagination
                v-if="props.jobs.last_page > 1"
                :items-per-page="props.jobs.per_page"
                :total="props.jobs.total"
                :default-page="props.jobs.current_page"
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
                            :is-active="item.value === props.jobs.current_page"
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
</template>
