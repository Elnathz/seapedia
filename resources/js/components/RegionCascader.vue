<script setup lang="ts">
import { ref, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    province: string;
    city: string;
    district: string;
    village: string;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update:province', value: string): void;
    (e: 'update:city', value: string): void;
    (e: 'update:district', value: string): void;
    (e: 'update:village', value: string): void;
}>();

interface Region {
    id: string;
    name: string;
}

const provinces = ref<Region[]>([]);
const cities = ref<Region[]>([]);
const districts = ref<Region[]>([]);
const villages = ref<Region[]>([]);

const selectedProvinceId = ref<string>('');
const selectedCityId = ref<string>('');
const selectedDistrictId = ref<string>('');

const isLoading = ref({
    provinces: false,
    cities: false,
    districts: false,
    villages: false,
});

const fetchProvinces = async () => {
    isLoading.value.provinces = true;

    try {
        const res = await fetch(
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json',
        );
        provinces.value = await res.json();

        // If editing and province exists, find its ID to fetch cities
        if (props.province) {
            const p = provinces.value.find((x) => x.name === props.province);

            if (p) {
                selectedProvinceId.value = p.id;
                await fetchCities(p.id);
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value.provinces = false;
    }
};

const fetchCities = async (provinceId: string) => {
    if (!provinceId) {
        return;
    }

    isLoading.value.cities = true;

    try {
        const res = await fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`,
        );
        cities.value = await res.json();

        if (props.city) {
            const c = cities.value.find((x) => x.name === props.city);

            if (c) {
                selectedCityId.value = c.id;
                await fetchDistricts(c.id);
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value.cities = false;
    }
};

const fetchDistricts = async (cityId: string) => {
    if (!cityId) {
        return;
    }

    isLoading.value.districts = true;

    try {
        const res = await fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/districts/${cityId}.json`,
        );
        districts.value = await res.json();

        if (props.district) {
            const d = districts.value.find((x) => x.name === props.district);

            if (d) {
                selectedDistrictId.value = d.id;
                await fetchVillages(d.id);
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value.districts = false;
    }
};

const fetchVillages = async (districtId: string) => {
    if (!districtId) {
        return;
    }

    isLoading.value.villages = true;

    try {
        const res = await fetch(
            `https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`,
        );
        villages.value = await res.json();
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value.villages = false;
    }
};

onMounted(() => {
    fetchProvinces();
});

const API = 'https://www.emsifa.com/api-wilayah-indonesia/api';

async function loadList(path: string): Promise<Region[]> {
    try {
        const res = await fetch(`${API}/${path}`);

        return (await res.json()) as Region[];
    } catch {
        return [];
    }
}

// Region names from reverse-geocoding rarely match the dataset verbatim (case,
// "Kota"/"Kabupaten" prefixes, abbreviations), so match leniently: exact first,
// then normalized-equal, then a contains either way. Returns undefined when
// nothing is close enough — the caller stops and leaves that level manual.
function normalize(value: string): string {
    return value
        .toLowerCase()
        .replace(/^(kabupaten|kota|kab\.?|kec\.?|kel\.?|desa)\s+/i, '')
        .trim();
}

function matchRegion(list: Region[], name?: string): Region | undefined {
    if (!name) {
        return undefined;
    }

    const lower = name.toLowerCase();
    const exact = list.find((x) => x.name.toLowerCase() === lower);

    if (exact) {
        return exact;
    }

    const n = normalize(name);

    return (
        list.find((x) => normalize(x.name) === n) ??
        list.find(
            (x) =>
                normalize(x.name).includes(n) || n.includes(normalize(x.name)),
        )
    );
}

/**
 * Auto-fill the cascade from reverse-geocoded parts (called by the parent when
 * the map pin moves). Resolves top-down, fetching each dependent list and
 * emitting the dataset's canonical name so the dropdowns display and the form
 * stores a value that exists in the dataset. Best-effort: it stops at the first
 * level that has no close match, leaving the rest for manual selection.
 */
async function applyGeo(geo: {
    province?: string;
    city?: string;
    district?: string;
    village?: string;
}): Promise<void> {
    const p = matchRegion(provinces.value, geo.province);

    if (!p) {
        return;
    }

    selectedProvinceId.value = p.id;
    emit('update:province', p.name);
    emit('update:city', '');
    emit('update:district', '');
    emit('update:village', '');
    districts.value = [];
    villages.value = [];

    cities.value = await loadList(`regencies/${p.id}.json`);
    const c = matchRegion(cities.value, geo.city);

    if (!c) {
        return;
    }

    selectedCityId.value = c.id;
    emit('update:city', c.name);

    districts.value = await loadList(`districts/${c.id}.json`);
    const d = matchRegion(districts.value, geo.district);

    if (!d) {
        return;
    }

    selectedDistrictId.value = d.id;
    emit('update:district', d.name);

    villages.value = await loadList(`villages/${d.id}.json`);
    const v = matchRegion(villages.value, geo.village);

    if (v) {
        emit('update:village', v.name);
    }
}

defineExpose({ applyGeo });

const onProvinceChange = async (val: any) => {
    const name = val as string;

    if (!name) {
        return;
    }

    emit('update:province', name);
    emit('update:city', '');
    emit('update:district', '');
    emit('update:village', '');
    cities.value = [];
    districts.value = [];
    villages.value = [];

    const p = provinces.value.find((x) => x.name === name);

    if (p) {
        selectedProvinceId.value = p.id;
        await fetchCities(p.id);
    }
};

const onCityChange = async (val: any) => {
    const name = val as string;

    if (!name) {
        return;
    }

    emit('update:city', name);
    emit('update:district', '');
    emit('update:village', '');
    districts.value = [];
    villages.value = [];

    const c = cities.value.find((x) => x.name === name);

    if (c) {
        selectedCityId.value = c.id;
        await fetchDistricts(c.id);
    }
};

const onDistrictChange = async (val: any) => {
    const name = val as string;

    if (!name) {
        return;
    }

    emit('update:district', name);
    emit('update:village', '');
    villages.value = [];

    const d = districts.value.find((x) => x.name === name);

    if (d) {
        selectedDistrictId.value = d.id;
        await fetchVillages(d.id);
    }
};

const onVillageChange = (val: any) => {
    const name = val as string;

    if (!name) {
        return;
    }

    emit('update:village', name);
};
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label>Provinsi</Label>
            <Select
                :model-value="props.province"
                @update:model-value="onProvinceChange"
                :disabled="isLoading.provinces"
            >
                <SelectTrigger>
                    <SelectValue placeholder="Pilih Provinsi" />
                </SelectTrigger>
                <SelectContent class="max-h-64">
                    <SelectItem
                        v-for="item in provinces"
                        :key="item.id"
                        :value="item.name"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors?.province" />
        </div>

        <div class="grid gap-2">
            <Label>Kota/Kabupaten</Label>
            <Select
                :model-value="props.city"
                @update:model-value="onCityChange"
                :disabled="!props.province || isLoading.cities"
            >
                <SelectTrigger>
                    <SelectValue placeholder="Pilih Kota/Kabupaten" />
                </SelectTrigger>
                <SelectContent class="max-h-64">
                    <SelectItem
                        v-for="item in cities"
                        :key="item.id"
                        :value="item.name"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors?.city" />
        </div>

        <div class="grid gap-2">
            <Label>Kecamatan</Label>
            <Select
                :model-value="props.district"
                @update:model-value="onDistrictChange"
                :disabled="!props.city || isLoading.districts"
            >
                <SelectTrigger>
                    <SelectValue placeholder="Pilih Kecamatan" />
                </SelectTrigger>
                <SelectContent class="max-h-64">
                    <SelectItem
                        v-for="item in districts"
                        :key="item.id"
                        :value="item.name"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors?.district" />
        </div>

        <div class="grid gap-2">
            <Label>Kelurahan/Desa</Label>
            <Select
                :model-value="props.village"
                @update:model-value="onVillageChange"
                :disabled="!props.district || isLoading.villages"
            >
                <SelectTrigger>
                    <SelectValue placeholder="Pilih Kelurahan" />
                </SelectTrigger>
                <SelectContent class="max-h-64">
                    <SelectItem
                        v-for="item in villages"
                        :key="item.id"
                        :value="item.name"
                    >
                        {{ item.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors?.village" />
        </div>

        <input type="hidden" name="province" :value="props.province" />
        <input type="hidden" name="city" :value="props.city" />
        <input type="hidden" name="district" :value="props.district" />
        <input type="hidden" name="village" :value="props.village" />
    </div>
</template>
