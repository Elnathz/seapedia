<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import SellerProductController from '@/actions/App/Http/Controllers/Web/SellerProductController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useCategories } from '@/composables/useCategories';
import { index as productsIndex } from '@/routes/seller/products';

interface ProductData {
    id: number;
    name: string;
    description: string | null;
    category_id: number;
    price: number;
    stock: number;
    image_path: string | null;
    variants?: { id: number; name: string; price: number; stock: number }[];
    images?: { id: number; image_path: string }[];
}

const props = defineProps<{
    product: ProductData | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Produk', href: productsIndex() }],
    },
});

const { t } = useI18n();
const { categories } = useCategories();

const formBinding = computed(() =>
    props.product
        ? SellerProductController.update.form(props.product.id)
        : SellerProductController.store.form(),
);

// Cascading category picker: parent narrows the subcategory list. The hidden
// `category_id` field submits the leaf when one exists, otherwise the parent.
const parentId = ref('');
const childId = ref('');

const childOptions = computed(
    () =>
        categories.value.find((root) => String(root.id) === parentId.value)
            ?.children ?? [],
);

const submittedCategoryId = computed(() =>
    childOptions.value.length > 0 ? childId.value : parentId.value,
);

watch(parentId, () => {
    childId.value = '';
});

// Prefill the cascade when editing: locate the product's category in the tree.
if (props.product) {
    for (const root of categories.value) {
        if (root.id === props.product.category_id) {
            parentId.value = String(root.id);
            break;
        }

        const child = root.children?.find(
            (c) => c.id === props.product?.category_id,
        );

        if (child) {
            parentId.value = String(root.id);
            childId.value = String(child.id);
            break;
        }
    }
}

const initialImageUrl = props.product?.image_path
    ? `/storage/${props.product.image_path}`
    : null;

const hasVariants = ref(!!(props.product?.variants && props.product.variants.length > 0));
const variants = ref(
    props.product?.variants && props.product.variants.length > 0
        ? props.product.variants
        : [{ name: '', price: 0, stock: 0 }]
);

function addVariant() {
    variants.value.push({ name: '', price: 0, stock: 0 });
}

function removeVariant(index: number) {
    variants.value.splice(index, 1);
}
const deletedImageIds = ref<number[]>([]);
const newImagePreviews = ref<string[]>([]);
const existingImages = computed(() => {
    return props.product?.images?.filter(img => !deletedImageIds.value.includes(img.id)) ?? [];
});

function handleImageSelect(e: Event) {
    const files = (e.target as HTMLInputElement).files;

    if (!files) {
return;
}

    newImagePreviews.value = Array.from(files).map(f => URL.createObjectURL(f));
}
</script>

