export interface BannerNode {
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

export function bannerSrc(path: string): string {
    return path.startsWith('images/') ? `/${path}` : `/storage/${path}`;
}
