<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Flame, Search, Zap, Tag, ShoppingCart, X, Menu, ChevronRight, ChevronLeft } from '@lucide/vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Logo from '@/components/brand/Logo.vue';
import {
    Avatar,
    AvatarFallback,
    AvatarImage,
} from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
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
import { index as cartIndex } from '@/routes/buyer/cart';
import { index as catalogIndex } from '@/routes/catalog';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const { t } = useI18n();
const { categories } = useCategories();
const showAllCategoriesModal = ref(false);
// Controlled so tapping "Semua Kategori" can close the mobile category
// Sheet before the all-categories Dialog opens — otherwise the Sheet
// overlay stacks on top of the Dialog and covers it (mobile z-index bug).
const mobileCategoriesOpen = ref(false);

function openAllCategories() {
    mobileCategoriesOpen.value = false;
    activeMobileParent.value = null;
    showAllCategoriesModal.value = true;
}

const page = usePage();
const isLandingPage = computed(() => page.component === 'Welcome');
const user = computed(() => page.props.auth.user as import('@/types').User);

// Promo bar — rotating messages
const promoMessages = [
    { icon: Zap,   text: 'Satu akun. Belanja, jualan, dan antar tanpa registrasi ulang.' },
    { icon: Flame, text: '🎉 Daftar sekarang dan nikmati pengalaman marketplace multi-role.' },
];
const promoIndex = ref(0);
let promoTimer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    promoTimer = setInterval(() => {
        promoIndex.value = (promoIndex.value + 1) % promoMessages.length;
    }, 3500);
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    if (promoTimer) {
        clearInterval(promoTimer);
    }
    window.removeEventListener('scroll', handleScroll);
});

const isScrolled = ref(false);
const activeSection = ref('hero');

function handleScroll() {
    isScrolled.value = window.scrollY > 20;
    
    if (isLandingPage.value) {
        const sections = ['hero', 'about', 'stores', 'reviews'];
        const scrollPosition = window.scrollY + 120; // Offset for navbar height
        
        for (const section of [...sections].reverse()) {
            const el = document.getElementById(section);
            if (el && el.offsetTop <= scrollPosition) {
                activeSection.value = section;
                break;
            }
        }
    }
}

const searchQuery = ref('');

function searchCatalog() {
    const q = searchQuery.value.trim();
    router.get(catalogIndex.url(q ? { query: { q } } : undefined));
}

function catalogUrl(slug?: string) {
    return catalogIndex.url(slug ? { query: { category: slug } } : undefined);
}


