<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Store as StoreIcon } from '@lucide/vue';
import { computed } from 'vue';
import SellerStoreController from '@/actions/App/Http/Controllers/Web/SellerStoreController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { show as showSellerStore } from '@/routes/seller/store';

interface StoreData {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
}

const props = defineProps<{
    store: StoreData | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Toko Saya', href: showSellerStore() }],
    },
});

const formBinding = computed(() =>
    props.store
        ? SellerStoreController.update.form(props.store.id)
        : SellerStoreController.store.form(),
);
</script>

<template>
    <Head title="Toko Saya" />

    <div class="flex flex-col gap-6">
        <Heading
            variant="small"
            :title="store ? store.name : 'Buat toko'"
            :description="
                store
                    ? 'Kelola informasi toko Anda.'
                    : 'Lengkapi informasi toko sebelum mulai menambahkan produk.'
            "
        />

        <div
            v-if="!store"
            class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border px-6 py-10 text-center"
        >
            <StoreIcon class="size-10 text-muted-foreground" />
            <div class="space-y-1">
                <p class="font-medium text-foreground">Belum ada toko</p>
                <p class="text-sm text-muted-foreground">
                    Buat toko Anda untuk mulai berjualan di SEAPEDIA.
                </p>
            </div>
        </div>

        <Form
            v-bind="formBinding"
            class="max-w-lg space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name"
                    >Nama toko <span class="text-destructive">*</span></Label
                >
                <Input
                    id="name"
                    name="name"
                    :default-value="store?.name"
                    required
                    maxlength="255"
                    placeholder="Contoh: Toko Berkah"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Deskripsi</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="store?.description ?? ''"
                    maxlength="2000"
                    rows="4"
                    placeholder="Ceritakan tentang toko Anda..."
                />
                <InputError :message="errors.description" />
            </div>

            <div class="flex items-center gap-3">
                <Button :disabled="processing" type="submit">
                    {{ store ? 'Simpan' : 'Buat Toko' }}
                </Button>
                <Badge v-if="store?.is_active" variant="secondary">Aktif</Badge>
            </div>
        </Form>
    </div>
</template>
