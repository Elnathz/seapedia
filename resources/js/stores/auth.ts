import { usePage } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    const page = usePage();

    const user = computed(() => page.props.auth.user);
    const roles = computed(() => page.props.auth.roles);
    const activeRole = computed(() => page.props.auth.activeRole);
    const cartItemCount = computed(() => page.props.auth.cartItemCount);
    const isAuthenticated = computed(() => user.value !== null);

    return { user, roles, activeRole, cartItemCount, isAuthenticated };
});
