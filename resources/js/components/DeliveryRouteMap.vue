<script setup lang="ts">
import 'leaflet/dist/leaflet.css';
import type { Map as LMap, Marker as LMarker, Polyline } from 'leaflet';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        originLat: number;
        originLng: number;
        destLat: number;
        destLng: number;
        originLabel?: string;
        destLabel?: string;
        height?: string;
    }>(),
    { originLabel: 'Toko', destLabel: 'Tujuan', height: '300px' },
);

const mapEl = ref<HTMLElement | null>(null);

// Leaflet reads `window` at import time, so it is loaded lazily inside
// onMounted (client only) to stay SSR-safe under Inertia.

let L: any = null;
let map: LMap | null = null;
let mover: LMarker | null = null;
let line: Polyline | null = null;
let frame = 0;
let resizeObserver: ResizeObserver | null = null;

function dotIcon(color: string, size: number) {
    return L.divIcon({
        className: 'delivery-route-dot',
        html: `<span style="display:block;width:${size}px;height:${size}px;border-radius:9999px;background:${color};box-shadow:0 0 0 4px ${color}33,0 1px 3px rgba(0,0,0,.35)"></span>`,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
    });
}

function pinIcon(color: string) {
    return L.divIcon({
        className: 'delivery-route-pin',
        html: `<svg width="26" height="34" viewBox="0 0 24 32" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.4 0 0 5.4 0 12c0 8.4 12 20 12 20s12-11.6 12-20C24 5.4 18.6 0 12 0z" fill="${color}"/><circle cx="12" cy="12" r="5" fill="#fff"/></svg>`,
        iconSize: [26, 34],
        iconAnchor: [13, 34],
    });
}

function animate(reduced: boolean): void {
    if (reduced || !map || !mover) {
        return;
    }

    const from: [number, number] = [props.originLat, props.originLng];
    const to: [number, number] = [props.destLat, props.destLng];
    const durationMs = 4200;
    let start = 0;

    const step = (now: number) => {
        if (!start) {
            start = now;
        }

        // Ease in-out so the courier accelerates away from the store and
        // slows into the destination, then loops.
        const raw = ((now - start) % durationMs) / durationMs;
        const t = raw < 0.5 ? 2 * raw * raw : 1 - (-2 * raw + 2) ** 2 / 2;

        mover!.setLatLng([
            from[0] + (to[0] - from[0]) * t,
            from[1] + (to[1] - from[1]) * t,
        ]);
        frame = requestAnimationFrame(step);
    };

    frame = requestAnimationFrame(step);
}

onMounted(async () => {
    if (!mapEl.value) {
        return;
    }

    L = (await import('leaflet')).default;

    const origin: [number, number] = [props.originLat, props.originLng];
    const dest: [number, number] = [props.destLat, props.destLng];

    map = L.map(mapEl.value, {
        zoomControl: true,
        scrollWheelZoom: false,
        attributionControl: false,
    });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    L.marker(origin, { icon: pinIcon('#0d9488') })
        .addTo(map!)
        .bindTooltip(props.originLabel, { direction: 'top', offset: [0, -30] });
    L.marker(dest, { icon: pinIcon('#f59e0b') })
        .addTo(map!)
        .bindTooltip(props.destLabel, { direction: 'top', offset: [0, -30] });

    line = L.polyline([origin, dest], {
        color: '#0d9488',
        weight: 3,
        opacity: 0.7,
        dashArray: '6 8',
    }).addTo(map!);

    map!.fitBounds(line!.getBounds(), { padding: [40, 40] });

    const reduced = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    mover = L.marker(origin, {
        icon: dotIcon('#0d9488', reduced ? 12 : 14),
    }).addTo(map!);

    setTimeout(() => map?.invalidateSize(), 250);
    resizeObserver = new ResizeObserver(() => map?.invalidateSize());
    resizeObserver.observe(mapEl.value);

    animate(reduced);
});

onBeforeUnmount(() => {
    if (frame) {
        cancelAnimationFrame(frame);
    }

    resizeObserver?.disconnect();
    resizeObserver = null;
    map?.remove();
    map = null;
    mover = null;
    line = null;
});
</script>

<template>
    <div
        ref="mapEl"
        :style="{ height }"
        class="relative isolate w-full overflow-hidden rounded-xl border border-border"
    ></div>
</template>
