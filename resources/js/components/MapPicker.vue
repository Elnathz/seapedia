<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import { LocateFixed, Search } from '@lucide/vue';
import type {
    DivIcon,
    LeafletMouseEvent,
    Map as LMap,
    Marker as LMarker,
} from 'leaflet';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = withDefaults(
    defineProps<{
        latitude: number | null;
        longitude: number | null;
        height?: string;
    }>(),
    { height: '260px' },
);

const emit = defineEmits<{
    'update:latitude': [value: number];
    'update:longitude': [value: number];
}>();

// Fallback centre (Tugu Muda, Semarang) until the user drops a pin.
const DEFAULT = { lat: -7.0051, lng: 110.4093 };

const mapEl = ref<HTMLElement | null>(null);
const search = ref('');
const searching = ref(false);
const searchError = ref('');

// Leaflet touches `window` at import time, so it is loaded lazily inside
// onMounted (client only) — a top-level import would crash Inertia SSR.
 
let L: any = null;
let map: LMap | null = null;
let marker: LMarker | null = null;
let pinIcon: DivIcon | null = null;
let resizeObserver: ResizeObserver | null = null;

function round7(value: number): number {
    return Math.round(value * 1e7) / 1e7;
}

function placeMarker(lat: number, lng: number, pan = true): void {
    emit('update:latitude', round7(lat));
    emit('update:longitude', round7(lng));

    if (!map || !L || !pinIcon) {
        return;
    }

    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], {
            icon: pinIcon,
            draggable: true,
        }).addTo(map);
        marker!.on('dragend', () => {
            const p = marker!.getLatLng();
            placeMarker(p.lat, p.lng, false);
        });
    }

    if (pan) {
        map.setView([lat, lng], Math.max(map.getZoom(), 15));
    }
}

async function geocode(): Promise<void> {
    const query = search.value.trim();

    if (!query) {
        return;
    }

    searching.value = true;
    searchError.value = '';

    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=id&q=${encodeURIComponent(query)}`,
            { headers: { 'Accept-Language': 'id' } },
        );
        const data = await res.json();

        if (data[0]) {
            placeMarker(
                parseFloat(data[0].lat),
                parseFloat(data[0].lon),
                true,
            );
        } else {
            searchError.value = 'Lokasi tidak ditemukan. Coba kata kunci lain.';
        }
    } catch {
        searchError.value = 'Gagal mencari lokasi. Cek koneksi internet.';
    } finally {
        searching.value = false;
    }
}

function useMyLocation(): void {
    if (!navigator.geolocation) {
        searchError.value = 'Perangkat tidak mendukung lokasi.';

        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => placeMarker(pos.coords.latitude, pos.coords.longitude, true),
        () => (searchError.value = 'Izin lokasi ditolak.'),
    );
}

onMounted(async () => {
    if (!mapEl.value) {
        return;
    }

    L = (await import('leaflet')).default;

    // A teal SVG pin as a divIcon avoids Leaflet's bundler-broken default marker.
    pinIcon = L.divIcon({
        className: 'map-picker-pin',
        html: '<svg width="30" height="40" viewBox="0 0 24 32" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.4 0 0 5.4 0 12c0 8.4 12 20 12 20s12-11.6 12-20C24 5.4 18.6 0 12 0z" fill="#0d9488"/><circle cx="12" cy="12" r="5" fill="#fff"/></svg>',
        iconSize: [30, 40],
        iconAnchor: [15, 40],
    });

    const startLat = props.latitude ?? DEFAULT.lat;
    const startLng = props.longitude ?? DEFAULT.lng;

    map = L.map(mapEl.value).setView(
        [startLat, startLng],
        props.latitude ? 15 : 12,
    );
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19,
    }).addTo(map);

    map!.on('click', (e: LeafletMouseEvent) =>
        placeMarker(e.latlng.lat, e.latlng.lng, false),
    );

    if (props.latitude !== null && props.longitude !== null) {
        placeMarker(props.latitude, props.longitude, false);
    }

    // The map often mounts inside a dialog that is still animating, so its
    // container starts at 0 height; recompute once it settles and on resize.
    setTimeout(() => map?.invalidateSize(), 250);
    resizeObserver = new ResizeObserver(() => map?.invalidateSize());
    resizeObserver.observe(mapEl.value);
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    resizeObserver = null;
    map?.remove();
    map = null;
    marker = null;
});

// External reset (dialog reopened for another address) re-centres the pin.
watch(
    () => [props.latitude, props.longitude] as const,
    ([lat, lng]) => {
        if (lat === null || lng === null || !map) {
            return;
        }

        const current = marker?.getLatLng();

        if (!current || current.lat !== lat || current.lng !== lng) {
            placeMarker(lat, lng, true);
        }
    },
);
</script>

<template>
    <div class="space-y-2">
        <div class="flex gap-2">
            <Input
                v-model="search"
                placeholder="Cari alamat/tempat lalu Enter"
                class="h-9"
                @keyup.enter.prevent="geocode"
            />
            <Button
                type="button"
                variant="secondary"
                size="sm"
                :disabled="searching"
                @click="geocode"
            >
                <Search class="size-4" />
            </Button>
            <Button
                type="button"
                variant="outline"
                size="sm"
                title="Gunakan lokasi saya"
                @click="useMyLocation"
            >
                <LocateFixed class="size-4" />
            </Button>
        </div>

        <!-- isolate: contains Leaflet's high z-index panes/controls so they
             never paint above the app's dialogs and mobile sidebar sheet. -->
        <div
            ref="mapEl"
            :style="{ height }"
            class="relative isolate w-full overflow-hidden rounded-xl border border-border"
        ></div>

        <p v-if="searchError" class="text-xs text-destructive">
            {{ searchError }}
        </p>
        <p v-else class="text-xs text-muted-foreground">
            Klik peta atau geser pin untuk menandai lokasi.
            <span v-if="latitude && longitude" class="tabular-nums">
                ({{ latitude.toFixed(5) }}, {{ longitude.toFixed(5) }})
            </span>
        </p>
    </div>
</template>
