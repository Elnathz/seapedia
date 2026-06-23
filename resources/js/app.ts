import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { initializeTheme } from '@/composables/useAppearance';
import en from '@/i18n/en';
import id from '@/i18n/id';
import AuthLayout from '@/layouts/AuthLayout.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/**
 * Read the server-resolved locale straight out of the embedded initial-page
 * JSON (present in the HTML before Vue ever mounts) so the i18n instance
 * boots with the correct language on the very first paint — no SSR/client
 * locale mismatch to hydrate away.
 */
function resolveInitialLocale(): 'id' | 'en' {
    try {
        const json =
            document.querySelector('script[data-page]')?.textContent ?? '{}';
        const page = JSON.parse(json) as { props?: { locale?: string } };

        return page.props?.locale === 'en' ? 'en' : 'id';
    } catch {
        return 'id';
    }
}

const i18n = createI18n({
    legacy: false,
    locale: resolveInitialLocale(),
    fallbackLocale: 'id',
    messages: { id, en },
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return GuestLayout;
            case name.startsWith('role/'):
                return null;
            case name.startsWith('reviews/'):
            case name.startsWith('catalog/'):
            case name.startsWith('stores/'):
                return GuestLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [DashboardLayout, SettingsLayout];
            default:
                return DashboardLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    withApp: (app) => {
        app.use(createPinia());
        app.use(i18n);
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