<template>
    <Head :title="product ? t('product.editTitle') : t('product.addTitle')" />

    <div class="flex flex-col gap-6">
        <Heading
            variant="small"
            :title="product ? t('product.editTitle') : t('product.addTitle')"
            :description="
                product
                    ? t('product.editDescription')
                    : t('product.addDescription')
            "
        />

        <Form
            v-bind="formBinding"
            class="max-w-lg space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">
                    {{ t('product.nameLabel') }} <RequiredMark />
                </Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="product?.name"
                    required
                    minlength="3"
                    maxlength="150"
                    :placeholder="t('product.namePlaceholder')"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">
                    {{ t('product.descriptionLabel') }}
                </Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="product?.description ?? ''"
                    maxlength="2000"
                    rows="4"
                    :placeholder="t('product.descriptionPlaceholder')"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2">
                <Label>
                    {{ t('product.categoryLabel') }} <RequiredMark />
                </Label>
                <input
                    type="hidden"
                    name="category_id"
                    :value="submittedCategoryId"
                />
                <div class="grid gap-2 sm:grid-cols-2">
                    <Select v-model="parentId">
                        <SelectTrigger
                            id="category_parent"
                            class="w-full"
                            :aria-label="t('product.categoryLabel')"
                        >
                            <SelectValue
                                :placeholder="
                                    t('product.categoryParentPlaceholder')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="root in categories"
                                :key="root.id"
                                :value="String(root.id)"
                            >
                                {{ root.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-if="childOptions.length > 0" v-model="childId">
                        <SelectTrigger id="category_child" class="w-full">
                            <SelectValue
                                :placeholder="
                                    t('product.categoryChildPlaceholder')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="child in childOptions"
                                :key="child.id"
                                :value="String(child.id)"
                            >
                                {{ child.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ t('product.categoryHelp') }}
                </p>
                <InputError :message="errors.category_id" />
            </div>

            <div class="flex items-center space-x-2 pt-4">
                <Checkbox id="has_variants" :checked="hasVariants" @update:checked="(v) => hasVariants = !!v" />
                <input type="hidden" name="has_variants" :value="hasVariants ? '1' : '0'" />
                <label for="has_variants" class="text-sm font-medium leading-none cursor-pointer">
                    Produk memiliki varian
                </label>
            </div>

            <div v-if="!hasVariants" class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="price">
                        {{ t('product.priceLabel') }} <RequiredMark />
                    </Label>
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        min="100"
                        step="1"
                        :default-value="product?.price"
                        :required="!hasVariants"
                        placeholder="0"
                    />
                    <InputError :message="errors.price" />
                </div>
                <div class="grid gap-2">
                    <Label for="stock">
                        {{ t('product.stockLabel') }} <RequiredMark />
                    </Label>
                    <Input
                        id="stock"
                        name="stock"
                        type="number"
                        min="0"
                        step="1"
                        :default-value="product?.stock"
                        :required="!hasVariants"
                        placeholder="0"
                    />
                    <InputError :message="errors.stock" />
                </div>
            </div>

            <div v-else class="space-y-4 pt-2">
                <div class="flex items-center justify-between">
                    <Label>Varian Produk <RequiredMark /></Label>
                    <Button type="button" variant="outline" size="sm" @click="addVariant">Tambah Varian</Button>
                </div>
                <div v-for="(variant, idx) in variants" :key="idx" class="p-4 border rounded-xl space-y-4 bg-muted/30 relative">
                    <input type="hidden" :name="`variants[${idx}][id]`" :value="variant.id" />
                    
                    <div class="grid gap-2">
                        <Label>Nama Varian (mis. Merah, L)</Label>
                        <Input :name="`variants[${idx}][name]`" v-model="variant.name" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Harga</Label>
                            <Input :name="`variants[${idx}][price]`" v-model="variant.price" type="number" required />
                        </div>
                        <div class="grid gap-2">
                            <Label>Stok</Label>
                            <Input :name="`variants[${idx}][stock]`" v-model="variant.stock" type="number" required />
                        </div>
                    </div>
                    <Button v-if="variants.length > 1" type="button" variant="destructive" size="sm" class="absolute top-2 right-2 h-8 px-2" @click="removeVariant(idx)">Hapus</Button>
                </div>
                <InputError :message="errors.variants" />
            </div>

            <div class="grid gap-2">
                <Label>Gambar Produk (Maks. 5)</Label>
                <div class="flex flex-wrap gap-4">
                    <div v-for="img in existingImages" :key="'img-'+img.id" class="relative size-24 border rounded-xl overflow-hidden group">
                        <img :src="`/storage/${img.image_path}`" class="size-full object-cover" />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <Button type="button" variant="destructive" size="sm" class="h-8 px-2" @click="deletedImageIds.push(img.id)">Hapus</Button>
                        </div>
                    </div>
                    
                    <div v-for="(preview, idx) in newImagePreviews" :key="'new-'+idx" class="relative size-24 border rounded-xl overflow-hidden">
                        <img :src="preview" class="size-full object-cover" />
                    </div>
                    
                    <label class="size-24 border border-dashed rounded-xl flex flex-col items-center justify-center cursor-pointer hover:bg-muted text-muted-foreground transition-colors gap-1">
                        <span class="text-xl leading-none">+</span>
                        <span class="text-[10px]">Pilih Gambar</span>
                        <input type="file" name="images[]" multiple accept="image/*" class="hidden" @change="handleImageSelect" />
                    </label>
                </div>
                <input v-for="id in deletedImageIds" :key="'del-'+id" type="hidden" name="deleted_image_ids[]" :value="id" />
                <p class="text-xs text-muted-foreground">Pilih gambar untuk mengupload. Gambar pertama akan menjadi gambar utama. Untuk mengubah gambar baru yang dipilih, klik Pilih Gambar lagi.</p>
                <InputError :message="errors.images" />
            </div>

            <Button :disabled="processing" type="submit">
                {{ product ? t('common.save') : t('product.addTitle') }}
            </Button>
        </Form>
    </div>
</template>