const showMobileSearch = ref(false);
const activeMobileParent = ref<any>(null);
function easeInOutCubic(t: number): number {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

function scrollToSection(e: Event, id: string) {
    e.preventDefault();
    const target = document.getElementById(id);

    if (!target) {
return;
}

    const navbarOffset = 80;
    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarOffset;
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    
    // 500ms duration matching "Premium" motion personality
    const duration = 500;
    let start: number | null = null;

    function animation(currentTime: number) {
        if (start === null) {
start = currentTime;
}

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
    <div class="promo-bar hidden border-b border-[#21C8B9]/20 bg-[#21C8B9] py-1.5 text-center text-xs font-medium text-white sm:block overflow-hidden">
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

    <header :class="['sticky top-0 z-40 overflow-visible transition-all duration-300', isScrolled ? 'bg-white/90 backdrop-blur-xl border-b border-border/30 shadow-[0_2px_10px_rgba(0,0,0,0.03)]' : 'bg-white border-b border-transparent']">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 lg:gap-10">
                <!-- Mobile Landing Menu (Sheet) -->
                <Sheet v-if="isLandingPage">
                    <SheetTrigger as-child>
                        <Button variant="ghost" size="icon" class="lg:hidden shrink-0">
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-72 p-0 flex flex-col">
                        <div class="px-5 py-5 border-b border-border bg-muted/10">
                            <span class="font-bold text-lg text-foreground">Menu Utama</span>
                        </div>
                        <div class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-1">
                            <a href="#hero" @click="scrollToSection($event, 'hero')" class="block w-full rounded-md px-3 py-2.5 text-base font-medium text-foreground hover:bg-muted transition-colors">Home</a>
                            <a href="#about" @click="scrollToSection($event, 'about')" class="block w-full rounded-md px-3 py-2.5 text-base font-medium text-foreground hover:bg-muted transition-colors">Cara Kerja</a>
                            <a href="#stores" @click="scrollToSection($event, 'stores')" class="block w-full rounded-md px-3 py-2.5 text-base font-medium text-foreground hover:bg-muted transition-colors">Marketplace</a>
                            <a href="#reviews" @click="scrollToSection($event, 'reviews')" class="block w-full rounded-md px-3 py-2.5 text-base font-medium text-foreground hover:bg-muted transition-colors">Review</a>
                        </div>
                        <div v-if="!auth.isAuthenticated" class="border-t border-border p-5 flex flex-col gap-3 bg-muted/10">
                            <Button as-child variant="outline" class="w-full">
                                <Link :href="login()">Login</Link>
                            </Button>
                            <Button as-child class="w-full bg-primary font-bold text-white shadow-md hover:scale-105 transition-transform">
                                <Link :href="register()">Daftar Gratis</Link>
                            </Button>
                        </div>
                    </SheetContent>
                </Sheet>

                <!-- Mobile Category Menu (Sheet) -->
                <Sheet v-if="categories.length && !isLandingPage" v-model:open="mobileCategoriesOpen">
                    <SheetTrigger as-child>
                        <Button variant="ghost" size="icon" class="lg:hidden shrink-0">
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-80 p-0 flex flex-col gap-0">
                        <template v-if="!activeMobileParent">
                            <div class="px-5 py-4 border-b border-border font-bold text-lg">
                                Kategori
                            </div>
                            <div class="flex-1 overflow-y-auto">
                                <button
                                    v-for="root in categories.slice(0, 6)"
                                    :key="root.id"
                                    class="w-full flex items-center justify-between px-5 py-3.5 border-b border-border hover:bg-muted/50 transition-colors text-left text-sm font-medium"
                                    @click="activeMobileParent = root"
                                >
                                    <span>{{ root.name }}</span>
                                    <ChevronRight class="size-4 text-muted-foreground" />
                                </button>
                                <button
                                    @click="openAllCategories"
                                    class="w-full flex items-center px-5 py-3.5 border-b border-border hover:bg-muted/50 transition-colors text-left text-sm font-medium text-primary"
                                >
                                    {{ t('nav.allCategories') }}
                                </button>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex items-center gap-2 px-2 py-2 border-b border-border font-bold text-base">
                                <Button variant="ghost" size="icon" @click="activeMobileParent = null" class="shrink-0 size-10">
                                    <ChevronLeft class="size-5" />
                                </Button>
                                <span class="truncate">{{ activeMobileParent.name }}</span>
                            </div>
                            <div class="flex-1 overflow-y-auto">
                                <Link
                                    :href="catalogUrl(activeMobileParent.slug)"
                                    class="w-full flex items-center px-5 py-3.5 border-b border-border hover:bg-muted/50 transition-colors text-left text-sm"
                                >
                                    Semua Produk di {{ activeMobileParent.name }}
                                </Link>
                                <Link
                                    v-for="child in activeMobileParent.children"
                                    :key="child.id"
                                    :href="catalogUrl(child.slug)"
                                    class="w-full flex items-center justify-between px-5 py-3.5 border-b border-border hover:bg-muted/50 transition-colors text-left text-sm"
                                >
                                    <span>{{ child.name }}</span>
                                    <ChevronRight class="size-4 text-muted-foreground" />
                                </Link>
                            </div>
                        </template>
                    </SheetContent>
                </Sheet>

                <!-- Logo -->
                <a v-if="isLandingPage" href="#hero" @click="scrollToSection($event, 'hero')" class="flex shrink-0 items-center cursor-pointer">
                    <Logo class="hidden md:block h-12 w-auto sm:h-10 lg:h-[70px]" />
                    <img src="/seapedia-logo.svg" alt="Seapedia" class="block md:hidden h-7 w-auto ml-1" />
                </a>
                <Link v-else :href="catalogUrl()" class="flex shrink-0 items-center">
                    <Logo class="hidden md:block h-12 w-auto sm:h-10 lg:h-[60px]" />
                    <img src="/favicon.svg" alt="Seapedia" class="block md:hidden h-8 w-auto ml-1" />
                </Link>

                <!-- Landing Page Menu -->
                <div v-if="isLandingPage" class="hidden lg:flex items-center gap-7">
                    <a href="#hero" @click="scrollToSection($event, 'hero')" :class="['nav-link relative text-base font-semibold transition-colors hover:text-primary', activeSection === 'hero' ? 'text-primary' : 'text-muted-foreground']">Home</a>
                    <a href="#about" @click="scrollToSection($event, 'about')" :class="['nav-link relative text-base font-semibold transition-colors hover:text-primary', activeSection === 'about' ? 'text-primary' : 'text-muted-foreground']">Cara Kerja</a>
                    <a href="#stores" @click="scrollToSection($event, 'stores')" :class="['nav-link relative text-base font-semibold transition-colors hover:text-primary', activeSection === 'stores' ? 'text-primary' : 'text-muted-foreground']">Marketplace</a>
                    <a href="#reviews" @click="scrollToSection($event, 'reviews')" :class="['nav-link relative text-base font-semibold transition-colors hover:text-primary', activeSection === 'reviews' ? 'text-primary' : 'text-muted-foreground']">Review</a>
                </div>

                <!-- Category mega-dropdown (lg+) -->
                <NavigationMenu v-else-if="categories.length" class="hidden shrink-0 lg:flex">
                    <NavigationMenuList>
                        <NavigationMenuItem>
                            <NavigationMenuTrigger class="bg-primary/10 text-primary hover:bg-primary/20 hover:text-primary font-semibold data-[state=open]:bg-primary/20 data-[state=open]:text-primary">
                                Kategori
                            </NavigationMenuTrigger>
                            <NavigationMenuContent>
                                <div class="grid w-136 grid-cols-2 gap-x-6 gap-y-4 p-5">
                                    <div v-for="root in categories.slice(0, 6)" :key="root.id" class="min-w-0">
                                        <NavigationMenuLink as-child>
                                            <Link :href="catalogUrl(root.slug)" class="block truncate text-sm font-semibold text-foreground transition-colors hover:text-primary">
                                                {{ root.name }}
                                            </Link>
                                        </NavigationMenuLink>
                                        <ul class="mt-1.5 space-y-1">
                                            <li v-for="child in root.children?.slice(0, 4)" :key="child.id">
                                                <NavigationMenuLink as-child>
                                                    <Link :href="catalogUrl(child.slug)" class="block truncate text-sm text-muted-foreground transition-colors hover:text-primary">
                                                        {{ child.name }}
                                                    </Link>
                                                </NavigationMenuLink>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="border-t border-border bg-muted/20 px-5 py-3">
                                    <button type="button" @click.prevent="showAllCategoriesModal = true" class="text-sm font-medium text-primary hover:underline w-full text-left">
                                        Tampilkan Semua Kategori &rarr;
                                    </button>
                                </div>
                            </NavigationMenuContent>
                        </NavigationMenuItem>
                    </NavigationMenuList>
                </NavigationMenu>
            </div>

            <!-- Search bar (Desktop) -->
            <form v-if="!isLandingPage" class="hidden md:flex flex-1 mx-4" @submit.prevent="searchCatalog">
                <div class="flex w-full max-w-2xl overflow-hidden rounded-xl border border-border bg-muted/60 ring-1 ring-transparent transition-all focus-within:border-primary/40 focus-within:bg-white focus-within:ring-primary/20">
                    <input v-model="searchQuery" type="search" placeholder="Cari produk..." class="w-full bg-transparent px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" autocomplete="off" />
                    <button type="submit" class="flex shrink-0 items-center justify-center gap-1.5 bg-primary px-4 text-sm font-medium text-white transition-colors hover:bg-primary/90">
                        <Search class="size-4" />
                        <span>Cari</span>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-1 md:gap-2">
                <!-- Mobile Search Toggle -->
                <Button v-if="!isLandingPage" variant="ghost" size="icon" class="md:hidden text-muted-foreground" @click="showMobileSearch = !showMobileSearch">
                    <Search class="size-5" />
                </Button>
                
                <!-- Authenticated state -->
                <template v-if="auth.isAuthenticated">
                    <Button v-if="isLandingPage" as-child variant="default" class="hidden sm:inline-flex bg-primary font-semibold text-white shadow-sm transition-transform hover:scale-105 active:scale-95">
                        <Link :href="catalogUrl()">Mulai Belanja</Link>
                    </Button>
                    <Button v-if="auth.activeRole === 'buyer'" as-child variant="ghost" size="icon" class="relative inline-flex hover:bg-primary/10 hover:text-primary transition-colors text-muted-foreground mr-1">
                        <Link :href="cartIndex.url()">
                            <ShoppingCart class="size-[22px]" />
                            <span v-if="auth.cartItemCount > 0" class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[9px] font-bold text-white shadow-sm ring-2 ring-white">
                                {{ auth.cartItemCount > 10 ? '10+' : auth.cartItemCount }}
                            </span>
                        </Link>
                    </Button>

                    <DropdownMenu :modal="false">
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="gap-2 px-1 md:px-2 py-1.5 focus-visible:ring-0">
                                <div class="hidden md:flex items-center gap-2 text-left">
                                    <div class="grid flex-1 text-sm leading-tight">
                                        <span class="truncate font-medium">{{ user.name }}</span>
                                    </div>
                                </div>
                                <Avatar v-if="user" class="h-8 w-8 overflow-hidden rounded-full border border-border/50">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="bg-primary text-primary-foreground text-xs font-semibold">
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
                    <div class="flex items-center gap-2">
                        <Button as-child variant="ghost" class="hidden sm:inline-flex text-muted-foreground hover:text-primary hover:bg-transparent">
                            <Link :href="login()">Login</Link>
                        </Button>
                        <Button as-child variant="default" class="bg-primary font-bold text-white shadow-md transition-all duration-300 hover:scale-105 hover:shadow-lg active:scale-95 text-xs px-3 h-8 md:text-sm md:h-10 md:px-4">
                            <Link :href="register()">Daftar</Link>
                        </Button>
                    </div>
                </template>
            </div>
        </div>

        <!-- SVG Wave — flat top attaches to navbar, wavy bottom hangs down -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-full overflow-hidden" aria-hidden="true" style="height: 32px;">
            <svg
                viewBox="0 0 1440 48"
                fill="none"
                preserveAspectRatio="none"
                class="navbar-wave absolute inset-0 h-full w-[120%] left-[-10%] drop-shadow-sm"
            >
                <path
                    d="M0 0 L0 32 C240 48 480 8 720 32 C960 48 1200 8 1440 32 L1440 0 Z"
                    class="fill-[#21C8B9]/10"
                />
                <path
                    d="M0 0 L0 24 C180 48 360 0 540 24 C720 48 900 0 1080 24 C1260 48 1440 0 1440 24 L1440 0 Z"
                    class="fill-[#21C8B9]/20"
                />
            </svg>
        </div>

        <!-- Mobile Search Dropdown -->
        <div v-show="showMobileSearch && !isLandingPage" class="md:hidden border-t border-border/50 bg-white px-4 py-3 shadow-inner">
            <form @submit.prevent="searchCatalog" class="flex w-full overflow-hidden rounded-xl border border-border bg-muted/60 ring-1 ring-transparent focus-within:border-primary/40 focus-within:bg-white focus-within:ring-primary/20">
                <input v-model="searchQuery" type="search" placeholder="Cari produk..." class="w-full bg-transparent px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none" autocomplete="off" />
                <button type="submit" class="flex shrink-0 items-center justify-center bg-primary px-3 text-white transition-colors hover:bg-primary/90">
                    <Search class="size-4" />
                </button>
            </form>
        </div>
    </header>

    <Dialog :open="showAllCategoriesModal" @update:open="showAllCategoriesModal = $event">
        <DialogContent class="max-w-4xl max-h-[85vh] overflow-y-auto p-6 sm:p-10" aria-describedby="dialog-description">
            <DialogHeader>
                <DialogTitle class="text-2xl font-bold mb-4">Semua Kategori</DialogTitle>
                <DialogDescription id="dialog-description" class="sr-only">
                    Daftar semua kategori yang tersedia di Seapedia
                </DialogDescription>
            </DialogHeader>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-10 mt-2">
                <div v-for="root in categories" :key="root.id" class="flex flex-col gap-2">
                    <Link :href="catalogUrl(root.slug)" @click="showAllCategoriesModal = false" class="text-lg font-bold text-foreground hover:text-primary transition-colors">
                        {{ root.name }}
                    </Link>
                    <div class="flex flex-col gap-1.5 mt-2">
                        <Link
                            v-for="child in root.children"
                            :key="child.id"
                            :href="catalogUrl(child.slug)"
                            @click="showAllCategoriesModal = false"
                            class="text-sm font-medium text-muted-foreground hover:text-primary transition-colors"
                        >
                            {{ child.name }}
                        </Link>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
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

/* Nav Link Underline Slide Animation */
.nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -4px;
    left: 50%;
    background-color: hsl(var(--primary));
    transition: all 0.3s cubic-bezier(0.05, 0.7, 0.1, 1);
    transform: translateX(-50%);
    opacity: 0;
}
.nav-link:hover::after,
.nav-link.text-primary::after {
    width: 100%;
    opacity: 1;
}
</style>
