<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Images, Pencil, Plus, Trash2, Search, Loader2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
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

const props = defineProps<{ banners: BannerRow[] }>();

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

const recommendedSortOrder = computed(() => {
    const matchingBanners = props.banners.filter(
        (b) => b.placement === formPlacement.value
    );
    if (matchingBanners.length === 0) return 0;
    const maxOrder = Math.max(...matchingBanners.map((b) => b.sort_order));
    return maxOrder + 1;
});

const formSortOrder = ref(0);

watch([formPlacement, editing], () => {
    if (editing.value) {
        formSortOrder.value = editing.value.sort_order;
    } else {
        formSortOrder.value = recommendedSortOrder.value;
    }
}, { immediate: true });

// Autocomplete CTA URL Logic
const ctaType = ref<'category' | 'product' | 'store' | 'custom'>('custom');
const ctaUrlValue = ref('');
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const searchLoading = ref(false);
const selectedItem = ref<any | null>(null);
const dropdownOpen = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

function initializeCtaUrl(banner: BannerRow | null) {
    searchResults.value = [];
    searchQuery.value = '';
    selectedItem.value = null;
    dropdownOpen.value = false;

    if (!banner || !banner.cta_url) {
        ctaType.value = 'custom';
        ctaUrlValue.value = '';
        return;
    }

    const url = banner.cta_url;
    ctaUrlValue.value = url;

    if (url.startsWith('/catalog?category=')) {
        ctaType.value = 'category';
        const slug = url.split('category=')[1] || '';
        searchQuery.value = slug;
        selectedItem.value = { name: `Kategori: ${slug}`, slug: slug };
    } else if (url.startsWith('/catalog/')) {
        ctaType.value = 'product';
        const slug = url.split('/catalog/')[1] || '';
        searchQuery.value = slug;
        selectedItem.value = { name: `Produk: ${slug}`, slug: slug };
    } else if (url.startsWith('/stores/')) {
        ctaType.value = 'store';
        const slug = url.split('/stores/')[1] || '';
        searchQuery.value = slug;
        selectedItem.value = { name: `Toko: ${slug}`, slug: slug };
    } else {
        ctaType.value = 'custom';
    }
}

watch(ctaType, () => {
    searchQuery.value = '';
    searchResults.value = [];
    selectedItem.value = null;
    dropdownOpen.value = false;
    if (ctaType.value !== 'custom') {
        ctaUrlValue.value = '';
    }
});

function performSearch() {
    if (ctaType.value === 'custom') return;

    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    if (!searchQuery.value) {
        searchResults.value = [];
        return;
    }

    searchLoading.value = true;
    dropdownOpen.value = true;

    const currentType = ctaType.value;

    searchTimeout = setTimeout(async () => {
        try {
            const endpoint = currentType === 'category' ? 'categories' : `${currentType}s`;
            const response = await fetch(`/admin/banners/search-${endpoint}?q=${encodeURIComponent(searchQuery.value)}`);
            if (response.ok) {
                const data = await response.json();
                if (ctaType.value === currentType) {
                    searchResults.value = data;
                }
            }
        } catch (err) {
            console.error('Error searching:', err);
        } finally {
            if (ctaType.value === currentType) {
                searchLoading.value = false;
            }
        }
    }, 300);
}

async function openDropdown() {
    if (ctaType.value === 'custom') return;
    dropdownOpen.value = true;
    searchLoading.value = true;
    
    const currentType = ctaType.value;

    try {
        const endpoint = currentType === 'category' ? 'categories' : `${currentType}s`;
        const response = await fetch(`/admin/banners/search-${endpoint}?q=${encodeURIComponent(searchQuery.value)}`);
        if (response.ok) {
            const data = await response.json();
            if (ctaType.value === currentType) {
                searchResults.value = data;
            }
        }
    } catch (err) {
        console.error('Error fetching list:', err);
    } finally {
        if (ctaType.value === currentType) {
            searchLoading.value = false;
        }
    }
}

function selectItem(item: any) {
    selectedItem.value = item;
    searchQuery.value = item.name;
    dropdownOpen.value = false;

    if (ctaType.value === 'category') {
        ctaUrlValue.value = `/catalog?category=${item.slug}`;
    } else if (ctaType.value === 'product') {
        ctaUrlValue.value = `/catalog/${item.slug}`;
    } else if (ctaType.value === 'store') {
        ctaUrlValue.value = `/stores/${item.slug}`;
    }
}

