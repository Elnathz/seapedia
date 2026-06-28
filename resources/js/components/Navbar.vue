<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Flame, Search } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Logo from '@/components/brand/Logo.vue';
import LocaleToggle from '@/components/LocaleToggle.vue';
import RoleBadge from '@/components/RoleBadge.vue';
import { Button } from '@/components/ui/button';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
import { useCategories } from '@/composables/useCategories';
import { dashboard, home, login, register } from '@/routes';
import { index as catalogIndex } from '@/routes/catalog';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const { t } = useI18n();
const { categories } = useCategories();

const searchQuery = ref('');

function searchCatalog() {
    const q = searchQuery.value.trim();
    router.get(catalogIndex.url(q ? { query: { q } } : undefined));
}

function catalogUrl(slug?: string) {
    return catalogIndex.url(slug ? { query: { category: slug } } : undefined);
}
</script>

<template>
    <!-- Promo top-bar (desktop only) -->
    <div class="hidden border-b border-primary/20 bg-gradient-to-r from-primary via-brand to-primary py-1.5 text-center text-xs font-medium text-white sm:block">
        <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 px-4">
            <Flame class="size-3.5 text-white/90" />
            <span>Gratis ongkir pesanan pertama</span>
            <span class="text-white/40">·</span>
            <span>Diskon hingga 20%</span>
            <span class="text-white/40">·</span>
            <span>Daftar sekarang</span>
        </div>
    </div>

    <header
        class="sticky top-0 z-40 border-b border-border bg-white/95 shadow-sm backdrop-blur"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:gap-4 lg:px-8"
        >
            <!-- Logo -->
            <Link :href="home()" class="flex shrink-0 items-center">
                <Logo class="h-16 w-auto" />
            </Link>

            <!-- Category mega-dropdown (lg+) -->
            <NavigationMenu
                v-if="categories.length"
                class="hidden shrink-0 lg:flex"
            >
                <NavigationMenuList>
                    <NavigationMenuItem>
                        <NavigationMenuTrigger class="bg-transparent">
                            {{ t('nav.catalog') }}
                        </NavigationMenuTrigger>
                        <NavigationMenuContent>
                            <div
                                class="grid w-[34rem] grid-cols-2 gap-x-6 gap-y-4 p-5"
                            >
                                <div
                                    v-for="root in categories"
                                    :key="root.id"
                                    class="min-w-0"
                                >
                                    <NavigationMenuLink as-child>
                                        <Link
                                            :href="catalogUrl(root.slug)"
                                            class="block truncate text-sm font-semibold text-foreground transition-colors hover:text-primary"
                                        >
                                            {{ root.name }}
                                        </Link>
                                    </NavigationMenuLink>
                                    <ul class="mt-1.5 space-y-1">
                                        <li
                                            v-for="child in root.children"
                                            :key="child.id"
                                        >
                                            <NavigationMenuLink as-child>
                                                <Link
                                                    :href="
                                                        catalogUrl(child.slug)
                                                    "
                                                    class="block truncate text-sm text-muted-foreground transition-colors hover:text-primary"
                                                >
                                                    {{ child.name }}
                                                </Link>
                                            </NavigationMenuLink>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="border-t border-border px-5 py-3">
                                <NavigationMenuLink as-child>
                                    <Link
                                        :href="catalogUrl()"
                                        class="text-sm font-medium text-primary hover:underline"
                                    >
                                        {{ t('nav.allCategories') }}
                                    </Link>
                                </NavigationMenuLink>
                            </div>
                        </NavigationMenuContent>
                    </NavigationMenuItem>
                </NavigationMenuList>
            </NavigationMenu>

            <!-- Search bar (hidden on mobile) -->
            <form
                class="hidden flex-1 md:flex"
                @submit.prevent="searchCatalog"
            >
                <div
                    class="flex w-full max-w-2xl overflow-hidden rounded-xl border border-border bg-muted/60 ring-1 ring-transparent transition-all focus-within:border-primary/40 focus-within:bg-white focus-within:ring-primary/20"
                >
                    <input
                        v-model="searchQuery"
                        type="search"
                        placeholder="Cari produk, toko, atau kategori..."
                        class="w-full bg-transparent px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none"
                        autocomplete="off"
                    />
                    <button
                        type="submit"
                        class="flex shrink-0 items-center gap-1.5 bg-primary px-4 text-sm font-medium text-white transition-colors hover:bg-primary/90"
                    >
                        <Search class="size-4" />
                        <span class="hidden lg:inline">Cari</span>
                    </button>
                </div>
            </form>

            <!-- Authenticated state -->
            <div v-if="auth.isAuthenticated" class="flex items-center gap-2">
                <Link
                    :href="catalogUrl()"
                    class="hidden rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground lg:inline-flex"
                >
                    {{ t('nav.catalog') }}
                </Link>
                <LocaleToggle />
                <RoleBadge />
                <Button as-child size="sm">
                    <Link :href="dashboard()">{{ t('nav.dashboard') }}</Link>
                </Button>
            </div>

            <!-- Guest CTAs -->
            <div v-else class="flex shrink-0 items-center gap-2">
                <Link
                    :href="catalogUrl()"
                    class="hidden rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground lg:inline-flex"
                >
                    {{ t('nav.catalog') }}
                </Link>
                <Button as-child variant="ghost" size="sm">
                    <Link :href="login()">{{ t('nav.login') }}</Link>
                </Button>
                <Button as-child size="sm">
                    <Link :href="register()">{{ t('nav.register') }}</Link>
                </Button>
            </div>
        </div>
    </header>
    <!-- SVG wave divider -->
    <div class="pointer-events-none relative z-30 -mt-px" aria-hidden="true">
        <svg viewBox="0 0 1440 32" fill="none" preserveAspectRatio="none" class="block w-full" style="height: 24px">
            <path d="M0 16 C240 32 480 0 720 16 C960 32 1200 0 1440 16 L1440 32 L0 32 Z" class="fill-primary/[0.07]" />
        </svg>
    </div>
</template>
