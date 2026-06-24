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
