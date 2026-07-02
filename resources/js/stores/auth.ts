import { usePage } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage();

    const user = computed(() => (page.props.auth as any)?.user ?? null);
    const roles = computed(() => (page.props.auth as any)?.roles ?? []);
    const activeRole = computed(
        () => (page.props.auth as any)?.activeRole ?? null,
    );
    const cartItemCount = computed(
        () => (page.props.auth as any)?.cartItemCount ?? 0,
    );
    const isAuthenticated = computed(() => user.value !== null);

    return { user, roles, activeRole, cartItemCount, isAuthenticated };
});
