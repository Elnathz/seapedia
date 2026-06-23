<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { Package, Pencil, Plus, Trash2 } from '@lucide/vue';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatIDR } from '@/lib/utils';
import { create as createProduct } from '@/routes/seller/products';

interface ProductRow {
    id: number;
    name: string;
    price: number;
    stock: number;
    image_path: string | null;
    is_active: boolean;
}

defineProps<{
    products: ProductRow[];
}>();

const { t } = useI18n();
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

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ t('product.columnImage') }}</TableHead>
                    <TableHead>{{ t('product.columnName') }}</TableHead>
                    <TableHead>{{ t('product.columnPrice') }}</TableHead>
                    <TableHead>{{ t('product.columnStock') }}</TableHead>
                    <TableHead>{{ t('product.columnStatus') }}</TableHead>
                    <TableHead class="text-right">{{
                        t('product.columnActions')
                    }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="product in products" :key="product.id">
                    <TableCell>
                        <img
                            v-if="product.image_path"
                            :src="`/storage/${product.image_path}`"
                            :alt="product.name"
                            loading="lazy"
                            class="size-12 rounded-md border border-border object-cover"
                        />
                        <div
                            v-else
                            class="flex size-12 items-center justify-center rounded-md border border-dashed border-border text-muted-foreground"
                        >
                            <Package class="size-5" />
                        </div>
                    </TableCell>
                    <TableCell class="font-medium">{{
                        product.name
                    }}</TableCell>
                    <TableCell class="tabular-nums">{{
                        formatIDR(product.price)
                    }}</TableCell>
                    <TableCell class="tabular-nums">{{
                        product.stock
                    }}</TableCell>
                    <TableCell>
                        <Badge
                            :variant="
                                product.is_active ? 'secondary' : 'outline'
                            "
                        >
                            {{
                                product.is_active
                                    ? t('product.active')
                                    : t('product.inactive')
                            }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex justify-end gap-2">
                            <Button as-child size="sm" variant="outline">
                                <Link
                                    :href="
                                        SellerProductController.edit.url(
                                            product.id,
                                        )
                                    "
                                >
                                    <Pencil class="size-4" />
                                    <span class="sr-only">{{
                                        t('product.edit', {
                                            name: product.name,
                                        })
                                    }}</span>
                                </Link>
                            </Button>

                            <Dialog>
                                <DialogTrigger as-child>
                                    <Button size="sm" variant="destructive">
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
                                                    { name: product.name },
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
                                                <Button variant="secondary">{{
                                                    t('common.cancel')
                                                }}</Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                :disabled="processing"
                                            >
                                                {{ t('product.deleteConfirm') }}
                                            </Button>
                                        </DialogFooter>
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
