<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Images, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import ImageCropField from '@/components/ImageCropField.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
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
import { bannerSrc } from '@/types/banner';

interface BannerRow {
    id: number;
    placement: 'main' | 'side';
    image_path: string;
    title: string;
    subtitle: string | null;
    badge_label: string | null;
    cta_label: string | null;
    cta_url: string | null;
    sort_order: number;
    is_active: boolean;
}

defineProps<{ banners: BannerRow[] }>();

const page = usePage();
const errors = computed(() => (page.props.errors as Record<string, string>) ?? {});

const formOpen = ref(false);
const editing = ref<BannerRow | null>(null);
const deleteTarget = ref<BannerRow | null>(null);
const processing = ref(false);

const formPlacement = ref<'main' | 'side'>('main');
const formActive = ref(true);

const aspectRatio = computed(() => (formPlacement.value === 'main' ? 5 / 2 : 3 / 2));
const initialImageUrl = computed(() =>
    editing.value ? bannerSrc(editing.value.image_path) : null,
);

function openCreate() {
    editing.value = null;
    formPlacement.value = 'main';
    formActive.value = true;
    formOpen.value = true;
}

function openEdit(banner: BannerRow) {
    editing.value = banner;
    formPlacement.value = banner.placement;
    formActive.value = banner.is_active;
    formOpen.value = true;
}

function closeForm() {
    formOpen.value = false;
    editing.value = null;
}

function submitForm(e: Event) {
    const el = e.currentTarget as HTMLFormElement;
    const fd = new FormData(el);
    fd.set('is_active', formActive.value ? '1' : '0');
    fd.set('placement', formPlacement.value);

    processing.value = true;

    if (editing.value) {
        fd.append('_method', 'PUT');
        router.post(`/admin/banners/${editing.value.id}`, fd as any, {
            onSuccess: closeForm,
            onFinish: () => (processing.value = false),
        });
    } else {
        router.post('/admin/banners', fd as any, {
            onSuccess: closeForm,
            onFinish: () => (processing.value = false),
        });
    }
}

function confirmDelete() {
    if (!deleteTarget.value) {
return;
}

    router.delete(`/admin/banners/${deleteTarget.value.id}`, {
        onSuccess: () => (deleteTarget.value = null),
    });
}
</script>

