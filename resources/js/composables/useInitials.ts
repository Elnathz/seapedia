export type UseInitialsReturn = {
    getInitials: (fullName?: string) => string;
    getGradientClass: (name: string) => string;
};

function getInitial(name: string): string {
    return Array.from(name)[0] ?? '';
}

export function getInitials(fullName?: string): string {
    if (!fullName) {
        return '';
    }

    const names = fullName.trim().split(/\s+/u).filter(Boolean);

    if (names.length === 0) {
        return '';
    }

    if (names.length === 1) {
        return getInitial(names[0]).toUpperCase();
    }

    return `${getInitial(names[0])}${getInitial(names[names.length - 1])}`.toUpperCase();
}

const gradients = [
    'bg-gradient-to-br from-blue-400 to-blue-600',
    'bg-gradient-to-br from-teal-400 to-teal-600',
    'bg-gradient-to-br from-amber-400 to-amber-600',
    'bg-gradient-to-br from-rose-400 to-rose-600',
    'bg-gradient-to-br from-violet-400 to-violet-600',
    'bg-gradient-to-br from-emerald-400 to-emerald-600',
    'bg-gradient-to-br from-cyan-400 to-cyan-600',
    'bg-gradient-to-br from-orange-400 to-orange-600',
];

export function getGradientClass(name: string): string {
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return gradients[Math.abs(hash) % gradients.length];
}

export function useInitials(): UseInitialsReturn {
    return { getInitials, getGradientClass };
}

