import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { CategoryNode } from '@/types/catalog';

/**
 * Read the platform category tree shared on every Inertia response
 * (see HandleInertiaRequests). Roots carry their active children.
 */
export function useCategories() {
    const page = usePage();

    const categories = computed<CategoryNode[]>(
        () => (page.props.categories as CategoryNode[]) ?? [],
    );

    return { categories };
}
