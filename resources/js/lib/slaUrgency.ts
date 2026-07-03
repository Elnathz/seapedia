/**
 * Presentation-only helpers for the SLA "near-cancel" signal. The rule that
 * decides which bucket an order falls in lives on the server (App\Enums\
 * SlaUrgency, golden rule 17); this file only maps the resulting string to a
 * label and colour so the seller and driver views render it identically.
 */
export type SlaUrgencyKey = 'overdue' | 'critical' | 'normal';

/**
 * The countdown label. `ticks` is whole simulated day-ticks remaining
 * (negative once overdue); the copy stays Indonesian to match the app's
 * status vocabulary.
 */
export function slaUrgencyLabel(urgency: SlaUrgencyKey, ticks: number): string {
    if (urgency === 'overdue') {
        return 'Lewat tenggat';
    }

    if (urgency === 'critical') {
        return 'Segera berakhir';
    }

    if (ticks <= 1) {
        return 'Berakhir dalam 1 hari';
    }

    return `Berakhir dalam ${ticks} hari`;
}

/** Whether this order is at-risk enough to warrant a visible flag. */
export function isAtRisk(urgency: SlaUrgencyKey): boolean {
    return urgency === 'overdue' || urgency === 'critical';
}

interface UrgencyTone {
    /** Chip/badge classes. */
    badge: string;
    /** Left accent-rail class for cards. */
    rail: string;
}

const TONES: Record<SlaUrgencyKey, UrgencyTone> = {
    overdue: {
        badge: 'border-destructive/30 bg-destructive/10 text-destructive',
        rail: 'bg-destructive',
    },
    critical: {
        badge: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
        rail: 'bg-amber-500',
    },
    normal: {
        badge: 'border-border bg-muted text-muted-foreground',
        rail: 'bg-transparent',
    },
};

export function slaUrgencyTone(urgency: SlaUrgencyKey): UrgencyTone {
    return TONES[urgency] ?? TONES.normal;
}
