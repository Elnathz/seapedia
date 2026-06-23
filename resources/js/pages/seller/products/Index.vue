<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { Package, Pencil, Plus, Trash2 } from '@lucide/vue';
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
</script>

<template>
    <Head title="Produk" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Produk"
                description="Kelola produk di toko Anda."
            />
            <Button as-child>
                <Link :href="createProduct()">
                    <Plus class="size-4" />
                    Tambah Produk
                </Link>
            </Button>
        </div>

        <EmptyState
            v-if="products.length === 0"
            :icon="Package"
            title="Belum ada produk"
            description="Tambahkan produk pertama Anda agar muncul di katalog publik."
            action-label="Tambah Produk"
            @action="router.visit(createProduct().url)"
        />

        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead>Gambar</TableHead>
                    <TableHead>Nama</TableHead>
                    <TableHead>Harga</TableHead>
                    <TableHead>Stok</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
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
                            {{ product.is_active ? 'Aktif' : 'Nonaktif' }}
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
                                    <span class="sr-only"
                                        >Edit {{ product.name }}</span
                                    >
                                </Link>
                            </Button>

                            <Dialog>
                                <DialogTrigger as-child>
                                    <Button size="sm" variant="destructive">
                                        <Trash2 class="size-4" />
                                        <span class="sr-only"
                                            >Hapus {{ product.name }}</span
                                        >
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
                                            <DialogTitle
                                                >Hapus "{{
                                                    product.name
                                                }}"?</DialogTitle
                                            >
                                            <DialogDescription>
                                                Tindakan ini tidak dapat
                                                dibatalkan. Produk akan hilang
                                                dari katalog publik.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <DialogFooter class="mt-4 gap-2">
                                            <DialogClose as-child>
                                                <Button variant="secondary"
                                                    >Batal</Button
                                                >
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                :disabled="processing"
                                            >
                                                Hapus
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
