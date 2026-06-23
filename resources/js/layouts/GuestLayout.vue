<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Navbar from '@/components/Navbar.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <Navbar />
        <main class="flex-1">
            <slot />
        </main>
        <Footer />
        <Toaster />
    </div>
</template>
