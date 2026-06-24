export type DiscountStatusKey = 'active' | 'inactive' | 'expired' | 'used_up';

const BADGE_VARIANTS: Record<
    DiscountStatusKey,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    active: 'default',
    inactive: 'outline',
    expired: 'destructive',
    used_up: 'secondary',
};

export function discountStatusBadgeVariant(status: DiscountStatusKey) {
    return BADGE_VARIANTS[status] ?? 'outline';
}

const ACCENT_CLASSES: Record<DiscountStatusKey, string> = {
    active: 'bg-emerald-500',
    inactive: 'bg-muted-foreground/40',
    expired: 'bg-destructive',
    used_up: 'bg-amber-500',
};

/** Tailwind bg-* class for the left status stripe on discount rows. */
export function discountStatusAccent(status: DiscountStatusKey): string {
    return ACCENT_CLASSES[status] ?? 'bg-muted-foreground/40';
}
