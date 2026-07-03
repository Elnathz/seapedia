<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, MapPin, Navigation, Store, Truck } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DriverJobController from '@/actions/App/Http/Controllers/Web/DriverJobController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
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
        delivery_distance_km: number | null;
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

const props = defineProps<{
    jobs: PaginatedJobs;
    currentMethod?: string;
    activeJobsCount: number;
    maxActiveJobs: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pesanan Tersedia', href: DriverJobController.index() },
        ],
    },
});

const { t } = useI18n();

const atCap = computed(() => props.activeJobsCount >= props.maxActiveJobs);

const methodLabelKey: Record<DeliveryMethodKey, string> = {
    instant: 'checkout.instant',
    next_day: 'checkout.nextDay',
    regular: 'checkout.regular',
};

function goToPage(page: number) {
    router.get(
        DriverJobController.index.url(),
        { page, method: props.currentMethod },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const methods = [
    { value: '', label: 'Semua' },
    { value: 'instant', label: 'Instant' },
    { value: 'next_day', label: 'Next Day' },
    { value: 'regular', label: 'Regular' },
];

function filterMethod(method: string) {
    router.get(
        DriverJobController.index.url(),
        { method },
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="t('driver.jobsTitle')" />

    <div class="flex flex-col gap-6">
        <Heading variant="small" :title="t('driver.jobsTitle')" />

        <div
            v-if="props.activeJobsCount > 0"
            class="flex items-center justify-between gap-4 rounded-xl border p-4"
            :class="
                atCap
                    ? 'border-destructive/20 bg-destructive/10'
                    : 'border-amber-500/25 bg-amber-500/10'
            "
        >
            <div class="min-w-0">
                <h3
                    class="flex items-center gap-2 font-bold"
                    :class="
                        atCap
                            ? 'text-destructive'
                            : 'text-amber-600 dark:text-amber-500'
                    "
                >
                    <Truck class="size-4 shrink-0" />
                    {{ atCap ? 'Batas pekerjaan tercapai' : 'Pekerjaan Aktif' }}
                    <span class="tabular-nums"
                        >({{ props.activeJobsCount }}/{{
                            props.maxActiveJobs
                        }})</span
                    >
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        atCap
                            ? `Selesaikan salah satu pekerjaan aktif sebelum mengambil yang baru.`
                            : `Anda masih bisa mengambil ${props.maxActiveJobs - props.activeJobsCount} pekerjaan lagi.`
                    }}
                </p>
            </div>
            <Link
                href="/dashboard"
                class="shrink-0 rounded bg-foreground/10 px-4 py-2 text-sm font-medium transition-colors hover:bg-foreground/15"
            >
                Lihat
            </Link>
        </div>

        <!-- Tabs Filter -->
        <div
            class="scrollbar-hide flex gap-2 overflow-x-auto border-b border-border pb-2"
        >
            <button
                v-for="m in methods"
                :key="m.value"
                @click="filterMethod(m.value)"
                class="border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors"
                :class="[
                    (props.currentMethod || '') === m.value
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted hover:text-foreground',
                ]"
            >
                {{ m.label }}
            </button>
        </div>

        <EmptyState
            v-if="props.jobs.data.length === 0"
            :icon="Truck"
            :title="t('driver.jobsEmptyTitle')"
            :description="t('driver.jobsEmptyDescription')"
        />

        <template v-else>
            <div class="grid gap-3 lg:grid-cols-2">
                <Link
                    v-for="job in props.jobs.data"
                    :key="job.id"
                    :href="DriverJobController.show.url(job.id)"
                    class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-xl border bg-card p-4 pl-5 transition-all hover:border-primary/50 hover:shadow-sm"
                >
                    <span
                        class="absolute inset-y-0 left-0 w-1 bg-amber-500"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <p class="font-mono font-semibold">
                                {{ job.order.code }}
                            </p>
                            <Badge variant="outline">
                                {{
                                    t(methodLabelKey[job.order.delivery_method])
                                }}
                            </Badge>
                        </div>
                        <p
                            class="flex items-center gap-1.5 text-sm text-muted-foreground"
                        >
                            <Store class="size-3.5 shrink-0" />
                            {{ job.order.store.name }}
                        </p>
                        <p
                            class="flex items-center gap-1.5 truncate text-xs text-muted-foreground"
                        >
                            <MapPin class="size-3.5 shrink-0" />
                            {{ job.order.ship_address }}
                        </p>
                        <p
                            v-if="job.order.delivery_distance_km != null"
                            class="flex items-center gap-1.5 text-xs font-medium text-foreground/70"
                        >
                            <Navigation
                                class="size-3.5 shrink-0 text-primary"
                            />
                            ~{{ job.order.delivery_distance_km.toFixed(1) }} km
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <div class="text-right">
                            <p class="font-semibold text-primary tabular-nums">
                                +{{ formatIDR(job.earning_preview) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ t('driver.earningPreview') }}
                            </p>
                        </div>
                        <ArrowRight
                            class="size-4 text-muted-foreground transition-transform group-hover:translate-x-0.5"
                        />
                    </div>
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