<template>
    <Head title="Kelola Banner" />

    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Kelola Banner"
                description="Atur banner katalog: bagian utama (2.5:1) dan samping (3:2)."
            />
            <Button @click="openCreate">
                <Plus class="mr-2 size-4" />
                Tambah Banner
            </Button>
        </div>

        <EmptyState
            v-if="banners.length === 0"
            :icon="Images"
            title="Belum ada banner"
            description="Tambahkan banner untuk ditampilkan di halaman katalog."
        />

        <div v-else class="overflow-hidden rounded-lg border border-border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 text-left">Gambar</th>
                        <th class="px-4 py-3 text-left">Posisi</th>
                        <th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-center">Urutan</th>
                        <th class="px-4 py-3 text-center">Aktif</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="banner in banners"
                        :key="banner.id"
                        class="border-t border-border hover:bg-muted/20"
                    >
                        <td class="px-4 py-3">
                            <img
                                :src="bannerSrc(banner.image_path)"
                                :alt="banner.title"
                                class="h-10 w-20 rounded object-cover"
                            />
                        </td>
                        <td class="px-4 py-3">
                            <Badge :variant="banner.placement === 'main' ? 'default' : 'secondary'">
                                {{ banner.placement === 'main' ? 'Utama' : 'Samping' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ banner.title }}</td>
                        <td class="px-4 py-3 text-center tabular-nums">{{ banner.sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            <Badge :variant="banner.is_active ? 'default' : 'outline'">
                                {{ banner.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :aria-label="`Edit ${banner.title}`"
                                    @click="openEdit(banner)"
                                >
                                    <Pencil class="size-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    :aria-label="`Hapus ${banner.title}`"
                                    @click="deleteTarget = banner"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create / Edit Dialog -->
    <Dialog v-model:open="formOpen">
        <DialogContent class="max-w-2xl overflow-y-auto max-h-[90vh]">
            <DialogHeader>
                <DialogTitle>
                    {{ editing ? 'Edit Banner' : 'Tambah Banner' }}
                </DialogTitle>
            </DialogHeader>
            <form
                v-if="formOpen"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="submitForm"
            >
                <!-- Placement -->
                <div class="grid gap-2">
                    <Label>Posisi <span class="text-destructive">*</span></Label>
                    <Select v-model="formPlacement">
                        <SelectTrigger class="w-48">
                            <SelectValue placeholder="Pilih posisi" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="main">Utama (rasio 5:2)</SelectItem>
                            <SelectItem value="side">Samping (rasio 3:2)</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.placement" />
                </div>

                <!-- Title -->
                <div class="grid gap-2">
                    <Label for="title">Judul <span class="text-destructive">*</span></Label>
                    <Input
                        id="title"
                        name="title"
                        :default-value="editing?.title ?? ''"
                        required
                        maxlength="120"
                        placeholder="Judul banner"
                    />
                    <InputError :message="errors.title" />
                </div>

                <!-- Subtitle -->
                <div class="grid gap-2">
                    <Label for="subtitle">Subjudul</Label>
                    <Input
                        id="subtitle"
                        name="subtitle"
                        :default-value="editing?.subtitle ?? ''"
                        maxlength="200"
                        placeholder="Subjudul opsional"
                    />
                    <InputError :message="errors.subtitle" />
                </div>

                <!-- Badge + CTA in 2 cols -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="badge_label">Label Badge</Label>
                        <Input
                            id="badge_label"
                            name="badge_label"
                            :default-value="editing?.badge_label ?? ''"
                            maxlength="40"
                            placeholder="mis. DISKON 20%"
                        />
                        <InputError :message="errors.badge_label" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cta_label">Label Tombol CTA</Label>
                        <Input
                            id="cta_label"
                            name="cta_label"
                            :default-value="editing?.cta_label ?? ''"
                            maxlength="40"
                            placeholder="mis. Lihat Promo"
                        />
                        <InputError :message="errors.cta_label" />
                    </div>
                </div>

                <!-- CTA URL + Sort -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="cta_url">URL Tombol CTA</Label>
                        <Input
                            id="cta_url"
                            name="cta_url"
                            :default-value="editing?.cta_url ?? ''"
                            maxlength="200"
                            placeholder="/catalog?category=..."
                        />
                        <InputError :message="errors.cta_url" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="sort_order">Urutan</Label>
                        <Input
                            id="sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                            max="1000"
                            :default-value="editing?.sort_order ?? 0"
                        />
                        <InputError :message="errors.sort_order" />
                    </div>
                </div>

                <!-- is_active -->
                <div class="flex items-center gap-3">
                    <Checkbox
                        id="is_active"
                        :checked="formActive"
                        @update:checked="(v) => (formActive = !!v)"
                    />
                    <Label for="is_active" class="cursor-pointer">Aktif</Label>
                </div>

                <!-- Image -->
                <div class="grid gap-2">
                    <Label>
                        Gambar
                        <span v-if="!editing" class="text-destructive">*</span>
                        <span v-else class="text-xs text-muted-foreground">(kosongkan untuk tidak mengubah)</span>
                    </Label>
                    <ImageCropField
                        :key="editing?.id ?? 'new'"
                        :aspect-ratio="aspectRatio"
                        name="image"
                        :initial-url="initialImageUrl"
                    />
                    <InputError :message="errors.image" />
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button type="button" variant="outline">Batal</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        {{ editing ? 'Simpan' : 'Tambahkan' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="!!deleteTarget" @update:open="(v) => !v && (deleteTarget = null)">
        <DialogContent class="max-w-sm">
            <DialogHeader>
                <DialogTitle>Hapus banner?</DialogTitle>
            </DialogHeader>
            <p class="text-sm text-muted-foreground">
                Banner "<strong>{{ deleteTarget?.title }}</strong>" akan dihapus permanen.
            </p>
            <DialogFooter>
                <Button variant="outline" @click="deleteTarget = null">Batal</Button>
                <Button variant="destructive" @click="confirmDelete">Hapus</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
