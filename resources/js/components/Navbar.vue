<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Flame, Search, Zap, Tag, ShoppingCart, X, Menu } from '@lucide/vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCategories } from '@/composables/useCategories';
import { dashboard, home, login, register } from '@/routes';
import { index as catalogIndex } from '@/routes/catalog';
import { index as cartIndex } from '@/routes/buyer/cart';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const { t } = useI18n();
const { categories } = useCategories();

const page = usePage();
const isLandingPage = computed(() => page.component === 'Welcome');
const user = computed(() => page.props.auth.user as import('@/types').User);

// Promo bar — rotating messages
const promoMessages = [
    { icon: Flame, text: 'Gratis ongkir pesanan pertama kamu' },
    { icon: Tag,   text: 'Diskon hingga 20% dengan kode promo' },
    { icon: Zap,   text: 'Daftar sekarang dan langsung bisa belanja' },
];
const promoIndex = ref(0);
let promoTimer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    promoTimer = setInterval(() => {
        promoIndex.value = (promoIndex.value + 1) % promoMessages.length;
    }, 3500);
});

onUnmounted(() => {
    if (promoTimer) clearInterval(promoTimer);
});

const searchQuery = ref('');

function searchCatalog() {
    const q = searchQuery.value.trim();
    router.get(catalogIndex.url(q ? { query: { q } } : undefined));
}

function catalogUrl(slug?: string) {
    return catalogIndex.url(slug ? { query: { category: slug } } : undefined);
}


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
    <!-- Animated Promo top-bar (desktop only) -->
    <div class="promo-bar hidden border-b border-primary/20 bg-gradient-to-r from-primary via-brand to-primary py-1.5 text-center text-xs font-medium text-white sm:block overflow-hidden">
        <div class="relative mx-auto flex max-w-7xl items-center justify-center gap-2 px-4">
            <TransitionGroup
                enter-active-class="transition-all duration-500 ease-out"
                enter-from-class="opacity-0 translate-y-3"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-400 ease-in absolute"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-3"
            >
                <div :key="promoIndex" class="flex items-center justify-center gap-2">
                    <component :is="promoMessages[promoIndex].icon" class="promo-icon size-3.5 text-white/90" />
                    <span>{{ promoMessages[promoIndex].text }}</span>
                </div>
            </TransitionGroup>
        </div>
    </div>

    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur overflow-visible border-b border-border/40">
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
                        <Link :href="catalogUrl()">Mulai Belanja</Link>
                    </Button>
                    <RoleBadge />
                    
                    <Button v-if="auth.activeRole === 'buyer'" as-child variant="ghost" size="icon" class="relative hover:bg-primary/10 hover:text-primary transition-colors text-muted-foreground mr-1">
                        <Link :href="cartIndex.url()">
                            <ShoppingCart class="size-[22px]" />
                            <span v-if="auth.cartItemCount > 0" class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[9px] font-bold text-white shadow-sm ring-2 ring-white">
                                {{ auth.cartItemCount > 10 ? '10+' : auth.cartItemCount }}
                            </span>
                        </Link>
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="gap-2 px-2 py-1.5 focus-visible:ring-0">
                                <UserInfo :user="user" class="hidden md:flex" />
                                <Avatar v-if="user" class="h-8 w-8 md:hidden overflow-hidden rounded-lg">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="rounded-lg bg-primary text-primary-foreground text-xs font-semibold">
                                        {{ user.name.substring(0, 2).toUpperCase() }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56 rounded-lg" :side-offset="8">
                            <UserMenuContent :user="user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </template>

                <!-- Guest CTAs -->
                <template v-else>
                    <Button v-if="isLandingPage" as-child variant="default" size="sm" class="hidden sm:inline-flex bg-primary font-semibold text-white shadow-sm transition-transform hover:scale-105 active:scale-95">
                        <Link :href="catalogUrl()">Jelajahi Katalog</Link>
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

        <!-- SVG Wave — flat top attaches to navbar, wavy bottom hangs down -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-full overflow-hidden" aria-hidden="true" style="height: 32px;">
            <svg
                viewBox="0 0 1440 48"
                fill="none"
                preserveAspectRatio="none"
                class="navbar-wave absolute inset-0 h-full w-[120%] -left-[10%] drop-shadow-sm"
            >
                <path
                    d="M0 0 L0 32 C240 48 480 8 720 32 C960 48 1200 8 1440 32 L1440 0 Z"
                    class="fill-primary/10"
                />
                <path
                    d="M0 0 L0 24 C180 48 360 0 540 24 C720 48 900 0 1080 24 C1260 48 1440 0 1440 24 L1440 0 Z"
                    class="fill-primary/20"
                />
            </svg>
        </div>
    </header>

</template>

<style scoped>
/* Premium motion: 350-500ms, cubic-bezier(0.4,0,0.2,1) */
@keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

@keyframes flame-pulse {
    0%, 100% {
        opacity: 1;
        filter: drop-shadow(0 0 2px rgba(255, 200, 100, 0.6));
    }
    50% {
        opacity: 0.85;
        filter: drop-shadow(0 0 6px rgba(255, 150, 50, 0.9));
    }
}

@keyframes wave-sway {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(-40px); }
}

@media (prefers-reduced-motion: no-preference) {
    .promo-bar {
        background-size: 200% 100%;
        animation: gradient-shift 8s ease-in-out infinite;
    }

    .promo-icon {
        animation: flame-pulse 2s ease-in-out infinite;
    }

    .navbar-wave {
        animation: wave-sway 10s ease-in-out infinite;
        will-change: transform;
    }
}
</style>
