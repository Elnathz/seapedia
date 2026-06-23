<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SellerProductController from '@/actions/App/Http/Controllers/Web/SellerProductController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index as productsIndex } from '@/routes/seller/products';

interface ProductData {
    id: number;
    name: string;
    description: string | null;
    price: number;
    stock: number;
    image_path: string | null;
}

const props = defineProps<{
    product: ProductData | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Produk', href: productsIndex() }],
    },
});

const formBinding = computed(() =>
    props.product
        ? SellerProductController.update.form(props.product.id)
        : SellerProductController.store.form(),
);

const previewUrl = ref<string | null>(
    props.product?.image_path ? `/storage/${props.product.image_path}` : null,
);

function onImageChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    previewUrl.value = file
        ? URL.createObjectURL(file)
        : props.product?.image_path
          ? `/storage/${props.product.image_path}`
          : null;
}
</script>

<template>
    <Head :title="product ? 'Edit Produk' : 'Tambah Produk'" />

    <div class="flex flex-col gap-6">
        <Heading
            variant="small"
            :title="product ? 'Edit Produk' : 'Tambah Produk'"
            :description="
                product
                    ? 'Perbarui informasi produk Anda.'
                    : 'Lengkapi detail produk baru.'
            "
        />

        <Form
            v-bind="formBinding"
            class="max-w-lg space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name"
                    >Nama produk <span class="text-destructive">*</span></Label
                >
                <Input
                    id="name"
                    name="name"
                    :default-value="product?.name"
                    required
                    maxlength="255"
                    placeholder="Contoh: Kopi Susu Gula Aren"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Deskripsi</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="product?.description ?? ''"
                    maxlength="2000"
                    rows="4"
                    placeholder="Ceritakan tentang produk ini..."
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="price"
                        >Harga (Rp)
                        <span class="text-destructive">*</span></Label
                    >
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        min="0"
                        step="1"
                        :default-value="product?.price"
                        required
                        placeholder="0"
                    />
                    <InputError :message="errors.price" />
                </div>
                <div class="grid gap-2">
                    <Label for="stock"
                        >Stok <span class="text-destructive">*</span></Label
                    >
                    <Input
                        id="stock"
                        name="stock"
                        type="number"
                        min="0"
                        step="1"
                        :default-value="product?.stock"
                        required
                        placeholder="0"
                    />
                    <InputError :message="errors.stock" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="image">Gambar produk</Label>
                <Input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @change="onImageChange"
                />
                <InputError :message="errors.image" />
                <img
                    v-if="previewUrl"
                    :src="previewUrl"
                    alt="Pratinjau gambar produk"
                    class="mt-2 size-32 rounded-md border border-border object-cover"
                />
            </div>

            <Button :disabled="processing" type="submit">
                {{ product ? 'Simpan' : 'Tambah Produk' }}
            </Button>
        </Form>
    </div>
</template>
