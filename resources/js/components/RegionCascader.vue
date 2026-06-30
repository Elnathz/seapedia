<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

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

const { t } = useI18n();

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
        const res = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        provinces.value = await res.json();
        
        // If editing and province exists, find its ID to fetch cities
        if (props.province) {
            const p = provinces.value.find(x => x.name === props.province);
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
    if (!provinceId) return;
    isLoading.value.cities = true;
    try {
        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`);
        cities.value = await res.json();
        
        if (props.city) {
            const c = cities.value.find(x => x.name === props.city);
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
    if (!cityId) return;
    isLoading.value.districts = true;
    try {
        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${cityId}.json`);
        districts.value = await res.json();
        
        if (props.district) {
            const d = districts.value.find(x => x.name === props.district);
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
    if (!districtId) return;
    isLoading.value.villages = true;
    try {
        const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`);
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

const onProvinceChange = async (val: any) => {
    const name = val as string;
    if (!name) return;
    emit('update:province', name);
    emit('update:city', '');
    emit('update:district', '');
    emit('update:village', '');
    cities.value = [];
    districts.value = [];
    villages.value = [];
    
    const p = provinces.value.find(x => x.name === name);
    if (p) {
        selectedProvinceId.value = p.id;
        await fetchCities(p.id);
    }
};

const onCityChange = async (val: any) => {
    const name = val as string;
    if (!name) return;
    emit('update:city', name);
    emit('update:district', '');
    emit('update:village', '');
    districts.value = [];
    villages.value = [];
    
    const c = cities.value.find(x => x.name === name);
    if (c) {
        selectedCityId.value = c.id;
        await fetchDistricts(c.id);
    }
};

const onDistrictChange = async (val: any) => {
    const name = val as string;
    if (!name) return;
    emit('update:district', name);
    emit('update:village', '');
    villages.value = [];
    
    const d = districts.value.find(x => x.name === name);
    if (d) {
        selectedDistrictId.value = d.id;
        await fetchVillages(d.id);
    }
};

const onVillageChange = (val: any) => {
    const name = val as string;
    if (!name) return;
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
                    <SelectItem v-for="item in provinces" :key="item.id" :value="item.name">
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
                    <SelectItem v-for="item in cities" :key="item.id" :value="item.name">
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
                    <SelectItem v-for="item in districts" :key="item.id" :value="item.name">
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
                    <SelectItem v-for="item in villages" :key="item.id" :value="item.name">
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
