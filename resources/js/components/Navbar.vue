<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Flame, Search } from '@lucide/vue';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Logo from '@/components/brand/Logo.vue';
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

const page = usePage();
const isLandingPage = computed(() => page.component === 'Welcome');

const searchQuery = ref('');

function searchCatalog() {
    const q = searchQuery.value.trim();
    router.get(catalogIndex.url(q ? { query: { q } } : undefined));
}

function catalogUrl(slug?: string) {
    return catalogIndex.url(slug ? { query: { category: slug } } : undefined);
}
import { Menu, X } from '@lucide/vue';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';

const showMobileSearch = ref(false);

function easeInOutCubic(t: number): number {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

function scrollToSection(e: Event, id: string) {
    e.preventDefault();
    const target = document.getElementById(id);
    if (!target) return;

    const navbarOffset = 80;
    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarOffset;
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    
    // 500ms duration matching "Premium" motion personality
    const duration = 500;
    let start: number | null = null;

    function animation(currentTime: number) {
        if (start === null) start = currentTime;
        const timeElapsed = currentTime - start;
        const progress = Math.min(timeElapsed / duration, 1);
        
        window.scrollTo(0, startPosition + distance * easeInOutCubic(progress));

        if (timeElapsed < duration) {
            requestAnimationFrame(animation);
        }
    }

    requestAnimationFrame(animation);
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

    <header class="sticky top-0 z-40 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 px-3 sm:gap-3 sm:px-6 lg:gap-4 lg:px-8">
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Mobile Category Menu (Sheet) -->
                <Sheet v-if="categories.length && !isLandingPage">
                    <SheetTrigger as-child>
                        <Button variant="ghost" size="icon" class="lg:hidden shrink-0">
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-80">
                        <SheetHeader>
                            <SheetTitle class="text-left">Kategori</SheetTitle>
                        </SheetHeader>
                        <div class="mt-6 flex flex-col gap-4">
                            <div v-for="root in categories" :key="root.id">
                                <Link :href="catalogUrl(root.slug)" class="block font-semibold text-foreground hover:text-primary">
                                    {{ root.name }}
                                </Link>
                                <div class="mt-2 flex flex-col gap-2 pl-4 border-l border-border">
                                    <Link v-for="child in root.children" :key="child.id" :href="catalogUrl(child.slug)" class="text-sm text-muted-foreground hover:text-primary">
                                        {{ child.name }}
                                    </Link>
                                </div>
                            </div>
                            <Link :href="catalogUrl()" class="font-medium text-primary hover:underline mt-2">
                                {{ t('nav.allCategories') }}
                            </Link>
                        </div>
                    </SheetContent>
                </Sheet>

                <!-- Logo -->
                <Link :href="isLandingPage ? home() : catalogUrl()" class="flex shrink-0 items-center">
                    <Logo class="h-10 w-auto sm:h-12 lg:h-16" />
                </Link>

                <!-- Landing Page Menu -->
                <div v-if="isLandingPage" class="hidden lg:flex items-center gap-6">
                    <a href="#roles" @click="scrollToSection($event, 'roles')" class="text-sm font-semibold text-muted-foreground hover:text-primary transition-colors">Peran</a>
                    <a href="#stores" @click="scrollToSection($event, 'stores')" class="text-sm font-semibold text-muted-foreground hover:text-primary transition-colors">Toko Populer</a>
                    <a href="#categories" @click="scrollToSection($event, 'categories')" class="text-sm font-semibold text-muted-foreground hover:text-primary transition-colors">Kategori</a>
                    <a href="#featured" @click="scrollToSection($event, 'featured')" class="text-sm font-semibold text-muted-foreground hover:text-primary transition-colors">Produk Unggulan</a>
                    <a href="#reviews" @click="scrollToSection($event, 'reviews')" class="text-sm font-semibold text-muted-foreground hover:text-primary transition-colors">Ulasan</a>
                </div>

                <!-- Category mega-dropdown (lg+) -->
                <NavigationMenu v-else-if="categories.length" class="hidden shrink-0 lg:flex">
                    <NavigationMenuList>
                        <NavigationMenuItem>
                            <NavigationMenuTrigger class="bg-transparent">
                                Kategori
                            </NavigationMenuTrigger>
                            <NavigationMenuContent>
                                <div class="grid w-[34rem] grid-cols-2 gap-x-6 gap-y-4 p-5">
                                    <div v-for="root in categories" :key="root.id" class="min-w-0">
                                        <NavigationMenuLink as-child>
                                            <Link :href="catalogUrl(root.slug)" class="block truncate text-sm font-semibold text-foreground transition-colors hover:text-primary">
                                                {{ root.name }}
                                            </Link>
                                        </NavigationMenuLink>
                                        <ul class="mt-1.5 space-y-1">
                                            <li v-for="child in root.children" :key="child.id">
                                                <NavigationMenuLink as-child>
                                                    <Link :href="catalogUrl(child.slug)" class="block truncate text-sm text-muted-foreground transition-colors hover:text-primary">
                                                        {{ child.name }}
                                                    </Link>
                                                </NavigationMenuLink>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="border-t border-border px-5 py-3">
                                    <NavigationMenuLink as-child>
                                        <Link :href="catalogUrl()" class="text-sm font-medium text-primary hover:underline">
                                            {{ t('nav.allCategories') }}
                                        </Link>
                                    </NavigationMenuLink>
                                </div>
                            </NavigationMenuContent>
                        </NavigationMenuItem>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>

            <!-- Search bar (desktop) -->
            <form v-if="!isLandingPage" class="hidden flex-1 md:flex" @submit.prevent="searchCatalog">
                <div class="flex w-full max-w-2xl overflow-hidden rounded-xl border border-border bg-muted/60 ring-1 ring-transparent transition-all focus-within:border-primary/40 focus-within:bg-white focus-within:ring-primary/20">
                    <input v-model="searchQuery" type="search" placeholder="Cari produk, toko, atau kategori..." class="w-full bg-transparent px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" autocomplete="off" />
                    <button type="submit" class="flex shrink-0 items-center gap-1.5 bg-primary px-4 text-sm font-medium text-white transition-colors hover:bg-primary/90">
                        <Search class="size-4" />
                        <span class="hidden lg:inline">Cari</span>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-2">
                <!-- Mobile search toggle -->
                <Button v-if="!isLandingPage" variant="ghost" size="icon" class="md:hidden" @click="showMobileSearch = !showMobileSearch">
                    <X v-if="showMobileSearch" class="size-5" />
                    <Search v-else class="size-5" />
                </Button>

                <!-- Authenticated state -->
                <template v-if="auth.isAuthenticated">
                    <Button v-if="isLandingPage" as-child variant="default" size="sm" class="hidden sm:inline-flex bg-primary font-semibold text-white shadow-sm transition-transform hover:scale-105 active:scale-95">
                        <Link :href="catalogUrl()">Mulai Belanja 🛍️</Link>
                    </Button>
                    <RoleBadge />
                    <Button as-child size="sm">
                        <Link :href="dashboard()">{{ t('nav.dashboard') }}</Link>
                    </Button>
                </template>

                <!-- Guest CTAs -->
                <template v-else>
                    <Button v-if="isLandingPage" as-child variant="default" size="sm" class="hidden sm:inline-flex bg-primary font-semibold text-white shadow-sm transition-transform hover:scale-105 active:scale-95">
                        <Link :href="catalogUrl()">Jelajahi Katalog 🛍️</Link>
                    </Button>
                    <Button as-child variant="ghost" size="sm" class="hidden sm:inline-flex">
                        <Link :href="login()">{{ t('nav.login') }}</Link>
                    </Button>
                    <Button as-child size="sm">
                        <Link :href="register()">{{ t('nav.register') }}</Link>
                    </Button>
                </template>
            </div>
        </div>

        <!-- Mobile Search Dropdown -->
        <div v-if="showMobileSearch && !isLandingPage" class="border-t border-border p-3 md:hidden">
            <form @submit.prevent="searchCatalog" class="flex w-full overflow-hidden rounded-lg border border-border bg-muted/60">
                <input v-model="searchQuery" type="search" placeholder="Cari produk..." class="w-full bg-transparent px-3 py-2 text-sm text-foreground focus:outline-none" autocomplete="off" />
                <button type="submit" class="bg-primary px-3 text-white">
                    <Search class="size-4" />
                </button>
            </form>
        </div>

        <!-- SVG wave divider integrated as bottom edge -->
        <div class="pointer-events-none relative z-30" aria-hidden="true" style="margin-bottom: -24px;">
            <svg viewBox="0 0 1440 32" fill="none" preserveAspectRatio="none" class="block w-full" style="height: 24px">
                <path d="M0 16 C240 32 480 0 720 16 C960 32 1200 0 1440 16 L1440 32 L0 32 Z" class="fill-primary/[0.07]" />
            </svg>
        </div>
    </header>
</template>
