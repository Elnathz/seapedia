<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    Book,
    Coffee,
    CupSoda,
    Gift,
    Headphones,
    Laptop,
    Package,
    Pencil,
    Plus,
    Shirt,
    ShoppingBasket,
    Smartphone,
    Tags,
    Trash2,
    UtensilsCrossed,
} from '@lucide/vue';
import type { Component } from 'vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminCategoryController from '@/actions/App/Http/Controllers/Web/Admin/CategoryController';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import RequiredMark from '@/components/RequiredMark.vue';
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
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface CategoryRow {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    parent_id: number | null;
    is_active: boolean;
    products_count: number;
    children?: CategoryRow[];
}

const props = defineProps<{ categories: CategoryRow[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelola Kategori', href: AdminCategoryController.index() },
        ],
    },
});

const { t } = useI18n();

const iconChoices: { name: string; icon: Component }[] = [
    { name: 'UtensilsCrossed', icon: UtensilsCrossed },
    { name: 'CupSoda', icon: CupSoda },
    { name: 'Coffee', icon: Coffee },
    { name: 'Smartphone', icon: Smartphone },
    { name: 'Laptop', icon: Laptop },
    { name: 'Headphones', icon: Headphones },
    { name: 'Shirt', icon: Shirt },
    { name: 'ShoppingBasket', icon: ShoppingBasket },
    { name: 'Book', icon: Book },
    { name: 'Gift', icon: Gift },
    { name: 'Package', icon: Package },
];

const iconMap = Object.fromEntries(
    iconChoices.map((choice) => [choice.name, choice.icon]),
);

function iconFor(name: string | null): Component {
    return (name && iconMap[name]) || Package;
}

const dialogOpen = ref(false);
const editing = ref<CategoryRow | null>(null);
const parentValue = ref('none');
const iconValue = ref('Package');

const deleteTarget = ref<CategoryRow | null>(null);

const formBinding = computed(() =>
    editing.value
        ? AdminCategoryController.update.form(editing.value.slug)
        : AdminCategoryController.store.form(),
);

function openCreate() {
    editing.value = null;
    parentValue.value = 'none';
    iconValue.value = 'Package';
    dialogOpen.value = true;
}

function openEdit(category: CategoryRow) {
    editing.value = category;
    parentValue.value = category.parent_id ? String(category.parent_id) : 'none';
    iconValue.value = category.icon ?? 'Package';
    dialogOpen.value = true;
}
</script>

