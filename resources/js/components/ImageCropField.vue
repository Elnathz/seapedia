<script setup lang="ts">
import { ref } from 'vue';
import { Cropper } from 'vue-advanced-cropper';
import { useI18n } from 'vue-i18n';
import 'vue-advanced-cropper/dist/style.css';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        aspectRatio: number;
        name?: string;
        maxBytes?: number;
        initialUrl?: string | null;
    }>(),
    { name: 'image', maxBytes: 2 * 1024 * 1024, initialUrl: null },
);

const { t } = useI18n();
const fileInput = ref<HTMLInputElement | null>(null);
const cropOpen = ref(false);
const cropSrc = ref<string | null>(null);
const previewUrl = ref<string | null>(props.initialUrl);
const cropperRef = ref<InstanceType<typeof Cropper> | null>(null);

function onPick(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
return;
}

    if (file.size > props.maxBytes) {
        toast.error(t('product.imageTooLarge'));
        (event.target as HTMLInputElement).value = '';

        return;
    }

    cropSrc.value = URL.createObjectURL(file);
    cropOpen.value = true;
}

function apply() {
    const result = (cropperRef.value as any)?.getResult?.();
    let canvas = result?.canvas;

    if (!canvas) {
        return;
    }

    const maxDimension = 1200;
    if (canvas.width > maxDimension || canvas.height > maxDimension) {
        const scale = Math.min(maxDimension / canvas.width, maxDimension / canvas.height);
        const resizedCanvas = document.createElement('canvas');
        resizedCanvas.width = canvas.width * scale;
        resizedCanvas.height = canvas.height * scale;
        const ctx = resizedCanvas.getContext('2d');
        if (ctx) {
            ctx.drawImage(canvas, 0, 0, resizedCanvas.width, resizedCanvas.height);
            canvas = resizedCanvas;
        }
    }

    canvas.toBlob(
        (blob: Blob | null) => {
            if (!blob) {
return;
}

            const cropped = new File([blob], 'image.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(cropped);

            if (fileInput.value) {
fileInput.value.files = dt.files;
}

            previewUrl.value = URL.createObjectURL(cropped);
            cropOpen.value = false;
        },
        'image/jpeg',
        0.9,
    );
}
</script>

<template>
    <div class="grid gap-2">
        <!-- Hidden file input that Inertia <Form> will submit -->
        <input ref="fileInput" type="file" :name="name" class="hidden" />

        <label class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm hover:bg-muted">
            {{ t('product.cropChoose') }}
            <input
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="hidden"
                @change="onPick"
            />
        </label>

        <img
            v-if="previewUrl"
            :src="previewUrl"
            alt=""
            class="mt-1 size-32 rounded-md border border-border object-cover"
        />

        <Dialog v-model:open="cropOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ t('product.cropChoose') }}</DialogTitle>
                </DialogHeader>
                <Cropper
                    v-if="cropSrc"
                    ref="cropperRef"
                    :src="cropSrc"
                    :stencil-props="{ aspectRatio }"
                    class="h-72 w-full max-w-full overflow-hidden bg-muted"
                />
                <DialogFooter>
                    <Button type="button" @click="apply">{{ t('product.cropApply') }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
