<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
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

const MAX_IMAGE_BYTES = 2 * 1024 * 1024;

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

const previewUrl = ref<string | null>(
    props.product?.image_path ? `/storage/${props.product.image_path}` : null,
);

function resetPreview() {
    previewUrl.value = props.product?.image_path
        ? `/storage/${props.product.image_path}`
        : null;
}

function onImageChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (file && file.size > MAX_IMAGE_BYTES) {
        toast.error(t('product.imageTooLarge'));
        input.value = '';
        resetPreview();

        return;
    }

    previewUrl.value = file ? URL.createObjectURL(file) : null;

    if (!file) {
        resetPreview();
    }
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

            <div class="grid grid-cols-2 gap-4">
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
                        required
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
                        required
                        placeholder="0"
                    />
                    <InputError :message="errors.stock" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="image">{{ t('product.imageLabel') }}</Label>
                <Input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @change="onImageChange"
                />
                <p class="text-xs text-muted-foreground">
                    {{ t('product.imageHelp') }}
                </p>
                <InputError :message="errors.image" />
                <img
                    v-if="previewUrl"
                    :src="previewUrl"
                    :alt="t('product.imagePreviewAlt')"
                    class="mt-2 size-32 rounded-md border border-border object-cover"
                />
            </div>

            <Button :disabled="processing" type="submit">
                {{ product ? t('common.save') : t('product.addTitle') }}
            </Button>
        </Form>
    </div>
</template>
