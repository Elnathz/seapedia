import { createInertiaApp, router } from '@inertiajs/vue3';
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
        if (typeof document === 'undefined') {
            return 'id';
        }

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

/**
 * The server's resolved locale can change between SPA visits independently
 * of the toggle (e.g. a guest browsing in `en` logs into an account whose
 * saved preference is `id` — the user's column always wins server-side).
 * Re-sync the client instance after every Inertia visit so it never drifts
 * from what the server just rendered.
 */
router.on('navigate', (event) => {
    const locale = event.detail.page.props.locale;

    if (locale === 'id' || locale === 'en') {
        i18n.global.locale.value = locale;
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
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

// Browser-only bootstrapping — skipped during Inertia SSR (no DOM in Node).
if (typeof document !== 'undefined') {
    // This will set light / dark mode on page load...
    initializeTheme();
    // Sprint 6: force light-only for demo — dark toggle hidden from UI, infra kept
    document.documentElement.classList.remove('dark');

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}
