export type DeliveryStatusKey =
    | 'available'
    | 'taken'
    | 'completed'
    | 'cancelled';

const LABELS: Record<DeliveryStatusKey, string> = {
    available: 'Menunggu Kurir',
    taken: 'Sedang Diantar',
    completed: 'Selesai Diantar',
    cancelled: 'Dibatalkan',
};

/**
 * Delivery status labels stay Indonesian in both locales, same rationale
 * as orderStatusLabel — they're the system's own status vocabulary.
 */
export function deliveryStatusLabel(status: DeliveryStatusKey): string {
    return LABELS[status] ?? status;
}

const BADGE_VARIANTS: Record<
    DeliveryStatusKey,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    available: 'outline',
    taken: 'secondary',
    completed: 'default',
    cancelled: 'destructive',
};

export function deliveryStatusBadgeVariant(status: DeliveryStatusKey) {
    return BADGE_VARIANTS[status] ?? 'outline';
}

const FILL_CLASSES: Record<DeliveryStatusKey, string> = {
    available: 'bg-amber-500',
    taken: 'bg-sky-500',
    completed: 'bg-emerald-500',
    cancelled: 'bg-destructive',
};

/** Tailwind bg-* class for proportion tracks (StatBar). */
export function deliveryStatusFill(status: DeliveryStatusKey): string {
    return FILL_CLASSES[status] ?? 'bg-primary';
}
