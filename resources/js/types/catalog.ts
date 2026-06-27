export interface CategoryNode {
    id: number;
    parent_id: number | null;
    name: string;
    slug: string;
    icon: string | null;
    is_active: boolean;
    sort_order: number;
    products_count: number;
    /** Only present on root categories: own + children product counts. */
    products_total?: number;
    children?: CategoryNode[];
}
