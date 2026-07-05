<script setup lang="ts">
import { ImagePlus, Loader2, Star, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';

interface ExistingImage {
    id: number;
    image_path: string;
}

const props = withDefaults(
    defineProps<{
        existingImages?: ExistingImage[];
        max?: number;
    }>(),
    { max: 5 },
);

const MAX_BYTES = 2 * 1024 * 1024; // matches the backend `max:2048` rule
const ACCEPT_TYPES = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/heic',
    'image/heif',
];
const HEIC_RE = /\.(heic|heif)$/i;

const deletedIds = ref<number[]>([]);
const newFiles = ref<File[]>([]);
const previews = ref<string[]>([]); // object URLs, index-aligned with newFiles
const submitInput = ref<HTMLInputElement | null>(null);
const converting = ref(false);
const error = ref('');

const visibleExisting = computed(() =>
    (props.existingImages ?? []).filter(
        (img) => !deletedIds.value.includes(img.id),
    ),
);
const totalCount = computed(
    () => visibleExisting.value.length + newFiles.value.length,
);
const isFull = computed(() => totalCount.value >= props.max);

function isHeic(file: File): boolean {
    return (
        file.type === 'image/heic' ||
        file.type === 'image/heif' ||
        HEIC_RE.test(file.name)
    );
}

function isAllowed(file: File): boolean {
    return ACCEPT_TYPES.includes(file.type) || isHeic(file);
}

// Keep the hidden submit input's FileList in step with the accumulated files so
// the Inertia <Form> posts the full set as images[] — this is what lets the
// user add pictures one at a time instead of selecting all five at once.
function syncInput(): void {
    if (!submitInput.value) {
        return;
    }

    const dt = new DataTransfer();
    newFiles.value.forEach((file) => dt.items.add(file));
    submitInput.value.files = dt.files;
}

async function onPick(event: Event): Promise<void> {
    error.value = '';

    const input = event.target as HTMLInputElement;
    const picked = Array.from(input.files ?? []);
    input.value = ''; // reset so the same file can be re-picked later

    if (!picked.length) {
        return;
    }

    const remaining = props.max - totalCount.value;

    if (remaining <= 0) {
        error.value = `Sudah ${props.max} gambar. Hapus salah satu dulu untuk menambah.`;

        return;
    }

    const toAdd = picked.slice(0, remaining);

    if (picked.length > remaining) {
        error.value = `Cuma bisa menambah ${remaining} gambar lagi (maks ${props.max}). Sisanya diabaikan.`;
    }

    converting.value = true;

    try {
        for (const file of toAdd) {
            if (!isAllowed(file)) {
                error.value =
                    'Format tidak didukung. Pakai JPG, PNG, WEBP, atau HEIC.';

                continue;
            }

            let out = file;

            // Browsers can't render HEIC and the server (GD only) can't decode
            // it, so convert to JPEG in the browser before it ever leaves.
            if (isHeic(file)) {
                const heic2any = (await import('heic2any')).default;
                const blob = (await heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.9,
                })) as Blob;
                out = new File([blob], file.name.replace(HEIC_RE, '.jpg'), {
                    type: 'image/jpeg',
                });
            }

            if (out.size > MAX_BYTES) {
                error.value = `"${out.name}" lebih dari 2MB, jadi dilewati.`;

                continue;
            }

            newFiles.value.push(out);
            previews.value.push(URL.createObjectURL(out));
        }

        syncInput();
    } catch {
        error.value = 'Gagal memproses gambar. Coba file lain.';
    } finally {
        converting.value = false;
    }
}

function removeNew(index: number): void {
    URL.revokeObjectURL(previews.value[index]);
    newFiles.value.splice(index, 1);
    previews.value.splice(index, 1);
    error.value = '';
    syncInput();
}

function removeExisting(id: number): void {
    deletedIds.value.push(id);
    error.value = '';
}

function openPicker(): void {
    if (isFull.value) {
        error.value = `Sudah ${props.max} gambar. Hapus salah satu dulu untuk menambah.`;

        return;
    }

    submitInput.value?.click();
}

onBeforeUnmount(() =>
    previews.value.forEach((url) => URL.revokeObjectURL(url)),
);
</script>

<template>
    <div class="space-y-3">
        <div class="flex flex-wrap gap-3">
            <!-- Existing images (edit mode) -->
            <div
                v-for="(img, idx) in visibleExisting"
                :key="'img-' + img.id"
                class="group relative size-24 overflow-hidden rounded-xl border border-border"
            >
                <img
                    :src="`/storage/${img.image_path}`"
                    class="size-full object-cover"
                    alt=""
                />
                <span
                    v-if="idx === 0"
                    class="absolute top-1 left-1 inline-flex items-center gap-0.5 rounded-full bg-primary/90 px-1.5 py-0.5 text-[9px] font-medium text-primary-foreground"
                >
                    <Star class="size-2.5" /> Utama
                </span>
                <button
                    type="button"
                    class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-black/55 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                    aria-label="Hapus gambar"
                    @click="removeExisting(img.id)"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Newly picked images -->
            <div
                v-for="(preview, idx) in previews"
                :key="'new-' + idx"
                class="group relative size-24 overflow-hidden rounded-xl border border-border"
            >
                <img :src="preview" class="size-full object-cover" alt="" />
                <span
                    v-if="visibleExisting.length === 0 && idx === 0"
                    class="absolute top-1 left-1 inline-flex items-center gap-0.5 rounded-full bg-primary/90 px-1.5 py-0.5 text-[9px] font-medium text-primary-foreground"
                >
                    <Star class="size-2.5" /> Utama
                </span>
                <button
                    type="button"
                    class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-black/55 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                    aria-label="Hapus gambar"
                    @click="removeNew(idx)"
                >
                    <X class="size-3.5" />
                </button>
            </div>

            <!-- Add tile — hidden once full so the limit is obvious -->
            <button
                v-if="!isFull"
                type="button"
                :disabled="converting"
                class="flex size-24 flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-border text-muted-foreground transition-colors hover:border-primary/50 hover:bg-primary/5 disabled:opacity-60"
                @click="openPicker"
            >
                <Loader2 v-if="converting" class="size-5 animate-spin" />
                <ImagePlus v-else class="size-5" />
                <span class="text-[10px] font-medium">{{
                    converting ? 'Memproses…' : 'Tambah'
                }}</span>
            </button>
        </div>

        <!-- Always-present submit input; the Form serialises it as images[]. -->
        <input
            ref="submitInput"
            type="file"
            name="images[]"
            multiple
            accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif"
            class="hidden"
            @change="onPick"
        />
        <input
            v-for="id in deletedIds"
            :key="'del-' + id"
            type="hidden"
            name="deleted_image_ids[]"
            :value="id"
        />

        <div class="space-y-1">
            <p class="text-xs text-muted-foreground">
                {{ totalCount }}/{{ max }} gambar · Format JPG, PNG, WEBP, HEIC
                · Maks 2MB tiap gambar. Gambar pertama jadi foto utama.
            </p>
            <p v-if="error" class="text-xs font-medium text-destructive">
                {{ error }}
            </p>
        </div>
    </div>
</template>
