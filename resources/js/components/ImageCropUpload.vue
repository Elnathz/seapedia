<script setup lang="ts">
import 'cropperjs/dist/cropper.css';
import { ImagePlus, RotateCcw } from '@lucide/vue';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        name: string;
        currentUrl?: string | null;
        aspect?: number;
        outputSize?: number;
    }>(),
    { currentUrl: null, aspect: 1, outputSize: 640 },
);

const pickerInput = ref<HTMLInputElement | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const imgEl = ref<HTMLImageElement | null>(null);

const previewUrl = ref<string | null>(props.currentUrl);
const cropOpen = ref(false);
const pickedUrl = ref<string | null>(null);
const error = ref('');

// cropperjs touches the DOM/window, so it is imported lazily (SSR-safe).
 
let cropper: any = null;

function openPicker(): void {
    pickerInput.value?.click();
}

function onPick(e: Event): void {
    const file = (e.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        error.value = 'File harus berupa gambar.';

        return;
    }

    error.value = '';
    pickedUrl.value = URL.createObjectURL(file);
    cropOpen.value = true;
    // Allow re-picking the same file next time.
    (e.target as HTMLInputElement).value = '';
}

async function initCropper(): Promise<void> {
    await nextTick();

    if (!imgEl.value) {
        return;
    }

    const Cropper = (await import('cropperjs')).default;
    cropper?.destroy();
    cropper = new Cropper(imgEl.value, {
        aspectRatio: props.aspect,
        viewMode: 1,
        autoCropArea: 1,
        background: false,
        responsive: true,
        movable: true,
        zoomable: true,
    });
}

function destroyCropper(): void {
    cropper?.destroy();
    cropper = null;
}

function applyCrop(): void {
    if (!cropper) {
        return;
    }

    const canvas = cropper.getCroppedCanvas({
        width: props.outputSize,
        height: props.outputSize,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(
        (blob: Blob | null) => {
            if (!blob) {
                return;
            }

            const file = new File([blob], 'store-logo.webp', {
                type: 'image/webp',
            });
            const dt = new DataTransfer();
            dt.items.add(file);

            if (fileInput.value) {
                fileInput.value.files = dt.files;
            }

            previewUrl.value = URL.createObjectURL(blob);
            cropOpen.value = false;
        },
        'image/webp',
        0.9,
    );
}

watch(cropOpen, (open) => {
    if (open) {
        initCropper();
    } else {
        destroyCropper();

        if (pickedUrl.value) {
            URL.revokeObjectURL(pickedUrl.value);
            pickedUrl.value = null;
        }
    }
});

onBeforeUnmount(destroyCropper);
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
        <!-- Square preview -->
        <div
            class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-border bg-muted"
        >
            <img
                v-if="previewUrl"
                :src="previewUrl"
                alt="Foto toko"
                class="size-full object-cover"
            />
            <ImagePlus v-else class="size-8 text-muted-foreground/50" />
        </div>

        <div class="space-y-2">
            <div class="flex flex-wrap gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="openPicker"
                >
                    <ImagePlus class="size-4" />
                    {{ previewUrl ? 'Ganti Foto' : 'Unggah Foto' }}
                </Button>
                <Button
                    v-if="previewUrl && !currentUrl"
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="openPicker"
                >
                    <RotateCcw class="size-4" />
                    Ulangi Crop
                </Button>
            </div>
            <ul class="space-y-0.5 text-xs text-muted-foreground">
                <li>Rasio <span class="font-medium">1:1 (persegi)</span>, akan dipotong otomatis.</li>
                <li>Format JPG, PNG, atau WEBP. Maksimal 2MB.</li>
                <li>Resolusi minimal 200x200 piksel.</li>
            </ul>
            <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
        </div>

        <!-- Hidden inputs: picker (not submitted) + named carrier for the crop -->
        <input
            ref="pickerInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="onPick"
        />
        <input ref="fileInput" :name="name" type="file" class="hidden" />

        <Dialog v-model:open="cropOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Sesuaikan Foto (1:1)</DialogTitle>
                </DialogHeader>
                <div class="max-h-[60vh] overflow-hidden rounded-lg bg-muted">
                    <img
                        v-if="pickedUrl"
                        ref="imgEl"
                        :src="pickedUrl"
                        alt="Crop foto toko"
                        class="block max-w-full"
                    />
                </div>
                <DialogFooter class="gap-2">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="cropOpen = false"
                    >
                        Batal
                    </Button>
                    <Button type="button" @click="applyCrop">
                        Gunakan Foto
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