<template>
    <Head :title="t('admin.manageCategoriesTitle')" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading variant="small" :title="t('admin.manageCategoriesTitle')" />
            <Button @click="openCreate">
                <Plus class="size-4" />
                {{ t('admin.addCategory') }}
            </Button>
        </div>

        <EmptyState
            v-if="props.categories.length === 0"
            :icon="Tags"
            :title="t('admin.categoriesEmptyTitle')"
            :description="t('admin.categoriesEmptyDescription')"
            :action-label="t('admin.addCategory')"
            @action="openCreate"
        />

        <div v-else class="flex flex-col gap-3">
            <div
                v-for="parent in props.categories"
                :key="parent.id"
                class="overflow-hidden rounded-xl border bg-card"
            >
                <!-- Parent row -->
                <div class="flex items-center gap-3 p-4">
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <component :is="iconFor(parent.icon)" class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-foreground">
                            {{ parent.name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{
                                t('admin.categoryProductsCount', {
                                    count: parent.products_count,
                                })
                            }}
                        </p>
                    </div>
                    <Badge v-if="!parent.is_active" variant="secondary">
                        {{ t('admin.statusInactive') }}
                    </Badge>
                    <Button
                        size="icon"
                        variant="ghost"
                        :aria-label="t('admin.editCategory')"
                        @click="openEdit(parent)"
                    >
                        <Pencil class="size-4" />
                    </Button>
                    <Button
                        size="icon"
                        variant="ghost"
                        class="text-destructive hover:text-destructive"
                        :aria-label="t('admin.editCategory')"
                        @click="deleteTarget = parent"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>

                <!-- Children -->
                <ul
                    v-if="parent.children && parent.children.length"
                    class="divide-y border-t bg-muted/30"
                >
                    <li
                        v-for="child in parent.children"
                        :key="child.id"
                        class="flex items-center gap-3 py-2.5 pr-3 pl-14"
                    >
                        <span class="min-w-0 flex-1 truncate text-sm">
                            {{ child.name }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{
                                t('admin.categoryProductsCount', {
                                    count: child.products_count,
                                })
                            }}
                        </span>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="size-8"
                            :aria-label="t('admin.editCategory')"
                            @click="openEdit(child)"
                        >
                            <Pencil class="size-3.5" />
                        </Button>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="size-8 text-destructive hover:text-destructive"
                            :aria-label="t('admin.editCategory')"
                            @click="deleteTarget = child"
                        >
                            <Trash2 class="size-3.5" />
                        </Button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Create / edit dialog -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <Form
                    v-bind="formBinding"
                    :options="{ preserveScroll: true }"
                    class="space-y-4"
                    @success="dialogOpen = false"
                    v-slot="{ errors, processing }"
                >
                    <DialogHeader>
                        <DialogTitle>
                            {{
                                editing
                                    ? t('admin.editCategory')
                                    : t('admin.addCategory')
                            }}
                        </DialogTitle>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="category_name">
                            {{ t('admin.categoryNameLabel') }} <RequiredMark />
                        </Label>
                        <Input
                            id="category_name"
                            name="name"
                            :default-value="editing?.name"
                            required
                            minlength="2"
                            maxlength="80"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_parent">
                            {{ t('admin.categoryParentLabel') }}
                        </Label>
                        <input
                            type="hidden"
                            name="parent_id"
                            :value="parentValue === 'none' ? '' : parentValue"
                        />
                        <Select v-model="parentValue">
                            <SelectTrigger id="category_parent" class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">
                                    {{ t('admin.categoryParentNone') }}
                                </SelectItem>
                                <SelectItem
                                    v-for="root in props.categories.filter(
                                        (c) => c.id !== editing?.id,
                                    )"
                                    :key="root.id"
                                    :value="String(root.id)"
                                >
                                    {{ root.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.parent_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category_icon">
                            {{ t('admin.categoryIconLabel') }}
                        </Label>
                        <input type="hidden" name="icon" :value="iconValue" />
                        <Select v-model="iconValue">
                            <SelectTrigger id="category_icon" class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="choice in iconChoices"
                                    :key="choice.name"
                                    :value="choice.name"
                                >
                                    <span class="flex items-center gap-2">
                                        <component
                                            :is="choice.icon"
                                            class="size-4"
                                        />
                                        {{ choice.name }}
                                    </span>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.icon" />
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" type="button">
                                {{ t('common.cancel') }}
                            </Button>
                        </DialogClose>
                        <Button type="submit" :disabled="processing">
                            {{ t('common.save') }}
                        </Button>
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>

        <!-- Delete confirm dialog -->
        <Dialog
            :open="deleteTarget !== null"
            @update:open="(open) => !open && (deleteTarget = null)"
        >
            <DialogContent v-if="deleteTarget">
                <Form
                    v-bind="AdminCategoryController.destroy.form(deleteTarget.slug)"
                    :options="{ preserveScroll: true }"
                    @success="deleteTarget = null"
                    v-slot="{ processing }"
                    class="space-y-4"
                >
                    <DialogHeader>
                        <DialogTitle>
                            {{
                                t('admin.deleteCategoryConfirmTitle', {
                                    name: deleteTarget.name,
                                })
                            }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ t('admin.deleteCategoryConfirmDescription') }}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" type="button">
                                {{ t('common.cancel') }}
                            </Button>
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
</template>
