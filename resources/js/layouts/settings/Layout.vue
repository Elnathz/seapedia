<script setup lang="ts">
import SettingsNav from '@/components/settings/SettingsNav.vue';
</script>

<template>
    <div class="mx-auto w-full max-w-2xl space-y-8 px-4 sm:px-6">
        <div class="flex flex-col gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Pengaturan</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola profil dan preferensi akunmu.
                </p>
            </div>
            <SettingsNav />
        </div>

        <section class="space-y-8">
            <slot />
        </section>
    </div>
</template>