function onFocusOut() {
    setTimeout(() => {
        dropdownOpen.value = false;
    }, 200);
}

function openCreate() {
    editing.value = null;
    formPlacement.value = 'main';
    formActive.value = true;
    initializeCtaUrl(null);
    formOpen.value = true;
}

function openEdit(banner: BannerRow) {
    editing.value = banner;
    formPlacement.value = banner.placement;
    formActive.value = banner.is_active;
    initializeCtaUrl(banner);
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
    fd.set('cta_url', ctaUrlValue.value);

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

                <!-- Badge + Sort in 2 cols -->
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
                        <Label for="sort_order">Urutan <span class="text-destructive">*</span></Label>
                        <Input
                            id="sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                            max="1000"
                            v-model="formSortOrder"
                            required
                        />
                        <p class="text-xs text-muted-foreground">
                            Rekomendasi berikutnya: <strong>{{ recommendedSortOrder }}</strong>
                        </p>
                        <InputError :message="errors.sort_order" />
                    </div>
                </div>

                <!-- CTA Tautan Settings Box -->
                <div class="grid gap-4 border border-border/80 rounded-xl p-4 bg-muted/20">
                    <h4 class="text-sm font-semibold text-foreground/90">Konfigurasi Tautan Banner</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Tipe Tujuan Tautan</Label>
                            <Select v-model="ctaType">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih tipe" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="category">Kategori</SelectItem>
                                    <SelectItem value="product">Produk</SelectItem>
                                    <SelectItem value="store">Toko</SelectItem>
                                    <SelectItem value="custom">Link Kustom</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Autocomplete Search for Category/Product/Store -->
                        <div v-if="ctaType !== 'custom'" class="grid gap-2 relative">
                            <Label>Pencarian {{ ctaType === 'category' ? 'Kategori' : ctaType === 'product' ? 'Produk' : 'Toko' }}</Label>
                            <div class="relative">
                                <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    class="pl-9"
                                    :placeholder="`Ketik untuk mencari ${ctaType === 'category' ? 'kategori' : ctaType === 'product' ? 'produk' : 'toko'}...`"
                                    @input="performSearch"
                                    @focus="openDropdown"
                                    @focusout="onFocusOut"
                                />
                                <Loader2 v-if="searchLoading" class="absolute right-2.5 top-2.5 size-4 animate-spin text-muted-foreground" />
                            </div>

                            <!-- Dropdown Options overlay -->
                            <div 
                                v-if="dropdownOpen && (searchResults.length > 0 || searchLoading)" 
                                class="absolute top-[calc(100%+4px)] left-0 right-0 z-50 rounded-md border bg-popover text-popover-foreground shadow-md outline-none max-h-60 overflow-y-auto"
                            >
                                <div v-if="searchLoading && searchResults.length === 0" class="p-3 text-xs text-muted-foreground text-center">
                                    Mencari...
                                </div>
                                <div v-else-if="searchResults.length === 0" class="p-3 text-xs text-muted-foreground text-center">
                                    Tidak ditemukan hasil.
                                </div>
                                <button
                                    v-for="item in searchResults"
                                    :key="item.id"
                                    type="button"
                                    class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 px-3 text-sm outline-none hover:bg-accent hover:text-accent-foreground text-left"
                                    @mousedown="selectItem(item)"
                                >
                                    {{ item.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Custom Link Field -->
                        <div v-else class="grid gap-2">
                            <Label for="custom_url">Link Kustom</Label>
                            <Input
                                id="custom_url"
                                v-model="ctaUrlValue"
                                placeholder="https://example.com/promo"
                                required
                                pattern="https?://.+"
                                title="Tautan wajib diawali dengan http:// atau https://"
                            />
                        </div>
                    </div>

                    <!-- Computed URL Preview -->
                    <div class="text-xs text-muted-foreground">
                        <span class="font-medium text-foreground">Hasil URL Banners:</span> 
                        <code class="ml-1 bg-muted px-1.5 py-0.5 rounded border text-[11px] select-all">{{ ctaUrlValue || '(Kosong/Belum diset)' }}</code>
                    </div>
                    <InputError :message="errors.cta_url" />
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
