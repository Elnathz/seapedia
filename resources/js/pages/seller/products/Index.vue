<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { Package, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SellerProductController from '@/actions/App/Http/Controllers/Web/SellerProductController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { formatIDR } from '@/lib/utils';
import { create as createProduct } from '@/routes/seller/products';

interface ProductRow {
    id: number;
    name: string;
    price: number;
    stock: number;
    image_path: string | null;
    is_active: boolean;
    category: { id: number; name: string } | null;
}

const props = defineProps<{
    products: ProductRow[];
}>();

const { t } = useI18n();

type CategoryGroup = {
    key: number | 'none';
    name: string;
    products: ProductRow[];
};

// Group the catalogue by category so the seller manages one category at a
// time; buckets are alphabetical with uncategorised products pinned last.
const productsByCategory = computed<CategoryGroup[]>(() => {
    const map = new Map<number | 'none', CategoryGroup>();

    for (const product of props.products) {
        const key = product.category?.id ?? 'none';

        if (!map.has(key)) {
            map.set(key, {
                key,
                name: product.category?.name ?? t('product.uncategorized'),
                products: [],
            });
        }

        map.get(key)!.products.push(product);
    }

    return [...map.values()].sort((a, b) => {
        if (a.key === 'none') {
            return 1;
        }

        if (b.key === 'none') {
            return -1;
        }

        return a.name.localeCompare(b.name);
    });
});
</script>

<template>
    <Head :title="t('product.title')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="t('product.title')"
                :description="t('product.manageDescription')"
            />
            <Button as-child>
                <Link :href="createProduct()">
                    <Plus class="size-4" />
                    {{ t('product.add') }}
                </Link>
            </Button>
        </div>

        <EmptyState
            v-if="products.length === 0"
            :icon="Package"
            :title="t('product.emptyTitle')"
            :description="t('product.emptyDescription')"
            :action-label="t('product.add')"
            @action="router.visit(createProduct().url)"
        />

        <div v-else class="flex flex-col gap-8">
            <section
                v-for="cat in productsByCategory"
                :key="cat.key"
                class="flex flex-col gap-4"
            >
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-semibold text-foreground">
                        {{ cat.name }}
                    </h2>
                    <span
                        class="rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground tabular-nums"
                    >
                        {{ cat.products.length }}
                    </span>
                    <span class="h-px flex-1 bg-border" aria-hidden="true" />
                </div>

                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="product in cat.products"
                        :key="product.id"
                        class="group flex flex-col overflow-hidden rounded-xl border border-border bg-card transition-shadow hover:shadow-md"
                    >
                        <!-- Image -->
                        <div
                            class="relative aspect-square overflow-hidden border-b border-border bg-muted"
                        >
                            <img
                                v-if="product.image_path"
                                :src="`/storage/${product.image_path}`"
                                :alt="product.name"
                                loading="lazy"
                                class="size-full object-cover"
                            />
                            <div
                                v-else
                                class="flex size-full items-center justify-center text-muted-foreground"
                            >
                                <Package class="size-8" />
                            </div>
                            <Badge
                                v-if="product.stock === 0"
                                variant="destructive"
                                class="absolute top-2 right-2"
                            >
                                {{ t('product.outOfStock') }}
                            </Badge>
                            <Badge
                                v-if="!product.is_active"
                                variant="secondary"
                                class="absolute top-2 left-2"
                            >
                                {{ t('product.inactive') }}
                            </Badge>
                        </div>

                        <!-- Body -->
                        <div class="flex flex-1 flex-col gap-1.5 p-3">
                            <Badge
                                v-if="product.category"
                                variant="secondary"
                                class="w-fit text-xs"
                            >
                                {{ product.category.name }}
                            </Badge>
                            <h3
                                class="line-clamp-2 text-sm leading-snug font-medium text-foreground"
                            >
                                {{ product.name }}
                            </h3>
                            <p class="font-semibold text-primary tabular-nums">
                                {{ formatIDR(product.price) }}
                            </p>
                            <p
                                class="text-xs"
                                :class="
                                    product.stock === 0
                                        ? 'text-destructive'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ t('product.stockLabel') }}:
                                <span class="tabular-nums">{{
                                    product.stock
                                }}</span>
                            </p>

                            <!-- Actions -->
                            <div
                                class="mt-auto flex items-center gap-2 border-t border-border pt-3"
                            >
                                <Button
                                    as-child
                                    size="sm"
                                    variant="outline"
                                    class="flex-1"
                                >
                                    <Link
                                        :href="
                                            SellerProductController.edit.url(
                                                product.id,
                                            )
                                        "
                                    >
                                        <Pencil class="size-4" />
                                        {{ t('product.editAction') }}
                                    </Link>
                                </Button>

                                <Dialog>
                                    <DialogTrigger as-child>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            class="text-destructive hover:text-destructive"
                                        >
                                            <Trash2 class="size-4" />
                                            <span class="sr-only">{{
                                                t('product.delete', {
                                                    name: product.name,
                                                })
                                            }}</span>
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <Form
                                            v-bind="
                                                SellerProductController.destroy.form(
                                                    product.id,
                                                )
                                            "
                                            :options="{ preserveScroll: true }"
                                            v-slot="{ processing }"
                                        >
                                            <DialogHeader class="space-y-3">
                                                <DialogTitle>{{
                                                    t(
                                                        'product.deleteConfirmTitle',
                                                        {
                                                            name: product.name,
                                                        },
                                                    )
                                                }}</DialogTitle>
                                                <DialogDescription>
                                                    {{
                                                        t(
                                                            'product.deleteConfirmDescription',
                                                        )
                                                    }}
                                                </DialogDescription>
                                            </DialogHeader>
                                            <DialogFooter class="mt-4 gap-2">
                                                <DialogClose as-child>
                                                    <Button
                                                        variant="secondary"
                                                        >{{
                                                            t('common.cancel')
                                                        }}</Button
                                                    >
                                                </DialogClose>
                                                <Button
                                                    type="submit"
                                                    variant="destructive"
                                                    :disabled="processing"
                                                >
                                                    {{
                                                        t(
                                                            'product.deleteConfirm',
                                                        )
                                                    }}
                                                </Button>
                                            </DialogFooter>
                                        </Form>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
